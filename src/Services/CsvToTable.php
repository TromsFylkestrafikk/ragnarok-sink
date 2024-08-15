<?php

namespace Ragnarok\Sink\Services;

use Closure;
use Exception;
use League\Csv\CharsetConverter;
use League\Csv\Reader;
use Ragnarok\Sink\Services\CsvToTable\CsvColumn;
use Ragnarok\Sink\Traits\LogPrintf;

/**
 * Map content of a Csv file to a database table
 */
class CsvToTable
{
    use LogPrintf;

    /**
     * @var string
     */
    protected $csvFile;

    /**
     * @var Reader
     */
    protected $csv;

    /**
     * @var string
     */
    protected $encoding = 'utf-8';

    /**
     * @var int
     */
    protected $maxRecErr = 3;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var array|null
     */
    protected $uniqueCols = null;

    /**
     * @var string[]
     */
    protected $nullVals;

    /**
     * @var CsvColumn[]
     */
    protected $columns = [];

    /**
     * @var int
     */
    protected $startAt = 1;

    /**
     * @var Closure|null
     */
    protected $filterHandler = null;

    /**
     * @var Closure|null
     */
    protected $preInsertHandler = null;

    /**
     * @var Closure|null
     */
    protected $csvPreparator = null;

    /**
     * @var DbBulkInsert
     */
    protected $feeder = null;

    /**
     * Counter for missing data columns in csv data.
     *
     * @var int[]
     */
    protected $missingCols = [];

    /**
     * Total number of processed records in csv.
     *
     * @var int
     */
    protected $processedRecs = 0;

    /**
     * Dummy run: Nothing is written to db.
     */
    protected $isDummy = false;

    /**
     * @param string $csvFile File name of CSV file to map
     * @param string $table Destination DB table to store records in.
     * @param array $uniqueCols DB columns that identifies unique records.
     */
    public function __construct(string $csvFile, string $table, array $uniqueCols = null)
    {
        $this->csvFile = $csvFile;
        $this->table = $table;
        $this->uniqueCols = $uniqueCols;
        $this->logPrintfInit("[CsvToTable:%s]: ", basename($csvFile));
    }

    /**
     * Add column mapping and handling.
     *
     * @param string $csvCol
     * @param string $dbCol
     *
     * @return CsvColumn
     */
    public function column($csvCol, $dbCol)
    {
        $mapper = new CsvColumn($csvCol, $dbCol);
        $this->columns[] = $mapper;
        return $mapper->nullValues($this->nullVals);
    }

    /**
     * Handler that determines whether records should be added or not.
     *
     * The handler receives a processed/prepared record and should return bool
     * for inclusion/exclusion.
     *
     * @param Closure $callback
     *
     * @return $this
     */
    public function filter(Closure $callback)
    {
        $this->filterHandler = $callback;
        return $this;
    }

    /**
     * @param string[]|string $value Values that evaluate to null globally.
     *
     * @return $this
     */
    public function nullValues($value)
    {
        $this->nullVals = (array) $value;
        return $this;
    }

    /**
     * @return Reader
     */
    public function getReader()
    {
        return $this->csv;
    }

    /**
     * Set callback handler preparing the CSV Reader object.
     *
     * @param Closure $handler
     *
     * @return $this
     */
    public function prepareCsvReader(Closure $handler)
    {
        $this->csvPreparator = $handler;
        return $this;
    }

    /**
     * Last-call processing of mapped record before DB insertion.
     *
     * The given closure receives the source (csv) and destination (db) records,
     * the latter by reference.
     *
     * @param Closure $handler Run this before each record insertion
     *
     * @return $this
     */
    public function preInsertRecord(Closure $handler)
    {
        $this->preInsertHandler = $handler;
        return $this;
    }

    /**
     * At what record to start retrieval.
     *
     * Note: Header is included, so 1 is the first body record.
     *
     * @return $this
     */
    public function offset(int $startAt = 1)
    {
        $this->startAt = $startAt;
        return $this;
    }

    /**
     * Execute the mapping process.
     *
     * @return $this
     */
    public function exec()
    {
        $this->debug("Running import");
        $this->feeder = new DbBulkInsert($this->table, $this->uniqueCols ? 'upsert' : 'insert');
        if ($this->isDummy) {
            $this->feeder->dummy();
        }
        if ($this->uniqueCols) {
            $this->feeder->unique($this->uniqueCols);
        }

        if (!$this->setupCsvReader()) {
            $this->logSummary();
            return $this;
        }

        $errCount = 0;
        foreach ($this->csv as $recordNr => $record) {
            try {
                $this->execRecord($record, $recordNr);
            } catch (Exception $except) {
                $errCount++;
                $this->error(
                    "Error while processing file '%s' at record #%d: %s. ",
                    $this->csvFile,
                    $recordNr,
                    $except->getMessage()
                );
                if ($errCount >= $this->maxRecErr) {
                    $this->error("Failed %d times. Halting further processing", $errCount);
                    $this->feeder->flush();
                    throw $except;
                }
            }
        }
        $this->feeder->flush();
        $this->logSummary();
        return $this;
    }

    /**
     * Get total number of processed records.
     *
     * @return int
     */
    public function getProcessedRecords(): int
    {
        return $this->processedRecs;
    }

    /**
     * Print summary statistics to log file.
     *
     * @return $this
     */
    public function logSummary()
    {
        $this->debug(
            "Added %d of %d seen records to database table %s",
            $this->feeder->getRecordsWritten(),
            $this->processedRecs,
            $this->table
        );
        if (count($this->missingCols)) {
            $this->warning(
                "Missing CSV data in the following columns: %s",
                implode('\n\t', array_map(
                    fn ($col, $count) => "$col: $count",
                    array_keys($this->missingCols),
                    $this->missingCols
                ))
            );
        }
        return $this;
    }

    /**
     * Dummy run. Nothing is written to db.
     */
    public function dummy(bool $isDummy = true): CsvToTable
    {
        $this->isDummy = $isDummy;
        return $this;
    }

    protected function execRecord($record, $recordNr)
    {
        if ($recordNr < $this->startAt) {
            return;
        }
        $this->processedRecs++;
        $dbRec = $this->processColumns($record);
        if (!$dbRec) {
            return;
        }
        if ($this->filterHandler && !call_user_func($this->filterHandler, $dbRec, $recordNr)) {
            return;
        }
        if ($this->preInsertHandler) {
            call_user_func_array($this->preInsertHandler, [$record, &$dbRec]);
        }
        $this->feeder->addRecord($dbRec);
    }

    protected function processColumns($record)
    {
        $processed = [];
        foreach ($this->columns as $column) {
            $val = $this->processColumn($record, $column);
            if (!$val && $column->isRequired()) {
                throw new Exception(sprintf("Missing required data for column '%s'", $column->getDbCol()));
            }
            $processed[$column->getDbCol()] = $val;
        }
        return $processed;
    }

    protected function processColumn($record, CsvColumn $column)
    {
        $csvCol = $column->getCsvCol();
        if (!isset($record[$csvCol])) {
            if (!isset($this->missingCols[$csvCol])) {
                $this->missingCols[$csvCol] = 0;
            }
            $this->missingCols[$csvCol]++;
            return $column->getDefault();
        }
        return $column->process($record[$csvCol]);
    }

    protected function setupCsvReader(): bool
    {
        $this->csv = Reader::createFromPath($this->csvFile);
        $this->csv->setHeaderOffset(0);

        try {
            $this->csv->getHeader();
        } catch (Exception $except) {
            $this->notice("Malformed or empty csv. Won't import");
            return false;
        }
        $this->encoding = [
            Reader::BOM_UTF8 => 'utf-8',
            Reader::BOM_UTF16_LE => 'utf-16',
            Reader::BOM_UTF16_BE => 'utf-16',
            Reader::BOM_UTF32_BE => 'utf-32',
            Reader::BOM_UTF32_LE => 'utf-32',
        ][$this->csv->getInputBOM()] ?? false;

        // getInputBom failes on UTF-8 with no BOM
        // extra check for utf-8 encoding if detected as iso-8859-1 (western)
        if (!$this->encoding) {
            // Force mb_detect_encoding to make a selection. Not doing so causes
            // it to wrongly select 'utf-8' in some cases when it really is
            // iso-8859.
            $this->encoding = strtolower(mb_detect_encoding(
                file_get_contents($this->csvFile, length: 8 * 1024),
                ['ASCII', 'UTF-8', 'ISO-8859-1']
            ));
        }

        if ($this->encoding !== 'utf-8') {
            CharsetConverter::addTo($this->csv, $this->encoding, 'utf-8');
        }

        if ($this->csvPreparator instanceof Closure) {
            call_user_func($this->csvPreparator, $this->csv);
        }
        return true;
    }
}

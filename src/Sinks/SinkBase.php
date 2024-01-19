<?php

namespace Ragnarok\Sink\Sinks;

use Illuminate\Support\Carbon;
use Ragnarok\Sink\Models\SinkFile;

/**
 * Foundation class for Ragnarok sinks.
 *
 * There should be no need to handle exceptions while implementing this API. The
 * Ragnarok framework will handle them globally.
 */
abstract class SinkBase
{
    /**
     * Machine readable ID of sink. Preferably in lower_snake_case
     *
     * This is the sink ID, so make sure it doesn't collide with any other
     * Ragnarok sinks. Also, changing this causes it to appear as a 'new' sink.
     *
     * @var string
     */
    public static $id = "example";

    /**
     * Title of sink for presentational purpose.
     *
     * @var string
     */
    public static $title = "Example";

    /**
     * Cron entry for when to perform new imports.
     *
     * Optional. This is a normal unix cron entry. e.g. '45 03 * * *' is run
     * every night at 03:45.
     *
     * @var string
     */
    public $cron = null;

    /**
     * List of tables for imported destination data.
     *
     * Return a keyed list of tables to store the final imported data. Table
     * name is the key and description of it is the value. The table migrations
     * created by sinks should have comments on all columns which aren't
     * obvious.
     *
     * @return string[]
     */
    public function destinationTables(): array
    {
        return [];
    }

    /**
     * Start date of data to import.
     *
     * @return Carbon
     */
    abstract public function getFromDate(): Carbon;

    /**
     * Most recent data to be imported.
     *
     * Usually this is today or yesterday.
     *
     * @return Carbon
     */
    abstract public function getToDate(): Carbon;

    /**
     * Get full a list of chunk identifiers from sink. Usually dates.
     *
     * IDs must be strings, order-able and in descending order.
     * Each chunk is an isolated set of data from sink.
     *
     * @return array
     */
    public function getChunkIds(): array
    {
        $fromDate = $this->getFromDate();
        $current = $this->getToDate();

        $ret = [];
        while ($current->isAfter($fromDate) || $current->isSameDay($fromDate)) {
            $ret[] = $current->format('Y-m-d');
            $current->subtract(1, 'day');
        }
        return $ret;
    }

    /**
     * Get total number of chunks from sink.
     *
     * @return int
     */
    public function chunksCount(): int
    {
        return $this->getFromDate()->daysUntil($this->getToDate())->count();
    }

    /**
     * Get updated import status for given chunk IDs.
     *
     * @param array $ids List of chunk IDs
     * @return array Keyed by chunk ID.
     */
    public function status(array $ids): array
    {
        return [];
    }

    /**
     * Fetch raw, unprocessed data from sink to local storage.
     *
     * IMPORTANT: Allow exceptions to pass through this operation!
     *
     * @param string $id Chunk ID to fetch data for.
     *
     * @return SinkFile|null
     */
    public function fetch(string $id): SinkFile|null
    {
        return null;
    }

    /**
     * Get Date and time this chunk belongs to.
     *
     * @param string $id Chunk ID to determine date for.
     *
     * @return Carbon
     */
    public function getChunkDate(string $id): Carbon
    {
        return new Carbon($id);
    }

    /**
     * Import chunk from sink.
     *
     * IMPORTANT: Allow exceptions to pass through this operation!
     *
     * @param string $id Chunk ID.
     * @param SinkFile $file The file retrieved by $this->fetch()
     *
     * @return int Total number of records/elements imported
     */
    abstract public function import(string $id, SinkFile $file): int;

    /**
     * Remove imported data from DB
     *
     * IMPORTANT: Allow exceptions to pass through this operation!
     *
     * @param string $id Chunk ID
     * @param SinkFile $file The file retrieved by $this->fetch()
     *
     * @return bool True on success
     */
    abstract public function deleteImport(string $id, SinkFile $file): bool;

    /**
     * Given a file name, get the chunk ID.
     *
     * This is a bit like self::fetch() in reverse. Whenever files are
     * previously downloaded or otherwise already exist in local storage, this
     * is used to re-add files as fetched chunks.
     *
     * @return string|null The chunk identifier previously given in
     * self::getChunkIds
     */
    public function filenameToChunkId(string $filename): string|null
    {
        return null;
        // Example code for files having date as part of file name in YYYY-MM-DD
        // format:
        // @begincode
        //     $matches = [];
        //     $hits = preg_match('|(?P<date>\d{4}-\d{2}-\d{2})\.zip$|', $filename, $matches);
        //     return $hits ? $matches['date'] : null;
        // @endcode
    }
}

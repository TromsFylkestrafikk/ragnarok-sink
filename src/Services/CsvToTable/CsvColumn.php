<?php

namespace TromsFylkestrafikk\RagnarokSink\Services\CsvToTable;

class CsvColumn
{
    /**
     * @var string
     */
    protected $csvCol;

    /**
     * @var string
     */
    protected $dbCol;

    /**
     * @var mixed
     */
    protected $defaultVal = null;

    /**
     * @var callable|null
     */
    protected $formatter = null;

    /**
     * List of values to interpreted as null.
     *
     * @var string[]
     */
    protected $nullVals = [];

    /**
     * @var bool
     */
    protected $req = false;

    public function __construct($csvCol, $dbCol)
    {
        $this->csvCol = $csvCol;
        $this->dbCol = $dbCol;
    }

    /**
     * @param Callable $formatter Reformat entries with this function.
     *
     * @return $this
     */
    public function format(callable $formatter)
    {
        $this->formatter = $formatter;
        return $this;
    }

    /**
     * Set default for empty values.
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function default($value = null)
    {
        $this->defaultVal = $value;
        return $this;
    }

    /**
     * Get default value for column.
     *
     * @return mixed
     */
    public function getDefault()
    {
        return $this->defaultVal;
    }

    /**
     * String values to be considered/interpreted as NULL values.
     *
     * @param string|array $values
     *
     * @return $this
     */
    public function nullValues($values)
    {
        $this->nullVals = (array) $values;
        return $this;
    }

    /**
     * Set required flag for this column.
     *
     * @param bool $req
     *
     * @return $this
     */
    public function required($req = true)
    {
        $this->req = $req;
        return $this;
    }

    /**
     * Getter for required flag
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->req;
    }

    /**
     * Name of CSV equivalent of this column.
     *
     * @return string
     */
    public function getCsvCol()
    {
        return $this->csvCol;
    }

    /**
     * Get database name of this column.
     *
     * @return string
     */
    public function getDbCol()
    {
        return $this->dbCol;
    }

    /**
     * Prepare, process and return processed column value.
     *
     * @return mixed
     */
    public function process($value)
    {
        $cand = trim($value);
        if (!strlen($value)) {
            return $this->defaultVal;
        }
        if (in_array($cand, $this->nullVals)) {
            return null;
        }
        if ($this->formatter) {
            return call_user_func($this->formatter, $cand);
        }
        return $cand;
    }
}

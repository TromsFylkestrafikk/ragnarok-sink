<?php

namespace TromsFylkestrafikk\RagnarokSink\Sinks;

use Illuminate\Support\Carbon;

/**
 * Foundation class for Ragnarok sinks.
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
    public $id = "example";

    /**
     * Human readable name of sink.
     *
     * @var string
     */
    public $title = "Example";

    /**
     * Start date of data to import.
     *
     * @return Carbon
     */
    abstract public function getFromDate(): Carbon;

    /**
     * How recently data be fetched.
     *
     * Usually this is today or yesterday.
     *
     * @return Carbon
     */
    abstract public function getToDate(): Carbon;

    /**
     * Get a list of chunk identifiers from sink. Usually dates.
     *
     * Each chunk is an isolated set of data from sink. Chunk IDs
     * should be in descending chunk order.
     *
     * @param mixed $mostRecent The most recent chunk to start from
     * @param int $amount Number of chunks to retrieve backwards.
     * @return array
     */
    public function getChunkIds($mostRecent = null, $amount = 20): array
    {
        $current = $mostRecent ? new Carbon($mostRecent) : $this->getToDate();
        $fromDate = $this->getFromDate();

        $ret = [];
        for ($count = 0; $count < $amount; $count++) {
            $ret[] = $current->format('Y-m-d');
            $current->subtract(1, 'day');
            if ($current->isBefore($fromDate)) {
                break;
            }
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
     * @return bool True on success.
     */
    public function fetch(): bool
    {
        return true;
    }

    /**
     * Import one chunk from sink.
     *
     * @return bool
     */
    abstract public function import(): bool;
}

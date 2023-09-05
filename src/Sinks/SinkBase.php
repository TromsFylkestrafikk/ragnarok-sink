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
     * @param string $id Chunk ID to fetch data for.
     *
     * @return bool True on success.
     */
    public function fetch($id): bool
    {
        return true;
    }

    /**
     * Remove chunk from local storage.
     *
     * @param string $id Chunk ID
     *
     * @return bool True on success
     */
    public function removeChunk($id): bool
    {
        return true;
    }

    /**
     * Import one chunk from sink.
     *
     * @param string $id Chunk ID.
     *
     * @return bool
     */
    abstract public function import($id): bool;

    /**
     * Remove imported data from DB
     *
     * @param string $id Chunk ID
     *
     * @return bool True on success
     */
    abstract public function deleteImport($id): bool;
}

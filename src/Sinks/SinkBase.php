<?php

namespace Ragnarok\Sink\Sinks;

use Illuminate\Support\Carbon;

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
     * @return int Total number of bytes.
     */
    public function fetch(string $id): int
    {
        return 0;
    }

    /**
     * Get chunk version or checksum.
     *
     * This is used to detect updates in raw data from sink. Make sure the
     * version string always is equal for the *same* original data, independent
     * of timestamp and source of origin.
     *
     * For sinks downloading a single file per chunk, the file's md5 checksum is
     * a perfect candidate as version.
     *
     * @param string $id
     */
    public function getChunkVersion($id): string
    {
        return $id;
    }

    /**
     * Remove chunk from local storage.
     *
     * IMPORTANT: Allow exceptions to pass through this operation!
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
     * IMPORTANT: Allow exceptions to pass through this operation!
     *
     * @param string $id Chunk ID.
     *
     * @return int Total number of records/elements imported
     */
    abstract public function import($id): int;

    /**
     * Remove imported data from DB
     *
     * IMPORTANT: Allow exceptions to pass through this operation!
     *
     * @param string $id Chunk ID
     *
     * @return bool True on success
     */
    abstract public function deleteImport($id): bool;
}

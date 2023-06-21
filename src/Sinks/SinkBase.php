<?php

namespace TromsFylkestrafikk\RagnarokSink\Sinks;

abstract class SinkBase
{
    /**
     * Human readable name of sink. Keep it short.
     *
     * @var string
     */
    public $name;

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
    public function import(): bool
    {
        return true;
    }
}

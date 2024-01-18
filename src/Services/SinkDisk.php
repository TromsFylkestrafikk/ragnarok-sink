<?php

namespace Ragnarok\Sink\Services;

use Illuminate\Contracts\Filesystem\Filesystem;

/**
 * Abstraction around local Storage::disk for sinks.
 */
class SinkDisk
{
    /**
     * @var Filesystem
     */
    protected $disk = null;

    public function __construct(public string $sinkId)
    {
        //
    }

    /**
     * @return Filesystem
     */
    public function getDisk(): Filesystem
    {
        if ($this->disk === null) {
            $this->disk = app('filesystem')->build(config('ragnarok_sink.local_disk'));
        }
        return $this->disk;
    }

    /**
     * List all files on sink's local disk.
     *
     * @return array
     */
    public function files(): array
    {
        return $this->getDisk()->files($this->sinkId);
    }
}

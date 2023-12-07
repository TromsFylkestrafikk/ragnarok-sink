<?php

namespace Ragnarok\Sink\Services;


use Ragnarok\Sink\Models\SinkFile;
use ZipArchive;

/**
 * Temporary extractor of chunk archive
 */
class ChunkExtractor
{
    protected $destDir = null;

    /**
     * @var LocalFile
     */
    protected $localFile;

    public function __construct(protected string $sinkId, protected SinkFile $file)
    {
        $this->localFile = new LocalFile($sinkId, $file);
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        $this->close();
    }

    public function extract(): ChunkExtractor
    {
        if ($this->destDir !== null) {
            return $this;
        }
        $this->destDir = uniqid($this->sinkId . '-');
        $disk = $this->localFile->getDisk();
        $disk->makeDirectory($this->destDir);
        $fullPath = $disk->path($this->destDir);
        $archive = new ZipArchive();
        $archive->open($disk->path($this->file->name));
        $archive->extractTo($fullPath);
        $archive->close();
        return $this;
    }

    /**
     * Get full path to destination directory where files are extracted.
     */
    public function getDestDir(): string|null
    {
        if ($this->destDir === null) {
            return null;
        }
        return $this->localFile->getDisk()->path($this->destDir);
    }

    public function close(): ChunkExtractor
    {
        if ($this->destDir === null) {
            return $this;
        }
        $this->localFile->getDisk()->deleteDirectory($this->destDir);
        return $this;
    }
}

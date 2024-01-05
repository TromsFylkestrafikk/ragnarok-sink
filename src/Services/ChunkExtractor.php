<?php

namespace Ragnarok\Sink\Services;

use Ragnarok\Sink\Models\SinkFile;
use Ragnarok\Sink\Traits\LogPrintf;
use ZipArchive;

/**
 * Temporary extractor of chunk archive
 */
class ChunkExtractor
{
    use LogPrintf;

    protected $destDir = null;

    /**
     * @var LocalFile
     */
    protected $localFile;

    public function __construct(protected string $sinkId, protected SinkFile $file)
    {
        $this->localFile = new LocalFile($sinkId, $file);
        $this->logPrintfInit('[ChunkExtractor %s]: ', $sinkId);
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
        $archive = new ZipArchive();
        $this->debug('Opening archive %s', $disk->path($this->file->name));
        $archive->open($disk->path($this->file->name));
        $this->debug('Extracting to %s', $this->getDestDir());
        $archive->extractTo($this->getDestDir());
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

    /**
     * Get all files within zip file.
     *
     * @return mixed[]
     */
    public function getFiles(): array
    {
        if ($this->destDir === null) {
            $this->extract();
        }
        $disk = $this->localFile->getDisk();
        return array_map(fn ($item) => $disk->path($item), $disk->files($this->destDir));
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

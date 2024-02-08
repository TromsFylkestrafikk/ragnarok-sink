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

    /**
     * @var string
     */
    protected $destDir = null;

    /**
     * @var bool
     */
    protected $extracted = false;

    /**
     * @var LocalFile
     */
    protected $localFile;

    public function __construct(protected string $sinkId, protected SinkFile $file)
    {
        $this->extracted = false;
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
        if ($this->extracted) {
            return $this;
        }
        $disk = $this->localFile->getDisk();
        $archive = new ZipArchive();
        $archivePath = $disk->path($this->file->name);
        $this->debug('Opening archive %s', $archivePath);
        $archive->open($archivePath);
        $this->debug('Extracting to %s', $this->getDestDir());
        $archive->extractTo($this->getDestDir());
        $archive->close();
        $this->extracted = true;
        return $this;
    }

    /**
     * Set destination directory for archive, relative to disk.
     */
    public function setDestDir(string $dir): ChunkExtractor
    {
        $this->destDir = $dir;
        return $this;
    }

    /**
     * Get full path to destination directory where files are extracted.
     */
    public function getDestDir(): string|null
    {
        if ($this->destDir === null) {
            $this->destDir = uniqid($this->sinkId . '-');
        }
        $disk = $this->localFile->getDisk();
        $fullPath = $this->localFile->getDisk()->path($this->destDir);
        if ($disk->exists($this->destDir)) {
            $this->warning('Directory exist. Import may be unstable', $fullPath);
        } else {
            $disk->makeDirectory($this->destDir);
        }
        return $fullPath;
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

<?php

namespace Ragnarok\Sink\Services;

use Ragnarok\Sink\Models\RawFile;
use Ragnarok\Sink\Services\LocalFile;
use ZipArchive;

/**
 * Create a zip archive for a single chunk.
 */
class ChunkArchive
{
    /**
     * Files to be added in archive
     *
     * @var string[]
     */
    protected $files = [];

    /**
     * @var LocalFile
     */
    protected $local;

    public function __construct(protected string $sinkId, protected string $chunkId)
    {
        //
    }

    /**
     * Add file to archive.
     *
     * @param string $filePath
     */
    public function addFile($filePath, $entryName = null): ChunkArchive
    {
        $this->files[] = [
            'path' => $filePath,
            'entryName' => $entryName ?: $this->sinkId . '/' . basename($filePath),
        ];
        return $this;
    }

    /**
     * Get base file name used for archive.
     */
    public function getFilename(): string
    {
        return $this->chunkId . '.zip';
    }

    /**
     * Write zip and store it in local file repository.
     */
    public function store(): RawFile
    {
        $this->local = LocalFile::createFromFilename($this->sinkId, $this->getFilename());
        $archive = new ZipArchive();
        $archive->open($this->local->getPath(), ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach ($this->files as $file) {
            $archive->addFile($file, $entryname);
        }
        $archive->close();
        $this->local->save();
    }
}

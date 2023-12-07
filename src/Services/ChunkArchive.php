<?php

namespace Ragnarok\Sink\Services;

use Ragnarok\Sink\Models\SinkFile;
use Ragnarok\Sink\Services\LocalFile;
use ZipArchive;

/**
 * Create a zip archive for a single chunk.
 */
class ChunkArchive
{
    /**
     * @var ZipArchive
     */
    protected $archive = null;

    /**
     * @var LocalFile
     */
    protected $local = null;

    public function __construct(protected string $sinkId, protected string $chunkId)
    {
        //
    }

    /**
     * Add file to archive.
     */
    public function addFile(string $filePath, string $entryname = null): ChunkArchive
    {
        $this->getArchive()->addFile($filePath, $entryname);
        return $this;
    }

    /**
     * Add file with provided content to archive.
     */
    public function addFromString(string $filename, string $content): ChunkArchive
    {
        $this->getArchive()->addFromString($filename, $content);
        return $this;
    }

    /**
     * Close/save zip archive and update/save SinkFile model.
     */
    public function save(): ChunkArchive
    {
        $this->getArchive()->close();
        $this->getLocal()->save();
        return $this;
    }

    public function getFile(): SinkFile
    {
        return $this->getLocal()->getFile();
    }

    /**
     * Get base file name used for archive.
     */
    public function getFilename(): string
    {
        return $this->chunkId . '.zip';
    }

    protected function getArchive(): ZipArchive
    {
        if ($this->archive === null) {
            $this->archive = new ZipArchive();
            $this->archive->open($this->getLocal()->getPath(), ZipArchive::CREATE | ZipArchive::OVERWRITE);
        }
        return $this->archive;
    }

    protected function getLocal(): LocalFile
    {
        if ($this->local === null) {
            $this->local = LocalFile::createFromFilename($this->sinkId, $this->getFilename());
        }
        return $this->local;
    }
}

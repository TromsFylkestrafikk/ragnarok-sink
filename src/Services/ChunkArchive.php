<?php

namespace Ragnarok\Sink\Services;

use Ragnarok\Sink\Models\SinkFile;
use Ragnarok\Sink\Services\LocalFile;
use Ragnarok\Sink\Traits\LogPrintf;
use ZipArchive;

/**
 * Create a zip archive for a single chunk.
 */
class ChunkArchive
{
    use LogPrintf;

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
        $this->logPrintfInit('[ChunkArchive %s]: ', $sinkId);
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
        $this->debug('Archive complete: %s', $this->getLocal()->getFile()->name);
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
            $this->debug('Creating archive: %s', $this->getLocal()->getPath());
            $this->archive = new ZipArchive();
            $this->archive->open(
                $this->getLocal()->assertDir()->getPath(),
                ZipArchive::CREATE | ZipArchive::OVERWRITE
            );
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

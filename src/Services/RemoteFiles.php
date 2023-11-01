<?php

namespace Ragnarok\Sink\Services;

use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use Ragnarok\Sink\Traits\LogPrintf;
use Ragnarok\Sink\Models\RawFile;

/**
 * Service for syncing, copying, maintaining remote files with local files.
 */
class RemoteFiles
{
    use LogPrintf;

    /**
     * Remote path where files are to be found.
     *
     * @var string
     */
    protected $rPath = '/';

    /**
     * @param string $sinkId Sink identifier.
     * @param LocalFiles $local Local file management service
     * @param Filesystem $rDisk Remote disk instance
     *
     * @return void
     */
    public function __construct(string $sinkId, protected LocalFiles $local, protected Filesystem $rDisk)
    {
        $this->logPrintfInit('[RemoteFile %s]: ', $sinkId);
    }

    /**
     * Get file model for given remote file.
     *
     * This will retrieve remote file if not existant or it differ from local,
     * otherwise a local copy will be returned.
     *
     * @param string $filename
     *
     * @return RawFile|null
     */
    public function getFile($filename)
    {
        $file = $this->local->getFile($filename);
        if (!$file) {
            $file = $this->copyFile($filename);
        }
        return $file;
    }

    /**
     * Get service for managing local files.
     *
     * @return LocalFiles
     */
    public function getLocal()
    {
        return $this->local;
    }

    public function getDisk(): Filesystem
    {
        return $this->rDisk;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setRemotePath($path)
    {
        $this->rPath = $path;
        return $this;
    }

    /**
     * Get remote file path of given file.
     *
     * @param string $filename
     * @return string
     */
    public function getRemoteFilePath($filename)
    {
        return rtrim($this->rPath, '/') . '/' . $filename;
    }

    /**
     * Copy file from remote and create new file model.
     *
     * @param string $filename
     *
     * @return RawFile|null
     */
    protected function copyFile($filename)
    {
        $content = $this->getRemoteFileContent($filename);
        return $this->local->toFile($filename, $content);
    }

    /**
     * @param string $filename
     *
     * @return string|null
     */
    protected function getRemoteFileContent($filename)
    {
        $rFilePath = $this->getRemoteFilePath($filename);
        if (!$this->rDisk->exists($rFilePath)) {
            throw new Exception(sprintf('Remote filepath does not exist: %s', $rFilePath));
        }
        return $this->rDisk->get($rFilePath);
    }
}

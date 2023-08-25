<?php

namespace TromsFylkestrafikk\RagnarokSink\Services;

use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use TromsFylkestrafikk\RagnarokSink\Traits\LogPrintf;
use TromsFylkestrafikk\RagnarokSink\Models\RawFile;

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
     * @var LocalFiles
     */
    protected $local = null;

    /**
     * @param string $sinkId Name of sink
     * @param Filesystem $rDisk Remote disk instance
     *
     * @return void
     */
    public function __construct(protected string $sinkId, protected Filesystem $rDisk)
    {
        $this->logPrintfInit('[RemoteFile]: ');
        $this->local = new LocalFiles($sinkId);
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

<?php

namespace Ragnarok\Sink\Services;

use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use Ragnarok\Sink\Traits\LogPrintf;
use Ragnarok\Sink\Models\SinkFile;

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
     * @param Filesystem $rDisk Remote disk instance
     *
     * @return void
     */
    public function __construct(protected string $sinkId, protected Filesystem $rDisk)
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
     * @return SinkFile|null
     */
    public function getFile($filename)
    {
        $local = LocalFile::find($this->sinkId, $filename);
        if (!$local) {
            $local = $this->copyFile($filename);
        }
        return $local->getFile();
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
     * Copy file from remote and update/create new file model.
     *
     * @param string $filename
     *
     * @return LocalFile|null
     */
    protected function copyFile($filename)
    {
        $local = LocalFile::createFromFilename($this->sinkId, $filename);
        $local->put($this->getRemoteFileContent($filename));
        return $local;
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

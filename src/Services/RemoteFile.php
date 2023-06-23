<?php

namespace TromsFylkestrafikk\RagnarokSink\Services;

use Exception;
use Illuminate\Filesystem\Filesystem;
use TromsFylkestrafikk\RagnarokSink\Traits\LogPrintf;
use TromsFylkestrafikk\RagnarokSink\Models\RawFile;

/**
 * Service for syncing, copying, maintaining remote files with local files.
 */
class RemoteFile
{
    use LogPrintf;

    /**
     * Remote path where files are to be found.
     *
     * @var string
     */
    protected $rPath = '/';

    /**
     * Local path where files are to be found.
     *
     * @var string
     */
    protected $lPath = '/';

    /**
     * @param Filesystem $rDisk
     * @param Filesystem $lDisk
     *
     * @return void
     */
    public function __construct(
        protected string $sinkName,
        protected Filesystem $rDisk,
        protected Filesystem $lDisk
    ) {
        $this->logPrintfInit('[RemoteFile]: ');
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
     * @param string $path
     * @return $this
     */
    public function setLocalPath($path = '/')
    {
        $this->lPath = $path;
        return $this;
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
        $lFilePath = $this->lFilePath($filename);
        /** @var RawFile $file */
        $file = RawFile::where('name', $lFilePath)->first();
        if (!$file) {
            $file = $this->createFile($filename);
        } elseif (!$this->lDisk->exists($lFilePath)) {
            $file = $this->refreshFile($file);
        }
        return $file;
    }

    /**
     * Get remote file path of given file.
     *
     * @param string $filename
     * @return string
     */
    public function rFilePath($filename)
    {
        return rtrim($this->rPath, '/') . '/' . $filename;
    }

    /**
     * Get local file path of given file.
     *
     * @param string $filename
     * @return string
     */
    public function lFilePath($filename)
    {
        return implode('/', [$this->sinkName, rtrim($this->lPath, '/'), $filename]);
    }

    /**
     * Check existence and checksum for given local file.
     *
     * @return bool
     */
    public function localChecksOut(RawFile $file)
    {
        return $this->lDisk->exists($file->name) && md5($this->lDisk->get($file->name)) === $file->checksum;
    }

    /**
     * Compare local file with remote, download and update status.
     *
     * @param RawFile $file
     *
     * @return RawFile
     */
    public function refreshFile(RawFile $file)
    {
        $newContent = $this->getRemoteFileContent($file->name);
        $existsLocal = $this->lDisk->exists($file->name);
        if (!$newContent) {
            // Server might be down. Not touching state of file unless the local
            // file is missing.
            if (!$existsLocal) {
                throw new Exception("Missing both local and remote file.");
            }
            return $file;
        }
        $newChecksum = md5($newContent);
        if ($newChecksum !== $file->checksum || !$existsLocal) {
            $this->lDisk->put($file->name, $newContent);
            if ($newChecksum !== $file->checksum) {
                $file->checksum = $newChecksum;
                $file->import_status = 'updated';
                $file->save();
            }
        }
        return $file;
    }

    /**
     * Create a new file model for given file.
     *
     * @param string $filename
     *
     * @return RawFile|null
     */
    protected function createFile($filename)
    {
        $copied = $this->copyFile($filename);
        if (!$copied) {
            return null;
        }

        $lFilePath = $this->lFilePath($filename);
        /** @var RawFile $file */
        $file = RawFile::create([
            'file_name' => $lFilePath,
            'checksum' => md5($this->lDisk->get($lFilePath)),
            'import_status' => 'new',
            'import_msg' => null,
        ]);
        return $file;
    }

    /**
     * Copies file from remote to local
     *
     * @param string $filename
     *
     * @return bool True if success
     */
    protected function copyFile($filename)
    {
        $content = $this->getRemoteFileContent($filename);
        if (!$content) {
            return false;
        }
        return $this->lDisk->put($filename, $content);
    }

    protected function getRemoteFileContent($filename)
    {
        $rFilePath = $this->rFilePath($filename);
        if (!$this->rDisk->exists($rFilePath)) {
            return null;
        }
        return $this->rDisk->get($rFilePath);
    }
}

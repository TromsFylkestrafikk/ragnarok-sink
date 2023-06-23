<?php

namespace TromsFylkestrafikk\RagnarokSink\Services;

use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use TromsFylkestrafikk\RagnarokSink\Traits\LogPrintf;
use TromsFylkestrafikk\RagnarokSink\Models\RawFile;

/**
 * Service for syncing, copying, maintaining remote files with local files.
 */
class RemoteFile
{
    use LogPrintf;

    /**
     * @var Filesystem
     */
    protected $lDisk;

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
     * @param string $sinkName Name of sink
     * @param Filesystem $rDisk Remote disk instance
     *
     * @return void
     */
    public function __construct(protected string $sinkName, protected Filesystem $rDisk)
    {
        $this->logPrintfInit('[RemoteFile]: ');
        $this->lDisk = app('filesystem')->build(config('ragnarok_sink.local_disk'));
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
            $file = $this->copyFile($filename);
        } elseif (!$this->lDisk->exists($lFilePath)) {
            $file = $this->refreshFile($file);
        }
        return $file;
    }

    /**
     * Remove local file from DB and disk.
     *
     * @param string $filename
     */
    public function rmLocalFile($filename)
    {
        $lFilePath = $this->lFilePath($filename);
        $file = RawFile::where('name', $lFilePath)->first();
        if (!$file) {
            return;
        }
        if ($this->lDisk->exists($file->name)) {
            $this->lDisk->delete($file->name);
        }
        $file->delete();
        return $this;
    }

    /**
     * @param string $filename
     * @return RawFile|null
     */
    public function resetImportStatus($filename)
    {
        $file = RawFile::where('name', $this->lFilePath($filename))->first();
        if (!$file) {
            return null;
        }
        $file->import_status = 'new';
        $file->save();
        return $file;
    }

    /**
     * @return $this
     */
    public function setRemoteDisk(Filesystem $disk)
    {
        $this->rDisk = $disk;
        return $this;
    }

    /**
     * @return $this
     */
    public function setLocalDisk(Filesystem $disk)
    {
        $this->lDisk = $disk;
        return $this;
    }

    public function getLocalDisk(): Filesystem
    {
        return $this->lDisk;
    }

    public function getRemoteDisk(): Filesystem
    {
        return $this->rDisk;
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
     * Copy file from remote and create new file model.
     *
     * @param string $filename
     *
     * @return RawFile|null
     */
    protected function copyFile($filename)
    {
        $copied = $this->copyFileContent($filename);
        if (!$copied) {
            return null;
        }

        $lFilePath = $this->lFilePath($filename);
        /** @var RawFile $file */
        $file = RawFile::create([
            'sink' => $this->sinkName,
            'name' => $lFilePath,
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
    protected function copyFileContent($filename)
    {
        $content = $this->getRemoteFileContent($filename);
        if (!$content) {
            return false;
        }
        return $this->lDisk->put($filename, $content);
    }

    /**
     * @param string $filename
     *
     * @return string|null
     */
    protected function getRemoteFileContent($filename)
    {
        $rFilePath = $this->rFilePath($filename);
        if (!$this->rDisk->exists($rFilePath)) {
            return null;
        }
        return $this->rDisk->get($rFilePath);
    }
}

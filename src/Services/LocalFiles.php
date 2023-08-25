<?php

namespace TromsFylkestrafikk\RagnarokSink\Services;

use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use TromsFylkestrafikk\RagnarokSink\Models\RawFile;

/**
 * Helper class to manage data in local files.
 */
class LocalFiles
{
    /**
     * @var Filesystem
     */
    protected $disk;

    /**
     * Local path where files are to be found.
     *
     * @var string
     */
    protected $path = '';

    public function __construct(protected string $sinkId)
    {
        $this->disk = app('filesystem')->build(config('ragnarok_sink.local_disk'));
    }

    /**
     * Write content to new or existing file.
     *
     * @param string $filename
     * @param string $content
     *
     * @return Rawfile
     */
    public function toFile($filename, $content)
    {
        $checksum = md5($content);
        $size = strlen($content);
        $filePath = $this->getFilePath($filename);
        $this->disk->put($filePath, $content);

        $existing = $this->getFile($filename);
        if ($existing && $existing->checksum !== $checksum) {
            $existing->fill([
                'size' => $size,
                'checksum' => $checksum,
            ]);
            $existing->save();
        }
        return $existing ?: RawFile::create([
            'sink_id' => $this->sinkId,
            'name' => $filePath,
            'size' => $size,
            'checksum' => $checksum,
        ]);
    }

    /**
     * Get file by name, if it exists.
     *
     * @param $filename
     *
     * @return RawFile|null
     */
    public function getFile($filename)
    {
        return RawFile::firstWhere(['sink_id' => $this->sinkId, 'name' => $this->getFilePath($filename)]);
    }

    /**
     * Remove file from DB and disk.
     *
     * @param string $filename
     *
     * @return $this
     */
    public function rmFile($filename)
    {
        $file = $this->getFile($filename);
        if (!$file) {
            return $this;
        }
        if ($this->disk->exists($file->name)) {
            $this->disk->delete($file->name);
        }
        $file->delete();
        return $this;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path = '')
    {
        $this->path = trim($path, '/');
        return $this;
    }

    /**
     * Get local file path of given file.
     *
     * @param string $filename
     * @return string
     */
    public function getFilePath($filename)
    {
        $lDir = $this->getLocalDir();
        // Make sure local directory exists
        if (!$this->disk->exists($lDir)) {
            $this->disk->makeDirectory($lDir);
        }
        return implode('/', [$lDir, ltrim($filename)]);
    }

    /**
     * Get local directory path
     *
     * @return string
     */
    public function getLocalDir()
    {
        $walk = ['/' . $this->sinkId];
        if (strlen($this->path)) {
            $walk[] = $this->path;
        }
        return implode('/', $walk);
    }
}

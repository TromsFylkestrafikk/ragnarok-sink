<?php

namespace Ragnarok\Sink\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Collection;
use Ragnarok\Sink\Models\RawFile;

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
     * @return RawFile
     */
    public function toFile($filename, $content): RawFile
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
    public function getFile($filename): RawFile|null
    {
        return RawFile::firstWhere(['sink_id' => $this->sinkId, 'name' => $this->getFilePath($filename)]);
    }

    /**
     * @param string $pattern
     */
    public function getFilesLike(string $pattern): Collection
    {
        return RawFile::where('sink_id', $this->sinkId)
            ->where('name', 'like', $this->getLocalDir() . '/' . $pattern)
            ->get();
    }

    /**
     * @param string|RawFile $file
     *
     * @return string|null
     */
    public function getContents($file): string|null
    {
        if (is_string($file)) {
            $file = $this->getFile($file);
        }
        return $this->disk->get($file->name);
    }

    public function getDisk(): Filesystem
    {
        return $this->disk;
    }

    /**
     * remove file from DB and disk.
     *
     * @param string $filename
     *
     * @return $this
     */
    public function rmFile($filename): LocalFiles
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
    public function setPath($path = ''): LocalFiles
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
    public function getFilePath($filename): string
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
    public function getLocalDir(): string
    {
        $walk = ['/' . $this->sinkId];
        if (strlen($this->path)) {
            $walk[] = $this->path;
        }
        return implode('/', $walk);
    }
}

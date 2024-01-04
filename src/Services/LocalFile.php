<?php

namespace Ragnarok\Sink\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Ragnarok\Sink\Models\SinkFile;

/**
 * Wrapper/helper around SinkFile entry
 */
class LocalFile
{
    /**
     * @var Filesystem
     */
    protected $disk = null;

    public function __construct(protected string $sinkId, protected SinkFile $file)
    {
        //
    }

    /**
     * Find existing LocalFile instance from filename
     */
    public static function find(string $sinkId, string $filename): LocalFile|null
    {
        $relPath = $sinkId . '/' . $filename;
        $file = SinkFile::firstWhere(['sink_id' => $sinkId, 'name' => $relPath]);
        if ($file) {
            return new static($sinkId, $file);
        }
        return null;
    }

    public static function createFromFilename(string $sinkId, string $filename): LocalFile
    {
        $local = self::find($sinkId, $filename);
        if ($local) {
            return $local;
        }
        return new static($sinkId, new SinkFile([
                'sink_id' => $sinkId,
                'name' => $sinkId . '/' . $filename,
                'size' => 0,
                'checksum' => '0',
        ]));
    }

    public function getFile(): SinkFile
    {
        return $this->file;
    }

    /**
     * Put/replace content in file and update model.
     *
     * @return LocalFile
     */
    public function put(string $content): LocalFile
    {
        $this->getDisk()->put($this->file->name, $content);
        $this->save();
        return $this;
    }

    /**
     * Get file contents.
     */
    public function get(): string
    {
        return $this->getDisk()->get($this->getFile()->name);
    }

    /**
     * Save model with updated values found in physical file.
     */
    public function save(): LocalFile
    {
        $filePath = $this->getPath();
        $this->file->checksum = md5_file($filePath);
        $this->file->size = filesize($filePath);
        $this->file->save();
        return $this;
    }

    /**
     * Get full file system path for file.
     */
    public function getPath(): string
    {
        return $this->getDisk()->path($this->file->name);
    }

    /**
     * @return Filesystem
     */
    public function getDisk(): Filesystem
    {
        if ($this->disk === null) {
            $this->disk = app('filesystem')->build(config('ragnarok_sink.local_disk'));
        }
        return $this->disk;
    }
}

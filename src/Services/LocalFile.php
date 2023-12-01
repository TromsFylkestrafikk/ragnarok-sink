<?php

namespace Ragnarok\Sink\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Ragnarok\Sink\Models\RawFile;


/**
 * Wrapper/helper around RawFile entry
 */
class LocalFile
{
    /**
     * @var Filesystem
     */
    protected $disk = null;

    public function __construct(protected string $sinkId, protected RawFile $file)
    {
        //
    }

    public static function createFromFilename(string $sinkId, string $filename): LocalFile
    {
        $relPath = $sinkId . '/' . $filename;
        $file = RawFile::firstWhere(['sink_id' => $this->sinkId, 'name' => $relPath]);
        if (!$file) {
            $file = new RawFile([
                'sink_id' => $sinkId,
                'name' => $relPath,
                'size' => 0,
                'checksum' => '0',
            ]);
        }
        return new static($sinkId, $file);
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

    public function getFile(): RawFile
    {
        return $this->file;
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

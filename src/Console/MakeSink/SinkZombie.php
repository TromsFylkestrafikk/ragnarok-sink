<?php

namespace Ragnarok\Sink\Console\MakeSink;

use Illuminate\Support\Str;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\Filesystem;

/**
 * It creates skeletons!
 */
class SinkZombie
{
    use \Ragnarok\Sink\Traits\LogPrintf;

    /**
     * @var string
     */
    public $destDir;

    /**
     * @var string
     */
    public $nameCamel;

    /**
     * @var string
     */
    public $nameKebab;

    /**
     * @var string
     */
    public $nameSnake;

    /**
     * @var string
     */
    public $nameStudly;

    /**
     * @var Filesystem
     */
    protected $fs;

    public function __construct(string $name, string $dest = null)
    {
        $this->logPrintfInit('[SinkSkel]: ');
        $name = Str::of($name);
        $this->nameCamel = $name->camel();
        $this->nameKebab = $name->kebab();
        $this->nameSnake = $name->snake();
        $this->nameStudly = $name->studly();
        $this->destDir = $dest ?: dirname(base_path()) . '/ragnarok-' . $this->nameKebab;
    }

    public function createSkel()
    {
        $this->fs = new Filesystem(
            new LocalFilesystemAdapter('/'),
            ['visibility' => 'public']
        );
        $this->assertDir($this->destDir);
        $stubDir = __DIR__ . '/stub';
        foreach ($this->fs->listContents(__DIR__ . '/stub', true) as $metaFile) {
            $relPath = substr($metaFile['path'], strlen($stubDir));
            $destPath = $this->destPath($relPath);
            if ($this->fs->directoryExists($metaFile['path'])) {
                $this->assertDir($destPath);
            } else {
                $this->debug('Creating file %s => %s', $metaFile['path'], $destPath);
                $this->createDestFile($metaFile['path'], $destPath);
            }
        }
    }

    protected function createDestFile($fromPath, $toPath): SinkZombie
    {
        $content = $this->fs->read($fromPath);
        $content = $this->transStr($content);
        $destDir = dirname($toPath);
        $this->fs->write($toPath, $content);
        return $this;
    }

    protected function assertDir($dir)
    {
        if (!$this->fs->directoryExists($dir)) {
            $this->fs->createDirectory($dir);
        }
    }

    protected function transFilePath(string $path): string
    {
        return $this->transStr($path, 'Snake');
    }

    protected function destPath($relFromPath) {
        return $this->destDir . '/' . $this->transFilePath($relFromPath);
    }

    protected function transStr(string $from, string $lowerMethod = 'Camel'): string
    {
        $dest = str_replace('dummy', $this->{'name' . $lowerMethod}, $from);
        return str_replace('Dummy', $this->nameStudly, $dest);
    }
}

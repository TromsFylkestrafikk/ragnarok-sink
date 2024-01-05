use Ragnarok\Sink\Models\SinkFile;
    public function __construct(protected string $sinkId, protected Filesystem $rDisk)
     * @return SinkFile|null
        $local = LocalFile::find($this->sinkId, $filename);
        if (!$local) {
            $local = $this->copyFile($filename);
        return $local->getFile();
     * Copy file from remote and update/create new file model.
     * @return LocalFile|null
        $local = LocalFile::createFromFilename($this->sinkId, $filename);
        $local->put($this->getRemoteFileContent($filename));
        return $local;
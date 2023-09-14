namespace Ragnarok\Sink\Services;
use Ragnarok\Sink\Traits\LogPrintf;
use Ragnarok\Sink\Models\RawFile;
    /**
     * Get service for managing local files.
     *
     * @return LocalFiles
     */
    public function getLocal()
    {
        return $this->local;
    }

    public function getDisk(): Filesystem
    {
        return $this->rDisk;
    }

<?php

namespace Ragnarok\Sink\Console;

use Illuminate\Console\Command;
use Ragnarok\Sink\Console\MakeSink\SinkZombie;

class MakeSink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ragnarok:make-sink
                            {name : Unique name of sink w/o spaces}
                            {dest? : Destination directory of package. Defaults to ../ragnarok-<name>}
                            {--f|force : Force overwrite of existing repo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new laravel sink composer package compatible with Ragnarok';

    protected SinkZombie $skelator;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->skelator = new SinkZombie($this->argument('name'), $this->argument('dest'));
        if (is_dir($this->skelator->destDir)) {
            if (!$this->option('force')) {
                $this->warn(sprintf('Directory exists already: %s. Use --force to override', $this->skelator->destDir));
                return self::FAILURE;
            }
        }
        $this->skelator->createSkel();
        return self::SUCCESS;
    }
}

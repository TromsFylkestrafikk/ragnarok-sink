<?php

namespace Ragnarok\Sink\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Collection;
use Ragnarok\Sink\Services\Registrar;

/**
 * @method static void register(string $id, string $sinkClass) Register your ragnarok sink here.
 * @method static Collection getSinkClasses()
 */
class SinkRegistrar extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Registrar::class;
    }
}

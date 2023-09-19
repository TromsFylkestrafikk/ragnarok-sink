<?php

namespace Ragnarok\Sink\Facades;

use Illuminate\Support\Facades\Facade;
use Ragnarok\Sink\Services\Registrar;

/**
 * @method static void register(string $sinkClass) Register your ragnarok sink here.
 * @method static array getSinkClasses()
 */
class SinkRegistrar extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Registrar::class;
    }
}

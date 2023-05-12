<?php

namespace Tromsfylkestrafikk\RagnarokSink;

use Illuminate\Support\Facades\Facade;

class RagnarokSinkFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ragnarok-sink';
    }
}

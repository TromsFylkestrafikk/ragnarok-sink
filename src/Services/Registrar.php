<?php

namespace Ragnarok\Sink\Services;

use Illuminate\Support\Collection;

/**
 * Service for registering ragnarok sinks.
 */
class Registrar
{
    /**
     * @var string[]
     */
    protected $sinkClasses = [];

    /**
     * Register your Ragnarok sink.
     *
     * Present your class implementing the \Ragnarok\Sink\Sinks\SinkBase base class.
     *
     * @param string $sinkClass Class implementing \Ragnarok\Sink\Sinks\SinkBase
     */
    public function register(string $sinkClass): void
    {
        $this->sinkClasses[$sinkClass::$id] = $sinkClass;
    }

    /**
     * @return Collection
     */
    public function getSinkClasses(): Collection
    {
        return collect($this->sinkClasses);
    }
}

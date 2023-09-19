<?php

namespace Ragnarok\Sink\Services;

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
     */
    public function register(string $sinkClass): void
    {
        $this->sinkClasses[] = $sinkClass;
    }

    public function getSinkClasses(): array
    {
        return $this->sinkClasses;
    }
}

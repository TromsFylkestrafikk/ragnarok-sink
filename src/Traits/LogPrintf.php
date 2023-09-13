<?php

namespace Ragnarok\Sink\Traits;

use Illuminate\Support\Facades\Log;

/**
 * Use printf syntax for writing log entries with optional prefix.
 *
 * @method public debug($message, ...$args)
 * @method public info($message, ...$args)
 * @method public notice($message, ...$args)
 * @method public warning($message, ...$args)
 * @method public error($message, ...$args)
 * @method public critical($message, ...$args)
 * @method public alert($message, ...$args)
 * @method public emergency($message, ...$args)
 */
trait LogPrintf
{
    /**
     * Use this as prefix for all log entries.
     *
     * @var string
     */
    protected $logPrefix = '';

    /**
     * Use these as arguments for log prefix.
     *
     * @var array
     */
    protected $logPrefixArgs = [];

    /**
     * available log levels.
     */
    protected $logLevels = ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'];

    /**
     * Create log shorthand methods.
     */
    public function logPrintfInit($prefix = '', ...$prefixArgs)
    {
        $this->logPrefix = $prefix;
        $this->logPrefixArgs = $prefixArgs;
    }

    public function __call($level, $args)
    {
        if (!in_array($level, $this->logLevels) || !count($args)) {
            return;
        }
        $realMsg = $this->logPrefix . $args[0];
        $realArgs = array_merge([$realMsg], $this->logPrefixArgs, array_slice($args, 1));
        Log::$level(call_user_func_array('sprintf', $realArgs));
    }
}

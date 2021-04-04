<?php

namespace RayGlobalScoped\Spatie\Ray\Payloads;

use RayGlobalScoped\Spatie\Ray\ArgumentConverter;
class LogPayload extends \RayGlobalScoped\Spatie\Ray\Payloads\Payload
{
    /** @var array */
    protected $values;
    public static function createForArguments(array $arguments) : \RayGlobalScoped\Spatie\Ray\Payloads\Payload
    {
        $dumpedArguments = \array_map(function ($argument) {
            return \RayGlobalScoped\Spatie\Ray\ArgumentConverter::convertToPrimitive($argument);
        }, $arguments);
        return new static($dumpedArguments);
    }
    public function __construct($values)
    {
        if (!\is_array($values)) {
            $values = [$values];
        }
        $this->values = $values;
    }
    public function getType() : string
    {
        return 'log';
    }
    public function getContent() : array
    {
        return ['values' => $this->values];
    }
}

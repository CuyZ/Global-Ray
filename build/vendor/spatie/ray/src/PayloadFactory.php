<?php

namespace RayGlobalScoped\Spatie\Ray;

use RayGlobalScoped\Carbon\CarbonInterface;
use RayGlobalScoped\Spatie\Ray\Payloads\BoolPayload;
use RayGlobalScoped\Spatie\Ray\Payloads\CarbonPayload;
use RayGlobalScoped\Spatie\Ray\Payloads\LogPayload;
use RayGlobalScoped\Spatie\Ray\Payloads\NullPayload;
use RayGlobalScoped\Spatie\Ray\Payloads\Payload;
class PayloadFactory
{
    /** @var array */
    protected $values;
    /** @var \Closure|null */
    protected static $payloadFinder = null;
    public static function createForValues(array $arguments) : array
    {
        return (new static($arguments))->getPayloads();
    }
    public static function registerPayloadFinder(callable $callable)
    {
        self::$payloadFinder = $callable;
    }
    public function __construct(array $values)
    {
        $this->values = $values;
    }
    public function getPayloads() : array
    {
        return \array_map(function ($value) {
            return $this->getPayload($value);
        }, $this->values);
    }
    protected function getPayload($value) : \RayGlobalScoped\Spatie\Ray\Payloads\Payload
    {
        if (self::$payloadFinder) {
            if ($payload = (static::$payloadFinder)($value)) {
                return $payload;
            }
        }
        if (\is_bool($value)) {
            return new \RayGlobalScoped\Spatie\Ray\Payloads\BoolPayload($value);
        }
        if (\is_null($value)) {
            return new \RayGlobalScoped\Spatie\Ray\Payloads\NullPayload();
        }
        if ($value instanceof \RayGlobalScoped\Carbon\CarbonInterface) {
            return new \RayGlobalScoped\Spatie\Ray\Payloads\CarbonPayload($value);
        }
        $primitiveValue = \RayGlobalScoped\Spatie\Ray\ArgumentConverter::convertToPrimitive($value);
        return new \RayGlobalScoped\Spatie\Ray\Payloads\LogPayload($primitiveValue);
    }
}

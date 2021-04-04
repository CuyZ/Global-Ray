<?php

namespace RayGlobalScoped\Spatie\Ray\Payloads;

class JsonStringPayload extends \RayGlobalScoped\Spatie\Ray\Payloads\Payload
{
    /** @var mixed */
    protected $value;
    public function __construct($value)
    {
        $this->value = $value;
    }
    public function getType() : string
    {
        return 'json_string';
    }
    public function getContent() : array
    {
        return ['value' => \json_encode($this->value)];
    }
}

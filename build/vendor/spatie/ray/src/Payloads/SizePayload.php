<?php

namespace RayGlobalScoped\Spatie\Ray\Payloads;

class SizePayload extends \RayGlobalScoped\Spatie\Ray\Payloads\Payload
{
    /** @var mixed */
    protected $size;
    public function __construct(string $size)
    {
        $this->size = $size;
    }
    public function getType() : string
    {
        return 'size';
    }
    public function getContent() : array
    {
        return ['size' => $this->size];
    }
}

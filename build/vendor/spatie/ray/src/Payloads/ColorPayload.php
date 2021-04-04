<?php

namespace RayGlobalScoped\Spatie\Ray\Payloads;

class ColorPayload extends \RayGlobalScoped\Spatie\Ray\Payloads\Payload
{
    /** @var mixed */
    protected $color;
    public function __construct(string $color)
    {
        $this->color = $color;
    }
    public function getType() : string
    {
        return 'color';
    }
    public function getContent() : array
    {
        return ['color' => $this->color];
    }
}

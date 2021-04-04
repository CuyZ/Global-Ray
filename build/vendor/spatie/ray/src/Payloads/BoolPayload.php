<?php

namespace RayGlobalScoped\Spatie\Ray\Payloads;

class BoolPayload extends \RayGlobalScoped\Spatie\Ray\Payloads\Payload
{
    /** @var bool */
    protected $bool;
    public function __construct(bool $bool)
    {
        $this->bool = $bool;
    }
    public function getType() : string
    {
        return 'custom';
    }
    public function getContent() : array
    {
        return ['content' => $this->bool, 'label' => 'Boolean'];
    }
}

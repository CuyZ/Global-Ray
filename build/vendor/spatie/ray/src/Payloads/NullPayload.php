<?php

namespace RayGlobalScoped\Spatie\Ray\Payloads;

class NullPayload extends \RayGlobalScoped\Spatie\Ray\Payloads\Payload
{
    /** @var bool */
    protected $bool;
    public function getType() : string
    {
        return 'custom';
    }
    public function getContent() : array
    {
        return ['content' => null, 'label' => 'Null'];
    }
}

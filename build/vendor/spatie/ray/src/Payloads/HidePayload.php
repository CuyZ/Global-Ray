<?php

namespace RayGlobalScoped\Spatie\Ray\Payloads;

class HidePayload extends \RayGlobalScoped\Spatie\Ray\Payloads\Payload
{
    public function getType() : string
    {
        return 'hide';
    }
}

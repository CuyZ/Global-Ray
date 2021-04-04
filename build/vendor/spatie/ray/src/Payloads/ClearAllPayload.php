<?php

namespace RayGlobalScoped\Spatie\Ray\Payloads;

class ClearAllPayload extends \RayGlobalScoped\Spatie\Ray\Payloads\Payload
{
    public function getType() : string
    {
        return 'clear_all';
    }
}

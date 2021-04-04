<?php

namespace RayGlobalScoped\Spatie\Ray\Payloads;

class RemovePayload extends \RayGlobalScoped\Spatie\Ray\Payloads\Payload
{
    public function getType() : string
    {
        return 'remove';
    }
}

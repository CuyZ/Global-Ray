<?php

namespace RayGlobalScoped\Spatie\Ray\Payloads;

class ShowAppPayload extends \RayGlobalScoped\Spatie\Ray\Payloads\Payload
{
    public function getType() : string
    {
        return 'show_app';
    }
}

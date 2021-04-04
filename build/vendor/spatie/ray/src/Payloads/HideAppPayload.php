<?php

namespace RayGlobalScoped\Spatie\Ray\Payloads;

class HideAppPayload extends \RayGlobalScoped\Spatie\Ray\Payloads\Payload
{
    public function getType() : string
    {
        return 'hide_app';
    }
}

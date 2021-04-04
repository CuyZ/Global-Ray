<?php

namespace RayGlobalScoped\Spatie\Ray\Payloads;

class NotifyPayload extends \RayGlobalScoped\Spatie\Ray\Payloads\Payload
{
    /** @var string */
    protected $text;
    public function __construct(string $text)
    {
        $this->text = $text;
    }
    public function getType() : string
    {
        return 'notify';
    }
    public function getContent() : array
    {
        return ['value' => $this->text];
    }
}

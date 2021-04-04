<?php

namespace RayGlobalScoped\Spatie\Ray\Payloads;

class NewScreenPayload extends \RayGlobalScoped\Spatie\Ray\Payloads\Payload
{
    /** @var mixed */
    protected $name;
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    public function getType() : string
    {
        return 'new_screen';
    }
    public function getContent() : array
    {
        return ['name' => $this->name];
    }
}

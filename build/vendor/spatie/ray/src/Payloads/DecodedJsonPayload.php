<?php

namespace RayGlobalScoped\Spatie\Ray\Payloads;

use RayGlobalScoped\Spatie\Ray\ArgumentConverter;
class DecodedJsonPayload extends \RayGlobalScoped\Spatie\Ray\Payloads\Payload
{
    /** @var string */
    protected $value;
    public function __construct(string $value)
    {
        $this->value = $value;
    }
    public function getType() : string
    {
        return 'custom';
    }
    public function getContent() : array
    {
        $decodedJson = \json_decode($this->value, \true);
        return ['content' => \RayGlobalScoped\Spatie\Ray\ArgumentConverter::convertToPrimitive($decodedJson), 'label' => ''];
    }
}

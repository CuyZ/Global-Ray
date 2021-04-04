<?php

namespace RayGlobalScoped\Spatie\Ray\Payloads;

class HtmlPayload extends \RayGlobalScoped\Spatie\Ray\Payloads\Payload
{
    /** @var string */
    protected $html;
    public function __construct(string $html = '')
    {
        $this->html = $html;
    }
    public function getType() : string
    {
        return 'custom';
    }
    public function getContent() : array
    {
        return ['content' => $this->html, 'label' => 'HTML'];
    }
}

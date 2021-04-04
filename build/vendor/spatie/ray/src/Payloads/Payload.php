<?php

namespace RayGlobalScoped\Spatie\Ray\Payloads;

use RayGlobalScoped\Spatie\Ray\Origin\DefaultOriginFactory;
use RayGlobalScoped\Spatie\Ray\Origin\Origin;
abstract class Payload
{
    /** @var string */
    public static $originFactoryClass = \RayGlobalScoped\Spatie\Ray\Origin\DefaultOriginFactory::class;
    public abstract function getType() : string;
    /** @var string|null */
    public $remotePath = null;
    /** @var string|null */
    public $localPath = null;
    public function replaceRemotePathWithLocalPath(string $filePath) : string
    {
        if (\is_null($this->remotePath) || \is_null($this->localPath)) {
            return $filePath;
        }
        $pattern = '~^' . \preg_quote($this->remotePath, '~') . '~';
        return \preg_replace($pattern, $this->localPath, $filePath);
    }
    public function getContent() : array
    {
        return [];
    }
    public function toArray() : array
    {
        return ['type' => $this->getType(), 'content' => $this->getContent(), 'origin' => $this->getOrigin()->toArray()];
    }
    public function toJson() : string
    {
        return \json_encode($this->toArray());
    }
    protected function getOrigin() : \RayGlobalScoped\Spatie\Ray\Origin\Origin
    {
        /** @var \Spatie\Ray\Origin\OriginFactory $originFactory */
        $originFactory = new self::$originFactoryClass();
        $origin = $originFactory->getOrigin();
        $origin->file = $this->replaceRemotePathWithLocalPath($origin->file);
        return $origin;
    }
}

<?php

namespace RayGlobalScoped\Spatie\Ray\Origin;

use RayGlobalScoped\Spatie\Backtrace\Backtrace;
use RayGlobalScoped\Spatie\Backtrace\Frame;
use RayGlobalScoped\Spatie\Ray\Ray;
class DefaultOriginFactory implements \RayGlobalScoped\Spatie\Ray\Origin\OriginFactory
{
    public function getOrigin() : \RayGlobalScoped\Spatie\Ray\Origin\Origin
    {
        $frame = $this->getFrame();
        return new \RayGlobalScoped\Spatie\Ray\Origin\Origin($frame ? $frame->file : null, $frame ? $frame->lineNumber : null, \RayGlobalScoped\Spatie\Ray\Origin\Hostname::get());
    }
    /**
     * @return \Spatie\Backtrace\Frame|null
     */
    protected function getFrame()
    {
        $frames = $this->getAllFrames();
        $indexOfRay = $this->getIndexOfRayFrame($frames);
        return $frames[$indexOfRay] ?? null;
    }
    protected function getAllFrames() : array
    {
        $frames = \RayGlobalScoped\Spatie\Backtrace\Backtrace::create()->frames();
        return \array_reverse($frames, \true);
    }
    /**
     * @param array $frames
     *
     * @return int|null
     */
    protected function getIndexOfRayFrame(array $frames)
    {
        $index = $this->search(function (\RayGlobalScoped\Spatie\Backtrace\Frame $frame) {
            if ($frame->class === \RayGlobalScoped\Spatie\Ray\Ray::class) {
                return \true;
            }
            if ($this->startsWith($frame->file, \dirname(__DIR__))) {
                return \true;
            }
            return \false;
        }, $frames);
        return $index + 1;
    }
    public function startsWith(string $hayStack, string $needle) : bool
    {
        return \strpos($hayStack, $needle) === 0;
    }
    /**
     * @param callable $callable
     * @param array $items
     *
     * @return int|null
     */
    protected function search(callable $callable, array $items)
    {
        foreach ($items as $key => $item) {
            if ($callable($item, $key)) {
                return $key;
            }
        }
        return null;
    }
}

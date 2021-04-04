<?php

namespace RayGlobalScoped\Spatie\Ray\Concerns;

use RayGlobalScoped\Spatie\Backtrace\Frame;
trait RemovesRayFrames
{
    protected function removeRayFrames(array $frames) : array
    {
        $frames = \array_filter($frames, function (\RayGlobalScoped\Spatie\Backtrace\Frame $frame) {
            return !$this->isRayFrame($frame);
        });
        return \array_values($frames);
    }
    protected function isRayFrame(\RayGlobalScoped\Spatie\Backtrace\Frame $frame) : bool
    {
        foreach ($this->rayNamespaces() as $rayNamespace) {
            if (\substr($frame->class, 0, \strlen($rayNamespace)) === $rayNamespace) {
                return \true;
            }
        }
        return \false;
    }
    protected function rayNamespaces() : array
    {
        return ['RayGlobalScoped\\Spatie\\Ray', 'RayGlobalScoped\\Spatie\\LaravelRay', 'RayGlobalScoped\\Spatie\\WordPressRay'];
    }
}

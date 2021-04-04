<?php

namespace RayGlobalScoped\Spatie\Ray\Origin;

interface OriginFactory
{
    public function getOrigin() : \RayGlobalScoped\Spatie\Ray\Origin\Origin;
}

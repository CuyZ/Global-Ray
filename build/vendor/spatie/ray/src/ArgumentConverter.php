<?php

namespace RayGlobalScoped\Spatie\Ray;

use RayGlobalScoped\Symfony\Component\VarDumper\Cloner\VarCloner;
use RayGlobalScoped\Symfony\Component\VarDumper\Dumper\HtmlDumper;
class ArgumentConverter
{
    public static function convertToPrimitive($argument)
    {
        if (\is_null($argument)) {
            return null;
        }
        if (\is_string($argument)) {
            return $argument;
        }
        if (\is_int($argument)) {
            return $argument;
        }
        if (\is_bool($argument)) {
            return $argument;
        }
        $cloner = new \RayGlobalScoped\Symfony\Component\VarDumper\Cloner\VarCloner();
        $dumper = new \RayGlobalScoped\Symfony\Component\VarDumper\Dumper\HtmlDumper();
        $clonedArgument = $cloner->cloneVar($argument);
        return $dumper->dump($clonedArgument, \true);
    }
}

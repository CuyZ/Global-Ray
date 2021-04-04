<?php

declare (strict_types=1);
namespace RayGlobalScoped\Brick\Math\Exception;

/**
 * Exception thrown when a number cannot be represented at the requested scale without rounding.
 */
class RoundingNecessaryException extends \RayGlobalScoped\Brick\Math\Exception\MathException
{
    /**
     * @return RoundingNecessaryException
     *
     * @psalm-pure
     */
    public static function roundingNecessary() : \RayGlobalScoped\Brick\Math\Exception\RoundingNecessaryException
    {
        return new self('Rounding is necessary to represent the result of the operation at this scale.');
    }
}

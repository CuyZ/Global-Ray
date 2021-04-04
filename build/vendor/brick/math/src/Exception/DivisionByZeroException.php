<?php

declare (strict_types=1);
namespace RayGlobalScoped\Brick\Math\Exception;

/**
 * Exception thrown when a division by zero occurs.
 */
class DivisionByZeroException extends \RayGlobalScoped\Brick\Math\Exception\MathException
{
    /**
     * @return DivisionByZeroException
     *
     * @psalm-pure
     */
    public static function divisionByZero() : \RayGlobalScoped\Brick\Math\Exception\DivisionByZeroException
    {
        return new self('Division by zero.');
    }
    /**
     * @return DivisionByZeroException
     *
     * @psalm-pure
     */
    public static function modulusMustNotBeZero() : \RayGlobalScoped\Brick\Math\Exception\DivisionByZeroException
    {
        return new self('The modulus must not be zero.');
    }
    /**
     * @return DivisionByZeroException
     *
     * @psalm-pure
     */
    public static function denominatorMustNotBeZero() : \RayGlobalScoped\Brick\Math\Exception\DivisionByZeroException
    {
        return new self('The denominator of a rational number cannot be zero.');
    }
}

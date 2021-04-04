<?php

/**
 * This file is part of the ramsey/uuid library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ben Ramsey <ben@benramsey.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
declare (strict_types=1);
namespace RayGlobalScoped\Ramsey\Uuid\Math;

use RayGlobalScoped\Brick\Math\BigDecimal;
use RayGlobalScoped\Brick\Math\BigInteger;
use RayGlobalScoped\Brick\Math\Exception\MathException;
use RayGlobalScoped\Brick\Math\RoundingMode as BrickMathRounding;
use RayGlobalScoped\Ramsey\Uuid\Exception\InvalidArgumentException;
use RayGlobalScoped\Ramsey\Uuid\Type\Decimal;
use RayGlobalScoped\Ramsey\Uuid\Type\Hexadecimal;
use RayGlobalScoped\Ramsey\Uuid\Type\Integer as IntegerObject;
use RayGlobalScoped\Ramsey\Uuid\Type\NumberInterface;
/**
 * A calculator using the brick/math library for arbitrary-precision arithmetic
 *
 * @psalm-immutable
 */
final class BrickMathCalculator implements \RayGlobalScoped\Ramsey\Uuid\Math\CalculatorInterface
{
    private const ROUNDING_MODE_MAP = [\RayGlobalScoped\Ramsey\Uuid\Math\RoundingMode::UNNECESSARY => \RayGlobalScoped\Brick\Math\RoundingMode::UNNECESSARY, \RayGlobalScoped\Ramsey\Uuid\Math\RoundingMode::UP => \RayGlobalScoped\Brick\Math\RoundingMode::UP, \RayGlobalScoped\Ramsey\Uuid\Math\RoundingMode::DOWN => \RayGlobalScoped\Brick\Math\RoundingMode::DOWN, \RayGlobalScoped\Ramsey\Uuid\Math\RoundingMode::CEILING => \RayGlobalScoped\Brick\Math\RoundingMode::CEILING, \RayGlobalScoped\Ramsey\Uuid\Math\RoundingMode::FLOOR => \RayGlobalScoped\Brick\Math\RoundingMode::FLOOR, \RayGlobalScoped\Ramsey\Uuid\Math\RoundingMode::HALF_UP => \RayGlobalScoped\Brick\Math\RoundingMode::HALF_UP, \RayGlobalScoped\Ramsey\Uuid\Math\RoundingMode::HALF_DOWN => \RayGlobalScoped\Brick\Math\RoundingMode::HALF_DOWN, \RayGlobalScoped\Ramsey\Uuid\Math\RoundingMode::HALF_CEILING => \RayGlobalScoped\Brick\Math\RoundingMode::HALF_CEILING, \RayGlobalScoped\Ramsey\Uuid\Math\RoundingMode::HALF_FLOOR => \RayGlobalScoped\Brick\Math\RoundingMode::HALF_FLOOR, \RayGlobalScoped\Ramsey\Uuid\Math\RoundingMode::HALF_EVEN => \RayGlobalScoped\Brick\Math\RoundingMode::HALF_EVEN];
    public function add(\RayGlobalScoped\Ramsey\Uuid\Type\NumberInterface $augend, \RayGlobalScoped\Ramsey\Uuid\Type\NumberInterface ...$addends) : \RayGlobalScoped\Ramsey\Uuid\Type\NumberInterface
    {
        $sum = \RayGlobalScoped\Brick\Math\BigInteger::of($augend->toString());
        foreach ($addends as $addend) {
            $sum = $sum->plus($addend->toString());
        }
        return new \RayGlobalScoped\Ramsey\Uuid\Type\Integer((string) $sum);
    }
    public function subtract(\RayGlobalScoped\Ramsey\Uuid\Type\NumberInterface $minuend, \RayGlobalScoped\Ramsey\Uuid\Type\NumberInterface ...$subtrahends) : \RayGlobalScoped\Ramsey\Uuid\Type\NumberInterface
    {
        $difference = \RayGlobalScoped\Brick\Math\BigInteger::of($minuend->toString());
        foreach ($subtrahends as $subtrahend) {
            $difference = $difference->minus($subtrahend->toString());
        }
        return new \RayGlobalScoped\Ramsey\Uuid\Type\Integer((string) $difference);
    }
    public function multiply(\RayGlobalScoped\Ramsey\Uuid\Type\NumberInterface $multiplicand, \RayGlobalScoped\Ramsey\Uuid\Type\NumberInterface ...$multipliers) : \RayGlobalScoped\Ramsey\Uuid\Type\NumberInterface
    {
        $product = \RayGlobalScoped\Brick\Math\BigInteger::of($multiplicand->toString());
        foreach ($multipliers as $multiplier) {
            $product = $product->multipliedBy($multiplier->toString());
        }
        return new \RayGlobalScoped\Ramsey\Uuid\Type\Integer((string) $product);
    }
    public function divide(int $roundingMode, int $scale, \RayGlobalScoped\Ramsey\Uuid\Type\NumberInterface $dividend, \RayGlobalScoped\Ramsey\Uuid\Type\NumberInterface ...$divisors) : \RayGlobalScoped\Ramsey\Uuid\Type\NumberInterface
    {
        $brickRounding = $this->getBrickRoundingMode($roundingMode);
        $quotient = \RayGlobalScoped\Brick\Math\BigDecimal::of($dividend->toString());
        foreach ($divisors as $divisor) {
            $quotient = $quotient->dividedBy($divisor->toString(), $scale, $brickRounding);
        }
        if ($scale === 0) {
            return new \RayGlobalScoped\Ramsey\Uuid\Type\Integer((string) $quotient->toBigInteger());
        }
        return new \RayGlobalScoped\Ramsey\Uuid\Type\Decimal((string) $quotient);
    }
    public function fromBase(string $value, int $base) : \RayGlobalScoped\Ramsey\Uuid\Type\Integer
    {
        try {
            return new \RayGlobalScoped\Ramsey\Uuid\Type\Integer((string) \RayGlobalScoped\Brick\Math\BigInteger::fromBase($value, $base));
        } catch (\RayGlobalScoped\Brick\Math\Exception\MathException|\InvalidArgumentException $exception) {
            throw new \RayGlobalScoped\Ramsey\Uuid\Exception\InvalidArgumentException($exception->getMessage(), (int) $exception->getCode(), $exception);
        }
    }
    public function toBase(\RayGlobalScoped\Ramsey\Uuid\Type\Integer $value, int $base) : string
    {
        try {
            return \RayGlobalScoped\Brick\Math\BigInteger::of($value->toString())->toBase($base);
        } catch (\RayGlobalScoped\Brick\Math\Exception\MathException|\InvalidArgumentException $exception) {
            throw new \RayGlobalScoped\Ramsey\Uuid\Exception\InvalidArgumentException($exception->getMessage(), (int) $exception->getCode(), $exception);
        }
    }
    public function toHexadecimal(\RayGlobalScoped\Ramsey\Uuid\Type\Integer $value) : \RayGlobalScoped\Ramsey\Uuid\Type\Hexadecimal
    {
        return new \RayGlobalScoped\Ramsey\Uuid\Type\Hexadecimal($this->toBase($value, 16));
    }
    public function toInteger(\RayGlobalScoped\Ramsey\Uuid\Type\Hexadecimal $value) : \RayGlobalScoped\Ramsey\Uuid\Type\Integer
    {
        return $this->fromBase($value->toString(), 16);
    }
    /**
     * Maps ramsey/uuid rounding modes to those used by brick/math
     */
    private function getBrickRoundingMode(int $roundingMode) : int
    {
        return self::ROUNDING_MODE_MAP[$roundingMode] ?? 0;
    }
}

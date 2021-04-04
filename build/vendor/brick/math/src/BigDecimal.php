<?php

declare (strict_types=1);
namespace RayGlobalScoped\Brick\Math;

use RayGlobalScoped\Brick\Math\Exception\DivisionByZeroException;
use RayGlobalScoped\Brick\Math\Exception\MathException;
use RayGlobalScoped\Brick\Math\Exception\NegativeNumberException;
use RayGlobalScoped\Brick\Math\Internal\Calculator;
/**
 * Immutable, arbitrary-precision signed decimal numbers.
 *
 * @psalm-immutable
 */
final class BigDecimal extends \RayGlobalScoped\Brick\Math\BigNumber
{
    /**
     * The unscaled value of this decimal number.
     *
     * This is a string of digits with an optional leading minus sign.
     * No leading zero must be present.
     * No leading minus sign must be present if the value is 0.
     *
     * @var string
     */
    private $value;
    /**
     * The scale (number of digits after the decimal point) of this decimal number.
     *
     * This must be zero or more.
     *
     * @var int
     */
    private $scale;
    /**
     * Protected constructor. Use a factory method to obtain an instance.
     *
     * @param string $value The unscaled value, validated.
     * @param int    $scale The scale, validated.
     */
    protected function __construct(string $value, int $scale = 0)
    {
        $this->value = $value;
        $this->scale = $scale;
    }
    /**
     * Creates a BigDecimal of the given value.
     *
     * @param BigNumber|int|float|string $value
     *
     * @return BigDecimal
     *
     * @throws MathException If the value cannot be converted to a BigDecimal.
     *
     * @psalm-pure
     */
    public static function of($value) : \RayGlobalScoped\Brick\Math\BigNumber
    {
        return parent::of($value)->toBigDecimal();
    }
    /**
     * Creates a BigDecimal from an unscaled value and a scale.
     *
     * Example: `(12345, 3)` will result in the BigDecimal `12.345`.
     *
     * @param BigNumber|int|float|string $value The unscaled value. Must be convertible to a BigInteger.
     * @param int                        $scale The scale of the number, positive or zero.
     *
     * @return BigDecimal
     *
     * @throws \InvalidArgumentException If the scale is negative.
     *
     * @psalm-pure
     */
    public static function ofUnscaledValue($value, int $scale = 0) : \RayGlobalScoped\Brick\Math\BigDecimal
    {
        if ($scale < 0) {
            throw new \InvalidArgumentException('The scale cannot be negative.');
        }
        return new \RayGlobalScoped\Brick\Math\BigDecimal((string) \RayGlobalScoped\Brick\Math\BigInteger::of($value), $scale);
    }
    /**
     * Returns a BigDecimal representing zero, with a scale of zero.
     *
     * @return BigDecimal
     *
     * @psalm-pure
     */
    public static function zero() : \RayGlobalScoped\Brick\Math\BigDecimal
    {
        /**
         * @psalm-suppress ImpureStaticVariable
         * @var BigDecimal|null $zero
         */
        static $zero;
        if ($zero === null) {
            $zero = new \RayGlobalScoped\Brick\Math\BigDecimal('0');
        }
        return $zero;
    }
    /**
     * Returns a BigDecimal representing one, with a scale of zero.
     *
     * @return BigDecimal
     *
     * @psalm-pure
     */
    public static function one() : \RayGlobalScoped\Brick\Math\BigDecimal
    {
        /**
         * @psalm-suppress ImpureStaticVariable
         * @var BigDecimal|null $one
         */
        static $one;
        if ($one === null) {
            $one = new \RayGlobalScoped\Brick\Math\BigDecimal('1');
        }
        return $one;
    }
    /**
     * Returns a BigDecimal representing ten, with a scale of zero.
     *
     * @return BigDecimal
     *
     * @psalm-pure
     */
    public static function ten() : \RayGlobalScoped\Brick\Math\BigDecimal
    {
        /**
         * @psalm-suppress ImpureStaticVariable
         * @var BigDecimal|null $ten
         */
        static $ten;
        if ($ten === null) {
            $ten = new \RayGlobalScoped\Brick\Math\BigDecimal('10');
        }
        return $ten;
    }
    /**
     * Returns the sum of this number and the given one.
     *
     * The result has a scale of `max($this->scale, $that->scale)`.
     *
     * @param BigNumber|int|float|string $that The number to add. Must be convertible to a BigDecimal.
     *
     * @return BigDecimal The result.
     *
     * @throws MathException If the number is not valid, or is not convertible to a BigDecimal.
     */
    public function plus($that) : \RayGlobalScoped\Brick\Math\BigDecimal
    {
        $that = \RayGlobalScoped\Brick\Math\BigDecimal::of($that);
        if ($that->value === '0' && $that->scale <= $this->scale) {
            return $this;
        }
        if ($this->value === '0' && $this->scale <= $that->scale) {
            return $that;
        }
        [$a, $b] = $this->scaleValues($this, $that);
        $value = \RayGlobalScoped\Brick\Math\Internal\Calculator::get()->add($a, $b);
        $scale = $this->scale > $that->scale ? $this->scale : $that->scale;
        return new \RayGlobalScoped\Brick\Math\BigDecimal($value, $scale);
    }
    /**
     * Returns the difference of this number and the given one.
     *
     * The result has a scale of `max($this->scale, $that->scale)`.
     *
     * @param BigNumber|int|float|string $that The number to subtract. Must be convertible to a BigDecimal.
     *
     * @return BigDecimal The result.
     *
     * @throws MathException If the number is not valid, or is not convertible to a BigDecimal.
     */
    public function minus($that) : \RayGlobalScoped\Brick\Math\BigDecimal
    {
        $that = \RayGlobalScoped\Brick\Math\BigDecimal::of($that);
        if ($that->value === '0' && $that->scale <= $this->scale) {
            return $this;
        }
        [$a, $b] = $this->scaleValues($this, $that);
        $value = \RayGlobalScoped\Brick\Math\Internal\Calculator::get()->sub($a, $b);
        $scale = $this->scale > $that->scale ? $this->scale : $that->scale;
        return new \RayGlobalScoped\Brick\Math\BigDecimal($value, $scale);
    }
    /**
     * Returns the product of this number and the given one.
     *
     * The result has a scale of `$this->scale + $that->scale`.
     *
     * @param BigNumber|int|float|string $that The multiplier. Must be convertible to a BigDecimal.
     *
     * @return BigDecimal The result.
     *
     * @throws MathException If the multiplier is not a valid number, or is not convertible to a BigDecimal.
     */
    public function multipliedBy($that) : \RayGlobalScoped\Brick\Math\BigDecimal
    {
        $that = \RayGlobalScoped\Brick\Math\BigDecimal::of($that);
        if ($that->value === '1' && $that->scale === 0) {
            return $this;
        }
        if ($this->value === '1' && $this->scale === 0) {
            return $that;
        }
        $value = \RayGlobalScoped\Brick\Math\Internal\Calculator::get()->mul($this->value, $that->value);
        $scale = $this->scale + $that->scale;
        return new \RayGlobalScoped\Brick\Math\BigDecimal($value, $scale);
    }
    /**
     * Returns the result of the division of this number by the given one, at the given scale.
     *
     * @param BigNumber|int|float|string $that         The divisor.
     * @param int|null                   $scale        The desired scale, or null to use the scale of this number.
     * @param int                        $roundingMode An optional rounding mode.
     *
     * @return BigDecimal
     *
     * @throws \InvalidArgumentException If the scale or rounding mode is invalid.
     * @throws MathException             If the number is invalid, is zero, or rounding was necessary.
     */
    public function dividedBy($that, ?int $scale = null, int $roundingMode = \RayGlobalScoped\Brick\Math\RoundingMode::UNNECESSARY) : \RayGlobalScoped\Brick\Math\BigDecimal
    {
        $that = \RayGlobalScoped\Brick\Math\BigDecimal::of($that);
        if ($that->isZero()) {
            throw \RayGlobalScoped\Brick\Math\Exception\DivisionByZeroException::divisionByZero();
        }
        if ($scale === null) {
            $scale = $this->scale;
        } elseif ($scale < 0) {
            throw new \InvalidArgumentException('Scale cannot be negative.');
        }
        if ($that->value === '1' && $that->scale === 0 && $scale === $this->scale) {
            return $this;
        }
        $p = $this->valueWithMinScale($that->scale + $scale);
        $q = $that->valueWithMinScale($this->scale - $scale);
        $result = \RayGlobalScoped\Brick\Math\Internal\Calculator::get()->divRound($p, $q, $roundingMode);
        return new \RayGlobalScoped\Brick\Math\BigDecimal($result, $scale);
    }
    /**
     * Returns the exact result of the division of this number by the given one.
     *
     * The scale of the result is automatically calculated to fit all the fraction digits.
     *
     * @param BigNumber|int|float|string $that The divisor. Must be convertible to a BigDecimal.
     *
     * @return BigDecimal The result.
     *
     * @throws MathException If the divisor is not a valid number, is not convertible to a BigDecimal, is zero,
     *                       or the result yields an infinite number of digits.
     */
    public function exactlyDividedBy($that) : \RayGlobalScoped\Brick\Math\BigDecimal
    {
        $that = \RayGlobalScoped\Brick\Math\BigDecimal::of($that);
        if ($that->value === '0') {
            throw \RayGlobalScoped\Brick\Math\Exception\DivisionByZeroException::divisionByZero();
        }
        [, $b] = $this->scaleValues($this, $that);
        $d = \rtrim($b, '0');
        $scale = \strlen($b) - \strlen($d);
        $calculator = \RayGlobalScoped\Brick\Math\Internal\Calculator::get();
        foreach ([5, 2] as $prime) {
            for (;;) {
                $lastDigit = (int) $d[-1];
                if ($lastDigit % $prime !== 0) {
                    break;
                }
                $d = $calculator->divQ($d, (string) $prime);
                $scale++;
            }
        }
        return $this->dividedBy($that, $scale)->stripTrailingZeros();
    }
    /**
     * Returns this number exponentiated to the given value.
     *
     * The result has a scale of `$this->scale * $exponent`.
     *
     * @param int $exponent The exponent.
     *
     * @return BigDecimal The result.
     *
     * @throws \InvalidArgumentException If the exponent is not in the range 0 to 1,000,000.
     */
    public function power(int $exponent) : \RayGlobalScoped\Brick\Math\BigDecimal
    {
        if ($exponent === 0) {
            return \RayGlobalScoped\Brick\Math\BigDecimal::one();
        }
        if ($exponent === 1) {
            return $this;
        }
        if ($exponent < 0 || $exponent > \RayGlobalScoped\Brick\Math\Internal\Calculator::MAX_POWER) {
            throw new \InvalidArgumentException(\sprintf('The exponent %d is not in the range 0 to %d.', $exponent, \RayGlobalScoped\Brick\Math\Internal\Calculator::MAX_POWER));
        }
        return new \RayGlobalScoped\Brick\Math\BigDecimal(\RayGlobalScoped\Brick\Math\Internal\Calculator::get()->pow($this->value, $exponent), $this->scale * $exponent);
    }
    /**
     * Returns the quotient of the division of this number by this given one.
     *
     * The quotient has a scale of `0`.
     *
     * @param BigNumber|int|float|string $that The divisor. Must be convertible to a BigDecimal.
     *
     * @return BigDecimal The quotient.
     *
     * @throws MathException If the divisor is not a valid decimal number, or is zero.
     */
    public function quotient($that) : \RayGlobalScoped\Brick\Math\BigDecimal
    {
        $that = \RayGlobalScoped\Brick\Math\BigDecimal::of($that);
        if ($that->isZero()) {
            throw \RayGlobalScoped\Brick\Math\Exception\DivisionByZeroException::divisionByZero();
        }
        $p = $this->valueWithMinScale($that->scale);
        $q = $that->valueWithMinScale($this->scale);
        $quotient = \RayGlobalScoped\Brick\Math\Internal\Calculator::get()->divQ($p, $q);
        return new \RayGlobalScoped\Brick\Math\BigDecimal($quotient, 0);
    }
    /**
     * Returns the remainder of the division of this number by this given one.
     *
     * The remainder has a scale of `max($this->scale, $that->scale)`.
     *
     * @param BigNumber|int|float|string $that The divisor. Must be convertible to a BigDecimal.
     *
     * @return BigDecimal The remainder.
     *
     * @throws MathException If the divisor is not a valid decimal number, or is zero.
     */
    public function remainder($that) : \RayGlobalScoped\Brick\Math\BigDecimal
    {
        $that = \RayGlobalScoped\Brick\Math\BigDecimal::of($that);
        if ($that->isZero()) {
            throw \RayGlobalScoped\Brick\Math\Exception\DivisionByZeroException::divisionByZero();
        }
        $p = $this->valueWithMinScale($that->scale);
        $q = $that->valueWithMinScale($this->scale);
        $remainder = \RayGlobalScoped\Brick\Math\Internal\Calculator::get()->divR($p, $q);
        $scale = $this->scale > $that->scale ? $this->scale : $that->scale;
        return new \RayGlobalScoped\Brick\Math\BigDecimal($remainder, $scale);
    }
    /**
     * Returns the quotient and remainder of the division of this number by the given one.
     *
     * The quotient has a scale of `0`, and the remainder has a scale of `max($this->scale, $that->scale)`.
     *
     * @param BigNumber|int|float|string $that The divisor. Must be convertible to a BigDecimal.
     *
     * @return BigDecimal[] An array containing the quotient and the remainder.
     *
     * @throws MathException If the divisor is not a valid decimal number, or is zero.
     */
    public function quotientAndRemainder($that) : array
    {
        $that = \RayGlobalScoped\Brick\Math\BigDecimal::of($that);
        if ($that->isZero()) {
            throw \RayGlobalScoped\Brick\Math\Exception\DivisionByZeroException::divisionByZero();
        }
        $p = $this->valueWithMinScale($that->scale);
        $q = $that->valueWithMinScale($this->scale);
        [$quotient, $remainder] = \RayGlobalScoped\Brick\Math\Internal\Calculator::get()->divQR($p, $q);
        $scale = $this->scale > $that->scale ? $this->scale : $that->scale;
        $quotient = new \RayGlobalScoped\Brick\Math\BigDecimal($quotient, 0);
        $remainder = new \RayGlobalScoped\Brick\Math\BigDecimal($remainder, $scale);
        return [$quotient, $remainder];
    }
    /**
     * Returns the square root of this number, rounded down to the given number of decimals.
     *
     * @param int $scale
     *
     * @return BigDecimal
     *
     * @throws \InvalidArgumentException If the scale is negative.
     * @throws NegativeNumberException If this number is negative.
     */
    public function sqrt(int $scale) : \RayGlobalScoped\Brick\Math\BigDecimal
    {
        if ($scale < 0) {
            throw new \InvalidArgumentException('Scale cannot be negative.');
        }
        if ($this->value === '0') {
            return new \RayGlobalScoped\Brick\Math\BigDecimal('0', $scale);
        }
        if ($this->value[0] === '-') {
            throw new \RayGlobalScoped\Brick\Math\Exception\NegativeNumberException('Cannot calculate the square root of a negative number.');
        }
        $value = $this->value;
        $addDigits = 2 * $scale - $this->scale;
        if ($addDigits > 0) {
            // add zeros
            $value .= \str_repeat('0', $addDigits);
        } elseif ($addDigits < 0) {
            // trim digits
            if (-$addDigits >= \strlen($this->value)) {
                // requesting a scale too low, will always yield a zero result
                return new \RayGlobalScoped\Brick\Math\BigDecimal('0', $scale);
            }
            $value = \substr($value, 0, $addDigits);
        }
        $value = \RayGlobalScoped\Brick\Math\Internal\Calculator::get()->sqrt($value);
        return new \RayGlobalScoped\Brick\Math\BigDecimal($value, $scale);
    }
    /**
     * Returns a copy of this BigDecimal with the decimal point moved $n places to the left.
     *
     * @param int $n
     *
     * @return BigDecimal
     */
    public function withPointMovedLeft(int $n) : \RayGlobalScoped\Brick\Math\BigDecimal
    {
        if ($n === 0) {
            return $this;
        }
        if ($n < 0) {
            return $this->withPointMovedRight(-$n);
        }
        return new \RayGlobalScoped\Brick\Math\BigDecimal($this->value, $this->scale + $n);
    }
    /**
     * Returns a copy of this BigDecimal with the decimal point moved $n places to the right.
     *
     * @param int $n
     *
     * @return BigDecimal
     */
    public function withPointMovedRight(int $n) : \RayGlobalScoped\Brick\Math\BigDecimal
    {
        if ($n === 0) {
            return $this;
        }
        if ($n < 0) {
            return $this->withPointMovedLeft(-$n);
        }
        $value = $this->value;
        $scale = $this->scale - $n;
        if ($scale < 0) {
            if ($value !== '0') {
                $value .= \str_repeat('0', -$scale);
            }
            $scale = 0;
        }
        return new \RayGlobalScoped\Brick\Math\BigDecimal($value, $scale);
    }
    /**
     * Returns a copy of this BigDecimal with any trailing zeros removed from the fractional part.
     *
     * @return BigDecimal
     */
    public function stripTrailingZeros() : \RayGlobalScoped\Brick\Math\BigDecimal
    {
        if ($this->scale === 0) {
            return $this;
        }
        $trimmedValue = \rtrim($this->value, '0');
        if ($trimmedValue === '') {
            return \RayGlobalScoped\Brick\Math\BigDecimal::zero();
        }
        $trimmableZeros = \strlen($this->value) - \strlen($trimmedValue);
        if ($trimmableZeros === 0) {
            return $this;
        }
        if ($trimmableZeros > $this->scale) {
            $trimmableZeros = $this->scale;
        }
        $value = \substr($this->value, 0, -$trimmableZeros);
        $scale = $this->scale - $trimmableZeros;
        return new \RayGlobalScoped\Brick\Math\BigDecimal($value, $scale);
    }
    /**
     * Returns the absolute value of this number.
     *
     * @return BigDecimal
     */
    public function abs() : \RayGlobalScoped\Brick\Math\BigDecimal
    {
        return $this->isNegative() ? $this->negated() : $this;
    }
    /**
     * Returns the negated value of this number.
     *
     * @return BigDecimal
     */
    public function negated() : \RayGlobalScoped\Brick\Math\BigDecimal
    {
        return new \RayGlobalScoped\Brick\Math\BigDecimal(\RayGlobalScoped\Brick\Math\Internal\Calculator::get()->neg($this->value), $this->scale);
    }
    /**
     * {@inheritdoc}
     */
    public function compareTo($that) : int
    {
        $that = \RayGlobalScoped\Brick\Math\BigNumber::of($that);
        if ($that instanceof \RayGlobalScoped\Brick\Math\BigInteger) {
            $that = $that->toBigDecimal();
        }
        if ($that instanceof \RayGlobalScoped\Brick\Math\BigDecimal) {
            [$a, $b] = $this->scaleValues($this, $that);
            return \RayGlobalScoped\Brick\Math\Internal\Calculator::get()->cmp($a, $b);
        }
        return -$that->compareTo($this);
    }
    /**
     * {@inheritdoc}
     */
    public function getSign() : int
    {
        return $this->value === '0' ? 0 : ($this->value[0] === '-' ? -1 : 1);
    }
    /**
     * @return BigInteger
     */
    public function getUnscaledValue() : \RayGlobalScoped\Brick\Math\BigInteger
    {
        return \RayGlobalScoped\Brick\Math\BigInteger::create($this->value);
    }
    /**
     * @return int
     */
    public function getScale() : int
    {
        return $this->scale;
    }
    /**
     * Returns a string representing the integral part of this decimal number.
     *
     * Example: `-123.456` => `-123`.
     *
     * @return string
     */
    public function getIntegralPart() : string
    {
        if ($this->scale === 0) {
            return $this->value;
        }
        $value = $this->getUnscaledValueWithLeadingZeros();
        return \substr($value, 0, -$this->scale);
    }
    /**
     * Returns a string representing the fractional part of this decimal number.
     *
     * If the scale is zero, an empty string is returned.
     *
     * Examples: `-123.456` => '456', `123` => ''.
     *
     * @return string
     */
    public function getFractionalPart() : string
    {
        if ($this->scale === 0) {
            return '';
        }
        $value = $this->getUnscaledValueWithLeadingZeros();
        return \substr($value, -$this->scale);
    }
    /**
     * Returns whether this decimal number has a non-zero fractional part.
     *
     * @return bool
     */
    public function hasNonZeroFractionalPart() : bool
    {
        return $this->getFractionalPart() !== \str_repeat('0', $this->scale);
    }
    /**
     * {@inheritdoc}
     */
    public function toBigInteger() : \RayGlobalScoped\Brick\Math\BigInteger
    {
        $zeroScaleDecimal = $this->scale === 0 ? $this : $this->dividedBy(1, 0);
        return \RayGlobalScoped\Brick\Math\BigInteger::create($zeroScaleDecimal->value);
    }
    /**
     * {@inheritdoc}
     */
    public function toBigDecimal() : \RayGlobalScoped\Brick\Math\BigDecimal
    {
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function toBigRational() : \RayGlobalScoped\Brick\Math\BigRational
    {
        $numerator = \RayGlobalScoped\Brick\Math\BigInteger::create($this->value);
        $denominator = \RayGlobalScoped\Brick\Math\BigInteger::create('1' . \str_repeat('0', $this->scale));
        return \RayGlobalScoped\Brick\Math\BigRational::create($numerator, $denominator, \false);
    }
    /**
     * {@inheritdoc}
     */
    public function toScale(int $scale, int $roundingMode = \RayGlobalScoped\Brick\Math\RoundingMode::UNNECESSARY) : \RayGlobalScoped\Brick\Math\BigDecimal
    {
        if ($scale === $this->scale) {
            return $this;
        }
        return $this->dividedBy(\RayGlobalScoped\Brick\Math\BigDecimal::one(), $scale, $roundingMode);
    }
    /**
     * {@inheritdoc}
     */
    public function toInt() : int
    {
        return $this->toBigInteger()->toInt();
    }
    /**
     * {@inheritdoc}
     */
    public function toFloat() : float
    {
        return (float) (string) $this;
    }
    /**
     * {@inheritdoc}
     */
    public function __toString() : string
    {
        if ($this->scale === 0) {
            return $this->value;
        }
        $value = $this->getUnscaledValueWithLeadingZeros();
        return \substr($value, 0, -$this->scale) . '.' . \substr($value, -$this->scale);
    }
    /**
     * This method is required by interface Serializable and SHOULD NOT be accessed directly.
     *
     * @internal
     *
     * @return string
     */
    public function serialize() : string
    {
        return $this->value . ':' . $this->scale;
    }
    /**
     * This method is only here to implement interface Serializable and cannot be accessed directly.
     *
     * @internal
     * @psalm-suppress RedundantPropertyInitializationCheck
     *
     * @param string $value
     *
     * @return void
     *
     * @throws \LogicException
     */
    public function unserialize($value) : void
    {
        if (isset($this->value)) {
            throw new \LogicException('unserialize() is an internal function, it must not be called directly.');
        }
        [$value, $scale] = \explode(':', $value);
        $this->value = $value;
        $this->scale = (int) $scale;
    }
    /**
     * Puts the internal values of the given decimal numbers on the same scale.
     *
     * @param BigDecimal $x The first decimal number.
     * @param BigDecimal $y The second decimal number.
     *
     * @return array{0: string, 1: string} The scaled integer values of $x and $y.
     */
    private function scaleValues(\RayGlobalScoped\Brick\Math\BigDecimal $x, \RayGlobalScoped\Brick\Math\BigDecimal $y) : array
    {
        $a = $x->value;
        $b = $y->value;
        if ($b !== '0' && $x->scale > $y->scale) {
            $b .= \str_repeat('0', $x->scale - $y->scale);
        } elseif ($a !== '0' && $x->scale < $y->scale) {
            $a .= \str_repeat('0', $y->scale - $x->scale);
        }
        return [$a, $b];
    }
    /**
     * @param int $scale
     *
     * @return string
     */
    private function valueWithMinScale(int $scale) : string
    {
        $value = $this->value;
        if ($this->value !== '0' && $scale > $this->scale) {
            $value .= \str_repeat('0', $scale - $this->scale);
        }
        return $value;
    }
    /**
     * Adds leading zeros if necessary to the unscaled value to represent the full decimal number.
     *
     * @return string
     */
    private function getUnscaledValueWithLeadingZeros() : string
    {
        $value = $this->value;
        $targetLength = $this->scale + 1;
        $negative = $value[0] === '-';
        $length = \strlen($value);
        if ($negative) {
            $length--;
        }
        if ($length >= $targetLength) {
            return $this->value;
        }
        if ($negative) {
            $value = \substr($value, 1);
        }
        $value = \str_pad($value, $targetLength, '0', \STR_PAD_LEFT);
        if ($negative) {
            $value = '-' . $value;
        }
        return $value;
    }
}

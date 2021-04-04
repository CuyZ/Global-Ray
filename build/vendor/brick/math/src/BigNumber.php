<?php

declare (strict_types=1);
namespace RayGlobalScoped\Brick\Math;

use RayGlobalScoped\Brick\Math\Exception\DivisionByZeroException;
use RayGlobalScoped\Brick\Math\Exception\MathException;
use RayGlobalScoped\Brick\Math\Exception\NumberFormatException;
use RayGlobalScoped\Brick\Math\Exception\RoundingNecessaryException;
/**
 * Common interface for arbitrary-precision rational numbers.
 *
 * @psalm-immutable
 */
abstract class BigNumber implements \Serializable, \JsonSerializable
{
    /**
     * The regular expression used to parse integer, decimal and rational numbers.
     */
    private const PARSE_REGEXP = '/^' . '(?<sign>[\\-\\+])?' . '(?:' . '(?:' . '(?<integral>[0-9]+)?' . '(?<point>\\.)?' . '(?<fractional>[0-9]+)?' . '(?:[eE](?<exponent>[\\-\\+]?[0-9]+))?' . ')|(?:' . '(?<numerator>[0-9]+)' . '\\/?' . '(?<denominator>[0-9]+)' . ')' . ')' . '$/';
    /**
     * Creates a BigNumber of the given value.
     *
     * The concrete return type is dependent on the given value, with the following rules:
     *
     * - BigNumber instances are returned as is
     * - integer numbers are returned as BigInteger
     * - floating point numbers are converted to a string then parsed as such
     * - strings containing a `/` character are returned as BigRational
     * - strings containing a `.` character or using an exponential notation are returned as BigDecimal
     * - strings containing only digits with an optional leading `+` or `-` sign are returned as BigInteger
     *
     * @param BigNumber|int|float|string $value
     *
     * @return BigNumber
     *
     * @throws NumberFormatException   If the format of the number is not valid.
     * @throws DivisionByZeroException If the value represents a rational number with a denominator of zero.
     *
     * @psalm-pure
     */
    public static function of($value) : \RayGlobalScoped\Brick\Math\BigNumber
    {
        if ($value instanceof \RayGlobalScoped\Brick\Math\BigNumber) {
            return $value;
        }
        if (\is_int($value)) {
            return new \RayGlobalScoped\Brick\Math\BigInteger((string) $value);
        }
        /** @psalm-suppress RedundantCastGivenDocblockType We cannot trust the untyped $value here! */
        $value = \is_float($value) ? self::floatToString($value) : (string) $value;
        $throw = static function () use($value) : void {
            throw new \RayGlobalScoped\Brick\Math\Exception\NumberFormatException(\sprintf('The given value "%s" does not represent a valid number.', $value));
        };
        if (\preg_match(self::PARSE_REGEXP, $value, $matches) !== 1) {
            $throw();
        }
        $getMatch = static function (string $value) use($matches) : ?string {
            return isset($matches[$value]) && $matches[$value] !== '' ? $matches[$value] : null;
        };
        $sign = $getMatch('sign');
        $numerator = $getMatch('numerator');
        $denominator = $getMatch('denominator');
        if ($numerator !== null) {
            \assert($denominator !== null);
            if ($sign !== null) {
                $numerator = $sign . $numerator;
            }
            $numerator = self::cleanUp($numerator);
            $denominator = self::cleanUp($denominator);
            if ($denominator === '0') {
                throw \RayGlobalScoped\Brick\Math\Exception\DivisionByZeroException::denominatorMustNotBeZero();
            }
            return new \RayGlobalScoped\Brick\Math\BigRational(new \RayGlobalScoped\Brick\Math\BigInteger($numerator), new \RayGlobalScoped\Brick\Math\BigInteger($denominator), \false);
        }
        $point = $getMatch('point');
        $integral = $getMatch('integral');
        $fractional = $getMatch('fractional');
        $exponent = $getMatch('exponent');
        if ($integral === null && $fractional === null) {
            $throw();
        }
        if ($integral === null) {
            $integral = '0';
        }
        if ($point !== null || $exponent !== null) {
            $fractional = $fractional ?? '';
            $exponent = $exponent !== null ? (int) $exponent : 0;
            if ($exponent === \PHP_INT_MIN || $exponent === \PHP_INT_MAX) {
                throw new \RayGlobalScoped\Brick\Math\Exception\NumberFormatException('Exponent too large.');
            }
            $unscaledValue = self::cleanUp(($sign ?? '') . $integral . $fractional);
            $scale = \strlen($fractional) - $exponent;
            if ($scale < 0) {
                if ($unscaledValue !== '0') {
                    $unscaledValue .= \str_repeat('0', -$scale);
                }
                $scale = 0;
            }
            return new \RayGlobalScoped\Brick\Math\BigDecimal($unscaledValue, $scale);
        }
        $integral = self::cleanUp(($sign ?? '') . $integral);
        return new \RayGlobalScoped\Brick\Math\BigInteger($integral);
    }
    /**
     * Safely converts float to string, avoiding locale-dependent issues.
     *
     * @see https://github.com/brick/math/pull/20
     *
     * @param float $float
     *
     * @return string
     *
     * @psalm-pure
     * @psalm-suppress ImpureFunctionCall
     */
    private static function floatToString(float $float) : string
    {
        $currentLocale = \setlocale(\LC_NUMERIC, '0');
        \setlocale(\LC_NUMERIC, 'C');
        $result = (string) $float;
        \setlocale(\LC_NUMERIC, $currentLocale);
        return $result;
    }
    /**
     * Proxy method to access protected constructors from sibling classes.
     *
     * @internal
     *
     * @param mixed ...$args The arguments to the constructor.
     *
     * @return static
     *
     * @psalm-pure
     * @psalm-suppress TooManyArguments
     * @psalm-suppress UnsafeInstantiation
     */
    protected static function create(...$args) : \RayGlobalScoped\Brick\Math\BigNumber
    {
        return new static(...$args);
    }
    /**
     * Returns the minimum of the given values.
     *
     * @param BigNumber|int|float|string ...$values The numbers to compare. All the numbers need to be convertible
     *                                              to an instance of the class this method is called on.
     *
     * @return static The minimum value.
     *
     * @throws \InvalidArgumentException If no values are given.
     * @throws MathException             If an argument is not valid.
     *
     * @psalm-suppress LessSpecificReturnStatement
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-pure
     */
    public static function min(...$values) : \RayGlobalScoped\Brick\Math\BigNumber
    {
        $min = null;
        foreach ($values as $value) {
            $value = static::of($value);
            if ($min === null || $value->isLessThan($min)) {
                $min = $value;
            }
        }
        if ($min === null) {
            throw new \InvalidArgumentException(__METHOD__ . '() expects at least one value.');
        }
        return $min;
    }
    /**
     * Returns the maximum of the given values.
     *
     * @param BigNumber|int|float|string ...$values The numbers to compare. All the numbers need to be convertible
     *                                              to an instance of the class this method is called on.
     *
     * @return static The maximum value.
     *
     * @throws \InvalidArgumentException If no values are given.
     * @throws MathException             If an argument is not valid.
     *
     * @psalm-suppress LessSpecificReturnStatement
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-pure
     */
    public static function max(...$values) : \RayGlobalScoped\Brick\Math\BigNumber
    {
        $max = null;
        foreach ($values as $value) {
            $value = static::of($value);
            if ($max === null || $value->isGreaterThan($max)) {
                $max = $value;
            }
        }
        if ($max === null) {
            throw new \InvalidArgumentException(__METHOD__ . '() expects at least one value.');
        }
        return $max;
    }
    /**
     * Returns the sum of the given values.
     *
     * @param BigNumber|int|float|string ...$values The numbers to add. All the numbers need to be convertible
     *                                              to an instance of the class this method is called on.
     *
     * @return static The sum.
     *
     * @throws \InvalidArgumentException If no values are given.
     * @throws MathException             If an argument is not valid.
     *
     * @psalm-suppress LessSpecificReturnStatement
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-pure
     */
    public static function sum(...$values) : \RayGlobalScoped\Brick\Math\BigNumber
    {
        /** @var BigNumber|null $sum */
        $sum = null;
        foreach ($values as $value) {
            $value = static::of($value);
            $sum = $sum === null ? $value : self::add($sum, $value);
        }
        if ($sum === null) {
            throw new \InvalidArgumentException(__METHOD__ . '() expects at least one value.');
        }
        return $sum;
    }
    /**
     * Adds two BigNumber instances in the correct order to avoid a RoundingNecessaryException.
     *
     * @todo This could be better resolved by creating an abstract protected method in BigNumber, and leaving to
     *       concrete classes the responsibility to perform the addition themselves or delegate it to the given number,
     *       depending on their ability to perform the operation. This will also require a version bump because we're
     *       potentially breaking custom BigNumber implementations (if any...)
     *
     * @param BigNumber $a
     * @param BigNumber $b
     *
     * @return BigNumber
     *
     * @psalm-pure
     */
    private static function add(\RayGlobalScoped\Brick\Math\BigNumber $a, \RayGlobalScoped\Brick\Math\BigNumber $b) : \RayGlobalScoped\Brick\Math\BigNumber
    {
        if ($a instanceof \RayGlobalScoped\Brick\Math\BigRational) {
            return $a->plus($b);
        }
        if ($b instanceof \RayGlobalScoped\Brick\Math\BigRational) {
            return $b->plus($a);
        }
        if ($a instanceof \RayGlobalScoped\Brick\Math\BigDecimal) {
            return $a->plus($b);
        }
        if ($b instanceof \RayGlobalScoped\Brick\Math\BigDecimal) {
            return $b->plus($a);
        }
        /** @var BigInteger $a */
        return $a->plus($b);
    }
    /**
     * Removes optional leading zeros and + sign from the given number.
     *
     * @param string $number The number, validated as a non-empty string of digits with optional leading sign.
     *
     * @return string
     *
     * @psalm-pure
     */
    private static function cleanUp(string $number) : string
    {
        $firstChar = $number[0];
        if ($firstChar === '+' || $firstChar === '-') {
            $number = \substr($number, 1);
        }
        $number = \ltrim($number, '0');
        if ($number === '') {
            return '0';
        }
        if ($firstChar === '-') {
            return '-' . $number;
        }
        return $number;
    }
    /**
     * Checks if this number is equal to the given one.
     *
     * @param BigNumber|int|float|string $that
     *
     * @return bool
     */
    public function isEqualTo($that) : bool
    {
        return $this->compareTo($that) === 0;
    }
    /**
     * Checks if this number is strictly lower than the given one.
     *
     * @param BigNumber|int|float|string $that
     *
     * @return bool
     */
    public function isLessThan($that) : bool
    {
        return $this->compareTo($that) < 0;
    }
    /**
     * Checks if this number is lower than or equal to the given one.
     *
     * @param BigNumber|int|float|string $that
     *
     * @return bool
     */
    public function isLessThanOrEqualTo($that) : bool
    {
        return $this->compareTo($that) <= 0;
    }
    /**
     * Checks if this number is strictly greater than the given one.
     *
     * @param BigNumber|int|float|string $that
     *
     * @return bool
     */
    public function isGreaterThan($that) : bool
    {
        return $this->compareTo($that) > 0;
    }
    /**
     * Checks if this number is greater than or equal to the given one.
     *
     * @param BigNumber|int|float|string $that
     *
     * @return bool
     */
    public function isGreaterThanOrEqualTo($that) : bool
    {
        return $this->compareTo($that) >= 0;
    }
    /**
     * Checks if this number equals zero.
     *
     * @return bool
     */
    public function isZero() : bool
    {
        return $this->getSign() === 0;
    }
    /**
     * Checks if this number is strictly negative.
     *
     * @return bool
     */
    public function isNegative() : bool
    {
        return $this->getSign() < 0;
    }
    /**
     * Checks if this number is negative or zero.
     *
     * @return bool
     */
    public function isNegativeOrZero() : bool
    {
        return $this->getSign() <= 0;
    }
    /**
     * Checks if this number is strictly positive.
     *
     * @return bool
     */
    public function isPositive() : bool
    {
        return $this->getSign() > 0;
    }
    /**
     * Checks if this number is positive or zero.
     *
     * @return bool
     */
    public function isPositiveOrZero() : bool
    {
        return $this->getSign() >= 0;
    }
    /**
     * Returns the sign of this number.
     *
     * @return int -1 if the number is negative, 0 if zero, 1 if positive.
     */
    public abstract function getSign() : int;
    /**
     * Compares this number to the given one.
     *
     * @param BigNumber|int|float|string $that
     *
     * @return int [-1,0,1] If `$this` is lower than, equal to, or greater than `$that`.
     *
     * @throws MathException If the number is not valid.
     */
    public abstract function compareTo($that) : int;
    /**
     * Converts this number to a BigInteger.
     *
     * @return BigInteger The converted number.
     *
     * @throws RoundingNecessaryException If this number cannot be converted to a BigInteger without rounding.
     */
    public abstract function toBigInteger() : \RayGlobalScoped\Brick\Math\BigInteger;
    /**
     * Converts this number to a BigDecimal.
     *
     * @return BigDecimal The converted number.
     *
     * @throws RoundingNecessaryException If this number cannot be converted to a BigDecimal without rounding.
     */
    public abstract function toBigDecimal() : \RayGlobalScoped\Brick\Math\BigDecimal;
    /**
     * Converts this number to a BigRational.
     *
     * @return BigRational The converted number.
     */
    public abstract function toBigRational() : \RayGlobalScoped\Brick\Math\BigRational;
    /**
     * Converts this number to a BigDecimal with the given scale, using rounding if necessary.
     *
     * @param int $scale        The scale of the resulting `BigDecimal`.
     * @param int $roundingMode A `RoundingMode` constant.
     *
     * @return BigDecimal
     *
     * @throws RoundingNecessaryException If this number cannot be converted to the given scale without rounding.
     *                                    This only applies when RoundingMode::UNNECESSARY is used.
     */
    public abstract function toScale(int $scale, int $roundingMode = \RayGlobalScoped\Brick\Math\RoundingMode::UNNECESSARY) : \RayGlobalScoped\Brick\Math\BigDecimal;
    /**
     * Returns the exact value of this number as a native integer.
     *
     * If this number cannot be converted to a native integer without losing precision, an exception is thrown.
     * Note that the acceptable range for an integer depends on the platform and differs for 32-bit and 64-bit.
     *
     * @return int The converted value.
     *
     * @throws MathException If this number cannot be exactly converted to a native integer.
     */
    public abstract function toInt() : int;
    /**
     * Returns an approximation of this number as a floating-point value.
     *
     * Note that this method can discard information as the precision of a floating-point value
     * is inherently limited.
     *
     * If the number is greater than the largest representable floating point number, positive infinity is returned.
     * If the number is less than the smallest representable floating point number, negative infinity is returned.
     *
     * @return float The converted value.
     */
    public abstract function toFloat() : float;
    /**
     * Returns a string representation of this number.
     *
     * The output of this method can be parsed by the `of()` factory method;
     * this will yield an object equal to this one, without any information loss.
     *
     * @return string
     */
    public abstract function __toString() : string;
    /**
     * {@inheritdoc}
     */
    public function jsonSerialize() : string
    {
        return $this->__toString();
    }
}

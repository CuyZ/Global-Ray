<?php

declare (strict_types=1);
namespace RayGlobalScoped\Brick\Math\Internal;

use RayGlobalScoped\Brick\Math\Exception\RoundingNecessaryException;
use RayGlobalScoped\Brick\Math\RoundingMode;
/**
 * Performs basic operations on arbitrary size integers.
 *
 * Unless otherwise specified, all parameters must be validated as non-empty strings of digits,
 * without leading zero, and with an optional leading minus sign if the number is not zero.
 *
 * Any other parameter format will lead to undefined behaviour.
 * All methods must return strings respecting this format, unless specified otherwise.
 *
 * @internal
 *
 * @psalm-immutable
 */
abstract class Calculator
{
    /**
     * The maximum exponent value allowed for the pow() method.
     */
    public const MAX_POWER = 1000000;
    /**
     * The alphabet for converting from and to base 2 to 36, lowercase.
     */
    public const ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyz';
    /**
     * The Calculator instance in use.
     *
     * @var Calculator|null
     */
    private static $instance;
    /**
     * Sets the Calculator instance to use.
     *
     * An instance is typically set only in unit tests: the autodetect is usually the best option.
     *
     * @param Calculator|null $calculator The calculator instance, or NULL to revert to autodetect.
     *
     * @return void
     */
    public static final function set(?\RayGlobalScoped\Brick\Math\Internal\Calculator $calculator) : void
    {
        self::$instance = $calculator;
    }
    /**
     * Returns the Calculator instance to use.
     *
     * If none has been explicitly set, the fastest available implementation will be returned.
     *
     * @return Calculator
     *
     * @psalm-pure
     * @psalm-suppress ImpureStaticProperty
     */
    public static final function get() : \RayGlobalScoped\Brick\Math\Internal\Calculator
    {
        if (self::$instance === null) {
            /** @psalm-suppress ImpureMethodCall */
            self::$instance = self::detect();
        }
        return self::$instance;
    }
    /**
     * Returns the fastest available Calculator implementation.
     *
     * @codeCoverageIgnore
     *
     * @return Calculator
     */
    private static function detect() : \RayGlobalScoped\Brick\Math\Internal\Calculator
    {
        if (\extension_loaded('gmp')) {
            return new \RayGlobalScoped\Brick\Math\Internal\Calculator\GmpCalculator();
        }
        if (\extension_loaded('bcmath')) {
            return new \RayGlobalScoped\Brick\Math\Internal\Calculator\BcMathCalculator();
        }
        return new \RayGlobalScoped\Brick\Math\Internal\Calculator\NativeCalculator();
    }
    /**
     * Extracts the sign & digits of the operands.
     *
     * @param string $a The first operand.
     * @param string $b The second operand.
     *
     * @return array{0: bool, 1: bool, 2: string, 3: string} Whether $a and $b are negative, followed by their digits.
     */
    protected final function init(string $a, string $b) : array
    {
        return [$aNeg = $a[0] === '-', $bNeg = $b[0] === '-', $aNeg ? \substr($a, 1) : $a, $bNeg ? \substr($b, 1) : $b];
    }
    /**
     * Returns the absolute value of a number.
     *
     * @param string $n The number.
     *
     * @return string The absolute value.
     */
    public final function abs(string $n) : string
    {
        return $n[0] === '-' ? \substr($n, 1) : $n;
    }
    /**
     * Negates a number.
     *
     * @param string $n The number.
     *
     * @return string The negated value.
     */
    public final function neg(string $n) : string
    {
        if ($n === '0') {
            return '0';
        }
        if ($n[0] === '-') {
            return \substr($n, 1);
        }
        return '-' . $n;
    }
    /**
     * Compares two numbers.
     *
     * @param string $a The first number.
     * @param string $b The second number.
     *
     * @return int [-1, 0, 1] If the first number is less than, equal to, or greater than the second number.
     */
    public final function cmp(string $a, string $b) : int
    {
        [$aNeg, $bNeg, $aDig, $bDig] = $this->init($a, $b);
        if ($aNeg && !$bNeg) {
            return -1;
        }
        if ($bNeg && !$aNeg) {
            return 1;
        }
        $aLen = \strlen($aDig);
        $bLen = \strlen($bDig);
        if ($aLen < $bLen) {
            $result = -1;
        } elseif ($aLen > $bLen) {
            $result = 1;
        } else {
            $result = $aDig <=> $bDig;
        }
        return $aNeg ? -$result : $result;
    }
    /**
     * Adds two numbers.
     *
     * @param string $a The augend.
     * @param string $b The addend.
     *
     * @return string The sum.
     */
    public abstract function add(string $a, string $b) : string;
    /**
     * Subtracts two numbers.
     *
     * @param string $a The minuend.
     * @param string $b The subtrahend.
     *
     * @return string The difference.
     */
    public abstract function sub(string $a, string $b) : string;
    /**
     * Multiplies two numbers.
     *
     * @param string $a The multiplicand.
     * @param string $b The multiplier.
     *
     * @return string The product.
     */
    public abstract function mul(string $a, string $b) : string;
    /**
     * Returns the quotient of the division of two numbers.
     *
     * @param string $a The dividend.
     * @param string $b The divisor, must not be zero.
     *
     * @return string The quotient.
     */
    public abstract function divQ(string $a, string $b) : string;
    /**
     * Returns the remainder of the division of two numbers.
     *
     * @param string $a The dividend.
     * @param string $b The divisor, must not be zero.
     *
     * @return string The remainder.
     */
    public abstract function divR(string $a, string $b) : string;
    /**
     * Returns the quotient and remainder of the division of two numbers.
     *
     * @param string $a The dividend.
     * @param string $b The divisor, must not be zero.
     *
     * @return string[] An array containing the quotient and remainder.
     */
    public abstract function divQR(string $a, string $b) : array;
    /**
     * Exponentiates a number.
     *
     * @param string $a The base number.
     * @param int    $e The exponent, validated as an integer between 0 and MAX_POWER.
     *
     * @return string The power.
     */
    public abstract function pow(string $a, int $e) : string;
    /**
     * @param string $a
     * @param string $b The modulus; must not be zero.
     *
     * @return string
     */
    public function mod(string $a, string $b) : string
    {
        return $this->divR($this->add($this->divR($a, $b), $b), $b);
    }
    /**
     * Returns the modular multiplicative inverse of $x modulo $m.
     *
     * If $x has no multiplicative inverse mod m, this method must return null.
     *
     * This method can be overridden by the concrete implementation if the underlying library has built-in support.
     *
     * @param string $x
     * @param string $m The modulus; must not be negative or zero.
     *
     * @return string|null
     */
    public function modInverse(string $x, string $m) : ?string
    {
        if ($m === '1') {
            return '0';
        }
        $modVal = $x;
        if ($x[0] === '-' || $this->cmp($this->abs($x), $m) >= 0) {
            $modVal = $this->mod($x, $m);
        }
        $x = '0';
        $y = '0';
        $g = $this->gcdExtended($modVal, $m, $x, $y);
        if ($g !== '1') {
            return null;
        }
        return $this->mod($this->add($this->mod($x, $m), $m), $m);
    }
    /**
     * Raises a number into power with modulo.
     *
     * @param string $base The base number; must be positive or zero.
     * @param string $exp  The exponent; must be positive or zero.
     * @param string $mod  The modulus; must be strictly positive.
     *
     * @return string The power.
     */
    public abstract function modPow(string $base, string $exp, string $mod) : string;
    /**
     * Returns the greatest common divisor of the two numbers.
     *
     * This method can be overridden by the concrete implementation if the underlying library
     * has built-in support for GCD calculations.
     *
     * @param string $a The first number.
     * @param string $b The second number.
     *
     * @return string The GCD, always positive, or zero if both arguments are zero.
     */
    public function gcd(string $a, string $b) : string
    {
        if ($a === '0') {
            return $this->abs($b);
        }
        if ($b === '0') {
            return $this->abs($a);
        }
        return $this->gcd($b, $this->divR($a, $b));
    }
    private function gcdExtended(string $a, string $b, string &$x, string &$y) : string
    {
        if ($a === '0') {
            $x = '0';
            $y = '1';
            return $b;
        }
        $x1 = '0';
        $y1 = '0';
        $gcd = $this->gcdExtended($this->mod($b, $a), $a, $x1, $y1);
        $x = $this->sub($y1, $this->mul($this->divQ($b, $a), $x1));
        $y = $x1;
        return $gcd;
    }
    /**
     * Returns the square root of the given number, rounded down.
     *
     * The result is the largest x such that x² ≤ n.
     * The input MUST NOT be negative.
     *
     * @param string $n The number.
     *
     * @return string The square root.
     */
    public abstract function sqrt(string $n) : string;
    /**
     * Converts a number from an arbitrary base.
     *
     * This method can be overridden by the concrete implementation if the underlying library
     * has built-in support for base conversion.
     *
     * @param string $number The number, positive or zero, non-empty, case-insensitively validated for the given base.
     * @param int    $base   The base of the number, validated from 2 to 36.
     *
     * @return string The converted number, following the Calculator conventions.
     */
    public function fromBase(string $number, int $base) : string
    {
        return $this->fromArbitraryBase(\strtolower($number), self::ALPHABET, $base);
    }
    /**
     * Converts a number to an arbitrary base.
     *
     * This method can be overridden by the concrete implementation if the underlying library
     * has built-in support for base conversion.
     *
     * @param string $number The number to convert, following the Calculator conventions.
     * @param int    $base   The base to convert to, validated from 2 to 36.
     *
     * @return string The converted number, lowercase.
     */
    public function toBase(string $number, int $base) : string
    {
        $negative = $number[0] === '-';
        if ($negative) {
            $number = \substr($number, 1);
        }
        $number = $this->toArbitraryBase($number, self::ALPHABET, $base);
        if ($negative) {
            return '-' . $number;
        }
        return $number;
    }
    /**
     * Converts a non-negative number in an arbitrary base using a custom alphabet, to base 10.
     *
     * @param string $number   The number to convert, validated as a non-empty string,
     *                         containing only chars in the given alphabet/base.
     * @param string $alphabet The alphabet that contains every digit, validated as 2 chars minimum.
     * @param int    $base     The base of the number, validated from 2 to alphabet length.
     *
     * @return string The number in base 10, following the Calculator conventions.
     */
    public final function fromArbitraryBase(string $number, string $alphabet, int $base) : string
    {
        // remove leading "zeros"
        $number = \ltrim($number, $alphabet[0]);
        if ($number === '') {
            return '0';
        }
        // optimize for "one"
        if ($number === $alphabet[1]) {
            return '1';
        }
        $result = '0';
        $power = '1';
        $base = (string) $base;
        for ($i = \strlen($number) - 1; $i >= 0; $i--) {
            $index = \strpos($alphabet, $number[$i]);
            if ($index !== 0) {
                $result = $this->add($result, $index === 1 ? $power : $this->mul($power, (string) $index));
            }
            if ($i !== 0) {
                $power = $this->mul($power, $base);
            }
        }
        return $result;
    }
    /**
     * Converts a non-negative number to an arbitrary base using a custom alphabet.
     *
     * @param string $number   The number to convert, positive or zero, following the Calculator conventions.
     * @param string $alphabet The alphabet that contains every digit, validated as 2 chars minimum.
     * @param int    $base     The base to convert to, validated from 2 to alphabet length.
     *
     * @return string The converted number in the given alphabet.
     */
    public final function toArbitraryBase(string $number, string $alphabet, int $base) : string
    {
        if ($number === '0') {
            return $alphabet[0];
        }
        $base = (string) $base;
        $result = '';
        while ($number !== '0') {
            [$number, $remainder] = $this->divQR($number, $base);
            $remainder = (int) $remainder;
            $result .= $alphabet[$remainder];
        }
        return \strrev($result);
    }
    /**
     * Performs a rounded division.
     *
     * Rounding is performed when the remainder of the division is not zero.
     *
     * @param string $a            The dividend.
     * @param string $b            The divisor, must not be zero.
     * @param int    $roundingMode The rounding mode.
     *
     * @return string
     *
     * @throws \InvalidArgumentException  If the rounding mode is invalid.
     * @throws RoundingNecessaryException If RoundingMode::UNNECESSARY is provided but rounding is necessary.
     */
    public final function divRound(string $a, string $b, int $roundingMode) : string
    {
        [$quotient, $remainder] = $this->divQR($a, $b);
        $hasDiscardedFraction = $remainder !== '0';
        $isPositiveOrZero = ($a[0] === '-') === ($b[0] === '-');
        $discardedFractionSign = function () use($remainder, $b) : int {
            $r = $this->abs($this->mul($remainder, '2'));
            $b = $this->abs($b);
            return $this->cmp($r, $b);
        };
        $increment = \false;
        switch ($roundingMode) {
            case \RayGlobalScoped\Brick\Math\RoundingMode::UNNECESSARY:
                if ($hasDiscardedFraction) {
                    throw \RayGlobalScoped\Brick\Math\Exception\RoundingNecessaryException::roundingNecessary();
                }
                break;
            case \RayGlobalScoped\Brick\Math\RoundingMode::UP:
                $increment = $hasDiscardedFraction;
                break;
            case \RayGlobalScoped\Brick\Math\RoundingMode::DOWN:
                break;
            case \RayGlobalScoped\Brick\Math\RoundingMode::CEILING:
                $increment = $hasDiscardedFraction && $isPositiveOrZero;
                break;
            case \RayGlobalScoped\Brick\Math\RoundingMode::FLOOR:
                $increment = $hasDiscardedFraction && !$isPositiveOrZero;
                break;
            case \RayGlobalScoped\Brick\Math\RoundingMode::HALF_UP:
                $increment = $discardedFractionSign() >= 0;
                break;
            case \RayGlobalScoped\Brick\Math\RoundingMode::HALF_DOWN:
                $increment = $discardedFractionSign() > 0;
                break;
            case \RayGlobalScoped\Brick\Math\RoundingMode::HALF_CEILING:
                $increment = $isPositiveOrZero ? $discardedFractionSign() >= 0 : $discardedFractionSign() > 0;
                break;
            case \RayGlobalScoped\Brick\Math\RoundingMode::HALF_FLOOR:
                $increment = $isPositiveOrZero ? $discardedFractionSign() > 0 : $discardedFractionSign() >= 0;
                break;
            case \RayGlobalScoped\Brick\Math\RoundingMode::HALF_EVEN:
                $lastDigit = (int) $quotient[-1];
                $lastDigitIsEven = $lastDigit % 2 === 0;
                $increment = $lastDigitIsEven ? $discardedFractionSign() > 0 : $discardedFractionSign() >= 0;
                break;
            default:
                throw new \InvalidArgumentException('Invalid rounding mode.');
        }
        if ($increment) {
            return $this->add($quotient, $isPositiveOrZero ? '1' : '-1');
        }
        return $quotient;
    }
    /**
     * Calculates bitwise AND of two numbers.
     *
     * This method can be overridden by the concrete implementation if the underlying library
     * has built-in support for bitwise operations.
     *
     * @param string $a
     * @param string $b
     *
     * @return string
     */
    public function and(string $a, string $b) : string
    {
        return $this->bitwise('and', $a, $b);
    }
    /**
     * Calculates bitwise OR of two numbers.
     *
     * This method can be overridden by the concrete implementation if the underlying library
     * has built-in support for bitwise operations.
     *
     * @param string $a
     * @param string $b
     *
     * @return string
     */
    public function or(string $a, string $b) : string
    {
        return $this->bitwise('or', $a, $b);
    }
    /**
     * Calculates bitwise XOR of two numbers.
     *
     * This method can be overridden by the concrete implementation if the underlying library
     * has built-in support for bitwise operations.
     *
     * @param string $a
     * @param string $b
     *
     * @return string
     */
    public function xor(string $a, string $b) : string
    {
        return $this->bitwise('xor', $a, $b);
    }
    /**
     * Performs a bitwise operation on a decimal number.
     *
     * @param string $operator The operator to use, must be "and", "or" or "xor".
     * @param string $a        The left operand.
     * @param string $b        The right operand.
     *
     * @return string
     */
    private function bitwise(string $operator, string $a, string $b) : string
    {
        [$aNeg, $bNeg, $aDig, $bDig] = $this->init($a, $b);
        $aBin = $this->toBinary($aDig);
        $bBin = $this->toBinary($bDig);
        $aLen = \strlen($aBin);
        $bLen = \strlen($bBin);
        if ($aLen > $bLen) {
            $bBin = \str_repeat("\0", $aLen - $bLen) . $bBin;
        } elseif ($bLen > $aLen) {
            $aBin = \str_repeat("\0", $bLen - $aLen) . $aBin;
        }
        if ($aNeg) {
            $aBin = $this->twosComplement($aBin);
        }
        if ($bNeg) {
            $bBin = $this->twosComplement($bBin);
        }
        switch ($operator) {
            case 'and':
                $value = $aBin & $bBin;
                $negative = ($aNeg and $bNeg);
                break;
            case 'or':
                $value = $aBin | $bBin;
                $negative = ($aNeg or $bNeg);
                break;
            case 'xor':
                $value = $aBin ^ $bBin;
                $negative = ($aNeg xor $bNeg);
                break;
            // @codeCoverageIgnoreStart
            default:
                throw new \InvalidArgumentException('Invalid bitwise operator.');
        }
        if ($negative) {
            $value = $this->twosComplement($value);
        }
        $result = $this->toDecimal($value);
        return $negative ? $this->neg($result) : $result;
    }
    /**
     * @psalm-suppress InvalidOperand
     * @see https://github.com/vimeo/psalm/issues/4456
     *
     * @param string $number A positive, binary number.
     *
     * @return string
     */
    private function twosComplement(string $number) : string
    {
        $xor = \str_repeat("�", \strlen($number));
        $number ^= $xor;
        for ($i = \strlen($number) - 1; $i >= 0; $i--) {
            $byte = \ord($number[$i]);
            if (++$byte !== 256) {
                $number[$i] = \chr($byte);
                break;
            }
            $number[$i] = "\0";
            if ($i === 0) {
                $number = "\1" . $number;
            }
        }
        return $number;
    }
    /**
     * Converts a decimal number to a binary string.
     *
     * @param string $number The number to convert, positive or zero, only digits.
     *
     * @return string
     */
    private function toBinary(string $number) : string
    {
        $result = '';
        while ($number !== '0') {
            [$number, $remainder] = $this->divQR($number, '256');
            $result .= \chr((int) $remainder);
        }
        return \strrev($result);
    }
    /**
     * Returns the positive decimal representation of a binary number.
     *
     * @param string $bytes The bytes representing the number.
     *
     * @return string
     */
    private function toDecimal(string $bytes) : string
    {
        $result = '0';
        $power = '1';
        for ($i = \strlen($bytes) - 1; $i >= 0; $i--) {
            $index = \ord($bytes[$i]);
            if ($index !== 0) {
                $result = $this->add($result, $index === 1 ? $power : $this->mul($power, (string) $index));
            }
            if ($i !== 0) {
                $power = $this->mul($power, '256');
            }
        }
        return $result;
    }
}

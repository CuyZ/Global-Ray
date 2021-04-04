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
namespace RayGlobalScoped\Ramsey\Uuid\Converter\Number;

use RayGlobalScoped\Ramsey\Uuid\Converter\NumberConverterInterface;
use RayGlobalScoped\Ramsey\Uuid\Math\CalculatorInterface;
use RayGlobalScoped\Ramsey\Uuid\Type\Integer as IntegerObject;
/**
 * GenericNumberConverter uses the provided calculate to convert decimal
 * numbers to and from hexadecimal values
 *
 * @psalm-immutable
 */
class GenericNumberConverter implements \RayGlobalScoped\Ramsey\Uuid\Converter\NumberConverterInterface
{
    /**
     * @var CalculatorInterface
     */
    private $calculator;
    public function __construct(\RayGlobalScoped\Ramsey\Uuid\Math\CalculatorInterface $calculator)
    {
        $this->calculator = $calculator;
    }
    /**
     * @inheritDoc
     * @psalm-pure
     * @psalm-return numeric-string
     * @psalm-suppress MoreSpecificReturnType we know that the retrieved `string` is never empty
     * @psalm-suppress LessSpecificReturnStatement we know that the retrieved `string` is never empty
     */
    public function fromHex(string $hex) : string
    {
        return $this->calculator->fromBase($hex, 16)->toString();
    }
    /**
     * @inheritDoc
     * @psalm-pure
     * @psalm-return non-empty-string
     * @psalm-suppress MoreSpecificReturnType we know that the retrieved `string` is never empty
     * @psalm-suppress LessSpecificReturnStatement we know that the retrieved `string` is never empty
     */
    public function toHex(string $number) : string
    {
        return $this->calculator->toBase(new \RayGlobalScoped\Ramsey\Uuid\Type\Integer($number), 16);
    }
}

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
namespace RayGlobalScoped\Ramsey\Uuid\Converter\Time;

use RayGlobalScoped\Ramsey\Uuid\Converter\TimeConverterInterface;
use RayGlobalScoped\Ramsey\Uuid\Math\BrickMathCalculator;
use RayGlobalScoped\Ramsey\Uuid\Type\Hexadecimal;
use RayGlobalScoped\Ramsey\Uuid\Type\Time;
/**
 * Previously used to integrate moontoast/math as a bignum arithmetic library,
 * BigNumberTimeConverter is deprecated in favor of GenericTimeConverter
 *
 * @deprecated Transition to {@see GenericTimeConverter}.
 *
 * @psalm-immutable
 */
class BigNumberTimeConverter implements \RayGlobalScoped\Ramsey\Uuid\Converter\TimeConverterInterface
{
    /**
     * @var TimeConverterInterface
     */
    private $converter;
    public function __construct()
    {
        $this->converter = new \RayGlobalScoped\Ramsey\Uuid\Converter\Time\GenericTimeConverter(new \RayGlobalScoped\Ramsey\Uuid\Math\BrickMathCalculator());
    }
    public function calculateTime(string $seconds, string $microseconds) : \RayGlobalScoped\Ramsey\Uuid\Type\Hexadecimal
    {
        return $this->converter->calculateTime($seconds, $microseconds);
    }
    public function convertTime(\RayGlobalScoped\Ramsey\Uuid\Type\Hexadecimal $uuidTimestamp) : \RayGlobalScoped\Ramsey\Uuid\Type\Time
    {
        return $this->converter->convertTime($uuidTimestamp);
    }
}

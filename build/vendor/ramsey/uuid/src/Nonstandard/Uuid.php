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
namespace RayGlobalScoped\Ramsey\Uuid\Nonstandard;

use RayGlobalScoped\Ramsey\Uuid\Codec\CodecInterface;
use RayGlobalScoped\Ramsey\Uuid\Converter\NumberConverterInterface;
use RayGlobalScoped\Ramsey\Uuid\Converter\TimeConverterInterface;
use RayGlobalScoped\Ramsey\Uuid\Uuid as BaseUuid;
use RayGlobalScoped\Ramsey\Uuid\UuidInterface;
/**
 * Nonstandard\Uuid is a UUID that doesn't conform to RFC 4122
 *
 * @psalm-immutable
 */
final class Uuid extends \RayGlobalScoped\Ramsey\Uuid\Uuid implements \RayGlobalScoped\Ramsey\Uuid\UuidInterface
{
    public function __construct(\RayGlobalScoped\Ramsey\Uuid\Nonstandard\Fields $fields, \RayGlobalScoped\Ramsey\Uuid\Converter\NumberConverterInterface $numberConverter, \RayGlobalScoped\Ramsey\Uuid\Codec\CodecInterface $codec, \RayGlobalScoped\Ramsey\Uuid\Converter\TimeConverterInterface $timeConverter)
    {
        parent::__construct($fields, $numberConverter, $codec, $timeConverter);
    }
}

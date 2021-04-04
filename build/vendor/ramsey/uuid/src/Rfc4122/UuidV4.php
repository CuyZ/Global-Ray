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
namespace RayGlobalScoped\Ramsey\Uuid\Rfc4122;

use RayGlobalScoped\Ramsey\Uuid\Codec\CodecInterface;
use RayGlobalScoped\Ramsey\Uuid\Converter\NumberConverterInterface;
use RayGlobalScoped\Ramsey\Uuid\Converter\TimeConverterInterface;
use RayGlobalScoped\Ramsey\Uuid\Exception\InvalidArgumentException;
use RayGlobalScoped\Ramsey\Uuid\Rfc4122\FieldsInterface as Rfc4122FieldsInterface;
use RayGlobalScoped\Ramsey\Uuid\Uuid;
/**
 * Random, or version 4, UUIDs are randomly or pseudo-randomly generated 128-bit
 * integers
 *
 * @psalm-immutable
 */
final class UuidV4 extends \RayGlobalScoped\Ramsey\Uuid\Uuid implements \RayGlobalScoped\Ramsey\Uuid\Rfc4122\UuidInterface
{
    /**
     * Creates a version 4 (random) UUID
     *
     * @param Rfc4122FieldsInterface $fields The fields from which to construct a UUID
     * @param NumberConverterInterface $numberConverter The number converter to use
     *     for converting hex values to/from integers
     * @param CodecInterface $codec The codec to use when encoding or decoding
     *     UUID strings
     * @param TimeConverterInterface $timeConverter The time converter to use
     *     for converting timestamps extracted from a UUID to unix timestamps
     */
    public function __construct(\RayGlobalScoped\Ramsey\Uuid\Rfc4122\FieldsInterface $fields, \RayGlobalScoped\Ramsey\Uuid\Converter\NumberConverterInterface $numberConverter, \RayGlobalScoped\Ramsey\Uuid\Codec\CodecInterface $codec, \RayGlobalScoped\Ramsey\Uuid\Converter\TimeConverterInterface $timeConverter)
    {
        if ($fields->getVersion() !== \RayGlobalScoped\Ramsey\Uuid\Uuid::UUID_TYPE_RANDOM) {
            throw new \RayGlobalScoped\Ramsey\Uuid\Exception\InvalidArgumentException('Fields used to create a UuidV4 must represent a ' . 'version 4 (random) UUID');
        }
        parent::__construct($fields, $numberConverter, $codec, $timeConverter);
    }
}

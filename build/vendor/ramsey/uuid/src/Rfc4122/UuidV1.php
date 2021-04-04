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

use DateTimeImmutable;
use DateTimeInterface;
use RayGlobalScoped\Ramsey\Uuid\Codec\CodecInterface;
use RayGlobalScoped\Ramsey\Uuid\Converter\NumberConverterInterface;
use RayGlobalScoped\Ramsey\Uuid\Converter\TimeConverterInterface;
use RayGlobalScoped\Ramsey\Uuid\Exception\DateTimeException;
use RayGlobalScoped\Ramsey\Uuid\Exception\InvalidArgumentException;
use RayGlobalScoped\Ramsey\Uuid\Rfc4122\FieldsInterface as Rfc4122FieldsInterface;
use RayGlobalScoped\Ramsey\Uuid\Uuid;
use Throwable;
use function str_pad;
use const STR_PAD_LEFT;
/**
 * Time-based, or version 1, UUIDs include timestamp, clock sequence, and node
 * values that are combined into a 128-bit unsigned integer
 *
 * @psalm-immutable
 */
final class UuidV1 extends \RayGlobalScoped\Ramsey\Uuid\Uuid implements \RayGlobalScoped\Ramsey\Uuid\Rfc4122\UuidInterface
{
    /**
     * Creates a version 1 (time-based) UUID
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
        if ($fields->getVersion() !== \RayGlobalScoped\Ramsey\Uuid\Uuid::UUID_TYPE_TIME) {
            throw new \RayGlobalScoped\Ramsey\Uuid\Exception\InvalidArgumentException('Fields used to create a UuidV1 must represent a ' . 'version 1 (time-based) UUID');
        }
        parent::__construct($fields, $numberConverter, $codec, $timeConverter);
    }
    /**
     * Returns a DateTimeInterface object representing the timestamp associated
     * with the UUID
     *
     * The timestamp value is only meaningful in a time-based UUID, which
     * has version type 1.
     *
     * @return DateTimeImmutable A PHP DateTimeImmutable instance representing
     *     the timestamp of a version 1 UUID
     */
    public function getDateTime() : \DateTimeInterface
    {
        $time = $this->timeConverter->convertTime($this->fields->getTimestamp());
        try {
            return new \DateTimeImmutable('@' . $time->getSeconds()->toString() . '.' . \str_pad($time->getMicroseconds()->toString(), 6, '0', \STR_PAD_LEFT));
        } catch (\Throwable $e) {
            throw new \RayGlobalScoped\Ramsey\Uuid\Exception\DateTimeException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}

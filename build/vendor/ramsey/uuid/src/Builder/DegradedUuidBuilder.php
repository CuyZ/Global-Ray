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
namespace RayGlobalScoped\Ramsey\Uuid\Builder;

use RayGlobalScoped\Ramsey\Uuid\Codec\CodecInterface;
use RayGlobalScoped\Ramsey\Uuid\Converter\NumberConverterInterface;
use RayGlobalScoped\Ramsey\Uuid\Converter\Time\DegradedTimeConverter;
use RayGlobalScoped\Ramsey\Uuid\Converter\TimeConverterInterface;
use RayGlobalScoped\Ramsey\Uuid\DegradedUuid;
use RayGlobalScoped\Ramsey\Uuid\Rfc4122\Fields as Rfc4122Fields;
use RayGlobalScoped\Ramsey\Uuid\UuidInterface;
/**
 * @deprecated DegradedUuid instances are no longer necessary to support 32-bit
 *     systems. Transition to {@see DefaultUuidBuilder}.
 *
 * @psalm-immutable
 */
class DegradedUuidBuilder implements \RayGlobalScoped\Ramsey\Uuid\Builder\UuidBuilderInterface
{
    /**
     * @var NumberConverterInterface
     */
    private $numberConverter;
    /**
     * @var TimeConverterInterface
     */
    private $timeConverter;
    /**
     * @param NumberConverterInterface $numberConverter The number converter to
     *     use when constructing the DegradedUuid
     * @param TimeConverterInterface|null $timeConverter The time converter to use
     *     for converting timestamps extracted from a UUID to Unix timestamps
     */
    public function __construct(\RayGlobalScoped\Ramsey\Uuid\Converter\NumberConverterInterface $numberConverter, ?\RayGlobalScoped\Ramsey\Uuid\Converter\TimeConverterInterface $timeConverter = null)
    {
        $this->numberConverter = $numberConverter;
        $this->timeConverter = $timeConverter ?: new \RayGlobalScoped\Ramsey\Uuid\Converter\Time\DegradedTimeConverter();
    }
    /**
     * Builds and returns a DegradedUuid
     *
     * @param CodecInterface $codec The codec to use for building this DegradedUuid instance
     * @param string $bytes The byte string from which to construct a UUID
     *
     * @return DegradedUuid The DegradedUuidBuild returns an instance of Ramsey\Uuid\DegradedUuid
     *
     * @psalm-pure
     */
    public function build(\RayGlobalScoped\Ramsey\Uuid\Codec\CodecInterface $codec, string $bytes) : \RayGlobalScoped\Ramsey\Uuid\UuidInterface
    {
        return new \RayGlobalScoped\Ramsey\Uuid\DegradedUuid(new \RayGlobalScoped\Ramsey\Uuid\Rfc4122\Fields($bytes), $this->numberConverter, $codec, $this->timeConverter);
    }
}

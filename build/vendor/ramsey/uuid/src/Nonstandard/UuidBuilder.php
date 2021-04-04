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

use RayGlobalScoped\Ramsey\Uuid\Builder\UuidBuilderInterface;
use RayGlobalScoped\Ramsey\Uuid\Codec\CodecInterface;
use RayGlobalScoped\Ramsey\Uuid\Converter\NumberConverterInterface;
use RayGlobalScoped\Ramsey\Uuid\Converter\TimeConverterInterface;
use RayGlobalScoped\Ramsey\Uuid\Exception\UnableToBuildUuidException;
use RayGlobalScoped\Ramsey\Uuid\UuidInterface;
use Throwable;
/**
 * Nonstandard\UuidBuilder builds instances of Nonstandard\Uuid
 *
 * @psalm-immutable
 */
class UuidBuilder implements \RayGlobalScoped\Ramsey\Uuid\Builder\UuidBuilderInterface
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
     *     use when constructing the Nonstandard\Uuid
     * @param TimeConverterInterface $timeConverter The time converter to use
     *     for converting timestamps extracted from a UUID to Unix timestamps
     */
    public function __construct(\RayGlobalScoped\Ramsey\Uuid\Converter\NumberConverterInterface $numberConverter, \RayGlobalScoped\Ramsey\Uuid\Converter\TimeConverterInterface $timeConverter)
    {
        $this->numberConverter = $numberConverter;
        $this->timeConverter = $timeConverter;
    }
    /**
     * Builds and returns a Nonstandard\Uuid
     *
     * @param CodecInterface $codec The codec to use for building this instance
     * @param string $bytes The byte string from which to construct a UUID
     *
     * @return Uuid The Nonstandard\UuidBuilder returns an instance of
     *     Nonstandard\Uuid
     *
     * @psalm-pure
     */
    public function build(\RayGlobalScoped\Ramsey\Uuid\Codec\CodecInterface $codec, string $bytes) : \RayGlobalScoped\Ramsey\Uuid\UuidInterface
    {
        try {
            return new \RayGlobalScoped\Ramsey\Uuid\Nonstandard\Uuid($this->buildFields($bytes), $this->numberConverter, $codec, $this->timeConverter);
        } catch (\Throwable $e) {
            throw new \RayGlobalScoped\Ramsey\Uuid\Exception\UnableToBuildUuidException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
    /**
     * Proxy method to allow injecting a mock, for testing
     */
    protected function buildFields(string $bytes) : \RayGlobalScoped\Ramsey\Uuid\Nonstandard\Fields
    {
        return new \RayGlobalScoped\Ramsey\Uuid\Nonstandard\Fields($bytes);
    }
}

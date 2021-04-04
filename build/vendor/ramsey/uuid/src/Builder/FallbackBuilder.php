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
use RayGlobalScoped\Ramsey\Uuid\Exception\BuilderNotFoundException;
use RayGlobalScoped\Ramsey\Uuid\Exception\UnableToBuildUuidException;
use RayGlobalScoped\Ramsey\Uuid\UuidInterface;
/**
 * FallbackBuilder builds a UUID by stepping through a list of UUID builders
 * until a UUID can be constructed without exceptions
 *
 * @psalm-immutable
 */
class FallbackBuilder implements \RayGlobalScoped\Ramsey\Uuid\Builder\UuidBuilderInterface
{
    /**
     * @var BuilderCollection
     */
    private $builders;
    /**
     * @param BuilderCollection $builders An array of UUID builders
     */
    public function __construct(\RayGlobalScoped\Ramsey\Uuid\Builder\BuilderCollection $builders)
    {
        $this->builders = $builders;
    }
    /**
     * Builds and returns a UuidInterface instance using the first builder that
     * succeeds
     *
     * @param CodecInterface $codec The codec to use for building this instance
     * @param string $bytes The byte string from which to construct a UUID
     *
     * @return UuidInterface an instance of a UUID object
     *
     * @psalm-pure
     */
    public function build(\RayGlobalScoped\Ramsey\Uuid\Codec\CodecInterface $codec, string $bytes) : \RayGlobalScoped\Ramsey\Uuid\UuidInterface
    {
        $lastBuilderException = null;
        /** @var UuidBuilderInterface $builder */
        foreach ($this->builders as $builder) {
            try {
                return $builder->build($codec, $bytes);
            } catch (\RayGlobalScoped\Ramsey\Uuid\Exception\UnableToBuildUuidException $exception) {
                $lastBuilderException = $exception;
                continue;
            }
        }
        throw new \RayGlobalScoped\Ramsey\Uuid\Exception\BuilderNotFoundException('Could not find a suitable builder for the provided codec and fields', 0, $lastBuilderException);
    }
}

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

use RayGlobalScoped\Ramsey\Collection\AbstractCollection;
use RayGlobalScoped\Ramsey\Collection\CollectionInterface;
use RayGlobalScoped\Ramsey\Uuid\Converter\Number\GenericNumberConverter;
use RayGlobalScoped\Ramsey\Uuid\Converter\Time\GenericTimeConverter;
use RayGlobalScoped\Ramsey\Uuid\Converter\Time\PhpTimeConverter;
use RayGlobalScoped\Ramsey\Uuid\Guid\GuidBuilder;
use RayGlobalScoped\Ramsey\Uuid\Math\BrickMathCalculator;
use RayGlobalScoped\Ramsey\Uuid\Nonstandard\UuidBuilder as NonstandardUuidBuilder;
use RayGlobalScoped\Ramsey\Uuid\Rfc4122\UuidBuilder as Rfc4122UuidBuilder;
use Traversable;
/**
 * A collection of UuidBuilderInterface objects
 */
class BuilderCollection extends \RayGlobalScoped\Ramsey\Collection\AbstractCollection implements \RayGlobalScoped\Ramsey\Collection\CollectionInterface
{
    public function getType() : string
    {
        return \RayGlobalScoped\Ramsey\Uuid\Builder\UuidBuilderInterface::class;
    }
    /**
     * @psalm-mutation-free
     * @psalm-suppress ImpureMethodCall
     * @psalm-suppress InvalidTemplateParam
     */
    public function getIterator() : \Traversable
    {
        return parent::getIterator();
    }
    /**
     * Re-constructs the object from its serialized form
     *
     * @param string $serialized The serialized PHP string to unserialize into
     *     a UuidInterface instance
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function unserialize($serialized) : void
    {
        /** @var mixed[] $data */
        $data = \unserialize($serialized, ['allowed_classes' => [\RayGlobalScoped\Ramsey\Uuid\Math\BrickMathCalculator::class, \RayGlobalScoped\Ramsey\Uuid\Converter\Number\GenericNumberConverter::class, \RayGlobalScoped\Ramsey\Uuid\Converter\Time\GenericTimeConverter::class, \RayGlobalScoped\Ramsey\Uuid\Guid\GuidBuilder::class, \RayGlobalScoped\Ramsey\Uuid\Nonstandard\UuidBuilder::class, \RayGlobalScoped\Ramsey\Uuid\Converter\Time\PhpTimeConverter::class, \RayGlobalScoped\Ramsey\Uuid\Rfc4122\UuidBuilder::class]]);
        $this->data = $data;
    }
}

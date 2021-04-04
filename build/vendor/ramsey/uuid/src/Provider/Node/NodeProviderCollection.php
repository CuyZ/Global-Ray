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
namespace RayGlobalScoped\Ramsey\Uuid\Provider\Node;

use RayGlobalScoped\Ramsey\Collection\AbstractCollection;
use RayGlobalScoped\Ramsey\Collection\CollectionInterface;
use RayGlobalScoped\Ramsey\Uuid\Provider\NodeProviderInterface;
use RayGlobalScoped\Ramsey\Uuid\Type\Hexadecimal;
/**
 * A collection of NodeProviderInterface objects
 */
class NodeProviderCollection extends \RayGlobalScoped\Ramsey\Collection\AbstractCollection implements \RayGlobalScoped\Ramsey\Collection\CollectionInterface
{
    public function getType() : string
    {
        return \RayGlobalScoped\Ramsey\Uuid\Provider\NodeProviderInterface::class;
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
        $data = \unserialize($serialized, ['allowed_classes' => [\RayGlobalScoped\Ramsey\Uuid\Type\Hexadecimal::class, \RayGlobalScoped\Ramsey\Uuid\Provider\Node\RandomNodeProvider::class, \RayGlobalScoped\Ramsey\Uuid\Provider\Node\StaticNodeProvider::class, \RayGlobalScoped\Ramsey\Uuid\Provider\Node\SystemNodeProvider::class]]);
        $this->data = $data;
    }
}

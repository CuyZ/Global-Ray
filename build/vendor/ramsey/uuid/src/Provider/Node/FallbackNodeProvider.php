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

use RayGlobalScoped\Ramsey\Uuid\Exception\NodeException;
use RayGlobalScoped\Ramsey\Uuid\Provider\NodeProviderInterface;
use RayGlobalScoped\Ramsey\Uuid\Type\Hexadecimal;
/**
 * FallbackNodeProvider retrieves the system node ID by stepping through a list
 * of providers until a node ID can be obtained
 */
class FallbackNodeProvider implements \RayGlobalScoped\Ramsey\Uuid\Provider\NodeProviderInterface
{
    /**
     * @var NodeProviderCollection
     */
    private $nodeProviders;
    /**
     * @param NodeProviderCollection $providers Array of node providers
     */
    public function __construct(\RayGlobalScoped\Ramsey\Uuid\Provider\Node\NodeProviderCollection $providers)
    {
        $this->nodeProviders = $providers;
    }
    public function getNode() : \RayGlobalScoped\Ramsey\Uuid\Type\Hexadecimal
    {
        $lastProviderException = null;
        /** @var NodeProviderInterface $provider */
        foreach ($this->nodeProviders as $provider) {
            try {
                return $provider->getNode();
            } catch (\RayGlobalScoped\Ramsey\Uuid\Exception\NodeException $exception) {
                $lastProviderException = $exception;
                continue;
            }
        }
        throw new \RayGlobalScoped\Ramsey\Uuid\Exception\NodeException('Unable to find a suitable node provider', 0, $lastProviderException);
    }
}

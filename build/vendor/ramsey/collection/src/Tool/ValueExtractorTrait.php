<?php

/**
 * This file is part of the ramsey/collection library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ben Ramsey <ben@benramsey.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
declare (strict_types=1);
namespace RayGlobalScoped\Ramsey\Collection\Tool;

use RayGlobalScoped\Ramsey\Collection\Exception\ValueExtractionException;
use function get_class;
use function method_exists;
use function property_exists;
use function sprintf;
/**
 * Provides functionality to extract the value of a property or method from an object.
 */
trait ValueExtractorTrait
{
    /**
     * Extracts the value of the given property or method from the object.
     *
     * @param mixed $object The object to extract the value from.
     * @param string $propertyOrMethod The property or method for which the
     *     value should be extracted.
     *
     * @return mixed the value extracted from the specified property or method.
     *
     * @throws ValueExtractionException if the method or property is not defined.
     */
    protected function extractValue($object, string $propertyOrMethod)
    {
        if (!\is_object($object)) {
            throw new \RayGlobalScoped\Ramsey\Collection\Exception\ValueExtractionException('Unable to extract a value from a non-object');
        }
        if (\property_exists($object, $propertyOrMethod)) {
            return $object->{$propertyOrMethod};
        }
        if (\method_exists($object, $propertyOrMethod)) {
            return $object->{$propertyOrMethod}();
        }
        throw new \RayGlobalScoped\Ramsey\Collection\Exception\ValueExtractionException(\sprintf('Method or property "%s" not defined in %s', $propertyOrMethod, \get_class($object)));
    }
}

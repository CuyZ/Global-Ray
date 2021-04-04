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
namespace RayGlobalScoped\Ramsey\Uuid\Generator;

use RayGlobalScoped\Ramsey\Uuid\Converter\NumberConverterInterface;
use RayGlobalScoped\Ramsey\Uuid\Exception\DceSecurityException;
use RayGlobalScoped\Ramsey\Uuid\Provider\DceSecurityProviderInterface;
use RayGlobalScoped\Ramsey\Uuid\Type\Hexadecimal;
use RayGlobalScoped\Ramsey\Uuid\Type\Integer as IntegerObject;
use RayGlobalScoped\Ramsey\Uuid\Uuid;
use function hex2bin;
use function in_array;
use function pack;
use function str_pad;
use function strlen;
use function substr_replace;
use const STR_PAD_LEFT;
/**
 * DceSecurityGenerator generates strings of binary data based on a local
 * domain, local identifier, node ID, clock sequence, and the current time
 */
class DceSecurityGenerator implements \RayGlobalScoped\Ramsey\Uuid\Generator\DceSecurityGeneratorInterface
{
    private const DOMAINS = [\RayGlobalScoped\Ramsey\Uuid\Uuid::DCE_DOMAIN_PERSON, \RayGlobalScoped\Ramsey\Uuid\Uuid::DCE_DOMAIN_GROUP, \RayGlobalScoped\Ramsey\Uuid\Uuid::DCE_DOMAIN_ORG];
    /**
     * Upper bounds for the clock sequence in DCE Security UUIDs.
     */
    private const CLOCK_SEQ_HIGH = 63;
    /**
     * Lower bounds for the clock sequence in DCE Security UUIDs.
     */
    private const CLOCK_SEQ_LOW = 0;
    /**
     * @var NumberConverterInterface
     */
    private $numberConverter;
    /**
     * @var TimeGeneratorInterface
     */
    private $timeGenerator;
    /**
     * @var DceSecurityProviderInterface
     */
    private $dceSecurityProvider;
    public function __construct(\RayGlobalScoped\Ramsey\Uuid\Converter\NumberConverterInterface $numberConverter, \RayGlobalScoped\Ramsey\Uuid\Generator\TimeGeneratorInterface $timeGenerator, \RayGlobalScoped\Ramsey\Uuid\Provider\DceSecurityProviderInterface $dceSecurityProvider)
    {
        $this->numberConverter = $numberConverter;
        $this->timeGenerator = $timeGenerator;
        $this->dceSecurityProvider = $dceSecurityProvider;
    }
    public function generate(int $localDomain, ?\RayGlobalScoped\Ramsey\Uuid\Type\Integer $localIdentifier = null, ?\RayGlobalScoped\Ramsey\Uuid\Type\Hexadecimal $node = null, ?int $clockSeq = null) : string
    {
        if (!\in_array($localDomain, self::DOMAINS)) {
            throw new \RayGlobalScoped\Ramsey\Uuid\Exception\DceSecurityException('Local domain must be a valid DCE Security domain');
        }
        if ($localIdentifier && $localIdentifier->isNegative()) {
            throw new \RayGlobalScoped\Ramsey\Uuid\Exception\DceSecurityException('Local identifier out of bounds; it must be a value between 0 and 4294967295');
        }
        if ($clockSeq > self::CLOCK_SEQ_HIGH || $clockSeq < self::CLOCK_SEQ_LOW) {
            throw new \RayGlobalScoped\Ramsey\Uuid\Exception\DceSecurityException('Clock sequence out of bounds; it must be a value between 0 and 63');
        }
        switch ($localDomain) {
            case \RayGlobalScoped\Ramsey\Uuid\Uuid::DCE_DOMAIN_ORG:
                if ($localIdentifier === null) {
                    throw new \RayGlobalScoped\Ramsey\Uuid\Exception\DceSecurityException('A local identifier must be provided for the org domain');
                }
                break;
            case \RayGlobalScoped\Ramsey\Uuid\Uuid::DCE_DOMAIN_PERSON:
                if ($localIdentifier === null) {
                    $localIdentifier = $this->dceSecurityProvider->getUid();
                }
                break;
            case \RayGlobalScoped\Ramsey\Uuid\Uuid::DCE_DOMAIN_GROUP:
            default:
                if ($localIdentifier === null) {
                    $localIdentifier = $this->dceSecurityProvider->getGid();
                }
                break;
        }
        $identifierHex = $this->numberConverter->toHex($localIdentifier->toString());
        // The maximum value for the local identifier is 0xffffffff, or
        // 4294967295. This is 8 hexadecimal digits, so if the length of
        // hexadecimal digits is greater than 8, we know the value is greater
        // than 0xffffffff.
        if (\strlen($identifierHex) > 8) {
            throw new \RayGlobalScoped\Ramsey\Uuid\Exception\DceSecurityException('Local identifier out of bounds; it must be a value between 0 and 4294967295');
        }
        $domainByte = \pack('n', $localDomain)[1];
        $identifierBytes = \hex2bin(\str_pad($identifierHex, 8, '0', \STR_PAD_LEFT));
        if ($node instanceof \RayGlobalScoped\Ramsey\Uuid\Type\Hexadecimal) {
            $node = $node->toString();
        }
        // Shift the clock sequence 8 bits to the left, so it matches 0x3f00.
        if ($clockSeq !== null) {
            $clockSeq = $clockSeq << 8;
        }
        /** @var string $bytes */
        $bytes = $this->timeGenerator->generate($node, $clockSeq);
        // Replace bytes in the time-based UUID with DCE Security values.
        $bytes = \substr_replace($bytes, $identifierBytes, 0, 4);
        $bytes = \substr_replace($bytes, $domainByte, 9, 1);
        return $bytes;
    }
}

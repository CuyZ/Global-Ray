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
namespace RayGlobalScoped\Ramsey\Uuid;

use DateTimeInterface;
use RayGlobalScoped\Ramsey\Uuid\Builder\UuidBuilderInterface;
use RayGlobalScoped\Ramsey\Uuid\Codec\CodecInterface;
use RayGlobalScoped\Ramsey\Uuid\Converter\NumberConverterInterface;
use RayGlobalScoped\Ramsey\Uuid\Converter\TimeConverterInterface;
use RayGlobalScoped\Ramsey\Uuid\Generator\DceSecurityGeneratorInterface;
use RayGlobalScoped\Ramsey\Uuid\Generator\DefaultTimeGenerator;
use RayGlobalScoped\Ramsey\Uuid\Generator\NameGeneratorInterface;
use RayGlobalScoped\Ramsey\Uuid\Generator\RandomGeneratorInterface;
use RayGlobalScoped\Ramsey\Uuid\Generator\TimeGeneratorInterface;
use RayGlobalScoped\Ramsey\Uuid\Lazy\LazyUuidFromString;
use RayGlobalScoped\Ramsey\Uuid\Provider\NodeProviderInterface;
use RayGlobalScoped\Ramsey\Uuid\Provider\Time\FixedTimeProvider;
use RayGlobalScoped\Ramsey\Uuid\Type\Hexadecimal;
use RayGlobalScoped\Ramsey\Uuid\Type\Integer as IntegerObject;
use RayGlobalScoped\Ramsey\Uuid\Type\Time;
use RayGlobalScoped\Ramsey\Uuid\Validator\ValidatorInterface;
use function bin2hex;
use function hex2bin;
use function pack;
use function str_pad;
use function strtolower;
use function substr;
use function substr_replace;
use function unpack;
use const STR_PAD_LEFT;
class UuidFactory implements \RayGlobalScoped\Ramsey\Uuid\UuidFactoryInterface
{
    /**
     * @var CodecInterface
     */
    private $codec;
    /**
     * @var DceSecurityGeneratorInterface
     */
    private $dceSecurityGenerator;
    /**
     * @var NameGeneratorInterface
     */
    private $nameGenerator;
    /**
     * @var NodeProviderInterface
     */
    private $nodeProvider;
    /**
     * @var NumberConverterInterface
     */
    private $numberConverter;
    /**
     * @var RandomGeneratorInterface
     */
    private $randomGenerator;
    /**
     * @var TimeConverterInterface
     */
    private $timeConverter;
    /**
     * @var TimeGeneratorInterface
     */
    private $timeGenerator;
    /**
     * @var UuidBuilderInterface
     */
    private $uuidBuilder;
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /** @var bool whether the feature set was provided from outside, or we can operate under "default" assumptions */
    private $isDefaultFeatureSet;
    /**
     * @param FeatureSet $features A set of available features in the current environment
     */
    public function __construct(?\RayGlobalScoped\Ramsey\Uuid\FeatureSet $features = null)
    {
        $this->isDefaultFeatureSet = $features === null;
        $features = $features ?: new \RayGlobalScoped\Ramsey\Uuid\FeatureSet();
        $this->codec = $features->getCodec();
        $this->dceSecurityGenerator = $features->getDceSecurityGenerator();
        $this->nameGenerator = $features->getNameGenerator();
        $this->nodeProvider = $features->getNodeProvider();
        $this->numberConverter = $features->getNumberConverter();
        $this->randomGenerator = $features->getRandomGenerator();
        $this->timeConverter = $features->getTimeConverter();
        $this->timeGenerator = $features->getTimeGenerator();
        $this->uuidBuilder = $features->getBuilder();
        $this->validator = $features->getValidator();
    }
    /**
     * Returns the codec used by this factory
     */
    public function getCodec() : \RayGlobalScoped\Ramsey\Uuid\Codec\CodecInterface
    {
        return $this->codec;
    }
    /**
     * Sets the codec to use for this factory
     *
     * @param CodecInterface $codec A UUID encoder-decoder
     */
    public function setCodec(\RayGlobalScoped\Ramsey\Uuid\Codec\CodecInterface $codec) : void
    {
        $this->isDefaultFeatureSet = \false;
        $this->codec = $codec;
    }
    /**
     * Returns the name generator used by this factory
     */
    public function getNameGenerator() : \RayGlobalScoped\Ramsey\Uuid\Generator\NameGeneratorInterface
    {
        return $this->nameGenerator;
    }
    /**
     * Sets the name generator to use for this factory
     *
     * @param NameGeneratorInterface $nameGenerator A generator to generate
     *     binary data, based on a namespace and name
     */
    public function setNameGenerator(\RayGlobalScoped\Ramsey\Uuid\Generator\NameGeneratorInterface $nameGenerator) : void
    {
        $this->isDefaultFeatureSet = \false;
        $this->nameGenerator = $nameGenerator;
    }
    /**
     * Returns the node provider used by this factory
     */
    public function getNodeProvider() : \RayGlobalScoped\Ramsey\Uuid\Provider\NodeProviderInterface
    {
        return $this->nodeProvider;
    }
    /**
     * Returns the random generator used by this factory
     */
    public function getRandomGenerator() : \RayGlobalScoped\Ramsey\Uuid\Generator\RandomGeneratorInterface
    {
        return $this->randomGenerator;
    }
    /**
     * Returns the time generator used by this factory
     */
    public function getTimeGenerator() : \RayGlobalScoped\Ramsey\Uuid\Generator\TimeGeneratorInterface
    {
        return $this->timeGenerator;
    }
    /**
     * Sets the time generator to use for this factory
     *
     * @param TimeGeneratorInterface $generator A generator to generate binary
     *     data, based on the time
     */
    public function setTimeGenerator(\RayGlobalScoped\Ramsey\Uuid\Generator\TimeGeneratorInterface $generator) : void
    {
        $this->isDefaultFeatureSet = \false;
        $this->timeGenerator = $generator;
    }
    /**
     * Returns the DCE Security generator used by this factory
     */
    public function getDceSecurityGenerator() : \RayGlobalScoped\Ramsey\Uuid\Generator\DceSecurityGeneratorInterface
    {
        return $this->dceSecurityGenerator;
    }
    /**
     * Sets the DCE Security generator to use for this factory
     *
     * @param DceSecurityGeneratorInterface $generator A generator to generate
     *     binary data, based on a local domain and local identifier
     */
    public function setDceSecurityGenerator(\RayGlobalScoped\Ramsey\Uuid\Generator\DceSecurityGeneratorInterface $generator) : void
    {
        $this->isDefaultFeatureSet = \false;
        $this->dceSecurityGenerator = $generator;
    }
    /**
     * Returns the number converter used by this factory
     */
    public function getNumberConverter() : \RayGlobalScoped\Ramsey\Uuid\Converter\NumberConverterInterface
    {
        return $this->numberConverter;
    }
    /**
     * Sets the random generator to use for this factory
     *
     * @param RandomGeneratorInterface $generator A generator to generate binary
     *     data, based on some random input
     */
    public function setRandomGenerator(\RayGlobalScoped\Ramsey\Uuid\Generator\RandomGeneratorInterface $generator) : void
    {
        $this->isDefaultFeatureSet = \false;
        $this->randomGenerator = $generator;
    }
    /**
     * Sets the number converter to use for this factory
     *
     * @param NumberConverterInterface $converter A converter to use for working
     *     with large integers (i.e. integers greater than PHP_INT_MAX)
     */
    public function setNumberConverter(\RayGlobalScoped\Ramsey\Uuid\Converter\NumberConverterInterface $converter) : void
    {
        $this->isDefaultFeatureSet = \false;
        $this->numberConverter = $converter;
    }
    /**
     * Returns the UUID builder used by this factory
     */
    public function getUuidBuilder() : \RayGlobalScoped\Ramsey\Uuid\Builder\UuidBuilderInterface
    {
        return $this->uuidBuilder;
    }
    /**
     * Sets the UUID builder to use for this factory
     *
     * @param UuidBuilderInterface $builder A builder for constructing instances
     *     of UuidInterface
     */
    public function setUuidBuilder(\RayGlobalScoped\Ramsey\Uuid\Builder\UuidBuilderInterface $builder) : void
    {
        $this->isDefaultFeatureSet = \false;
        $this->uuidBuilder = $builder;
    }
    /**
     * @psalm-mutation-free
     */
    public function getValidator() : \RayGlobalScoped\Ramsey\Uuid\Validator\ValidatorInterface
    {
        return $this->validator;
    }
    /**
     * Sets the validator to use for this factory
     *
     * @param ValidatorInterface $validator A validator to use for validating
     *     whether a string is a valid UUID
     */
    public function setValidator(\RayGlobalScoped\Ramsey\Uuid\Validator\ValidatorInterface $validator) : void
    {
        $this->isDefaultFeatureSet = \false;
        $this->validator = $validator;
    }
    /**
     * @psalm-pure
     */
    public function fromBytes(string $bytes) : \RayGlobalScoped\Ramsey\Uuid\UuidInterface
    {
        return $this->codec->decodeBytes($bytes);
    }
    /**
     * @psalm-pure
     */
    public function fromString(string $uuid) : \RayGlobalScoped\Ramsey\Uuid\UuidInterface
    {
        $uuid = \strtolower($uuid);
        return $this->codec->decode($uuid);
    }
    /**
     * @psalm-pure
     */
    public function fromInteger(string $integer) : \RayGlobalScoped\Ramsey\Uuid\UuidInterface
    {
        $hex = $this->numberConverter->toHex($integer);
        $hex = \str_pad($hex, 32, '0', \STR_PAD_LEFT);
        return $this->fromString($hex);
    }
    public function fromDateTime(\DateTimeInterface $dateTime, ?\RayGlobalScoped\Ramsey\Uuid\Type\Hexadecimal $node = null, ?int $clockSeq = null) : \RayGlobalScoped\Ramsey\Uuid\UuidInterface
    {
        $timeProvider = new \RayGlobalScoped\Ramsey\Uuid\Provider\Time\FixedTimeProvider(new \RayGlobalScoped\Ramsey\Uuid\Type\Time($dateTime->format('U'), $dateTime->format('u')));
        $timeGenerator = new \RayGlobalScoped\Ramsey\Uuid\Generator\DefaultTimeGenerator($this->nodeProvider, $this->timeConverter, $timeProvider);
        $nodeHex = $node ? $node->toString() : null;
        $bytes = $timeGenerator->generate($nodeHex, $clockSeq);
        return $this->uuidFromBytesAndVersion($bytes, 1);
    }
    /**
     * @inheritDoc
     */
    public function uuid1($node = null, ?int $clockSeq = null) : \RayGlobalScoped\Ramsey\Uuid\UuidInterface
    {
        $bytes = $this->timeGenerator->generate($node, $clockSeq);
        return $this->uuidFromBytesAndVersion($bytes, 1);
    }
    public function uuid2(int $localDomain, ?\RayGlobalScoped\Ramsey\Uuid\Type\Integer $localIdentifier = null, ?\RayGlobalScoped\Ramsey\Uuid\Type\Hexadecimal $node = null, ?int $clockSeq = null) : \RayGlobalScoped\Ramsey\Uuid\UuidInterface
    {
        $bytes = $this->dceSecurityGenerator->generate($localDomain, $localIdentifier, $node, $clockSeq);
        return $this->uuidFromBytesAndVersion($bytes, 2);
    }
    /**
     * @inheritDoc
     * @psalm-pure
     */
    public function uuid3($ns, string $name) : \RayGlobalScoped\Ramsey\Uuid\UuidInterface
    {
        return $this->uuidFromNsAndName($ns, $name, 3, 'md5');
    }
    public function uuid4() : \RayGlobalScoped\Ramsey\Uuid\UuidInterface
    {
        $bytes = $this->randomGenerator->generate(16);
        return $this->uuidFromBytesAndVersion($bytes, 4);
    }
    /**
     * @inheritDoc
     * @psalm-pure
     */
    public function uuid5($ns, string $name) : \RayGlobalScoped\Ramsey\Uuid\UuidInterface
    {
        return $this->uuidFromNsAndName($ns, $name, 5, 'sha1');
    }
    public function uuid6(?\RayGlobalScoped\Ramsey\Uuid\Type\Hexadecimal $node = null, ?int $clockSeq = null) : \RayGlobalScoped\Ramsey\Uuid\UuidInterface
    {
        $nodeHex = $node ? $node->toString() : null;
        $bytes = $this->timeGenerator->generate($nodeHex, $clockSeq);
        // Rearrange the bytes, according to the UUID version 6 specification.
        $v6 = $bytes[6] . $bytes[7] . $bytes[4] . $bytes[5] . $bytes[0] . $bytes[1] . $bytes[2] . $bytes[3];
        $v6 = \bin2hex($v6);
        // Drop the first four bits, while adding an empty four bits for the
        // version field. This allows us to reconstruct the correct time from
        // the bytes of this UUID.
        $v6Bytes = \hex2bin(\substr($v6, 1, 12) . '0' . \substr($v6, -3));
        $v6Bytes .= \substr($bytes, 8);
        return $this->uuidFromBytesAndVersion($v6Bytes, 6);
    }
    /**
     * Returns a Uuid created from the provided byte string
     *
     * Uses the configured builder and codec and the provided byte string to
     * construct a Uuid object.
     *
     * @param string $bytes The byte string from which to construct a UUID
     *
     * @return UuidInterface An instance of UuidInterface, created from the
     *     provided bytes
     *
     * @psalm-pure
     */
    public function uuid(string $bytes) : \RayGlobalScoped\Ramsey\Uuid\UuidInterface
    {
        return $this->uuidBuilder->build($this->codec, $bytes);
    }
    /**
     * Returns a version 3 or 5 namespaced Uuid
     *
     * @param string|UuidInterface $ns The namespace (must be a valid UUID)
     * @param string $name The name to hash together with the namespace
     * @param int $version The version of UUID to create (3 or 5)
     * @param string $hashAlgorithm The hashing algorithm to use when hashing
     *     together the namespace and name
     *
     * @return UuidInterface An instance of UuidInterface, created by hashing
     *     together the provided namespace and name
     *
     * @psalm-pure
     */
    private function uuidFromNsAndName($ns, string $name, int $version, string $hashAlgorithm) : \RayGlobalScoped\Ramsey\Uuid\UuidInterface
    {
        if (!$ns instanceof \RayGlobalScoped\Ramsey\Uuid\UuidInterface) {
            $ns = $this->fromString($ns);
        }
        $bytes = $this->nameGenerator->generate($ns, $name, $hashAlgorithm);
        return $this->uuidFromBytesAndVersion(\substr($bytes, 0, 16), $version);
    }
    /**
     * Returns an RFC 4122 variant Uuid, created from the provided bytes and version
     *
     * @param string $bytes The byte string to convert to a UUID
     * @param int $version The RFC 4122 version to apply to the UUID
     *
     * @return UuidInterface An instance of UuidInterface, created from the
     *     byte string and version
     *
     * @psalm-pure
     */
    private function uuidFromBytesAndVersion(string $bytes, int $version) : \RayGlobalScoped\Ramsey\Uuid\UuidInterface
    {
        $timeHi = (int) \unpack('n*', \substr($bytes, 6, 2))[1];
        $timeHiAndVersion = \pack('n*', \RayGlobalScoped\Ramsey\Uuid\BinaryUtils::applyVersion($timeHi, $version));
        $clockSeqHi = (int) \unpack('n*', \substr($bytes, 8, 2))[1];
        $clockSeqHiAndReserved = \pack('n*', \RayGlobalScoped\Ramsey\Uuid\BinaryUtils::applyVariant($clockSeqHi));
        $bytes = \substr_replace($bytes, $timeHiAndVersion, 6, 2);
        $bytes = \substr_replace($bytes, $clockSeqHiAndReserved, 8, 2);
        if ($this->isDefaultFeatureSet) {
            return \RayGlobalScoped\Ramsey\Uuid\Lazy\LazyUuidFromString::fromBytes($bytes);
        }
        return $this->uuid($bytes);
    }
}

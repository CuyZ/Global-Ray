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

use RayGlobalScoped\Ramsey\Uuid\Builder\BuilderCollection;
use RayGlobalScoped\Ramsey\Uuid\Builder\FallbackBuilder;
use RayGlobalScoped\Ramsey\Uuid\Builder\UuidBuilderInterface;
use RayGlobalScoped\Ramsey\Uuid\Codec\CodecInterface;
use RayGlobalScoped\Ramsey\Uuid\Codec\GuidStringCodec;
use RayGlobalScoped\Ramsey\Uuid\Codec\StringCodec;
use RayGlobalScoped\Ramsey\Uuid\Converter\Number\GenericNumberConverter;
use RayGlobalScoped\Ramsey\Uuid\Converter\NumberConverterInterface;
use RayGlobalScoped\Ramsey\Uuid\Converter\Time\GenericTimeConverter;
use RayGlobalScoped\Ramsey\Uuid\Converter\Time\PhpTimeConverter;
use RayGlobalScoped\Ramsey\Uuid\Converter\TimeConverterInterface;
use RayGlobalScoped\Ramsey\Uuid\Generator\DceSecurityGenerator;
use RayGlobalScoped\Ramsey\Uuid\Generator\DceSecurityGeneratorInterface;
use RayGlobalScoped\Ramsey\Uuid\Generator\NameGeneratorFactory;
use RayGlobalScoped\Ramsey\Uuid\Generator\NameGeneratorInterface;
use RayGlobalScoped\Ramsey\Uuid\Generator\PeclUuidNameGenerator;
use RayGlobalScoped\Ramsey\Uuid\Generator\PeclUuidRandomGenerator;
use RayGlobalScoped\Ramsey\Uuid\Generator\PeclUuidTimeGenerator;
use RayGlobalScoped\Ramsey\Uuid\Generator\RandomGeneratorFactory;
use RayGlobalScoped\Ramsey\Uuid\Generator\RandomGeneratorInterface;
use RayGlobalScoped\Ramsey\Uuid\Generator\TimeGeneratorFactory;
use RayGlobalScoped\Ramsey\Uuid\Generator\TimeGeneratorInterface;
use RayGlobalScoped\Ramsey\Uuid\Guid\GuidBuilder;
use RayGlobalScoped\Ramsey\Uuid\Math\BrickMathCalculator;
use RayGlobalScoped\Ramsey\Uuid\Math\CalculatorInterface;
use RayGlobalScoped\Ramsey\Uuid\Nonstandard\UuidBuilder as NonstandardUuidBuilder;
use RayGlobalScoped\Ramsey\Uuid\Provider\Dce\SystemDceSecurityProvider;
use RayGlobalScoped\Ramsey\Uuid\Provider\DceSecurityProviderInterface;
use RayGlobalScoped\Ramsey\Uuid\Provider\Node\FallbackNodeProvider;
use RayGlobalScoped\Ramsey\Uuid\Provider\Node\NodeProviderCollection;
use RayGlobalScoped\Ramsey\Uuid\Provider\Node\RandomNodeProvider;
use RayGlobalScoped\Ramsey\Uuid\Provider\Node\SystemNodeProvider;
use RayGlobalScoped\Ramsey\Uuid\Provider\NodeProviderInterface;
use RayGlobalScoped\Ramsey\Uuid\Provider\Time\SystemTimeProvider;
use RayGlobalScoped\Ramsey\Uuid\Provider\TimeProviderInterface;
use RayGlobalScoped\Ramsey\Uuid\Rfc4122\UuidBuilder as Rfc4122UuidBuilder;
use RayGlobalScoped\Ramsey\Uuid\Validator\GenericValidator;
use RayGlobalScoped\Ramsey\Uuid\Validator\ValidatorInterface;
use const PHP_INT_SIZE;
/**
 * FeatureSet detects and exposes available features in the current environment
 *
 * A feature set is used by UuidFactory to determine the available features and
 * capabilities of the environment.
 */
class FeatureSet
{
    /**
     * @var bool
     */
    private $disableBigNumber = \false;
    /**
     * @var bool
     */
    private $disable64Bit = \false;
    /**
     * @var bool
     */
    private $ignoreSystemNode = \false;
    /**
     * @var bool
     */
    private $enablePecl = \false;
    /**
     * @var UuidBuilderInterface
     */
    private $builder;
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
     * @var TimeConverterInterface
     */
    private $timeConverter;
    /**
     * @var RandomGeneratorInterface
     */
    private $randomGenerator;
    /**
     * @var TimeGeneratorInterface
     */
    private $timeGenerator;
    /**
     * @var TimeProviderInterface
     */
    private $timeProvider;
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var CalculatorInterface
     */
    private $calculator;
    /**
     * @param bool $useGuids True build UUIDs using the GuidStringCodec
     * @param bool $force32Bit True to force the use of 32-bit functionality
     *     (primarily for testing purposes)
     * @param bool $forceNoBigNumber True to disable the use of moontoast/math
     *     (primarily for testing purposes)
     * @param bool $ignoreSystemNode True to disable attempts to check for the
     *     system node ID (primarily for testing purposes)
     * @param bool $enablePecl True to enable the use of the PeclUuidTimeGenerator
     *     to generate version 1 UUIDs
     */
    public function __construct(bool $useGuids = \false, bool $force32Bit = \false, bool $forceNoBigNumber = \false, bool $ignoreSystemNode = \false, bool $enablePecl = \false)
    {
        $this->disableBigNumber = $forceNoBigNumber;
        $this->disable64Bit = $force32Bit;
        $this->ignoreSystemNode = $ignoreSystemNode;
        $this->enablePecl = $enablePecl;
        $this->setCalculator(new \RayGlobalScoped\Ramsey\Uuid\Math\BrickMathCalculator());
        $this->builder = $this->buildUuidBuilder($useGuids);
        $this->codec = $this->buildCodec($useGuids);
        $this->nodeProvider = $this->buildNodeProvider();
        $this->nameGenerator = $this->buildNameGenerator();
        $this->randomGenerator = $this->buildRandomGenerator();
        $this->setTimeProvider(new \RayGlobalScoped\Ramsey\Uuid\Provider\Time\SystemTimeProvider());
        $this->setDceSecurityProvider(new \RayGlobalScoped\Ramsey\Uuid\Provider\Dce\SystemDceSecurityProvider());
        $this->validator = new \RayGlobalScoped\Ramsey\Uuid\Validator\GenericValidator();
    }
    /**
     * Returns the builder configured for this environment
     */
    public function getBuilder() : \RayGlobalScoped\Ramsey\Uuid\Builder\UuidBuilderInterface
    {
        return $this->builder;
    }
    /**
     * Returns the calculator configured for this environment
     */
    public function getCalculator() : \RayGlobalScoped\Ramsey\Uuid\Math\CalculatorInterface
    {
        return $this->calculator;
    }
    /**
     * Returns the codec configured for this environment
     */
    public function getCodec() : \RayGlobalScoped\Ramsey\Uuid\Codec\CodecInterface
    {
        return $this->codec;
    }
    /**
     * Returns the DCE Security generator configured for this environment
     */
    public function getDceSecurityGenerator() : \RayGlobalScoped\Ramsey\Uuid\Generator\DceSecurityGeneratorInterface
    {
        return $this->dceSecurityGenerator;
    }
    /**
     * Returns the name generator configured for this environment
     */
    public function getNameGenerator() : \RayGlobalScoped\Ramsey\Uuid\Generator\NameGeneratorInterface
    {
        return $this->nameGenerator;
    }
    /**
     * Returns the node provider configured for this environment
     */
    public function getNodeProvider() : \RayGlobalScoped\Ramsey\Uuid\Provider\NodeProviderInterface
    {
        return $this->nodeProvider;
    }
    /**
     * Returns the number converter configured for this environment
     */
    public function getNumberConverter() : \RayGlobalScoped\Ramsey\Uuid\Converter\NumberConverterInterface
    {
        return $this->numberConverter;
    }
    /**
     * Returns the random generator configured for this environment
     */
    public function getRandomGenerator() : \RayGlobalScoped\Ramsey\Uuid\Generator\RandomGeneratorInterface
    {
        return $this->randomGenerator;
    }
    /**
     * Returns the time converter configured for this environment
     */
    public function getTimeConverter() : \RayGlobalScoped\Ramsey\Uuid\Converter\TimeConverterInterface
    {
        return $this->timeConverter;
    }
    /**
     * Returns the time generator configured for this environment
     */
    public function getTimeGenerator() : \RayGlobalScoped\Ramsey\Uuid\Generator\TimeGeneratorInterface
    {
        return $this->timeGenerator;
    }
    /**
     * Returns the validator configured for this environment
     */
    public function getValidator() : \RayGlobalScoped\Ramsey\Uuid\Validator\ValidatorInterface
    {
        return $this->validator;
    }
    /**
     * Sets the calculator to use in this environment
     */
    public function setCalculator(\RayGlobalScoped\Ramsey\Uuid\Math\CalculatorInterface $calculator) : void
    {
        $this->calculator = $calculator;
        $this->numberConverter = $this->buildNumberConverter($calculator);
        $this->timeConverter = $this->buildTimeConverter($calculator);
        if (isset($this->timeProvider)) {
            $this->timeGenerator = $this->buildTimeGenerator($this->timeProvider);
        }
    }
    /**
     * Sets the DCE Security provider to use in this environment
     */
    public function setDceSecurityProvider(\RayGlobalScoped\Ramsey\Uuid\Provider\DceSecurityProviderInterface $dceSecurityProvider) : void
    {
        $this->dceSecurityGenerator = $this->buildDceSecurityGenerator($dceSecurityProvider);
    }
    /**
     * Sets the node provider to use in this environment
     */
    public function setNodeProvider(\RayGlobalScoped\Ramsey\Uuid\Provider\NodeProviderInterface $nodeProvider) : void
    {
        $this->nodeProvider = $nodeProvider;
        $this->timeGenerator = $this->buildTimeGenerator($this->timeProvider);
    }
    /**
     * Sets the time provider to use in this environment
     */
    public function setTimeProvider(\RayGlobalScoped\Ramsey\Uuid\Provider\TimeProviderInterface $timeProvider) : void
    {
        $this->timeProvider = $timeProvider;
        $this->timeGenerator = $this->buildTimeGenerator($timeProvider);
    }
    /**
     * Set the validator to use in this environment
     */
    public function setValidator(\RayGlobalScoped\Ramsey\Uuid\Validator\ValidatorInterface $validator) : void
    {
        $this->validator = $validator;
    }
    /**
     * Returns a codec configured for this environment
     *
     * @param bool $useGuids Whether to build UUIDs using the GuidStringCodec
     */
    private function buildCodec(bool $useGuids = \false) : \RayGlobalScoped\Ramsey\Uuid\Codec\CodecInterface
    {
        if ($useGuids) {
            return new \RayGlobalScoped\Ramsey\Uuid\Codec\GuidStringCodec($this->builder);
        }
        return new \RayGlobalScoped\Ramsey\Uuid\Codec\StringCodec($this->builder);
    }
    /**
     * Returns a DCE Security generator configured for this environment
     */
    private function buildDceSecurityGenerator(\RayGlobalScoped\Ramsey\Uuid\Provider\DceSecurityProviderInterface $dceSecurityProvider) : \RayGlobalScoped\Ramsey\Uuid\Generator\DceSecurityGeneratorInterface
    {
        return new \RayGlobalScoped\Ramsey\Uuid\Generator\DceSecurityGenerator($this->numberConverter, $this->timeGenerator, $dceSecurityProvider);
    }
    /**
     * Returns a node provider configured for this environment
     */
    private function buildNodeProvider() : \RayGlobalScoped\Ramsey\Uuid\Provider\NodeProviderInterface
    {
        if ($this->ignoreSystemNode) {
            return new \RayGlobalScoped\Ramsey\Uuid\Provider\Node\RandomNodeProvider();
        }
        return new \RayGlobalScoped\Ramsey\Uuid\Provider\Node\FallbackNodeProvider(new \RayGlobalScoped\Ramsey\Uuid\Provider\Node\NodeProviderCollection([new \RayGlobalScoped\Ramsey\Uuid\Provider\Node\SystemNodeProvider(), new \RayGlobalScoped\Ramsey\Uuid\Provider\Node\RandomNodeProvider()]));
    }
    /**
     * Returns a number converter configured for this environment
     */
    private function buildNumberConverter(\RayGlobalScoped\Ramsey\Uuid\Math\CalculatorInterface $calculator) : \RayGlobalScoped\Ramsey\Uuid\Converter\NumberConverterInterface
    {
        return new \RayGlobalScoped\Ramsey\Uuid\Converter\Number\GenericNumberConverter($calculator);
    }
    /**
     * Returns a random generator configured for this environment
     */
    private function buildRandomGenerator() : \RayGlobalScoped\Ramsey\Uuid\Generator\RandomGeneratorInterface
    {
        if ($this->enablePecl) {
            return new \RayGlobalScoped\Ramsey\Uuid\Generator\PeclUuidRandomGenerator();
        }
        return (new \RayGlobalScoped\Ramsey\Uuid\Generator\RandomGeneratorFactory())->getGenerator();
    }
    /**
     * Returns a time generator configured for this environment
     *
     * @param TimeProviderInterface $timeProvider The time provider to use with
     *     the time generator
     */
    private function buildTimeGenerator(\RayGlobalScoped\Ramsey\Uuid\Provider\TimeProviderInterface $timeProvider) : \RayGlobalScoped\Ramsey\Uuid\Generator\TimeGeneratorInterface
    {
        if ($this->enablePecl) {
            return new \RayGlobalScoped\Ramsey\Uuid\Generator\PeclUuidTimeGenerator();
        }
        return (new \RayGlobalScoped\Ramsey\Uuid\Generator\TimeGeneratorFactory($this->nodeProvider, $this->timeConverter, $timeProvider))->getGenerator();
    }
    /**
     * Returns a name generator configured for this environment
     */
    private function buildNameGenerator() : \RayGlobalScoped\Ramsey\Uuid\Generator\NameGeneratorInterface
    {
        if ($this->enablePecl) {
            return new \RayGlobalScoped\Ramsey\Uuid\Generator\PeclUuidNameGenerator();
        }
        return (new \RayGlobalScoped\Ramsey\Uuid\Generator\NameGeneratorFactory())->getGenerator();
    }
    /**
     * Returns a time converter configured for this environment
     */
    private function buildTimeConverter(\RayGlobalScoped\Ramsey\Uuid\Math\CalculatorInterface $calculator) : \RayGlobalScoped\Ramsey\Uuid\Converter\TimeConverterInterface
    {
        $genericConverter = new \RayGlobalScoped\Ramsey\Uuid\Converter\Time\GenericTimeConverter($calculator);
        if ($this->is64BitSystem()) {
            return new \RayGlobalScoped\Ramsey\Uuid\Converter\Time\PhpTimeConverter($calculator, $genericConverter);
        }
        return $genericConverter;
    }
    /**
     * Returns a UUID builder configured for this environment
     *
     * @param bool $useGuids Whether to build UUIDs using the GuidStringCodec
     */
    private function buildUuidBuilder(bool $useGuids = \false) : \RayGlobalScoped\Ramsey\Uuid\Builder\UuidBuilderInterface
    {
        if ($useGuids) {
            return new \RayGlobalScoped\Ramsey\Uuid\Guid\GuidBuilder($this->numberConverter, $this->timeConverter);
        }
        /** @psalm-suppress ImpureArgument */
        return new \RayGlobalScoped\Ramsey\Uuid\Builder\FallbackBuilder(new \RayGlobalScoped\Ramsey\Uuid\Builder\BuilderCollection([new \RayGlobalScoped\Ramsey\Uuid\Rfc4122\UuidBuilder($this->numberConverter, $this->timeConverter), new \RayGlobalScoped\Ramsey\Uuid\Nonstandard\UuidBuilder($this->numberConverter, $this->timeConverter)]));
    }
    /**
     * Returns true if the PHP build is 64-bit
     */
    private function is64BitSystem() : bool
    {
        return \PHP_INT_SIZE === 8 && !$this->disable64Bit;
    }
}

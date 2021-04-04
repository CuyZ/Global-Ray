<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RayGlobalScoped\Symfony\Component\VarDumper\Cloner;

use RayGlobalScoped\Symfony\Component\VarDumper\Caster\Caster;
use RayGlobalScoped\Symfony\Component\VarDumper\Exception\ThrowingCasterException;
/**
 * AbstractCloner implements a generic caster mechanism for objects and resources.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
abstract class AbstractCloner implements \RayGlobalScoped\Symfony\Component\VarDumper\Cloner\ClonerInterface
{
    public static $defaultCasters = ['__PHP_Incomplete_Class' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\Caster', 'castPhpIncompleteClass'], 'RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\CutStub' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'castStub'], 'RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\CutArrayStub' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'castCutArray'], 'RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ConstStub' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'castStub'], 'RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\EnumStub' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'castEnum'], 'Closure' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castClosure'], 'Generator' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castGenerator'], 'ReflectionType' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castType'], 'ReflectionAttribute' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castAttribute'], 'ReflectionGenerator' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castReflectionGenerator'], 'ReflectionClass' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castClass'], 'ReflectionClassConstant' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castClassConstant'], 'ReflectionFunctionAbstract' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castFunctionAbstract'], 'ReflectionMethod' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castMethod'], 'ReflectionParameter' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castParameter'], 'ReflectionProperty' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castProperty'], 'ReflectionReference' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castReference'], 'ReflectionExtension' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castExtension'], 'ReflectionZendExtension' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castZendExtension'], 'RayGlobalScoped\\Doctrine\\Common\\Persistence\\ObjectManager' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], 'RayGlobalScoped\\Doctrine\\Common\\Proxy\\Proxy' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DoctrineCaster', 'castCommonProxy'], 'RayGlobalScoped\\Doctrine\\ORM\\Proxy\\Proxy' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DoctrineCaster', 'castOrmProxy'], 'RayGlobalScoped\\Doctrine\\ORM\\PersistentCollection' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DoctrineCaster', 'castPersistentCollection'], 'RayGlobalScoped\\Doctrine\\Persistence\\ObjectManager' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], 'DOMException' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castException'], 'DOMStringList' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castLength'], 'DOMNameList' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castLength'], 'DOMImplementation' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castImplementation'], 'DOMImplementationList' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castLength'], 'DOMNode' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castNode'], 'DOMNameSpaceNode' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castNameSpaceNode'], 'DOMDocument' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castDocument'], 'DOMNodeList' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castLength'], 'DOMNamedNodeMap' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castLength'], 'DOMCharacterData' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castCharacterData'], 'DOMAttr' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castAttr'], 'DOMElement' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castElement'], 'DOMText' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castText'], 'DOMTypeinfo' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castTypeinfo'], 'DOMDomError' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castDomError'], 'DOMLocator' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castLocator'], 'DOMDocumentType' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castDocumentType'], 'DOMNotation' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castNotation'], 'DOMEntity' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castEntity'], 'DOMProcessingInstruction' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castProcessingInstruction'], 'DOMXPath' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castXPath'], 'XMLReader' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\XmlReaderCaster', 'castXmlReader'], 'ErrorException' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ExceptionCaster', 'castErrorException'], 'Exception' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ExceptionCaster', 'castException'], 'Error' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ExceptionCaster', 'castError'], 'RayGlobalScoped\\Symfony\\Bridge\\Monolog\\Logger' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], 'RayGlobalScoped\\Symfony\\Component\\DependencyInjection\\ContainerInterface' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], 'RayGlobalScoped\\Symfony\\Component\\EventDispatcher\\EventDispatcherInterface' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], 'RayGlobalScoped\\Symfony\\Component\\HttpClient\\CurlHttpClient' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\SymfonyCaster', 'castHttpClient'], 'RayGlobalScoped\\Symfony\\Component\\HttpClient\\NativeHttpClient' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\SymfonyCaster', 'castHttpClient'], 'RayGlobalScoped\\Symfony\\Component\\HttpClient\\Response\\CurlResponse' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\SymfonyCaster', 'castHttpClientResponse'], 'RayGlobalScoped\\Symfony\\Component\\HttpClient\\Response\\NativeResponse' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\SymfonyCaster', 'castHttpClientResponse'], 'RayGlobalScoped\\Symfony\\Component\\HttpFoundation\\Request' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\SymfonyCaster', 'castRequest'], 'RayGlobalScoped\\Symfony\\Component\\VarDumper\\Exception\\ThrowingCasterException' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ExceptionCaster', 'castThrowingCasterException'], 'RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\TraceStub' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ExceptionCaster', 'castTraceStub'], 'RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\FrameStub' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ExceptionCaster', 'castFrameStub'], 'RayGlobalScoped\\Symfony\\Component\\VarDumper\\Cloner\\AbstractCloner' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], 'RayGlobalScoped\\Symfony\\Component\\ErrorHandler\\Exception\\SilencedErrorContext' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ExceptionCaster', 'castSilencedErrorContext'], 'RayGlobalScoped\\Imagine\\Image\\ImageInterface' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ImagineCaster', 'castImage'], 'RayGlobalScoped\\Ramsey\\Uuid\\UuidInterface' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\UuidCaster', 'castRamseyUuid'], 'RayGlobalScoped\\ProxyManager\\Proxy\\ProxyInterface' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ProxyManagerCaster', 'castProxy'], 'PHPUnit_Framework_MockObject_MockObject' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], 'RayGlobalScoped\\PHPUnit\\Framework\\MockObject\\MockObject' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], 'RayGlobalScoped\\PHPUnit\\Framework\\MockObject\\Stub' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], 'RayGlobalScoped\\Prophecy\\Prophecy\\ProphecySubjectInterface' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], 'RayGlobalScoped\\Mockery\\MockInterface' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], 'PDO' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\PdoCaster', 'castPdo'], 'PDOStatement' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\PdoCaster', 'castPdoStatement'], 'AMQPConnection' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\AmqpCaster', 'castConnection'], 'AMQPChannel' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\AmqpCaster', 'castChannel'], 'AMQPQueue' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\AmqpCaster', 'castQueue'], 'AMQPExchange' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\AmqpCaster', 'castExchange'], 'AMQPEnvelope' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\AmqpCaster', 'castEnvelope'], 'ArrayObject' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castArrayObject'], 'ArrayIterator' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castArrayIterator'], 'SplDoublyLinkedList' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castDoublyLinkedList'], 'SplFileInfo' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castFileInfo'], 'SplFileObject' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castFileObject'], 'SplHeap' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castHeap'], 'SplObjectStorage' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castObjectStorage'], 'SplPriorityQueue' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castHeap'], 'OuterIterator' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castOuterIterator'], 'WeakReference' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castWeakReference'], 'Redis' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\RedisCaster', 'castRedis'], 'RedisArray' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\RedisCaster', 'castRedisArray'], 'RedisCluster' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\RedisCaster', 'castRedisCluster'], 'DateTimeInterface' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DateCaster', 'castDateTime'], 'DateInterval' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DateCaster', 'castInterval'], 'DateTimeZone' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DateCaster', 'castTimeZone'], 'DatePeriod' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DateCaster', 'castPeriod'], 'GMP' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\GmpCaster', 'castGmp'], 'MessageFormatter' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\IntlCaster', 'castMessageFormatter'], 'NumberFormatter' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\IntlCaster', 'castNumberFormatter'], 'IntlTimeZone' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\IntlCaster', 'castIntlTimeZone'], 'IntlCalendar' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\IntlCaster', 'castIntlCalendar'], 'IntlDateFormatter' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\IntlCaster', 'castIntlDateFormatter'], 'Memcached' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\MemcachedCaster', 'castMemcached'], 'RayGlobalScoped\\Ds\\Collection' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DsCaster', 'castCollection'], 'RayGlobalScoped\\Ds\\Map' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DsCaster', 'castMap'], 'RayGlobalScoped\\Ds\\Pair' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DsCaster', 'castPair'], 'RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DsPairStub' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\DsCaster', 'castPairStub'], 'CurlHandle' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castCurl'], ':curl' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castCurl'], ':dba' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castDba'], ':dba persistent' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castDba'], 'GdImage' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castGd'], ':gd' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castGd'], ':mysql link' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castMysqlLink'], ':pgsql large object' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\PgSqlCaster', 'castLargeObject'], ':pgsql link' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\PgSqlCaster', 'castLink'], ':pgsql link persistent' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\PgSqlCaster', 'castLink'], ':pgsql result' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\PgSqlCaster', 'castResult'], ':process' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castProcess'], ':stream' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castStream'], 'OpenSSLCertificate' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castOpensslX509'], ':OpenSSL X.509' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castOpensslX509'], ':persistent stream' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castStream'], ':stream-context' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castStreamContext'], 'XmlParser' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\XmlResourceCaster', 'castXml'], ':xml' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\XmlResourceCaster', 'castXml'], 'RdKafka' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castRdKafka'], 'RayGlobalScoped\\RdKafka\\Conf' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castConf'], 'RayGlobalScoped\\RdKafka\\KafkaConsumer' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castKafkaConsumer'], 'RayGlobalScoped\\RdKafka\\Metadata\\Broker' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castBrokerMetadata'], 'RayGlobalScoped\\RdKafka\\Metadata\\Collection' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castCollectionMetadata'], 'RayGlobalScoped\\RdKafka\\Metadata\\Partition' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castPartitionMetadata'], 'RayGlobalScoped\\RdKafka\\Metadata\\Topic' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castTopicMetadata'], 'RayGlobalScoped\\RdKafka\\Message' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castMessage'], 'RayGlobalScoped\\RdKafka\\Topic' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castTopic'], 'RayGlobalScoped\\RdKafka\\TopicPartition' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castTopicPartition'], 'RayGlobalScoped\\RdKafka\\TopicConf' => ['RayGlobalScoped\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castTopicConf']];
    protected $maxItems = 2500;
    protected $maxString = -1;
    protected $minDepth = 1;
    private $casters = [];
    private $prevErrorHandler;
    private $classInfo = [];
    private $filter = 0;
    /**
     * @param callable[]|null $casters A map of casters
     *
     * @see addCasters
     */
    public function __construct(array $casters = null)
    {
        if (null === $casters) {
            $casters = static::$defaultCasters;
        }
        $this->addCasters($casters);
    }
    /**
     * Adds casters for resources and objects.
     *
     * Maps resources or objects types to a callback.
     * Types are in the key, with a callable caster for value.
     * Resource types are to be prefixed with a `:`,
     * see e.g. static::$defaultCasters.
     *
     * @param callable[] $casters A map of casters
     */
    public function addCasters(array $casters)
    {
        foreach ($casters as $type => $callback) {
            $this->casters[$type][] = $callback;
        }
    }
    /**
     * Sets the maximum number of items to clone past the minimum depth in nested structures.
     */
    public function setMaxItems(int $maxItems)
    {
        $this->maxItems = $maxItems;
    }
    /**
     * Sets the maximum cloned length for strings.
     */
    public function setMaxString(int $maxString)
    {
        $this->maxString = $maxString;
    }
    /**
     * Sets the minimum tree depth where we are guaranteed to clone all the items.  After this
     * depth is reached, only setMaxItems items will be cloned.
     */
    public function setMinDepth(int $minDepth)
    {
        $this->minDepth = $minDepth;
    }
    /**
     * Clones a PHP variable.
     *
     * @param mixed $var    Any PHP variable
     * @param int   $filter A bit field of Caster::EXCLUDE_* constants
     *
     * @return Data The cloned variable represented by a Data object
     */
    public function cloneVar($var, int $filter = 0)
    {
        $this->prevErrorHandler = \set_error_handler(function ($type, $msg, $file, $line, $context = []) {
            if (\E_RECOVERABLE_ERROR === $type || \E_USER_ERROR === $type) {
                // Cloner never dies
                throw new \ErrorException($msg, 0, $type, $file, $line);
            }
            if ($this->prevErrorHandler) {
                return ($this->prevErrorHandler)($type, $msg, $file, $line, $context);
            }
            return \false;
        });
        $this->filter = $filter;
        if ($gc = \gc_enabled()) {
            \gc_disable();
        }
        try {
            return new \RayGlobalScoped\Symfony\Component\VarDumper\Cloner\Data($this->doClone($var));
        } finally {
            if ($gc) {
                \gc_enable();
            }
            \restore_error_handler();
            $this->prevErrorHandler = null;
        }
    }
    /**
     * Effectively clones the PHP variable.
     *
     * @param mixed $var Any PHP variable
     *
     * @return array The cloned variable represented in an array
     */
    protected abstract function doClone($var);
    /**
     * Casts an object to an array representation.
     *
     * @param bool $isNested True if the object is nested in the dumped structure
     *
     * @return array The object casted as array
     */
    protected function castObject(\RayGlobalScoped\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested)
    {
        $obj = $stub->value;
        $class = $stub->class;
        if (\PHP_VERSION_ID < 80000 ? "\0" === ($class[15] ?? null) : \false !== \strpos($class, "@anonymous\0")) {
            $stub->class = \get_debug_type($obj);
        }
        if (isset($this->classInfo[$class])) {
            [$i, $parents, $hasDebugInfo, $fileInfo] = $this->classInfo[$class];
        } else {
            $i = 2;
            $parents = [$class];
            $hasDebugInfo = \method_exists($class, '__debugInfo');
            foreach (\class_parents($class) as $p) {
                $parents[] = $p;
                ++$i;
            }
            foreach (\class_implements($class) as $p) {
                $parents[] = $p;
                ++$i;
            }
            $parents[] = '*';
            $r = new \ReflectionClass($class);
            $fileInfo = $r->isInternal() || $r->isSubclassOf(\RayGlobalScoped\Symfony\Component\VarDumper\Cloner\Stub::class) ? [] : ['file' => $r->getFileName(), 'line' => $r->getStartLine()];
            $this->classInfo[$class] = [$i, $parents, $hasDebugInfo, $fileInfo];
        }
        $stub->attr += $fileInfo;
        $a = \RayGlobalScoped\Symfony\Component\VarDumper\Caster\Caster::castObject($obj, $class, $hasDebugInfo, $stub->class);
        try {
            while ($i--) {
                if (!empty($this->casters[$p = $parents[$i]])) {
                    foreach ($this->casters[$p] as $callback) {
                        $a = $callback($obj, $a, $stub, $isNested, $this->filter);
                    }
                }
            }
        } catch (\Exception $e) {
            $a = [(\RayGlobalScoped\Symfony\Component\VarDumper\Cloner\Stub::TYPE_OBJECT === $stub->type ? \RayGlobalScoped\Symfony\Component\VarDumper\Caster\Caster::PREFIX_VIRTUAL : '') . '⚠' => new \RayGlobalScoped\Symfony\Component\VarDumper\Exception\ThrowingCasterException($e)] + $a;
        }
        return $a;
    }
    /**
     * Casts a resource to an array representation.
     *
     * @param bool $isNested True if the object is nested in the dumped structure
     *
     * @return array The resource casted as array
     */
    protected function castResource(\RayGlobalScoped\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested)
    {
        $a = [];
        $res = $stub->value;
        $type = $stub->class;
        try {
            if (!empty($this->casters[':' . $type])) {
                foreach ($this->casters[':' . $type] as $callback) {
                    $a = $callback($res, $a, $stub, $isNested, $this->filter);
                }
            }
        } catch (\Exception $e) {
            $a = [(\RayGlobalScoped\Symfony\Component\VarDumper\Cloner\Stub::TYPE_OBJECT === $stub->type ? \RayGlobalScoped\Symfony\Component\VarDumper\Caster\Caster::PREFIX_VIRTUAL : '') . '⚠' => new \RayGlobalScoped\Symfony\Component\VarDumper\Exception\ThrowingCasterException($e)] + $a;
        }
        return $a;
    }
}

<?php

namespace RayGlobalScoped\Spatie\Ray;

use RayGlobalScoped\Carbon\Carbon;
use Closure;
use RayGlobalScoped\Composer\InstalledVersions;
use Exception;
use RayGlobalScoped\Ramsey\Uuid\Uuid;
use RayGlobalScoped\Spatie\Backtrace\Backtrace;
use RayGlobalScoped\Spatie\LaravelRay\Ray as LaravelRay;
use RayGlobalScoped\Spatie\Macroable\Macroable;
use RayGlobalScoped\Spatie\Ray\Concerns\RayColors;
use RayGlobalScoped\Spatie\Ray\Concerns\RaySizes;
use RayGlobalScoped\Spatie\Ray\Origin\DefaultOriginFactory;
use RayGlobalScoped\Spatie\Ray\Payloads\CallerPayload;
use RayGlobalScoped\Spatie\Ray\Payloads\CarbonPayload;
use RayGlobalScoped\Spatie\Ray\Payloads\ClearAllPayload;
use RayGlobalScoped\Spatie\Ray\Payloads\ColorPayload;
use RayGlobalScoped\Spatie\Ray\Payloads\CreateLockPayload;
use RayGlobalScoped\Spatie\Ray\Payloads\CustomPayload;
use RayGlobalScoped\Spatie\Ray\Payloads\DecodedJsonPayload;
use RayGlobalScoped\Spatie\Ray\Payloads\ExceptionPayload;
use RayGlobalScoped\Spatie\Ray\Payloads\FileContentsPayload;
use RayGlobalScoped\Spatie\Ray\Payloads\HideAppPayload;
use RayGlobalScoped\Spatie\Ray\Payloads\HidePayload;
use RayGlobalScoped\Spatie\Ray\Payloads\HtmlPayload;
use RayGlobalScoped\Spatie\Ray\Payloads\ImagePayload;
use RayGlobalScoped\Spatie\Ray\Payloads\JsonStringPayload;
use RayGlobalScoped\Spatie\Ray\Payloads\LogPayload;
use RayGlobalScoped\Spatie\Ray\Payloads\MeasurePayload;
use RayGlobalScoped\Spatie\Ray\Payloads\NewScreenPayload;
use RayGlobalScoped\Spatie\Ray\Payloads\NotifyPayload;
use RayGlobalScoped\Spatie\Ray\Payloads\RemovePayload;
use RayGlobalScoped\Spatie\Ray\Payloads\ShowAppPayload;
use RayGlobalScoped\Spatie\Ray\Payloads\SizePayload;
use RayGlobalScoped\Spatie\Ray\Payloads\TablePayload;
use RayGlobalScoped\Spatie\Ray\Payloads\TracePayload;
use RayGlobalScoped\Spatie\Ray\Payloads\XmlPayload;
use RayGlobalScoped\Spatie\Ray\Settings\Settings;
use RayGlobalScoped\Spatie\Ray\Settings\SettingsFactory;
use RayGlobalScoped\Spatie\Ray\Support\Counters;
use RayGlobalScoped\Symfony\Component\Stopwatch\Stopwatch;
class Ray
{
    use RayColors;
    use RaySizes;
    use Macroable;
    /** @var \Spatie\Ray\Settings\Settings */
    public $settings;
    /** @var \Spatie\Ray\Client */
    protected static $client;
    /** @var \Spatie\Ray\Support\Counters */
    public static $counters;
    /** @var string */
    public static $fakeUuid;
    /** @var string */
    public $uuid = '';
    /** @var \Symfony\Component\Stopwatch\Stopwatch[] */
    public static $stopWatches = [];
    /** @var bool|null */
    public static $enabled = null;
    public static function create(\RayGlobalScoped\Spatie\Ray\Client $client = null, string $uuid = null) : self
    {
        $settings = \RayGlobalScoped\Spatie\Ray\Settings\SettingsFactory::createFromConfigFile();
        return new static($settings, $client, $uuid);
    }
    public function __construct(\RayGlobalScoped\Spatie\Ray\Settings\Settings $settings, \RayGlobalScoped\Spatie\Ray\Client $client = null, string $uuid = null)
    {
        $this->settings = $settings;
        self::$client = $client ?? self::$client ?? new \RayGlobalScoped\Spatie\Ray\Client($settings->port, $settings->host);
        self::$counters = self::$counters ?? new \RayGlobalScoped\Spatie\Ray\Support\Counters();
        $this->uuid = $uuid ?? static::$fakeUuid ?? \RayGlobalScoped\Ramsey\Uuid\Uuid::uuid4()->toString();
        static::$enabled = static::$enabled ?? $this->settings->enable ?? \true;
    }
    public function enable() : self
    {
        static::$enabled = \true;
        return $this;
    }
    public function disable() : self
    {
        static::$enabled = \false;
        return $this;
    }
    public function enabled() : bool
    {
        return static::$enabled || static::$enabled === null;
    }
    public function disabled() : bool
    {
        return static::$enabled === \false;
    }
    public static function useClient(\RayGlobalScoped\Spatie\Ray\Client $client) : void
    {
        self::$client = $client;
    }
    public function newScreen(string $name = '') : self
    {
        $payload = new \RayGlobalScoped\Spatie\Ray\Payloads\NewScreenPayload($name);
        return $this->sendRequest($payload);
    }
    public function clearAll() : self
    {
        $payload = new \RayGlobalScoped\Spatie\Ray\Payloads\ClearAllPayload();
        return $this->sendRequest($payload);
    }
    public function clearScreen() : self
    {
        return $this->newScreen();
    }
    public function color(string $color) : self
    {
        $payload = new \RayGlobalScoped\Spatie\Ray\Payloads\ColorPayload($color);
        return $this->sendRequest($payload);
    }
    public function size(string $size) : self
    {
        $payload = new \RayGlobalScoped\Spatie\Ray\Payloads\SizePayload($size);
        return $this->sendRequest($payload);
    }
    public function remove() : self
    {
        $payload = new \RayGlobalScoped\Spatie\Ray\Payloads\RemovePayload();
        return $this->sendRequest($payload);
    }
    public function hide() : self
    {
        $payload = new \RayGlobalScoped\Spatie\Ray\Payloads\HidePayload();
        return $this->sendRequest($payload);
    }
    /**
     * @param string|callable $stopwatchName
     *
     * @return $this
     */
    public function measure($stopwatchName = 'default') : self
    {
        if ($stopwatchName instanceof \Closure) {
            return $this->measureClosure($stopwatchName);
        }
        if (!isset(static::$stopWatches[$stopwatchName])) {
            $stopwatch = new \RayGlobalScoped\Symfony\Component\Stopwatch\Stopwatch(\true);
            static::$stopWatches[$stopwatchName] = $stopwatch;
            $event = $stopwatch->start($stopwatchName);
            $payload = new \RayGlobalScoped\Spatie\Ray\Payloads\MeasurePayload($stopwatchName, $event);
            $payload->concernsNewTimer();
            return $this->sendRequest($payload);
        }
        $stopwatch = static::$stopWatches[$stopwatchName];
        $event = $stopwatch->lap($stopwatchName);
        $payload = new \RayGlobalScoped\Spatie\Ray\Payloads\MeasurePayload($stopwatchName, $event);
        return $this->sendRequest($payload);
    }
    public function trace(?\Closure $startingFromFrame = null) : self
    {
        $backtrace = \RayGlobalScoped\Spatie\Backtrace\Backtrace::create();
        if (\class_exists(\RayGlobalScoped\Spatie\LaravelRay\Ray::class) && \function_exists('RayGlobalScoped\\base_path')) {
            $backtrace->applicationPath(base_path());
        }
        if ($startingFromFrame) {
            $backtrace->startingFromFrame($startingFromFrame);
        }
        $payload = new \RayGlobalScoped\Spatie\Ray\Payloads\TracePayload($backtrace->frames());
        return $this->sendRequest($payload);
    }
    public function backtrace(?\Closure $startingFromFrame = null) : self
    {
        return $this->trace($startingFromFrame);
    }
    public function caller() : self
    {
        $backtrace = \RayGlobalScoped\Spatie\Backtrace\Backtrace::create();
        $payload = new \RayGlobalScoped\Spatie\Ray\Payloads\CallerPayload($backtrace->frames());
        return $this->sendRequest($payload);
    }
    protected function measureClosure(\Closure $closure) : self
    {
        $stopwatch = new \RayGlobalScoped\Symfony\Component\Stopwatch\Stopwatch(\true);
        $stopwatch->start('closure');
        $closure();
        $event = $stopwatch->stop('closure');
        $payload = new \RayGlobalScoped\Spatie\Ray\Payloads\MeasurePayload('closure', $event);
        return $this->sendRequest($payload);
    }
    public function stopTime(string $stopwatchName = '') : self
    {
        if ($stopwatchName === '') {
            static::$stopWatches = [];
            return $this;
        }
        if (isset(static::$stopWatches[$stopwatchName])) {
            unset(static::$stopWatches[$stopwatchName]);
            return $this;
        }
        return $this;
    }
    public function notify(string $text) : self
    {
        $payload = new \RayGlobalScoped\Spatie\Ray\Payloads\NotifyPayload($text);
        return $this->sendRequest($payload);
    }
    /**
     * Sends the provided value(s) encoded as a JSON string using json_encode().
     */
    public function toJson(...$values) : self
    {
        $payloads = \array_map(function ($value) {
            return new \RayGlobalScoped\Spatie\Ray\Payloads\JsonStringPayload($value);
        }, $values);
        return $this->sendRequest($payloads);
    }
    /**
     * Sends the provided JSON string(s) decoded using json_decode().
     */
    public function json(string ...$jsons) : self
    {
        $payloads = \array_map(function ($json) {
            return new \RayGlobalScoped\Spatie\Ray\Payloads\DecodedJsonPayload($json);
        }, $jsons);
        return $this->sendRequest($payloads);
    }
    public function file(string $filename) : self
    {
        $payload = new \RayGlobalScoped\Spatie\Ray\Payloads\FileContentsPayload($filename);
        return $this->sendRequest($payload);
    }
    public function image(string $location) : self
    {
        $payload = new \RayGlobalScoped\Spatie\Ray\Payloads\ImagePayload($location);
        return $this->sendRequest($payload);
    }
    public function die($status = '') : void
    {
        die($status);
    }
    public function className(object $object) : self
    {
        return $this->send(\get_class($object));
    }
    public function phpinfo(string ...$properties) : self
    {
        if (!\count($properties)) {
            return $this->table(['PHP version' => \phpversion(), 'Memory limit' => \ini_get('memory_limit'), 'Max file upload size' => \ini_get('max_file_uploads'), 'Max post size' => \ini_get('post_max_size'), 'PHP ini file' => \php_ini_loaded_file(), "PHP scanned ini file" => \php_ini_scanned_files(), 'Extensions' => \implode(', ', \get_loaded_extensions())], 'PHPInfo');
        }
        $properties = \array_flip($properties);
        foreach ($properties as $property => $value) {
            $properties[$property] = \ini_get($property);
        }
        return $this->table($properties, 'PHPInfo');
    }
    public function showWhen($boolOrCallable) : self
    {
        if (\is_callable($boolOrCallable)) {
            $boolOrCallable = (bool) $boolOrCallable();
        }
        if (!$boolOrCallable) {
            $this->remove();
        }
        return $this;
    }
    public function showIf($boolOrCallable) : self
    {
        return $this->showWhen($boolOrCallable);
    }
    public function removeWhen($boolOrCallable) : self
    {
        if (\is_callable($boolOrCallable)) {
            $boolOrCallable = (bool) $boolOrCallable();
        }
        if ($boolOrCallable) {
            $this->remove();
        }
        return $this;
    }
    public function removeIf($boolOrCallable) : self
    {
        return $this->removeWhen($boolOrCallable);
    }
    public function carbon(?\RayGlobalScoped\Carbon\Carbon $carbon) : self
    {
        $payload = new \RayGlobalScoped\Spatie\Ray\Payloads\CarbonPayload($carbon);
        $this->sendRequest($payload);
        return $this;
    }
    public function ban() : self
    {
        return $this->send('🕶');
    }
    public function charles() : self
    {
        return $this->send('🎶 🎹 🎷 🕺');
    }
    public function table(array $values, $label = 'Table') : self
    {
        $payload = new \RayGlobalScoped\Spatie\Ray\Payloads\TablePayload($values, $label);
        return $this->sendRequest($payload);
    }
    public function count(?string $name = null) : self
    {
        $fingerPrint = (new \RayGlobalScoped\Spatie\Ray\Origin\DefaultOriginFactory())->getOrigin()->fingerPrint();
        [$ray, $times] = self::$counters->increment($name ?? $fingerPrint);
        $message = "Called ";
        if ($name) {
            $message .= "`{$name}` ";
        }
        $message .= "{$times} ";
        $message .= $times === 1 ? 'time' : 'times';
        $message .= '.';
        $ray->sendCustom($message, 'Count');
        return $ray;
    }
    public function clearCounters() : self
    {
        self::$counters->clear();
        return $this;
    }
    public function pause() : self
    {
        $lockName = \md5(\time());
        $payload = new \RayGlobalScoped\Spatie\Ray\Payloads\CreateLockPayload($lockName);
        $this->sendRequest($payload);
        do {
            \sleep(1);
        } while (self::$client->lockExists($lockName));
        return $this;
    }
    public function html(string $html = '') : self
    {
        $payload = new \RayGlobalScoped\Spatie\Ray\Payloads\HtmlPayload($html);
        return $this->sendRequest($payload);
    }
    public function exception(\Exception $exception, array $meta = []) : self
    {
        $payload = new \RayGlobalScoped\Spatie\Ray\Payloads\ExceptionPayload($exception, $meta);
        $this->sendRequest($payload);
        return $this;
    }
    public function xml(string $xml) : self
    {
        $payload = new \RayGlobalScoped\Spatie\Ray\Payloads\XmlPayload($xml);
        return $this->sendRequest($payload);
    }
    public function raw(...$arguments) : self
    {
        if (!\count($arguments)) {
            return $this;
        }
        $payloads = \array_map(function ($argument) {
            return \RayGlobalScoped\Spatie\Ray\Payloads\LogPayload::createForArguments([$argument]);
        }, $arguments);
        return $this->sendRequest($payloads);
    }
    public function send(...$arguments) : self
    {
        if (!\count($arguments)) {
            return $this;
        }
        if ($this->settings->always_send_raw_values) {
            return $this->raw(...$arguments);
        }
        $payloads = \RayGlobalScoped\Spatie\Ray\PayloadFactory::createForValues($arguments);
        return $this->sendRequest($payloads);
    }
    public function pass($argument)
    {
        $this->send($argument);
        return $argument;
    }
    public function showApp() : self
    {
        $payload = new \RayGlobalScoped\Spatie\Ray\Payloads\ShowAppPayload();
        return $this->sendRequest($payload);
    }
    public function hideApp() : self
    {
        $payload = new \RayGlobalScoped\Spatie\Ray\Payloads\HideAppPayload();
        return $this->sendRequest($payload);
    }
    public function sendCustom(string $content, string $label = '') : self
    {
        $customPayload = new \RayGlobalScoped\Spatie\Ray\Payloads\CustomPayload($content, $label);
        return $this->sendRequest($customPayload);
    }
    /**
     * @param \Spatie\Ray\Payloads\Payload|\Spatie\Ray\Payloads\Payload[] $payloads
     * @param array $meta
     *
     * @return $this
     * @throws \Exception
     */
    public function sendRequest($payloads, array $meta = []) : self
    {
        if (!$this->enabled()) {
            return $this;
        }
        if (!\is_array($payloads)) {
            $payloads = [$payloads];
        }
        try {
            if (\class_exists(\RayGlobalScoped\Composer\InstalledVersions::class)) {
                $meta['ray_package_version'] = \RayGlobalScoped\Composer\InstalledVersions::getVersion('spatie/ray');
            }
        } catch (\Exception $e) {
            // In WordPress this entire package will be rewritten
        }
        $allMeta = \array_merge(['php_version' => \phpversion(), 'php_version_id' => \PHP_VERSION_ID], $meta);
        foreach ($payloads as $payload) {
            $payload->remotePath = $this->settings->remote_path;
            $payload->localPath = $this->settings->local_path;
        }
        $request = new \RayGlobalScoped\Spatie\Ray\Request($this->uuid, $payloads, $allMeta);
        self::$client->send($request);
        return $this;
    }
    public static function makePathOsSafe(string $path) : string
    {
        return \str_replace('/', \DIRECTORY_SEPARATOR, $path);
    }
}

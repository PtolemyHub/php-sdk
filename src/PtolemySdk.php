<?php

namespace Ptolemy;

use GuzzleHttp\Promise\Utils;
use Http\Client\HttpAsyncClient;
use Http\Discovery\HttpAsyncClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Promise\Promise;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

class PtolemySdk
{
    private static PtolemySdk $sdk;

    private $dsn;
    private string $requestId;
    private bool $isDebugMode;
    private int $concurrentRequests;

    private HttpAsyncClient $httpAsyncClient;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;

    /** @var Promise[] */
    private array $promises = [];

    private function __construct(array $options = [])
    {
        $this->dsn = $options['dsn'] ?? null;
        $this->isDebugMode = $options['debug'] ?? false;
        $this->concurrentRequests = (int) $options['concurrentRequests'] ?? 1;
        $this->requestId = $this->generateRequestId();

        $this->httpAsyncClient = HttpAsyncClientDiscovery::find();
        $this->requestFactory = Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = Psr17FactoryDiscovery::findStreamFactory();

        register_shutdown_function([$this, 'onShutdown']);
    }

    public function onShutdown()
    {
        self::logDebug(sprintf(
            'onShutdown start with %s promises and chunk size of %s promises',
            count($this->promises),
            $this->concurrentRequests
        ));
        $start = hrtime(true);

        $chunks = array_chunk($this->promises, $this->concurrentRequests);
        foreach ($chunks as $i => $chunkPromises) {
            Utils::settle($chunkPromises)->wait();
            self::logDebug(sprintf(
                'promises chunk %s $done with %s promises, duration : %s milliseconds',
                $i,
                count($chunkPromises),
                (hrtime(true) - $start) / 1e+6
            ));
        }
        $duration = (hrtime(true) - $start) / 1e+6;
        self::logDebug(sprintf('onShutdown end, with an execution time of %s milliseconds', $duration));
    }

    private function getFulfilledCallback(): ?callable
    {
        if (!$this->isDebugMode) {
            return null;
        }

        return function (ResponseInterface $response) {
            self::logDebug($response->getStatusCode().' '.$response->getBody()->getContents());
            return $response;
        };
    }

    private function getRejectedCallback(): callable
    {
        return function(\Exception $exception) {
            self::logDebug($exception->getTraceAsString());
        };
    }

    private function generateRequestId(): string
    {
        $chars = array_flip(array_merge(range('a', 'z'), range(0, 9)));

        return implode('-', [
            implode('', array_rand($chars, 8)),
            implode('', array_rand($chars, 4)),
            implode('', array_rand($chars, 4)),
            implode('', array_rand($chars, 4)),
            implode('', array_rand($chars, 12)),
        ]);
    }

    public static function logDebug(string $message): void
    {
        file_put_contents('/usr/src/log.txt', sprintf("[%s] %s\n", date('H:i:s'), $message), FILE_APPEND);
    }

    public static function init(array $options = []): void
    {
        self::$sdk = new PtolemySdk($options);
    }

    public static function getSdk()
    {
        if (self::$sdk === null) {
            self::$sdk = new PtolemySdk();
        }

        return self::$sdk;
    }

    public function getDsn(): ?string
    {
        return $this->dsn;
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }

    public function addPromise(Promise $promise): void
    {
        $this->promises[] = $promise->then($this->getFulfilledCallback(), $this->getRejectedCallback());
    }

    public function getHttpAsyncClient(): HttpAsyncClient
    {
        return $this->httpAsyncClient;
    }

    public function getRequestFactory(): RequestFactoryInterface
    {
        return $this->requestFactory;
    }

    public function getStreamFactory(): StreamFactoryInterface
    {
        return $this->streamFactory;
    }
}

<?php

namespace Ptolemy;

use GuzzleHttp\Promise\Utils;
use Http\Client\HttpAsyncClient;
use Http\Discovery\HttpAsyncClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Ptolemy\DTO\Package;

class Shipper
{
    /** @var string */
    private $requestId;

    /** @var string */
    private $dsn;

    /** @var HttpAsyncClient */
    private $httpAsyncClient;

    /** @var RequestFactoryInterface */
    private $requestFactory;

    /** @var StreamFactoryInterface */
    private $streamFactory;

    public function __construct(string $dsn)
    {
        $this->dsn = $dsn;

        $this->requestId = $this->generateRequestId();

        $this->httpAsyncClient = HttpAsyncClientDiscovery::find();
        $this->requestFactory = Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = Psr17FactoryDiscovery::findStreamFactory();
    }

    public function shipPackages(array $packages, int $concurrentShipments): void
    {
        $promises = $this->preparePromises($packages);

        PtolemySdk::log(sprintf(
            'shipPackages start with %s promises and up to %s concurrent shipments',
            count($promises),
            $concurrentShipments
        ));
        $start = hrtime(true);

        $chunks = array_chunk($promises, $concurrentShipments);
        foreach ($chunks as $i => $chunkPromises) {
            Utils::settle($chunkPromises)->wait();
            PtolemySdk::log(sprintf(
                'promises chunk %s $done with %s promises, duration : %s milliseconds',
                $i,
                count($chunkPromises),
                (hrtime(true) - $start) / 1e+6
            ));
        }
        $duration = (hrtime(true) - $start) / 1e+6;
        PtolemySdk::log(sprintf('shipPackages end, with an execution time of %s milliseconds', $duration));
    }

    private function preparePromises(array $packages): array
    {
        $promises = [];

        /** @var Package $package */
        foreach ($packages as $package) {
            $stream = $this->streamFactory->createStream(json_encode([
                'requestId' => $this->requestId,
                'relationships' => $package->getSerializableRelationships()
            ]));

            $request = $this->requestFactory->createRequest('POST', $this->dsn)->withBody($stream);

            $promise = $this->httpAsyncClient->sendAsyncRequest($request);
            $promises[] = $promise->then($this->getFulfilledCallback(), $this->getRejectedCallback());
        }

        return $promises;
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

    private function getFulfilledCallback(): ?callable
    {
        return function (ResponseInterface $response) {
            PtolemySdk::log($response->getStatusCode().' '.$response->getBody()->getContents());
            return $response;
        };
    }

    private function getRejectedCallback(): callable
    {
        return function(\Exception $exception) {
            PtolemySdk::log($exception->getTraceAsString());
        };
    }
}

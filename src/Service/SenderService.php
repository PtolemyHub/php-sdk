<?php

namespace Ptolemy\Service;

use Ptolemy\DTO\EventDTO;
use Ptolemy\DTO\NodeDTO;
use Ptolemy\PtolemySdk;

class SenderService
{
    public static function track()
    {
        // Get the last 3 items of the backtrace (the current call to track(), the called and the caller)
        $backtrace = debug_backtrace(false, 3);

        // Shift the call to this function track()
        array_shift($backtrace);

        $caller = null;

        if (isset($backtrace[1])) {
            $caller = new NodeDTO($backtrace[1]['class'], $backtrace[1]['type'], $backtrace[1]['function']);
        }

        $callee = new NodeDTO($backtrace[0]['class'], $backtrace[0]['type'], $backtrace[0]['function']);

        $ptolemySdk = PtolemySdk::getSdk();

        $eventDTO = new EventDTO(new \DateTime(), $callee, $caller);

        $stream = $ptolemySdk->getStreamFactory()->createStream(json_encode([
            'requestId' => $ptolemySdk->getRequestId(),
            'date' => $eventDTO->getDate(),
            'data' => json_encode([
                'caller' => $eventDTO->getCaller() !== null ? self::transformNodeToArray($eventDTO->getCaller()): null,
                'callee' => self::transformNodeToArray($eventDTO->getCallee()),
            ])
        ]));

        $request = $ptolemySdk->getRequestFactory()->createRequest('POST', $ptolemySdk->getDsn())
            ->withBody($stream)
        ;

        PtolemySdk::logDebug(sprintf('Sent async request at %s', $eventDTO->getDate()));
        $ptolemySdk->addPromise($ptolemySdk->getHttpAsyncClient()->sendAsyncRequest($request));
    }

    private static function transformNodeToArray(NodeDTO $nodeDTO): array
    {
        return [
            'class' => $nodeDTO->getClass(),
            'function' => $nodeDTO->getFunction(),
            'type' => $nodeDTO->getType(),
        ];
    }
}

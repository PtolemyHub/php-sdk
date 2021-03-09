<?php

namespace Ptolemy;

use Ptolemy\DTO\Notebook;

class PtolemySdk
{
    private static PtolemySdk $sdk;

    private bool $isDebugMode;
    private int $concurrentRequests;
    private int $batchSize;

    private Notebook $notebook;
    private Packager $packager;
    private Shipper $shipper;

    private function __construct(array $options = [])
    {
        $this->isDebugMode = $options['debug'] ?? false;
        $this->concurrentRequests = (int) $options['concurrentRequests'] ?? 1;
        $this->batchSize = (int) ($options['batchSize'] ?? 50);

        $this->notebook = new Notebook();
        $this->packager = new Packager();
        $this->shipper = new Shipper($options['dsn']);

        register_shutdown_function([$this, 'onShutdown']);
    }

    public function getNotebook(): Notebook
    {
        return $this->notebook;
    }

    public function onShutdown()
    {
        $packages = $this->packager->package($this->notebook->getRelationships(), $this->batchSize);
        $this->shipper->shipPackages($packages, $this->concurrentRequests);
    }

    public static function log(string $message): void
    {
        $sdk = self::getSdk();
        if (!$sdk->isDebugMode) {
            return;
        }

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
}

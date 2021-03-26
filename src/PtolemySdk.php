<?php

namespace Ptolemy;

use Ptolemy\DTO\Notebook;

class PtolemySdk
{
    /** @var PtolemySdk */
    private static $sdk;

    /** @var bool */
    private $isDebugMode;

    /** @var bool */
    private $isEnabled;

    /** @var int */
    private $concurrentRequests;

    /** @var int */
    private $batchSize;

    /** @var Notebook */
    private $notebook;

    /** @var Packager */
    private $packager;

    /** @var Shipper */
    private $shipper;

    private function __construct()
    {
        $configuration = $this->loadConfiguration();
        $this->isEnabled = isset($configuration['dsn']);

        if (!$this->isEnabled) {
            return;
        }

        $this->isDebugMode = isset($configuration['debug']) ? (bool) $configuration['debug']: false;
        $this->concurrentRequests = isset($configuration['concurrentRequests']) ? (int) $configuration['concurrentRequests']: 5;
        $this->batchSize = isset($configuration['batchSize']) ? (int) $configuration['batchSize']: 50;

        $this->notebook = new Notebook();
        $this->packager = new Packager();
        $this->shipper = new Shipper($configuration['dsn']);

        register_shutdown_function([$this, 'onShutdown']);
    }

    private function loadConfiguration(): array
    {
        // Try path relative to vendor directory
        $configurationFilePath = dirname(__DIR__).'/../../../../ptolemyhub.json';
        if (!is_file($configurationFilePath)) {
            // Try path relative to script path execution
            global $argv;
            $configurationFilePath = dirname($argv[0]).'/ptolemyhub.json';
        }

        if (!is_file($configurationFilePath)) {
            return [];
        }

        return json_decode(file_get_contents($configurationFilePath), true);
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
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
//        $sdk = self::getSdk();
//        if (!$sdk->isDebugMode) {
//            return;
//        }

        file_put_contents('/usr/src/log.txt', sprintf("[%s] %s\n", date('H:i:s'), $message), FILE_APPEND);
    }

    public static function init(array $options = []): void
    {
        self::$sdk = new PtolemySdk();
    }

    public static function getSdk()
    {
        if (self::$sdk === null) {
            self::$sdk = new PtolemySdk();
        }

        return self::$sdk;
    }
}

<?php

namespace Ptolemy;

use Ptolemy\DTO\Node;
use Ptolemy\DTO\Relationship;

class Geographer
{
    public static function noteCall(): void
    {
        if (!PtolemySdk::getSdk()->isEnabled()) {
            return;
        }

        // Get the last 3 items of the backtrace (the current call to the function, the caller and the callee)
        $backtrace = debug_backtrace(false, 3);

        // Shift the call to this function track()
        array_shift($backtrace);

        // It's unclear in which case this may happen, but if it does (first ever call of the application ?), ignore it
        if (!isset($backtrace[1])) {
            return;
        }

        $caller = new Node($backtrace[1]['function']);
        if (isset($backtrace[1]['class'])) {
            $caller = new Node($backtrace[1]['function'], $backtrace[1]['class'], $backtrace[1]['type']);
        }

        $callee = new Node($backtrace[0]['function']);
        if (isset($backtrace[0]['class'])) {
            $callee = new Node($backtrace[0]['function'], $backtrace[0]['class'], $backtrace[0]['type']);
        }

        $relationship = new Relationship($caller, $callee);

        PtolemySdk::getSdk()->getNotebook()->addRelationship($relationship);
    }
}

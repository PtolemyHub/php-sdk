<?php

namespace Ptolemy\Service;

class SenderService
{
    public static function track()
    {
        // Get the last 3 items of the backtrace (the current call to track(), the called and the caller)
        $backtrace = debug_backtrace(false, 3);

        // Shift the call to this function track()
        array_shift($backtrace);

        $call = sprintf(
            '%s%s%s() ==> %s%s%s()',
            $backtrace[1]['class'], $backtrace[1]['type'], $backtrace[1]['function'],
            $backtrace[0]['class'], $backtrace[0]['type'], $backtrace[0]['function']
        );
        dump($call);
    }
}

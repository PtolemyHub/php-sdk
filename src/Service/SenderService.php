<?php

namespace Ptolemy\Service;

class SenderService
{
    public static function track()
    {
        // Get the last 3 items of the backtrace (the current call to track(), the called and the caller)
        $backtrace = debug_backtrace(false, 4);

        // Shift the call to this function track()
        array_shift($backtrace);

        dump($backtrace);
    }
}

<?php

namespace App;

use Exception;

/**
 * Thrown when the piGarden socket server cannot be reached at all
 * (host down, wrong address/port, connection refused, timeout).
 * Kept distinct from protocol errors so the UI can explain the
 * problem to the user and suggest what to check.
 */
class PiGardenConnectionException extends Exception
{
    /** @var string "host:port" that could not be reached */
    protected $target;

    public function __construct($message, $code = 0, $target = '')
    {
        parent::__construct($message, $code);
        $this->target = $target;
    }

    public function getTarget()
    {
        return $this->target;
    }
}

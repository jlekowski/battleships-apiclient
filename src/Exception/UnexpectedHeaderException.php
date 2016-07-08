<?php

namespace BattleshipsApi\Client\Exception;

class UnexpectedHeaderException extends \Exception
{
    /**
     * @param string $headerReceived
     * @param string $headerExpected
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($headerReceived, $headerExpected, $code = 0, \Exception $previous = null)
    {
        $message = sprintf('Incorrect header provided: %s (expected: %s)', $headerReceived, $headerExpected);

        parent::__construct($message, $code, $previous);
    }
}

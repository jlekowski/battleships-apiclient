<?php

namespace BattleshipsApi\Client\Event;

use Symfony\Component\EventDispatcher\Event;

class OnErrorEvent extends Event
{
    /**
     * @var \Exception
     */
    protected $exception;

    /**
     * @param \Exception $exception
     */
    public function __construct(\Exception $exception)
    {
        $this->exception = $exception;
    }

    /**
     * @return \Exception
     */
    public function getException(): \Exception
    {
        return $this->exception;
    }
}

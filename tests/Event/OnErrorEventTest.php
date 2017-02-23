<?php

namespace Tests\Event;

use BattleshipsApi\Client\Event\OnErrorEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class OnErrorEventTest extends TestCase
{
    /**
     * @var \Exception|ObjectProphecy
     */
    protected $exception;

    /**
     * @var OnErrorEvent
     */
    protected $event;

    public function setUp()
    {
        $this->exception = $this->prophesize(\Exception::class);
        $this->event = new OnErrorEvent($this->exception->reveal());
    }

    public function testGetException()
    {
        $this->assertSame($this->exception->reveal(), $this->event->getException());
    }
}

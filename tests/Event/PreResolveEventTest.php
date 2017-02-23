<?php

namespace Tests\Event;

use BattleshipsApi\Client\Event\PreResolveEvent;
use BattleshipsApi\Client\Request\ApiRequest;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class PreResolveEventTest extends TestCase
{
    /**
     * @var ApiRequest|ObjectProphecy
     */
    protected $apiRequest;

    /**
     * @var PreResolveEvent
     */
    protected $event;

    public function setUp()
    {
        $this->apiRequest = $this->prophesize(ApiRequest::class);
        $this->event = new PreResolveEvent($this->apiRequest->reveal());
    }

    public function testGetRequest()
    {
        $this->assertSame($this->apiRequest->reveal(), $this->event->getRequest());
    }
}

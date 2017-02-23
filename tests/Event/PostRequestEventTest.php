<?php

namespace Tests\Event;

use BattleshipsApi\Client\Event\PostRequestEvent;
use BattleshipsApi\Client\Request\ApiRequest;
use BattleshipsApi\Client\Response\ApiResponse;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class PostRequestEventTest extends TestCase
{
    /**
     * @var ApiRequest|ObjectProphecy
     */
    protected $apiRequest;

    /**
     * @var ApiResponse|ObjectProphecy
     */
    protected $apiResponse;

    /**
     * @var PostRequestEvent
     */
    protected $event;

    public function setUp()
    {
        $this->apiRequest = $this->prophesize(ApiRequest::class);
        $this->apiResponse = $this->prophesize(ApiResponse::class);
        $this->event = new PostRequestEvent($this->apiRequest->reveal(), $this->apiResponse->reveal());
    }

    public function testGetRequest()
    {
        $this->assertSame($this->apiRequest->reveal(), $this->event->getRequest());
    }

    public function testGetResponse()
    {
        $this->assertSame($this->apiResponse->reveal(), $this->event->getResponse());
    }
}

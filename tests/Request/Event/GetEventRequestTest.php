<?php

namespace Tests\Request\Event;

use BattleshipsApi\Client\Request\Event\GetEventRequest;
use PHPUnit\Framework\TestCase;

class GetEventRequestTest extends TestCase
{
    /**
     * @var GetEventRequest
     */
    protected $apiRequest;

    public function setUp()
    {
        $this->apiRequest = new GetEventRequest();
    }

    public function testSetGameId()
    {
        // returns itself
        $this->assertEquals($this->apiRequest, $this->apiRequest->setGameId(12));
    }

    public function testSetEventId()
    {
        // returns itself
        $this->assertEquals($this->apiRequest, $this->apiRequest->setEventId(34));
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The required option "gameId" is missing.
     */
    public function testResolveThrowsExceptionOnMissingGameId()
    {
        $this->apiRequest->setApiKey('testKey')->setEventId(34)->resolve();
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The required option "eventId" is missing.
     */
    public function testResolveThrowsExceptionOnMissingEventId()
    {
        $this->apiRequest->setApiKey('testKey')->setGameId(12)->resolve();
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The required option "apiKey" is missing.
     */
    public function testResolveThrowsExceptionOnMissingApiKey()
    {
        $this->apiRequest->setGameId(12)->setEventId(34)->resolve();
    }

    public function testSettingRequest()
    {
        // set required options and resolve
        $this->apiRequest->setApiKey('testKey')->setGameId(12)->setEventId(34)->resolve();

        // check http method
        $this->assertEquals('GET', $this->apiRequest->getHttpMethod());
        // check uri
        $this->assertEquals('/v1/games/12/events/34', $this->apiRequest->getUri());
        // check headers
        $this->assertEquals(['Authorization' => 'Bearer testKey'], $this->apiRequest->getHeaders());
        // check data
        $this->assertNull($this->apiRequest->getData());
    }
}

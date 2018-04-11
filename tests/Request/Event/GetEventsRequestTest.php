<?php

namespace Tests\Request\Event;

use BattleshipsApi\Client\Request\Event\EventTypes;
use BattleshipsApi\Client\Request\Event\GetEventsRequest;
use PHPUnit\Framework\TestCase;

class GetEventsRequestTest extends TestCase
{
    /**
     * @var GetEventsRequest
     */
    protected $apiRequest;

    public function setUp()
    {
        $this->apiRequest = new GetEventsRequest();
    }

    public function testSetGameId()
    {
        // returns itself
        $this->assertEquals($this->apiRequest, $this->apiRequest->setGameId(12));
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The required option "uri" is missing.
     */
    public function testResolveThrowsExceptionOnMissingGameId()
    {
        $this->apiRequest->setApiKey('testKey')->resolve();
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The option "type" with value "test-event" is invalid. Accepted values are:
     */
    public function testResolveThrowsExceptionOnIncorrectEventType()
    {
        $this->apiRequest->setApiKey('testKey')->setGameId(12)->setType('test-event')->resolve();
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The option "player" with value 3 is invalid.
     */
    public function testResolveThrowsExceptionWhenInvalidPlayer()
    {
        // player filter can be 1 or 2
        $this->apiRequest->setApiKey('testKey')->setGameId(12)->setPlayer(3)->resolve();
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The required option "apiKey" is missing.
     */
    public function testResolveThrowsExceptionOnMissingApiKey()
    {
        $this->apiRequest->setGameId(12)->resolve();
    }

    public function testSettingRequest()
    {
        // set required options and resolve
        $this->apiRequest->setApiKey('testKey')->setGameId(12)->setType(EventTypes::TYPES['SHOT'])->setPlayer(2)->setGt(9)->resolve();

        // check http method
        $this->assertEquals('GET', $this->apiRequest->getHttpMethod());
        // check uri
        $this->assertEquals(sprintf('/v1/games/12/events?gt=9&type=%s&player=2', EventTypes::TYPES['SHOT']), $this->apiRequest->getUri());
        // check headers
        $this->assertEquals(['Authorization' => 'Bearer testKey'], $this->apiRequest->getHeaders());
        // check data
        $this->assertNull($this->apiRequest->getData());
    }
}

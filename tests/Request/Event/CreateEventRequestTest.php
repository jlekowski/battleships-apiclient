<?php

namespace Tests\Request\Event;

use BattleshipsApi\Client\Request\Event\CreateEventRequest;
use BattleshipsApi\Client\Request\Event\EventTypes;
use PHPUnit\Framework\TestCase;

class CreateEventRequestTest extends TestCase
{
    /**
     * @var CreateEventRequest
     */
    protected $apiRequest;

    public function setUp()
    {
        $this->apiRequest = new CreateEventRequest();
    }

    public function testSetGameId()
    {
        // returns itself
        $this->assertEquals($this->apiRequest, $this->apiRequest->setGameId(12));
    }

    public function testSetEventType()
    {
        // returns itself
        $this->assertEquals($this->apiRequest, $this->apiRequest->setEventType(EventTypes::TYPES['CHAT']));
    }

    public function testSetEventValue()
    {
        // returns itself
        $this->assertEquals($this->apiRequest, $this->apiRequest->setEventValue('Chat text'));
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The required option "uri" is missing.
     */
    public function testResolveThrowsExceptionOnMissingGameId()
    {
        $this->apiRequest->setApiKey('testKey')->setEventType(EventTypes::TYPES['CHAT'])->setEventValue('Chat text')->resolve();
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The required option "type" is missing.
     */
    public function testResolveThrowsExceptionOnMissingEventType()
    {
        $this->apiRequest->setApiKey('testKey')->setGameId(12)->setEventValue('Chat text')->resolve();
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The option "type" with value "invalid" is invalid. Accepted values are:
     */
    public function testResolveThrowsExceptionOnIncorrectEventType()
    {
        $this->apiRequest->setApiKey('testKey')->setGameId(12)->setEventType('invalid')->setEventValue('Chat text')->resolve();
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The required option "value" is missing.
     */
    public function testResolveThrowsExceptionOnMissingEventValue()
    {
        $this->apiRequest->setApiKey('testKey')->setGameId(12)->setEventType(EventTypes::TYPES['CHAT'])->resolve();
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The required option "apiKey" is missing.
     */
    public function testResolveThrowsExceptionOnMissingApiKey()
    {
        $this->apiRequest->setGameId(12)->setEventType(EventTypes::TYPES['CHAT'])->setEventValue('Chat text')->resolve();
    }

    public function testSettingRequest()
    {
        // set required options and resolve
        $this->apiRequest->setApiKey('testKey')->setGameId(12)->setEventType(EventTypes::TYPES['CHAT'])->setEventValue('Chat text')->resolve();

        // check http method
        $this->assertEquals('POST', $this->apiRequest->getHttpMethod());
        // check uri
        $this->assertEquals('/v1/games/12/events', $this->apiRequest->getUri());
        // check headers
        $this->assertEquals(['Authorization' => 'Bearer testKey'], $this->apiRequest->getHeaders());
        // check data
        $this->assertEquals(['type' => EventTypes::TYPES['CHAT'], 'value' => 'Chat text'], $this->apiRequest->getData());
    }
}

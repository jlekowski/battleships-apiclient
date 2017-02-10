<?php

namespace Tests\Request\Game;

use BattleshipsApi\Client\Request\Game\GetGameRequest;
use PHPUnit\Framework\TestCase;

class GetGameRequestTest extends TestCase
{
    /**
     * @var GetGameRequest
     */
    protected $apiRequest;

    public function setUp()
    {
        $this->apiRequest = new GetGameRequest();
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
     * @expectedExceptionMessage The required option "apiKey" is missing.
     */
    public function testResolveThrowsExceptionOnMissingApiKey()
    {
        $this->apiRequest->setGameId(12)->resolve();
    }

    public function testSettingRequest()
    {
        // set required options and resolve
        $this->apiRequest->setApiKey('testKey')->setGameId(12)->resolve();

        // check http method
        $this->assertEquals('GET', $this->apiRequest->getHttpMethod());
        // check uri
        $this->assertEquals('/v1/games/12', $this->apiRequest->getUri());
        // check headers
        $this->assertEquals(['Authorization' => 'Bearer testKey'], $this->apiRequest->getHeaders());
        // check data
        $this->assertNull($this->apiRequest->getData());
    }
}

<?php

namespace Tests\Request\Game;

use BattleshipsApi\Client\Request\Game\GetGamesRequest;
use PHPUnit\Framework\TestCase;

class GetGamesRequestTest extends TestCase
{
    /**
     * @var GetGamesRequest
     */
    protected $apiRequest;

    public function setUp()
    {
        $this->apiRequest = new GetGamesRequest();
    }

    public function testSetAvailable()
    {
        // returns itself
        $this->assertEquals($this->apiRequest, $this->apiRequest->setAvailable(true));
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The required option "available" is missing.
     */
    public function testResolveThrowsExceptionOnMissingAvailable()
    {
        $this->apiRequest->setApiKey('testKey')->resolve();
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The required option "apiKey" is missing.
     */
    public function testResolveThrowsExceptionOnMissingApiKey()
    {
        $this->apiRequest->setAvailable(true)->resolve();
    }

    public function testSettingRequest()
    {
        // set required options and resolve
        $this->apiRequest->setApiKey('testKey')->setAvailable(true)->resolve();

        // check http method
        $this->assertEquals('GET', $this->apiRequest->getHttpMethod());
        // check uri
        $this->assertEquals('/v1/games?available=true', $this->apiRequest->getUri());
        // check headers
        $this->assertEquals(['Authorization' => 'Bearer testKey'], $this->apiRequest->getHeaders());
        // check data
        $this->assertNull($this->apiRequest->getData());
    }
}

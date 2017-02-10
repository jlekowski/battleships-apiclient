<?php

namespace Tests\Request\Game;

use BattleshipsApi\Client\Request\Game\CreateGameRequest;
use PHPUnit\Framework\TestCase;

class CreateGameRequestTest extends TestCase
{
    /**
     * @var CreateGameRequest
     */
    protected $apiRequest;

    public function setUp()
    {
        $this->apiRequest = new CreateGameRequest();
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The required option "apiKey" is missing.
     */
    public function testResolveThrowsExceptionOnMissingApiKey()
    {
        $this->apiRequest->resolve();
    }

    public function testSettingRequest()
    {
        // set required options and resolve
        $this->apiRequest->setApiKey('testKey')->resolve();

        // check http method
        $this->assertEquals('POST', $this->apiRequest->getHttpMethod());
        // check uri
        $this->assertEquals('/v1/games', $this->apiRequest->getUri());
        // check headers
        $this->assertEquals(['Authorization' => 'Bearer testKey'], $this->apiRequest->getHeaders());
        // check data
        $this->assertNull($this->apiRequest->getData());
    }
}

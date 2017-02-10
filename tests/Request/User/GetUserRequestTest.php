<?php

namespace Tests\Request\User;

use BattleshipsApi\Client\Request\User\GetUserRequest;
use PHPUnit\Framework\TestCase;

class GetUserRequestTest extends TestCase
{
    /**
     * @var GetUserRequest
     */
    protected $apiRequest;

    public function setUp()
    {
        $this->apiRequest = new GetUserRequest();
    }

    public function testSetUserId()
    {
        // returns itself
        $this->assertEquals($this->apiRequest, $this->apiRequest->setUserId(123));
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The required option "uri" is missing.
     */
    public function testResolveThrowsExceptionOnMissingUserId()
    {
        $this->apiRequest->setApiKey('testKey')->resolve();
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The required option "apiKey" is missing.
     */
    public function testResolveThrowsExceptionOnMissingApiKey()
    {
        $this->apiRequest->setUserId(123)->resolve();
    }

    public function testSettingRequest()
    {
        // set required options and resolve
        $this->apiRequest->setApiKey('testKey')->setUserId(123)->resolve();

        // check http method
        $this->assertEquals('GET', $this->apiRequest->getHttpMethod());
        // check uri
        $this->assertEquals('/v1/users/123', $this->apiRequest->getUri());
        // check headers
        $this->assertEquals(['Authorization' => 'Bearer testKey'], $this->apiRequest->getHeaders());
        // check data
        $this->assertNull($this->apiRequest->getData());
    }
}

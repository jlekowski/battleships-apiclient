<?php

namespace Tests\Request\User;

use BattleshipsApi\Client\Request\User\CreateUserRequest;
use PHPUnit\Framework\TestCase;

class CreateUserRequestTest extends TestCase
{
    /**
     * @var CreateUserRequest
     */
    protected $apiRequest;

    public function setUp()
    {
        $this->apiRequest = new CreateUserRequest();
    }

    public function testSetUsername()
    {
        // returns itself
        $this->assertEquals($this->apiRequest, $this->apiRequest->setUserName('testName'));
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The required option "name" is missing.
     */
    public function testResolveThrowsExceptionOnMissingUsername()
    {
        $this->apiRequest->resolve();
    }

    public function testSettingRequest()
    {
        // set required options and resolve
        $this->apiRequest->setUserName('testName')->resolve();

        // check http method
        $this->assertEquals('POST', $this->apiRequest->getHttpMethod());
        // check uri
        $this->assertEquals('/v1/users', $this->apiRequest->getUri());
        // check headers
        $this->assertEquals([], $this->apiRequest->getHeaders());
        // check data
        $this->assertEquals(['name' => 'testName'], (array)$this->apiRequest->getData());
    }
}

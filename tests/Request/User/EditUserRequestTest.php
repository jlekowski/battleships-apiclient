<?php

namespace Tests\Request\User;

use BattleshipsApi\Client\Request\User\EditUserRequest;
use PHPUnit\Framework\TestCase;

class EditUserRequestTest extends TestCase
{
    /**
     * @var EditUserRequest
     */
    protected $apiRequest;

    public function setUp()
    {
        $this->apiRequest = new EditUserRequest();
    }

    public function testSetUserId()
    {
        // returns itself
        $this->assertEquals($this->apiRequest, $this->apiRequest->setUserId(123));
    }

    public function testSetUsername()
    {
        // returns itself
        $this->assertEquals($this->apiRequest, $this->apiRequest->setUserName('testName'));
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The required option "uri" is missing.
     */
    public function testResolveThrowsExceptionOnMissingUserId()
    {
        $this->apiRequest->setApiKey('testKey')->setUserName('testName')->resolve();
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The required option "name" is missing.
     */
    public function testResolveThrowsExceptionOnMissingUsername()
    {
        $this->apiRequest->setApiKey('testKey')->setUserId(123)->resolve();
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The required option "apiKey" is missing.
     */
    public function testResolveThrowsExceptionOnMissingApiKey()
    {
        $this->apiRequest->setUserId(123)->setUserName('testName')->resolve();
    }

    public function testSettingRequest()
    {
        // set required options and resolve
        $this->apiRequest->setApiKey('testKey')->setUserId(123)->setUserName('testName')->resolve();

        // check http method
        $this->assertEquals('PATCH', $this->apiRequest->getHttpMethod());
        // check uri
        $this->assertEquals('/v1/users/123', $this->apiRequest->getUri());
        // check headers
        $this->assertEquals(['Authorization' => 'Bearer testKey'], $this->apiRequest->getHeaders());
        // check data
        $this->assertEquals(['name' => 'testName'], (array)$this->apiRequest->getData());
    }
}

<?php

namespace Tests\Response;

use BattleshipsApi\Client\Response\ApiResponse;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class ApiResponseTest extends TestCase
{
    /**
     * @var ApiResponse
     */
    protected $apiResponse;

    /**
     * @var ResponseInterface|ObjectProphecy
     */
    protected $response;

    /**
     * @var StreamInterface|ObjectProphecy
     */
    protected $stream;


    public function setUp()
    {
        $this->stream = $this->prophesize('Psr\Http\Message\StreamInterface');
        $this->stream->getContents()->willReturn('{"testKey":"testValue"}');

        $this->response = $this->prophesize('Psr\Http\Message\ResponseInterface');
        $this->response->getBody()->willReturn($this->stream);

        $this->apiResponse = new ApiResponse($this->response->reveal());
    }

    public function testGetResponse()
    {
        $this->assertEquals($this->response->reveal(), $this->apiResponse->getResponse());
    }

    public function testGetBody()
    {
        $this->assertEquals('{"testKey":"testValue"}', $this->apiResponse->getBody());
    }

    public function testGetJson()
    {
        $this->assertInstanceOf(\stdClass::class, $this->apiResponse->getJson());
        $this->assertEquals(['testKey' => 'testValue'], (array)$this->apiResponse->getJson());
    }

    public function testGetJsonError()
    {
        // no errors by default
        $this->assertEquals('', $this->apiResponse->getJsonError());

        $this->stream->getContents()->willReturn('{');
        $apiResponse = new ApiResponse($this->response->reveal());
        // json error
        $this->assertEquals('json_decode error: Syntax error', $apiResponse->getJsonError());
    }

    public function testGetHeader()
    {
        $this->response->hasHeader('testHeader')->willReturn(false);
        $this->assertNull($this->apiResponse->getHeader('testHeader'));

        $this->response->hasHeader('testHeader')->willReturn(true);
        $this->response->getHeader('testHeader')->willReturn(['testHeaderValue']);
        $this->assertEquals('testHeaderValue', $this->apiResponse->getHeader('testHeader'));
    }

    public function testGetHeaders()
    {
        $this->response->getHeaders()->willReturn([1,2,3]);
        $this->assertEquals([1,2,3], $this->apiResponse->getHeaders());
    }

    public function testGetNewId()
    {
        $this->response->hasHeader('Location')->willReturn(false);
        $this->assertNull($this->apiResponse->getNewId());

        $this->response->hasHeader('Location')->willReturn(true);
        $this->response->getHeader('Location')->willReturn(['/my/1/url/294']);
        $this->assertEquals(294, $this->apiResponse->getNewId());
    }
}

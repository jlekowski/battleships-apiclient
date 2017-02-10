<?php

namespace Tests\Exception;

use BattleshipsApi\Client\Exception\ApiException;
use BattleshipsApi\Client\Response\ApiResponse;
use PHPUnit\Framework\TestCase;

class ApiExceptionTest extends TestCase
{
    public function testGetMessage()
    {
        $apiException = new ApiException('Test msg');
        $this->assertEquals('Test msg', $apiException->getMessage());
    }

    public function testGetCode()
    {
        $apiException = new ApiException('Test msg', 123);
        $this->assertEquals(123, $apiException->getCode());
    }

    public function testGetPrevious()
    {
        $previousException = new \Exception();
        $apiException = new ApiException('Test msg', 123, $previousException);
        $this->assertEquals($previousException, $apiException->getPrevious());
    }

    public function testGetApiResponse()
    {
        $stream = $this->prophesize('Psr\Http\Message\StreamInterface');
        $stream->getContents()->willReturn('{"testKey":"testValue"}');
        $response = $this->prophesize('Psr\Http\Message\ResponseInterface');
        $response->getBody()->willReturn($stream);

        $requestException = $this->prophesize('GuzzleHttp\Exception\RequestException');
        $requestException->getResponse()->willReturn($response);
        $apiException = new ApiException('Test msg', 123, $requestException->reveal());

        $apiResponse = $apiException->getApiResponse();
        $this->assertInstanceOf(ApiResponse::class, $apiResponse);
        $this->assertEquals($response->reveal(), $apiResponse->getResponse());
    }

    public function testGetApiResponseWithoutRequestException()
    {
        $apiException = new ApiException('Test msg', 123, new \Exception());

        $this->assertNull($apiException->getApiResponse());
    }
}

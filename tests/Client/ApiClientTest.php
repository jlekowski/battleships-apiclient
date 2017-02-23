<?php

namespace Tests\Client;

use BattleshipsApi\Client\Client\ApiClient;
use BattleshipsApi\Client\Event\ApiClientEvents;
use BattleshipsApi\Client\Event\OnErrorEvent;
use BattleshipsApi\Client\Event\PostRequestEvent;
use BattleshipsApi\Client\Event\PreResolveEvent;
use BattleshipsApi\Client\Request\ApiRequest;
use BattleshipsApi\Client\Response\ApiResponse;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ApiClientTest extends TestCase
{
    /**
     * @var ClientInterface|ObjectProphecy
     */
    protected $httpClient;

    /**
     * @var EventDispatcherInterface|ObjectProphecy
     */
    protected $dispatcher;

    /**
     * @var ApiClient
     */
    protected $apiClient;

    public function setUp()
    {
        $this->httpClient = $this->prophesize(ClientInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->apiClient = new ApiClient($this->httpClient->reveal(), $this->dispatcher->reveal());
    }

    public function testCall()
    {
        $this->dispatcher->dispatch(ApiClientEvents::PRE_RESOLVE, Argument::type(PreResolveEvent::class))->shouldBeCalled();
        $this->dispatcher->dispatch(ApiClientEvents::ON_ERROR, Argument::any())->shouldNotBeCalled();
        $this->dispatcher->dispatch(ApiClientEvents::POST_REQUEST, Argument::type(PostRequestEvent::class))->shouldBeCalled();

        $apiRequest = $this->prophesize(ApiRequest::class);
        $apiRequest->resolve()->shouldBeCalled();
        $apiRequest->getHttpMethod()->willReturn('TEST');
        $apiRequest->getUri()->willReturn('http://test.api/v1');
        $apiRequest->getHeaders()->willReturn(['testHeader']);
        $apiRequest->getData()->willReturn(['testKey' => 'testValue']);

        $stream = $this->prophesize(StreamInterface::class);
        $stream->getContents()->willReturn('{"testKey":"testValue"}');

        $apiResponse = $this->prophesize(ResponseInterface::class);
        $apiResponse->getBody()->willReturn($stream);

        $this->httpClient
            ->request(
                'TEST',
                'http://test.api/v1',
                [
                    RequestOptions::HEADERS => ['testHeader'],
                    RequestOptions::JSON => ['testKey' => 'testValue'],
                    'base_uri' => null
                ]
            )
            ->willReturn($apiResponse)
        ;


        $response = $this->apiClient->call($apiRequest->reveal());
        $this->assertInstanceOf(ApiResponse::class, $response);
        $this->assertObjectHasAttribute('testKey', $response->getJson());
        $this->assertEquals(['testKey' => 'testValue'], (array)$response->getJson());
        $this->assertEquals($apiResponse->reveal(), $response->getResponse());
    }

    /**
     * @expectedException \BattleshipsApi\Client\Exception\ApiException
     * @expectedExceptionMessage Test Exception Message
     */
    public function testCallThrowsApiExceptionOnGuzzleException()
    {
        $this->dispatcher->dispatch(ApiClientEvents::PRE_RESOLVE, Argument::type(PreResolveEvent::class))->shouldBeCalled();
        $this->dispatcher->dispatch(ApiClientEvents::ON_ERROR, Argument::type(OnErrorEvent::class))->shouldBeCalled();
        $this->dispatcher->dispatch(ApiClientEvents::POST_REQUEST, Argument::any())->shouldNotBeCalled();

        $request = $this->prophesize(RequestInterface::class);
        $guzzleException = new RequestException('Test Exception Message', $request->reveal());
        $this->httpClient->request(Argument::cetera())->willThrow($guzzleException);

        $apiRequest = $this->prophesize(ApiRequest::class);
        $apiRequest->resolve()->shouldBeCalled();
        $apiRequest->getHttpMethod()->shouldBeCalled();
        $apiRequest->getUri()->shouldBeCalled();
        $apiRequest->getHeaders()->shouldBeCalled();
        $apiRequest->getData()->shouldBeCalled();

        $this->apiClient->call($apiRequest->reveal());
    }

    public function testSetBaseUri()
    {
        $this->dispatcher->dispatch(Argument::any());

        $apiRequest = $this->prophesize(ApiRequest::class);
        $apiRequest->resolve();
        $apiRequest->getHttpMethod()->willReturn('TEST');
        $apiRequest->getUri()->willReturn('/v1/users');
        $apiRequest->getHeaders()->willReturn(['testHeader']);
        $apiRequest->getData()->willReturn(['testKey' => 'testValue']);

        $stream = $this->prophesize(StreamInterface::class);
        $stream->getContents()->willReturn('{"testKey":"testValue"}');

        $apiResponse = $this->prophesize(ResponseInterface::class);
        $apiResponse->getBody()->willReturn($stream);

        $this->httpClient
            ->request(
                'TEST',
                '/v1/users',
                [
                    RequestOptions::HEADERS => ['testHeader'],
                    RequestOptions::JSON => ['testKey' => 'testValue'],
                    'base_uri' => 'http://base.uri:8080'
                ]
            )
            ->willReturn($apiResponse)
        ;


        $response = $this->apiClient
            ->setBaseUri('http://base.uri:8080')
            ->call($apiRequest->reveal())
        ;
        $this->assertInstanceOf(ApiResponse::class, $response);
        $this->assertObjectHasAttribute('testKey', $response->getJson());
        $this->assertEquals(['testKey' => 'testValue'], (array)$response->getJson());
        $this->assertEquals($apiResponse->reveal(), $response->getResponse());
    }

    public function testGetDispatcher()
    {
        $this->assertInstanceOf(EventDispatcherInterface::class, $this->apiClient->getDispatcher());
        $this->assertEquals($this->dispatcher->reveal(), $this->apiClient->getDispatcher());
    }
}

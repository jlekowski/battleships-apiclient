<?php

namespace Tests\Client;

use BattleshipsApi\Client\Client\ApiClient;
use BattleshipsApi\Client\Event\ApiClientEvents;
use BattleshipsApi\Client\Response\ApiResponse;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
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
        $this->httpClient = $this->prophesize('GuzzleHttp\ClientInterface');
        $this->dispatcher = $this->prophesize('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->apiClient = new ApiClient($this->httpClient->reveal(), $this->dispatcher->reveal());
    }

    public function testCall()
    {
        $this->dispatcher->dispatch(ApiClientEvents::PRE_RESOLVE, Argument::type('BattleshipsApi\Client\Event\PreResolveEvent'))->shouldBeCalled();
        $this->dispatcher->dispatch(ApiClientEvents::ON_ERROR, Argument::any())->shouldNotBeCalled();
        $this->dispatcher->dispatch(ApiClientEvents::POST_REQUEST, Argument::type('BattleshipsApi\Client\Event\PostRequestEvent'))->shouldBeCalled();

        $apiRequest = $this->prophesize('BattleshipsApi\Client\Request\ApiRequest');
        $apiRequest->resolve()->shouldBeCalled();
        $apiRequest->getHttpMethod()->willReturn('TEST');
        $apiRequest->getUri()->willReturn('http://test.api/v1');
        $apiRequest->getHeaders()->willReturn(['testHeader']);
        $apiRequest->getData()->willReturn(['testKey' => 'testValue']);

        $stream = $this->prophesize('Psr\Http\Message\StreamInterface');
        $stream->getContents()->willReturn('{"testKey":"testValue"}');

        $apiResponse = $this->prophesize('Psr\Http\Message\ResponseInterface');
        $apiResponse->getBody()->willReturn($stream);

        $this->httpClient
            ->request('TEST', 'http://test.api/v1', [RequestOptions::HEADERS => ['testHeader'], RequestOptions::JSON => ['testKey' => 'testValue']])
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
        $this->dispatcher->dispatch(ApiClientEvents::PRE_RESOLVE, Argument::type('BattleshipsApi\Client\Event\PreResolveEvent'))->shouldBeCalled();
        $this->dispatcher->dispatch(ApiClientEvents::ON_ERROR, Argument::type('BattleshipsApi\Client\Event\OnErrorEvent'))->shouldBeCalled();
        $this->dispatcher->dispatch(ApiClientEvents::POST_REQUEST, Argument::any())->shouldNotBeCalled();

        $request = $this->prophesize('Psr\Http\Message\RequestInterface');
        $guzzleException = new RequestException('Test Exception Message', $request->reveal());
        $this->httpClient->request(Argument::cetera())->willThrow($guzzleException);

        $apiRequest = $this->prophesize('BattleshipsApi\Client\Request\ApiRequest');
        $apiRequest->resolve()->shouldBeCalled();
        $apiRequest->getHttpMethod()->shouldBeCalled();
        $apiRequest->getUri()->shouldBeCalled();
        $apiRequest->getHeaders()->shouldBeCalled();
        $apiRequest->getData()->shouldBeCalled();

        $this->apiClient->call($apiRequest->reveal());
    }

    public function testGetDispatcher()
    {
        $this->assertInstanceOf(EventDispatcherInterface::class, $this->apiClient->getDispatcher());
        $this->assertEquals($this->dispatcher->reveal(), $this->apiClient->getDispatcher());
    }
}

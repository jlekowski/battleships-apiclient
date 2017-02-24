<?php

namespace Tests\Client;

use BattleshipsApi\Client\Client\ApiClient;
use BattleshipsApi\Client\Client\ApiClientFactory;
use BattleshipsApi\Client\Event\ApiClientEvents;
use BattleshipsApi\Client\Listener\RequestConfigListener;
use BattleshipsApi\Client\Subscriber\LogSubscriber;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApiClientFactoryTest extends TestCase
{
    public function testBuild()
    {
        $apiClient = ApiClientFactory::build();

        $this->assertInstanceOf(ApiClient::class, $apiClient);
        $this->assertInstanceOf(EventDispatcherInterface::class, $apiClient->getDispatcher());
    }

    public function testBuildWithBaseUriAndTimeout()
    {
        $apiClient = ApiClientFactory::build(['baseUri' => 'http://api.test', 'timeout' => 2]);

        $this->assertInstanceOf(ApiClient::class, $apiClient);
        $this->assertInstanceOf(EventDispatcherInterface::class, $apiClient->getDispatcher());

        // magic to check if baseUri and timeout are set in httpClient
        $getHttpClient = function () {
            return $this->httpClient;
        };
        /** @var ClientInterface $httpClient */
        $httpClient = $getHttpClient->call($apiClient);
        $this->assertInstanceOf(ClientInterface::class, $httpClient);
        $this->assertEquals(2, $httpClient->getConfig('timeout'));
        /** @var Uri $uri */
        $uri = $httpClient->getConfig('base_uri');
        $this->assertInstanceOf(Uri::class, $uri);
        $this->assertEquals('api.test', $uri->getHost());
    }

    public function testBuildWithVersionAndKey()
    {
        $apiClient = ApiClientFactory::build(['version' => 13, 'key' => 'test-key']);

        $this->assertInstanceOf(ApiClient::class, $apiClient);
        $this->assertInstanceOf(EventDispatcherInterface::class, $apiClient->getDispatcher());
        $this->assertCount(1, $apiClient->getDispatcher()->getListeners());
        $listeners = $apiClient->getDispatcher()->getListeners(ApiClientEvents::PRE_RESOLVE);
        $this->assertInstanceOf(RequestConfigListener::class, $listeners[0][0]);
    }

    public function testBuildWithLogger()
    {
        $logger = $this->prophesize(LoggerInterface::class);
        $apiClient = ApiClientFactory::build(['logger' => $logger->reveal()]);

        $this->assertInstanceOf(ApiClient::class, $apiClient);
        $this->assertInstanceOf(EventDispatcherInterface::class, $apiClient->getDispatcher());
        $listeners = $apiClient->getDispatcher()->getListeners();
        $this->assertInstanceOf(LogSubscriber::class, current($listeners)[0][0]);
    }

    public function testBuildWithSubscriber()
    {
        $subscriber = new class() implements EventSubscriberInterface {
            public static function getSubscribedEvents()
            {
                return ['testEvent' => 'testMethod'];
            }
        };
        $apiClient = ApiClientFactory::build(['subscribers' => [$subscriber]]);

        $this->assertInstanceOf(ApiClient::class, $apiClient);
        $this->assertInstanceOf(EventDispatcherInterface::class, $apiClient->getDispatcher());
        $listeners = $apiClient->getDispatcher()->getListeners();
        $this->assertSame($subscriber, current($listeners)[0][0]);
    }

    public function testBuildWithDispatcher()
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $apiClient = ApiClientFactory::build(['dispatcher' => $dispatcher->reveal()]);

        $this->assertInstanceOf(ApiClient::class, $apiClient);
        $this->assertSame($dispatcher->reveal(), $apiClient->getDispatcher());
    }

    public function testBuildWithAll()
    {
        $subscriber1 = new class() implements EventSubscriberInterface {
            public static function getSubscribedEvents()
            {
                return ['testEvent' => 'testMethod1'];
            }
        };
        $subscriber2 = new class() implements EventSubscriberInterface {
            public static function getSubscribedEvents()
            {
                return ['testEvent' => 'testMethod2'];
            }
        };
        $logger = $this->prophesize(LoggerInterface::class);
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->addListener(ApiClientEvents::PRE_RESOLVE, Argument::that(function ($value) {
            return is_array($value) && ($value[0] instanceof RequestConfigListener) && ($value[1] === 'onPreResolve');
        }))->shouldBeCalled();
        $dispatcher->addSubscriber(Argument::type(LogSubscriber::class))->shouldBeCalled();
        $dispatcher->addSubscriber($subscriber1)->shouldBeCalled();
        $dispatcher->addSubscriber($subscriber2)->shouldBeCalled();

        $buildConfig = [
            'baseUri' => 'http://api.test',
            'timeout' => 2,
            'version' => 13,
            'key' => 'test-key',
            'logger' => $logger->reveal(),
            'subscribers' => [$subscriber1, $subscriber2],
            'dispatcher' => $dispatcher->reveal()
        ];
        $apiClient = ApiClientFactory::build($buildConfig);

        $this->assertInstanceOf(ApiClient::class, $apiClient);
        $this->assertSame($dispatcher->reveal(), $apiClient->getDispatcher());

        // magic to check if baseUri and timeout are set in httpClient
        $getHttpClient = function () {
            return $this->httpClient;
        };
        /** @var ClientInterface $httpClient */
        $httpClient = $getHttpClient->call($apiClient);
        $this->assertInstanceOf(ClientInterface::class, $httpClient);
        $this->assertEquals(2, $httpClient->getConfig('timeout'));
        /** @var Uri $uri */
        $uri = $httpClient->getConfig('base_uri');
        $this->assertInstanceOf(Uri::class, $uri);
        $this->assertEquals('api.test', $uri->getHost());
    }
}

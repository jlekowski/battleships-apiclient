<?php

namespace Tests\Subscriber;

use BattleshipsApi\Client\Event\OnErrorEvent;
use BattleshipsApi\Client\Event\PostRequestEvent;
use BattleshipsApi\Client\Event\PreResolveEvent;
use BattleshipsApi\Client\Response\ApiResponse;
use BattleshipsApi\Client\Subscriber\LogSubscriber;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class LogSubscriberTest extends TestCase
{
    /**
     * @var LogSubscriber
     */
    protected $logSubscriber;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function setUp()
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->logSubscriber= new LogSubscriber($this->logger->reveal());
    }

    public function testGetSubscribedEvents()
    {
        $subscribedEvents = LogSubscriber::getSubscribedEvents();
        $this->assertNotCount(0, $subscribedEvents);
    }

    public function testOnPreResolve()
    {
        $event = $this->prophesize(PreResolveEvent::class);
        $event->getRequest();
        $this->logger->debug(Argument::cetera())->shouldBeCalled();

        $this->logSubscriber->onPreResolve($event->reveal());
    }

    public function testOnPostRequest()
    {
        $apiResponse = $this->prophesize(ApiResponse::class);
        $apiResponse->getBody();
        $event = $this->prophesize(PostRequestEvent::class);
        $event->getRequest();
        $event->getResponse()->willReturn($apiResponse);
        $this->logger->debug(Argument::cetera())->shouldBeCalled();

        $this->logSubscriber->onPostRequest($event->reveal());
    }

    public function testOnError()
    {
        $event = $this->prophesize(OnErrorEvent::class);
        $event->getException()->willReturn(new \Exception());
        $this->logger->error(Argument::cetera())->shouldBeCalled();

        $this->logSubscriber->onError($event->reveal());
    }
}

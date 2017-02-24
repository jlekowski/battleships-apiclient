<?php

namespace Tests\Listener;

use BattleshipsApi\Client\Event\PreResolveEvent;
use BattleshipsApi\Client\Listener\RequestConfigListener;
use BattleshipsApi\Client\Request\ApiRequest;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class RequestConfigListenerTest extends TestCase
{
    public function testConstructorWithoutApiKey()
    {
        $listener = new RequestConfigListener(11);
        $this->validateApiKeyAndVersion($listener, 11);
    }

    public function testConstructorWithApiKey()
    {
        $listener = new RequestConfigListener(11, 'testKey');
        $this->validateApiKeyAndVersion($listener, 11, 'testKey');
    }

    public function testSetApiVersion()
    {
        $listener = new RequestConfigListener();
        $listener->setApiVersion(12);
        $this->validateApiKeyAndVersion($listener, 12);
    }

    public function testSetApiKey()
    {
        $listener = new RequestConfigListener(13);
        $listener->setApiKey('testKey');
        $this->validateApiKeyAndVersion($listener, 13, 'testKey');

        $listener->setApiKey(null);
        $this->validateApiKeyAndVersion($listener, 13, null);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testOnPreResolveErrorWhenNoApiVersion()
    {
        $listener = new RequestConfigListener();
        $event = $this->prophesize(PreResolveEvent::class);
        $listener->onPreResolve($event->reveal());
    }

    protected function validateApiKeyAndVersion(RequestConfigListener $listener, int $apiVersion = null, string $apiKey = null)
    {
        $apiRequest = $this->prophesize(ApiRequest::class);
        $apiRequest->setApiVersion($apiVersion)->willReturn($apiRequest);
        if ($apiKey === null) {
            $apiRequest->setApiKey(Argument::any())->shouldNotBeCalled();
        } else {
            $apiRequest->setApiKey($apiKey)->willReturn($apiRequest);
        }

        $event = $this->prophesize(PreResolveEvent::class);
        $event->getRequest()->willReturn($apiRequest);

        $this->assertNull($listener->onPreResolve($event->reveal()));
    }
}

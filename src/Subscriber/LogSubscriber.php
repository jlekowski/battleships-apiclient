<?php

namespace BattleshipsApi\Client\Subscriber;

use BattleshipsApi\Client\Event\ApiClientEvents;
use BattleshipsApi\Client\Event\OnErrorEvent;
use BattleshipsApi\Client\Event\PostRequestEvent;
use BattleshipsApi\Client\Event\PreResolveEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LogSubscriber implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ApiClientEvents::PRE_RESOLVE => 'onPreResolve',
            ApiClientEvents::POST_REQUEST => 'onPostRequest',
            ApiClientEvents::ON_ERROR => 'onError'
        ];
    }

    /**
     * @param PreResolveEvent $event
     */
    public function onPreResolve(PreResolveEvent $event)
    {
        $this->logger->debug('Request to be resolved', [
            'class' => get_class($event->getRequest())
        ]);
    }

    /**
     * @param PostRequestEvent $event
     */
    public function onPostRequest(PostRequestEvent $event)
    {
        $this->logger->debug('Request successful', [
            'class' => get_class($event->getRequest()),
            'response' => $event->getResponse()->getBody()
        ]);
    }

    /**
     * @param OnErrorEvent $event
     */
    public function onError(OnErrorEvent $event)
    {
        $exception = $event->getException();
        $this->logger->error('Request failed', [
            'class' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode()
        ]);
    }
}

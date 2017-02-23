<?php

namespace BattleshipsApi\Client\Client;

use BattleshipsApi\Client\Event\ApiClientEvents;
use BattleshipsApi\Client\Listener\RequestConfigListener;
use BattleshipsApi\Client\Subscriber\LogSubscriber;
use GuzzleHttp\Client;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApiClientFactory
{
    /**
     * @param array $config
     *     - baseUri: API base URI
     *     - timeout: Client's timeout (in seconds)
     *     - version: API version
     *     - key: API authentication key
     *     - logger: API calls logger (Psr\Log\LoggerInterface)
     *     - subscribers: API Client subscribers (Symfony\Component\EventDispatcher\EventSubscriberInterface[])
     *     - dispatcher: API Client event dispatcher (Symfony\Component\EventDispatcher\EventDispatcherInterface)
     * @return ApiClient
     */
    public static function build(array $config = [])
    {
        // client config
        $clientConfig = [
            'base_uri' => $config['baseUri'] ?? null,
            'timeout' => $config['timeout'] ?? null
        ];
        // request config
        $apiVersion = $config['version'] ?? null;
        $apiKey = $config['key'] ?? null;
        // dispatcher config
        $logger = $config['logger'] ?? null;
        /** @var EventSubscriberInterface[] $subscribers */
        $subscribers = isset($config['subscribers']) ? (array)$config['subscribers'] : [];
        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $config['dispatcher'] ?? new EventDispatcher();


        // set API version/key for each request if provided
        if ($apiVersion || $apiKey) {
            $requestConfigListener = new RequestConfigListener($apiVersion, $apiKey);
            $dispatcher->addListener(ApiClientEvents::PRE_RESOLVE, [$requestConfigListener, 'onPreResolve']);
        }

        // add logging (LogSubscriber)
        if ($logger !== null) {
            $subscribers[] = new LogSubscriber($logger);
        }

        // add subscribers
        foreach ($subscribers as $subscriber) {
            $dispatcher->addSubscriber($subscriber);
        }

        return new ApiClient(new Client($clientConfig), $dispatcher);
    }
}

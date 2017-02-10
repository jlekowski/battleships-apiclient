<?php

namespace BattleshipsApi\Client\Client;

use BattleshipsApi\Client\Event\ApiClientEvents;
use BattleshipsApi\Client\Listener\RequestConfigListener;
use BattleshipsApi\Client\Subscriber\LogSubscriber;
use GuzzleHttp\Client;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApiClientFactory
{
    /**
     * @param string $baseUri
     * @param int $apiVersion
     * @param string $apiKey
     * @return ApiClient
     */
    public static function build(string $baseUri, int $apiVersion = null, string $apiKey = null): ApiClient
    {
        $client = new Client(['base_uri' => $baseUri]);
        $dispatcher = new EventDispatcher();
        $apiClient = new ApiClient($client, $dispatcher);

        if ($apiVersion || $apiKey) {
            $requestConfigListener = new RequestConfigListener($apiVersion, $apiKey);
            $dispatcher->addListener(ApiClientEvents::PRE_RESOLVE, [$requestConfigListener, 'onPreResolve']);
        }

        // IS THIS NEEDED?
        if (class_exists('\CliLogger')) {
            $logger = new \CliLogger();
            $logSubscriber = new LogSubscriber($logger);
            $dispatcher->addSubscriber($logSubscriber);
        }

        return $apiClient;
    }

    /**
     * @param array $config
     *     - baseUri: API base URI
     *     - version: API version
     *     - key: API authentication key
     * @param array $options
     *     - logger: Logger for API calls (LoggerInterface)
     *     - subscribers: API Client subscribers (EventSubscriberInterface[])
     * @return ApiClient
     */
    public static function build1(array $config, array $options = [])
    {
        $baseUri = $config['baseUri'] ?? null;
        $apiVersion = $config['version'] ?? null;
        $apiKey = $config['key'] ?? null;
        $logger = $options['logger'] ?? null;
        /** @var EventSubscriberInterface[] $subscribers */
        $subscribers = isset($options['subscribers']) ? (array)$options['subscribers'] : [];

        if ($baseUri === null) {
            throw new \InvalidArgumentException('Missing `baseUri` config');
        }

        $client = new Client(['base_uri' => $baseUri]);
        $dispatcher = new EventDispatcher();
        $apiClient = new ApiClient($client, $dispatcher);

        if ($apiVersion || $apiKey) {
            $requestConfigListener = new RequestConfigListener($apiVersion, $apiKey);
            $dispatcher->addListener(ApiClientEvents::PRE_RESOLVE, [$requestConfigListener, 'onPreResolve']);
        }

        if ($logger !== null) {
            $subscribers[] = new LogSubscriber($logger);
        }

        foreach ($subscribers as $subscriber) {
            $dispatcher->addSubscriber($subscriber);
        }

        return $apiClient;
    }
}

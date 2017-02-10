<?php

namespace BattleshipsApi\Client\Listener;

use BattleshipsApi\Client\Event\PreResolveEvent;

class RequestConfigListener
{
    /**
     * @var int
     */
    protected $apiVersion;

    /**
     * @var string|null
     */
    protected $apiKey;

    /**
     * @param int $apiVersion
     * @param string $apiKey
     */
    public function __construct(int $apiVersion = null, string $apiKey = null)
    {
        $this->apiVersion = $apiVersion;
        $this->apiKey = $apiKey;
    }

    /**
     * @param int $apiVersion
     * @return $this|RequestConfigListener
     */
    public function setApiVersion(int $apiVersion): self
    {
        $this->apiVersion = $apiVersion;

        return $this;
    }

    /**
     * @param string|null $apiKey
     * @return $this|RequestConfigListener
     */
    public function setApiKey(string $apiKey = null): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @param PreResolveEvent $event
     * @throws \RuntimeException
     */
    public function onPreResolve(PreResolveEvent $event)
    {
        if ($this->apiVersion === null) {
            throw new \RuntimeException('API Version must be set');
        }

        $event->getRequest()
            ->setApiVersion($this->apiVersion)
            ->setApiKey($this->apiKey)
        ;
    }
}

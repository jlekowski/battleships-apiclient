<?php

namespace BattleshipsApi\Client\Client;

use BattleshipsApi\Client\Event\ApiClientEvents;
use BattleshipsApi\Client\Event\OnErrorEvent;
use BattleshipsApi\Client\Event\PostRequestEvent;
use BattleshipsApi\Client\Event\PreResolveEvent;
use BattleshipsApi\Client\Response\ApiResponse;
use BattleshipsApi\Client\Exception\ApiException;
use BattleshipsApi\Client\Request\ApiRequest;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;

class ApiClient
{
    /**
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var string
     */
    protected $baseUri;

    /**
     * @param ClientInterface $httpClient
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(ClientInterface $httpClient, EventDispatcherInterface $dispatcher)
    {
        $this->httpClient = $httpClient;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param ApiRequest $request
     * @return ApiResponse
     * @throws ApiException
     * @throws ExceptionInterface
     */
    public function call(ApiRequest $request): ApiResponse
    {
        $this->dispatcher->dispatch(ApiClientEvents::PRE_RESOLVE, new PreResolveEvent($request));
        $request->resolve();
        try {
            $response = $this->httpClient->request(
                $request->getHttpMethod(),
                $request->getUri(),
                [
                    //RequestOptions::DEBUG => true,
                    RequestOptions::HEADERS => $request->getHeaders(),
                    RequestOptions::JSON => $request->getData(),
                    'base_uri' => $this->baseUri
                ]
            );
            $apiResponse = new ApiResponse($response);
        } catch (GuzzleException $e) {
            $this->dispatcher->dispatch(ApiClientEvents::ON_ERROR, new OnErrorEvent($e));
            throw new ApiException($e->getMessage(), $e->getCode(), $e);
        }

        $this->dispatcher->dispatch(ApiClientEvents::POST_REQUEST, new PostRequestEvent($request, $apiResponse));

        return $apiResponse;
    }

    /**
     * @param string|null $baseUri
     * @return $this|ApiClient
     */
    public function setBaseUri(string $baseUri = null): self
    {
        $this->baseUri = $baseUri;

        return $this;
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getDispatcher(): EventDispatcherInterface
    {
        return $this->dispatcher;
    }
}

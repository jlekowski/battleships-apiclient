<?php

namespace BattleshipsApi\Client\Event;

use BattleshipsApi\Client\Request\ApiRequest;
use BattleshipsApi\Client\Response\ApiResponse;
use Symfony\Component\EventDispatcher\Event;

class PostRequestEvent extends Event
{
    /**
     * @var ApiRequest
     */
    protected $request;

    /**
     * @var ApiResponse
     */
    protected $response;

    /**
     * @param ApiRequest $request
     * @param ApiResponse $response
     */
    public function __construct(ApiRequest $request, ApiResponse $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @return ApiRequest
     */
    public function getRequest(): ApiRequest
    {
        return $this->request;
    }

    /**
     * @return ApiResponse
     */
    public function getResponse(): ApiResponse
    {
        return $this->response;
    }
}

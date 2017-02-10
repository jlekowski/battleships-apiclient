<?php

namespace BattleshipsApi\Client\Event;

use BattleshipsApi\Client\Request\ApiRequest;
use Symfony\Component\EventDispatcher\Event;

class PreResolveEvent extends Event
{
    /**
     * @var ApiRequest
     */
    protected $request;

    /**
     * @param ApiRequest $request
     */
    public function __construct(ApiRequest $request)
    {
        $this->request = $request;
    }

    /**
     * @return ApiRequest
     */
    public function getRequest(): ApiRequest
    {
        return $this->request;
    }
}

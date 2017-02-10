<?php

namespace BattleshipsApi\Client\Exception;

use BattleshipsApi\Client\Response\ApiResponse;
use GuzzleHttp\Exception\RequestException;

class ApiException extends \Exception
{
    /**
     * @var ApiResponse|null
     */
    protected $response;

    /**
     * @inheritdoc
     */
    public function __construct($message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        if ($previous instanceof RequestException) {
            $response = $previous->getResponse();
            if ($response) {
                $this->response = new ApiResponse($response);
            }
        }
    }

    /**
     * @return ApiResponse|null
     */
    public function getApiResponse()
    {
        return $this->response;
    }
}

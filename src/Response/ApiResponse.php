<?php

namespace BattleshipsApi\Client\Response;

use Psr\Http\Message\ResponseInterface;

class ApiResponse
{
    const HEADER_API_KEY = 'Api-Key';
    const HEADER_VARNISH_DEBUG = 'X-Cache';

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var string
     */
    protected $body;

    /**
     * @var mixed
     */
    protected $json;

    /**
     * @var string
     */
    protected $jsonError = '';

    /**
     * @param ResponseInterface $response
     * @throws \InvalidArgumentException
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
        $this->body = $this->response->getBody()->getContents();
        try {
            $this->json = \GuzzleHttp\json_decode($this->body);
        } catch (\InvalidArgumentException $e) {
            $this->jsonError = $e->getMessage();
        }
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return mixed Result of json_decode()
     */
    public function getJson()
    {
        return $this->json;
    }

    /**
     * @return string
     */
    public function getJsonError(): string
    {
        return $this->jsonError;
    }

    /**
     * @param string $header
     * @return string|null
     */
    public function getHeader(string $header)
    {
        return $this->response->hasHeader($header) ? $this->response->getHeader($header)[0] : null;
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return $this->response->getHeaders();
    }

    /**
     * @return int|null
     */
    public function getNewId()
    {
        $location = $this->getHeader('Location');
        if ($location === null) {
            return null;
        }

        preg_match('/\/(\d+)$/', $location, $match);

        return (int)$match[1];
    }
}

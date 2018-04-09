<?php

namespace BattleshipsApi\Client\Request;

use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApiRequest
{
    /**
     * @var OptionsResolver
     */
    protected $resolver;

    /**
     * @var array
     */
    private $config = [];

    /**
     * @var array
     */
    private $resolved = [];

    /**
     * @var bool
     */
    private $isResolved = false;

    /**
     * ApiRequest constructor
     */
    public function __construct()
    {
        $this->resolver = new OptionsResolver();
        $this->resolver
            ->setRequired(['uri', 'apiVersion', 'httpMethod', 'apiKey'])
            ->setDefined(['headers', 'requestData'])
            ->setDefault('apiVersion', 1)
            ->setDefault('headers', [])
            ->setAllowedTypes('uri', 'string')
            ->setAllowedTypes('apiVersion', 'int')
            ->setAllowedTypes('headers', 'array')
            ->setAllowedValues('httpMethod', ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'])
            ->setNormalizer('headers', function (Options $options, $value) {
                if ($options['apiKey'] !== null) {
                    $value['Authorization'] = sprintf('Bearer %s', $options['apiKey']);
                }

                return $value;
            })
            ->setNormalizer('uri', function (Options $options, $value) {
                // if full URL set, use it - otherwise concatenate API version with it
                return filter_var($value, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) ?: sprintf('/v%d%s', $options['apiVersion'], $value);
            })
        ;
        $this->configure();
    }

    /**
     * @return string
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function getUri(): string
    {
        return $this->get('uri');
    }

    /**
     * @param string $uri
     * @return $this
     */
    public function setUri(string $uri): self
    {
        return $this->set('uri', $uri);
    }

    /**
     * @return string
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function getHttpMethod(): string
    {
        return $this->get('httpMethod');
    }

    /**
     * @param string $method
     * @return $this
     */
    public function setHttpMethod(string $method): self
    {
        return $this->set('httpMethod', $method);
    }

    /**
     * @return mixed Something that works with json_encode()
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function getData()
    {
        return $this->get('requestData');
    }

    /**
     * @param mixed $data
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setData($data): self
    {
        // support pure json
        if (is_string($data)) {
            $data = \GuzzleHttp\json_decode($data);
        }

        return $this->set('requestData', $data);
    }

    /**
     * @return array
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function getHeaders(): array
    {
        return $this->get('headers');
    }

    /**
     * @param string|null $apiKey
     * @return $this
     */
    public function setApiKey(string $apiKey = null): self
    {
        return $this->set('apiKey', $apiKey);
    }

    /**
     * @param int $apiVersion
     * @return $this
     */
    public function setApiVersion(int $apiVersion): self
    {
        return $this->set('apiVersion', $apiVersion);
    }

    /**
     * Resolve provided options
     * @return $this
     * @throws ExceptionInterface
     */
    public function resolve(): self
    {
        if (!$this->isResolved) {
            $this->resolved = $this->resolver->resolve($this->config);
            $this->isResolved = true;
        }

        return $this;
    }

    /**
     * @param string $key
     * @return mixed
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function get(string $key)
    {
        if (!$this->isResolved) {
            throw new \RuntimeException('Config options have not been resolved yet');
        }

        if (!$this->resolver->isDefined($key)) {
            throw new \InvalidArgumentException(sprintf('Config option `%s` does not exist', $key));
        }

        return $this->resolved[$key] ?? null;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     * @throws \InvalidArgumentException
     */
    protected function set(string $key, $value): self
    {
        if (!$this->resolver->isDefined($key)) {
            throw new \InvalidArgumentException(sprintf('Config option `%s` does not exist', $key));
        }

        $this->config[$key] = $value;

        $this->isResolved = false;

        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     * @throws \InvalidArgumentException
     */
    protected function add(string $key, $value): self
    {
        // if we add to a key, it must be an array
        $value = (array)$value;
        // merge if values already exist
        if (array_key_exists($key, $this->config) && is_array($this->config[$key])) {
            $value = array_merge($this->config[$key], $value);
        }

        return $this->set($key, $value);
    }

    /**
     * Configure resolver's options
     */
    protected function configure()
    {
    }
}

<?php

namespace BattleshipsApi\Client\Request;

use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApiRequest
{
    /**
     * List of available data parameters
     */
    /* protected */ const DATA = [];

    /**
     * List of available query parameters
     */
    /* protected */ const QUERY = [];

    /* private */ const ALLOWED_HTTP_METHODS = [
        'get' => 'GET',
        'create' => 'POST',
        'update' => 'PUT',
        'edit' => 'PATCH',
        'delete' => 'DELETE',
        'options' => 'OPTIONS'
    ];

    /**
     * @var OptionsResolver
     */
    protected $resolver;

    /**
     * @var OptionsResolver
     */
    protected $dataResolver;

    /**
     * @var OptionsResolver
     */
    protected $queryResolver;

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
     * @var array
     */
    private $data = [];

    /**
     * @var array
     */
    private $query = [];

    /**
     * ApiRequest constructor
     */
    public function __construct()
    {
        $this->resolver = new OptionsResolver();
        $this->resolver
            ->setRequired(['uri', 'apiVersion', 'httpMethod', 'apiKey'])
            ->setDefined(['headers', 'requestData', 'query'])
            ->setDefault('apiVersion', 1)
            ->setDefault('httpMethod', function (Options $options) {
                $shortClassName = substr(strrchr(static::class, '\\'), 1);
                $classPrefix = preg_split('/(?=[A-Z])/', $shortClassName, -1, PREG_SPLIT_NO_EMPTY)[0];

                // return http method based on class name
                return self::ALLOWED_HTTP_METHODS[strtolower($classPrefix)] ?? null;
            })
            ->setDefault('headers', [])
            ->setDefault('requestData', function (Options $options) {
                return $this->dataResolver->resolve($this->data)['data'] ?: null;
            })
            ->setDefault('query', function (Options $options) {
                return $this->queryResolver->resolve($this->query)['query'] ?: null;
            })
            ->setAllowedTypes('uri', 'string')
            ->setAllowedTypes('apiVersion', 'int')
            ->setAllowedTypes('headers', 'array')
            ->setAllowedValues('httpMethod', array_values(self::ALLOWED_HTTP_METHODS))
            ->setNormalizer('headers', function (Options $options, $value) {
                if ($options['apiKey'] !== null) {
                    $value['Authorization'] = sprintf('Bearer %s', $options['apiKey']);
                }

                return $value;
            })
            ->setNormalizer('uri', function (Options $options, $value) {
                // if full URL set, use it
                $uri = filter_var($value, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED);
                if ($uri) {
                    return $uri;
                }

                // prefix with API Version
                $uri = sprintf('/v%d%s', $options['apiVersion'], $value);
                // suffix with query params if set
                if ($options['query']) {
                    $uri = sprintf('%s?%s', $uri, http_build_query($options['query']));
                }

                return $uri;
            })
        ;

        $this->dataResolver = new OptionsResolver();
        // just DATA if required or defined not set -> otherwise required or []
        $requiredData = static::DATA['required'] ?? (isset(static::DATA['defined']) ? [] : static::DATA);
        $definedData = static::DATA['defined'] ?? [];
        $this->dataResolver
            ->setRequired('data')
            ->setRequired($requiredData)
            ->setDefined($definedData)
            ->setDefault('data', function (Options $options) use ($requiredData, $definedData) {
                $requestData = [];
                foreach (array_merge($requiredData, $definedData) as $optionKey) {
                    if (isset($options[$optionKey])) {
                        $requestData[$optionKey] = $options[$optionKey];
                    }
                }

                return $requestData;
            })
        ;

        $this->queryResolver = new OptionsResolver();
        $this->queryResolver
            ->setRequired('query')
            ->setDefined(static::QUERY)
            ->setDefault('query', function (Options $options) {
                $filters = [];
                foreach (static::QUERY as $optionKey) {
                    if (isset($options[$optionKey])) {
                        $filters[$optionKey] = $options[$optionKey];
                    }
                }

                return $filters;
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
     * @param array $query
     * @return $this
     */
    public function setQueryParams(array $query): self
    {
        return $this->set('query', $query);
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
     * @param mixed $value
     * @return $this
     * @throws \InvalidArgumentException
     */
    protected function setQueryParam(string $key, $value): self
    {
        if (!$this->queryResolver->isDefined($key)) {
            throw new \InvalidArgumentException(sprintf('Query config option `%s` does not exist', $key));
        }

        $this->query[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     * @throws \InvalidArgumentException
     */
    protected function setDataParam(string $key, $value): self
    {
        if (!$this->dataResolver->isDefined($key)) {
            throw new \InvalidArgumentException(sprintf('Data config option `%s` does not exist', $key));
        }

        $this->data[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @return mixed
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    private function get(string $key)
    {
        if (!$this->isResolved) {
            throw new \RuntimeException('Config options have not been resolved yet');
        }

        if (!$this->resolver->isDefined($key)) {
            // @codeCoverageIgnoreStart
            throw new \InvalidArgumentException(sprintf('Config option `%s` does not exist', $key));
            // @codeCoverageIgnoreEnd
        }

        return $this->resolved[$key] ?? null;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     * @throws \InvalidArgumentException
     */
    private function set(string $key, $value): self
    {
        if (!$this->resolver->isDefined($key)) {
            // @codeCoverageIgnoreStart
            throw new \InvalidArgumentException(sprintf('Config option `%s` does not exist', $key));
            // @codeCoverageIgnoreEnd
        }

        $this->config[$key] = $value;

        $this->isResolved = false;

        return $this;
    }

    /**
     * Configure resolver's options
     */
    protected function configure()
    {
    }
}

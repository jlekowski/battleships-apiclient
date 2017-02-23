<?php

namespace BattleshipsApi\Client\Command;

use BattleshipsApi\Client\Client\ApiClient;
use Symfony\Component\Console\Command\Command;

abstract class ApiClientAwareCommand extends Command
{
    /**
     * @var ApiClient
     */
    protected $apiClient;

    /**
     * @inheritdoc
     */
    public function __construct(ApiClient $apiClient, $name = null)
    {
        parent::__construct($name);
        $this->apiClient = $apiClient;
    }
}

<?php

namespace Tests\Client;

use BattleshipsApi\Client\Client\ApiClient;
use BattleshipsApi\Client\Client\ApiClientFactory;
use PHPUnit\Framework\TestCase;

class ApiClientFactoryTest extends TestCase
{
    public function testBuild()
    {
        $apiClient = ApiClientFactory::build('http://battleships-api.vagrant:8080');
        $this->assertInstanceOf(ApiClient::class, $apiClient);
    }
}

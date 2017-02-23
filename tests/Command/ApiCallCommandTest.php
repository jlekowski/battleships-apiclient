<?php

namespace Tests\Command;

use BattleshipsApi\Client\Client\ApiClient;
use BattleshipsApi\Client\Command\ApiCallCommand;
use BattleshipsApi\Client\Request\ApiRequest;
use BattleshipsApi\Client\Response\ApiResponse;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ApiCallCommandTest extends TestCase
{
    /**
     * @var ApiCallCommand
     */
    protected $command;

    /**
     * @var CommandTester
     */
    protected $commandTester;

    /**
     * @var ApiClient|ObjectProphecy
     */
    protected $apiClient;

    public function setUp()
    {
        $this->apiClient = $this->prophesize(ApiClient::class);

        $application = new Application();
        $application->add(new ApiCallCommand($this->apiClient->reveal()));

        $this->command = $application->find('api:call');
        $this->commandTester = new CommandTester($this->command);
    }

    public function testExecute()
    {
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->apiClient->getDispatcher()->willReturn($eventDispatcher);

        $apiResponse = $this->prophesize(ApiResponse::class);
        $apiResponse->getBody()->willReturn('test-body');
        $apiResponse->getJson()->willReturn(['a' => 1]);
        $apiResponse->getHeaders()->willReturn(['test-header' => 'test']);
        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(299);
        $apiResponse->getResponse()->willReturn($response);
        $this->apiClient->call(Argument::type(ApiRequest::class))->willReturn($apiResponse);

        $this->commandTester->execute([
            'command' => $this->command->getName(),
            '--url' => 'http://battleships-api.test/v1/users/1',
            '--method' => 'GET',
            '--key' => 'my.4pi.k3y',
            '--data' => '{}'
        ]);

        $expectedOutput = "Body:\ntest-body\nHTTP Code: 299\n";
        $this->assertEquals($expectedOutput, $this->commandTester->getDisplay());
    }

    public function testExecuteVerbose()
    {
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->apiClient->getDispatcher()->willReturn($eventDispatcher);

        $apiResponse = $this->prophesize(ApiResponse::class);
        $apiResponse->getBody()->willReturn('test-body');
        $apiResponse->getJson()->willReturn(['a' => 1]);
        $apiResponse->getHeaders()->willReturn(['test-header' => 'test']);
        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(299);
        $apiResponse->getResponse()->willReturn($response);
        $this->apiClient->call(Argument::type(ApiRequest::class))->willReturn($apiResponse);

        $this->commandTester->execute([
            'command' => $this->command->getName(),
            '--url' => 'http://battleships-api.test/v1/users/1',
            '--method' => 'GET',
        ], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);

        $expectedOutput = "Body:\ntest-body\nHTTP Code: 299\nArray%a[a] => 1%aFinished in %f";
        $this->assertStringMatchesFormat($expectedOutput, $this->commandTester->getDisplay());
    }

    public function testExecuteVeryVerbose()
    {
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->apiClient->getDispatcher()->willReturn($eventDispatcher);

        $apiResponse = $this->prophesize(ApiResponse::class);
        $apiResponse->getBody()->willReturn('test-body');
        $apiResponse->getJson()->willReturn(['a' => 1]);
        $apiResponse->getHeaders()->willReturn(['test-header' => 'test']);
        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(299);
        $apiResponse->getResponse()->willReturn($response);
        $this->apiClient->call(Argument::type(ApiRequest::class))->willReturn($apiResponse);

        $this->commandTester->execute([
            'command' => $this->command->getName(),
            '--url' => 'http://battleships-api.test/v1/users/1',
            '--method' => 'GET',
        ], ['verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE]);

        $expectedOutput = "Body:\ntest-body\nHTTP Code: 299\nArray%a[a] => 1%aArray%a[test-header] => test%aFinished in %f";
        $this->assertStringMatchesFormat($expectedOutput, $this->commandTester->getDisplay());
    }
}

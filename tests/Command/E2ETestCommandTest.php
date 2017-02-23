<?php

namespace Tests\Command;

use BattleshipsApi\Client\Client\ApiClient;
use BattleshipsApi\Client\Command\E2ETestCommand;
use BattleshipsApi\Client\Request\ApiRequest;
use BattleshipsApi\Client\Response\ApiResponse;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class E2ETestCommandTest extends TestCase
{
    /**
     * @var E2ETestCommand
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
        $application->add(new E2ETestCommand($this->apiClient->reveal()));

        $this->command = $application->find('test:e2e');
        $this->commandTester = new CommandTester($this->command);
    }

    public function testExecute()
    {
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->apiClient->getDispatcher()->willReturn($eventDispatcher);

        $this->apiClient->setBaseUri('http://battleships-api.test:8080')->willReturn($this->apiClient);

        $apiResponse = $this->prophesize(ApiResponse::class);
        $apiResponse->getBody()->willReturn('test-body');
        $game = new \stdClass();
        $game->id = 999;
        $apiResponse->getJson()->willReturn([$game, $game], [$game, $game], [$game]);
        $apiResponse->getHeaders()->willReturn(['test-header' => 'test']);
        $apiResponse->getHeader(Argument::type('string'))->willReturn('test-header-value');
        $apiResponse->getNewId()->willReturn(999);

        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(299);
        $apiResponse->getResponse()->willReturn($response);

        $this->apiClient->call(Argument::type(ApiRequest::class))->willReturn($apiResponse);

        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'uri' => 'http://battleships-api.test:8080',
            'version' => 22
        ]);

        $expectedOutput = "User Id: 999
User API Key: test-header-value
Game Id: 999
Game for player
User Patched (name)
User details
Game to be Patched (player ships)
Game Patched (player ships)
Event to be Posted (chat)
Chat added
Event details
Other Id: 999
Other API Key: test-header-value
Available games for other
Game to be Patched (other join)
Game Patched (other join)
Game for other
Game to be Patched (other ships)
Game Patched (other ships)
Shot added
Game for player
Finished in %f";
        $this->assertStringMatchesFormat($expectedOutput, $this->commandTester->getDisplay());
    }
}

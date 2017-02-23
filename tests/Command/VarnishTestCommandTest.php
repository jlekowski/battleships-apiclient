<?php

namespace Tests\Command;

use BattleshipsApi\Client\Client\ApiClient;
use BattleshipsApi\Client\Command\VarnishTestCommand;
use BattleshipsApi\Client\Request\ApiRequest;
use BattleshipsApi\Client\Response\ApiResponse;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class VarnishTestCommandTest extends TestCase
{
    /**
     * @var VarnishTestCommand
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
        $application->add(new VarnishTestCommand($this->apiClient->reveal()));

        $this->command = $application->find('test:varnish');
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
        $apiResponse->getHeader(Argument::type('string'))->willReturn(
            VarnishTestCommand::VARNISH_DEBUG_MISS,
            VarnishTestCommand::VARNISH_DEBUG_MISS,
            VarnishTestCommand::VARNISH_DEBUG_MISS,
            VarnishTestCommand::VARNISH_DEBUG_MISS,
            VarnishTestCommand::VARNISH_DEBUG_MISS,
            VarnishTestCommand::VARNISH_DEBUG_MISS,
            VarnishTestCommand::VARNISH_DEBUG_MISS,
            VarnishTestCommand::VARNISH_DEBUG_HIT,
            VarnishTestCommand::VARNISH_DEBUG_MISS,
            VarnishTestCommand::VARNISH_DEBUG_MISS,
            VarnishTestCommand::VARNISH_DEBUG_HIT,
            VarnishTestCommand::VARNISH_DEBUG_MISS,
            VarnishTestCommand::VARNISH_DEBUG_MISS,
            VarnishTestCommand::VARNISH_DEBUG_HIT,
            VarnishTestCommand::VARNISH_DEBUG_MISS,
            VarnishTestCommand::VARNISH_DEBUG_HIT,
            VarnishTestCommand::VARNISH_DEBUG_MISS,
            VarnishTestCommand::VARNISH_DEBUG_HIT,
            VarnishTestCommand::VARNISH_DEBUG_MISS,
            VarnishTestCommand::VARNISH_DEBUG_MISS,
            VarnishTestCommand::VARNISH_DEBUG_HIT,
            VarnishTestCommand::VARNISH_DEBUG_MISS,
            VarnishTestCommand::VARNISH_DEBUG_HIT,
            VarnishTestCommand::VARNISH_DEBUG_MISS,
            VarnishTestCommand::VARNISH_DEBUG_MISS,
            VarnishTestCommand::VARNISH_DEBUG_HIT
        );
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

        $expectedOutput = "Finished in %f";
        $this->assertStringMatchesFormat($expectedOutput, $this->commandTester->getDisplay(), $this->commandTester->getDisplay());
    }
}

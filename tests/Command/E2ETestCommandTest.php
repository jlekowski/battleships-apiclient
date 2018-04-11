<?php

namespace Tests\Command;

use BattleshipsApi\Client\Client\ApiClient;
use BattleshipsApi\Client\Command\E2ETestCommand;
use BattleshipsApi\Client\Event\ApiClientEvents;
use BattleshipsApi\Client\Event\PostRequestEvent;
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

    public function testExecuteSetsPostRequestListener()
    {
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $event = $this->prophesize(PostRequestEvent::class);
        $request = $this->prophesize(ApiRequest::class);
        $response = $this->prophesize(ResponseInterface::class);
        $apiResponse = $this->prophesize(ApiResponse::class);


        $event->getRequest()->willReturn($request);
        $event->getResponse()->willReturn($apiResponse);

        $response->getStatusCode()->willReturn(204);

        $request->getHttpMethod()->willReturn('PATCH');

        $apiResponse->getResponse()->willReturn($response);
        $apiResponse->getJson()->willReturn([]);
        $apiResponse->getHeaders()->willReturn([]);
        $apiResponse->getNewId()->willReturn(999);
        $apiResponse->getHeader(Argument::type('string'))->willReturn('test-header-value');

        $this->apiClient->getDispatcher()->willReturn($eventDispatcher);
        $this->apiClient->setBaseUri('http://battleships-api.test:8080')->willReturn($this->apiClient);
        $this->apiClient->call(Argument::type(ApiRequest::class))->willReturn($apiResponse);


        $eventDispatcher->addSubscriber(Argument::cetera())->shouldBeCalled();
        $eventDispatcher->addListener(Argument::cetera())->shouldBeCalled();
        $eventDispatcher->addListener(ApiClientEvents::POST_REQUEST, Argument::that(function ($value) use ($event) {
            if (!($value instanceof \Closure)) {
                return false;
            }
            $value($event->reveal());
            return true;
        }))->shouldBeCalled();


        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'uri' => 'http://battleships-api.test:8080',
            'version' => 22
        ]);
    }

    public function testExecutePostRequestResponseVerification()
    {
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $event = $this->prophesize(PostRequestEvent::class);
        $request = $this->prophesize(ApiRequest::class);
        $response = $this->prophesize(ResponseInterface::class);
        $apiResponse = $this->prophesize(ApiResponse::class);


        $event->getRequest()->willReturn($request);
        $event->getResponse()->willReturn($apiResponse);

        $response->getStatusCode()->willReturn(201);
        $response->getHeader('Content-Type')->willReturn(['application/json']);

        $request->getHttpMethod()->willReturn('POST');

        $apiResponse->getResponse()->willReturn($response);
        $apiResponse->getJson()->willReturn([]);
        $apiResponse->getHeaders()->willReturn([]);
        $apiResponse->getNewId()->willReturn(999);
        $apiResponse->getHeader(Argument::type('string'))->willReturn('test-header-value');

        $this->apiClient->getDispatcher()->willReturn($eventDispatcher);
        $this->apiClient->setBaseUri('http://battleships-api.test:8080')->willReturn($this->apiClient);
        $this->apiClient->call(Argument::type(ApiRequest::class))->willReturn($apiResponse);


        $eventDispatcher->addSubscriber(Argument::cetera())->shouldBeCalled();
        $eventDispatcher->addListener(Argument::cetera())->shouldBeCalled();
        $eventDispatcher->addListener(ApiClientEvents::POST_REQUEST, Argument::that(function ($value) use ($event) {
            if (!($value instanceof \Closure)) {
                return false;
            }
            $value($event->reveal());
            return true;
        }))->shouldBeCalled();


        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'uri' => 'http://battleships-api.test:8080',
            'version' => 22
        ]);
    }

    public function testExecuteThrowsExceptionWhenMissingApplicationJsonHeader()
    {
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $event = $this->prophesize(PostRequestEvent::class);
        $request = $this->prophesize(ApiRequest::class);
        $response = $this->prophesize(ResponseInterface::class);
        $apiResponse = $this->prophesize(ApiResponse::class);


        $event->getRequest()->willReturn($request);
        $event->getResponse()->willReturn($apiResponse);

        $response->getStatusCode()->willReturn(200);
        $response->getHeader('Content-Type')->willReturn(['non-application/json']);
        $response->getBody()->willReturn('error');

        $request->getHttpMethod()->willReturn('GET');
        $request->getUri()->willReturn('http://uri');

        $apiResponse->getResponse()->willReturn($response);
        $apiResponse->getJson()->willReturn([]);
        $apiResponse->getHeaders()->willReturn([]);
        $apiResponse->getNewId()->willReturn(999);
        $apiResponse->getHeader(Argument::type('string'))->willReturn('test-header-value');

        $this->apiClient->getDispatcher()->willReturn($eventDispatcher);
        $this->apiClient->setBaseUri('http://battleships-api.test:8080')->willReturn($this->apiClient);
        $this->apiClient->call(Argument::type(ApiRequest::class))->willReturn($apiResponse);


        $eventDispatcher->addSubscriber(Argument::cetera())->shouldBeCalled();
        $eventDispatcher->addListener(Argument::cetera())->shouldBeCalled();
        $eventDispatcher->addListener(ApiClientEvents::POST_REQUEST, Argument::that(function ($value) use ($event) {
            if (!($value instanceof \Closure)) {
                return false;
            }
            try {
                $value($event->reveal());
            } catch (\Exception $e) {
                // can't catch exception for the whole test, so need to assert here
                $this->assertStringStartsWith('Incorrect content type returned', $e->getMessage());
                return true;
            }
            return true;
        }))->shouldBeCalled();


        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'uri' => 'http://battleships-api.test:8080',
            'version' => 22
        ]);
    }

    public function testExecuteThrowsExceptionOnUnexpectedHttpCode()
    {
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $event = $this->prophesize(PostRequestEvent::class);
        $request = $this->prophesize(ApiRequest::class);
        $response = $this->prophesize(ResponseInterface::class);
        $apiResponse = $this->prophesize(ApiResponse::class);


        $event->getRequest()->willReturn($request);
        $event->getResponse()->willReturn($apiResponse);

        $response->getStatusCode()->willReturn(201);
        $response->getHeader('Content-Type')->willReturn(['application/json']);

        $request->getHttpMethod()->willReturn('GET');
        $request->getUri()->willReturn('http://uri');

        $apiResponse->getResponse()->willReturn($response);
        $apiResponse->getJson()->willReturn([]);
        $apiResponse->getHeaders()->willReturn([]);
        $apiResponse->getNewId()->willReturn(999);
        $apiResponse->getHeader(Argument::type('string'))->willReturn('test-header-value');

        $this->apiClient->getDispatcher()->willReturn($eventDispatcher);
        $this->apiClient->setBaseUri('http://battleships-api.test:8080')->willReturn($this->apiClient);
        $this->apiClient->call(Argument::type(ApiRequest::class))->willReturn($apiResponse);


        $eventDispatcher->addSubscriber(Argument::cetera())->shouldBeCalled();
        $eventDispatcher->addListener(Argument::cetera())->shouldBeCalled();
        $eventDispatcher->addListener(ApiClientEvents::POST_REQUEST, Argument::that(function ($value) use ($event) {
            if (!($value instanceof \Closure)) {
                return false;
            }
            try {
                $value($event->reveal());
            } catch (\Exception $e) {
                // can't catch exception for the whole test, so need to assert here
                $this->assertStringStartsWith('Incorrect http code', $e->getMessage());
                return true;
            }
            return true;
        }))->shouldBeCalled();


        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'uri' => 'http://battleships-api.test:8080',
            'version' => 22
        ]);
    }
}

<?php

namespace BattleshipsApi\Client\Command;

use BattleshipsApi\Client\Event\ApiClientEvents;
use BattleshipsApi\Client\Event\PostRequestEvent;
use BattleshipsApi\Client\Listener\RequestConfigListener;
use BattleshipsApi\Client\Exception\E2eException;
use BattleshipsApi\Client\Request\ApiRequest;
use BattleshipsApi\Client\Request\Event\CreateEventRequest;
use BattleshipsApi\Client\Request\Event\EventTypes;
use BattleshipsApi\Client\Request\Event\GetEventRequest;
use BattleshipsApi\Client\Request\Event\GetEventsRequest;
use BattleshipsApi\Client\Request\Game\CreateGameRequest;
use BattleshipsApi\Client\Request\Game\GetGameRequest;
use BattleshipsApi\Client\Request\Game\GetGamesRequest;
use BattleshipsApi\Client\Request\Game\EditGameRequest;
use BattleshipsApi\Client\Request\User\CreateUserRequest;
use BattleshipsApi\Client\Request\User\GetUserRequest;
use BattleshipsApi\Client\Request\User\EditUserRequest;
use BattleshipsApi\Client\Response\ApiResponse;
use BattleshipsApi\Client\Subscriber\LogSubscriber;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class E2ETestCommand extends ApiClientAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('test:e2e')
            ->setDescription('Runs E2E test')
            ->addArgument('uri', InputArgument::OPTIONAL, 'API base uri', 'http://battleships-api.dev.lekowski.pl:6081')
            ->addArgument('version', InputArgument::OPTIONAL, 'API version', 1)
            ->addUsage('http://battleships-api.vagrant')
            ->addUsage('http://battleships-api.dev.lekowski.pl:6081 1 -vv')
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('execute');

        $baseUri = $input->getArgument('uri');
        $apiVersion = (int)$input->getArgument('version');

        // set base uri
        $this->apiClient->setBaseUri($baseUri);
        // get dispatcher
        $dispatcher = $this->apiClient->getDispatcher();

        $logger = new ConsoleLogger($output);
        $dispatcher->addSubscriber(new LogSubscriber($logger));

        $requestConfigListener = new RequestConfigListener($apiVersion);
        $dispatcher->addListener(ApiClientEvents::PRE_RESOLVE, [$requestConfigListener, 'onPreResolve']);
        $dispatcher->addListener(ApiClientEvents::POST_REQUEST, function (PostRequestEvent $event) {
            $this->verifyResponse($event->getResponse(), $event->getRequest());
        });


        // create user
        $request = new CreateUserRequest();
        $request
            ->setUserName('New Player')
        ;

        $response = $this->apiClient->call($request);
        $userId = $response->getNewId();
        $apiKey = $response->getHeader(ApiResponse::HEADER_API_KEY);
        $output->writeln(sprintf('User Id: %s', $userId));
        $output->writeln(sprintf('User API Key: %s', $apiKey));

        // set api key
        $requestConfigListener->setApiKey($apiKey);

        // create game
        $request = new CreateGameRequest();
        $response = $this->apiClient->call($request);
        $gameId = $response->getNewId();
        $output->writeln(sprintf('Game Id: %s', $gameId));

        // get game
        $request = new GetGameRequest();
        $request
            ->setGameId($gameId)
        ;
        $response = $this->apiClient->call($request);
        $output->writeln('Game for player');
        $this->outputResponse($output, $response);

        // update user
        $request = new EditUserRequest();
        $request
            ->setUserId($userId)
            ->setUserName('New Player 132')
        ;
        $this->apiClient->call($request);
        $output->writeln('User Patched (name)');

        // get user
        $request = new GetUserRequest();
        $request
            ->setUserId($userId)
        ;
        $response = $this->apiClient->call($request);
        $output->writeln('User details');
        $this->outputResponse($output, $response);

        // update game
        $request = new EditGameRequest();
        $request
            ->setGameId($gameId)
            ->setPlayerShips(['A1','C2','D2','F2','H2','J2','F5','F6','I6','J6','A7','B7','C7','F7','F8','I9','J9','E10','F10','G10'])
        ;
        $output->writeln('Game to be Patched (player ships)');
        $response = $this->apiClient->call($request);
        $output->writeln('Game Patched (player ships)');
        $this->outputResponse($output, $response);

        // create event
        $output->writeln('Event to be Posted (chat)');
        $request = new CreateEventRequest();
        $request
            ->setGameId($gameId)
            ->setEventType(EventTypes::TYPES['CHAT'])
            ->setEventValue('Test chat')
        ;
        $response = $this->apiClient->call($request);
        $eventId = $response->getNewId();
        $output->writeln('Chat added');
        $this->outputResponse($output, $response);

        // get event
        $request = new GetEventRequest();
        $request
            ->setGameId($gameId)
            ->setEventId($eventId)
        ;
        $response = $this->apiClient->call($request);
        $output->writeln('Event details');
        $this->outputResponse($output, $response);

        // create user2
        $request = new CreateUserRequest();
        $request
            ->setUserName('New Other')
        ;

        $response = $this->apiClient->call($request);
        $userId2 = $response->getNewId();
        $apiKey2 = $response->getHeader(ApiResponse::HEADER_API_KEY);
        $output->writeln(sprintf('Other Id: %s', $userId2));
        $output->writeln(sprintf('Other API Key: %s', $apiKey2));

        // set api key
        $requestConfigListener->setApiKey($apiKey2);

        // get games (available)
        $request = new GetGamesRequest();
        $request
            ->setAvailable(true)
        ;
        $response = $this->apiClient->call($request);
        $output->writeln('Available games for other');
        $this->outputResponse($output, $response);

        // update game
        $output->writeln('Game to be Patched (other join)');
        $request = new EditGameRequest();
        $request
            ->setGameId($gameId)
            ->setJoinGame(true)
        ;
        $response = $this->apiClient->call($request);
        $output->writeln('Game Patched (other join)');
        $this->outputResponse($output, $response);

        // get game
        $request = new GetGameRequest();
        $request
            ->setGameId($gameId)
        ;
        $response = $this->apiClient->call($request);
        $output->writeln('Game for other');
        $this->outputResponse($output, $response);

        // update game
        $output->writeln('Game to be Patched (other ships)');
        $request = new EditGameRequest();
        $request
            ->setGameId($gameId)
            ->setPlayerShips(['A1','E1','A2','D3','E3','F3','J3','H4','J4','A5','B5','C5','D5','J5','H6','B9','E9','F9','B10','H10'])
        ;
        $response = $this->apiClient->call($request);
        $output->writeln('Game Patched (other ships)');
        $this->outputResponse($output, $response);

        // set api key
        $requestConfigListener->setApiKey($apiKey);

        // create event
        $request = new CreateEventRequest();
        $request
            ->setGameId($gameId)
            ->setEventType(EventTypes::TYPES['SHOT'])
            ->setEventValue('B10')
        ;
        $response = $this->apiClient->call($request);
        $output->writeln('Shot added');
        $this->outputResponse($output, $response);

        // get events
        $request = new GetEventsRequest();
        $request
            ->setGameId($gameId)
            ->setGt(0)
        ;
        $response = $this->apiClient->call($request);
        $this->outputResponse($output, $response);

        // get events
        $request = new GetEventsRequest();
        $request
            ->setGameId($gameId)
            ->setGt(0)
            ->setType(EventTypes::TYPES['SHOT'])
        ;
        $response = $this->apiClient->call($request);
        $this->outputResponse($output, $response);

        // get game
        $request = new GetGameRequest();
        $request
            ->setGameId($gameId)
        ;
        $response = $this->apiClient->call($request);
        $output->writeln('Game for player');
        $this->outputResponse($output, $response);

        $event = $stopwatch->stop('execute');
        $output->writeln(sprintf('<info>Finished in %s</info>', $event->getDuration() / 1000));
    }

    /**
     * @param ApiResponse $response
     * @param ApiRequest $request
     * @throws E2eException
     */
    protected function verifyResponse(ApiResponse $response, ApiRequest $request)
    {
        // verify response header
        if ($response->getResponse()->getStatusCode() !== 204
            && $response->getResponse()->getHeader('Content-Type')[0] !== 'application/json'
        ) {
            throw new E2eException(
                sprintf(
                    'Incorrect content type returned: %s (method: %s, path: %s, response: %s)',
                    $response->getResponse()->getHeader('Content-Type')[0],
                    $request->getHttpMethod(),
                    $request->getUri(),
                    $response->getResponse()->getBody()
                )
            );
        }

        switch ($request->getHttpMethod()) {
            case 'POST':
                $expectedHttpCode = 201;
                break;
            case 'PATCH':
                $expectedHttpCode = 204;
                break;
            case 'GET':
            default:
                $expectedHttpCode = 200;
                break;
        }

        if ($response->getResponse()->getStatusCode() !== $expectedHttpCode) {
            throw new E2eException(
                sprintf(
                    'Incorrect http code: %s instead of %s for method %s and path %s (body: %s)',
                    $response->getResponse()->getStatusCode(),
                    $expectedHttpCode,
                    $request->getHttpMethod(),
                    $request->getUri(),
                    print_r($response->getJson(), true)
                )
            );
        }
    }

    /**
     * @param OutputInterface $output
     * @param ApiResponse $response
     */
    protected function outputResponse(OutputInterface $output, $response)
    {
        $output->writeln(sprintf('<comment>%s</comment>', print_r($response->getJson(), true)), OutputInterface::VERBOSITY_VERBOSE);
        $output->writeln(sprintf('<comment>%s</comment>', print_r($response->getHeaders(), true)), OutputInterface::VERBOSITY_VERY_VERBOSE);
    }
}

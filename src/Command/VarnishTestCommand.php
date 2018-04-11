<?php

namespace BattleshipsApi\Client\Command;

use BattleshipsApi\Client\Event\ApiClientEvents;
use BattleshipsApi\Client\Event\PostRequestEvent;
use BattleshipsApi\Client\Listener\RequestConfigListener;
use BattleshipsApi\Client\Exception\UnexpectedHeaderException;
use BattleshipsApi\Client\Request\Event\CreateEventRequest;
use BattleshipsApi\Client\Request\Event\EventTypes;
use BattleshipsApi\Client\Request\Event\GetEventsRequest;
use BattleshipsApi\Client\Request\Game\CreateGameRequest;
use BattleshipsApi\Client\Request\Game\GetGamesRequest;
use BattleshipsApi\Client\Request\Game\EditGameRequest;
use BattleshipsApi\Client\Request\User\CreateUserRequest;
use BattleshipsApi\Client\Response\ApiResponse;
use BattleshipsApi\Client\Subscriber\LogSubscriber;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class VarnishTestCommand extends ApiClientAwareCommand
{
    const VARNISH_DEBUG_HIT = 'HIT';
    const VARNISH_DEBUG_MISS = 'MISS';
    // 100ms to prevent calling Varnish before cache is cleared (happens on VMs with no network latency)
    const VARNISH_INVALIDATION_LAG_SLEEP = 100000;

    /**
     * @var bool
     */
    private $varnishDebugEnabled = false;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('test:varnish')
            ->setDescription('Runs Varnish test')
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
        $dispatcher->addListener(ApiClientEvents::POST_REQUEST, function (PostRequestEvent $event) use ($logger) {
            $logger->info(sprintf(
                'Request `%s` cache: %s',
                get_class($event->getRequest()),
                $event->getResponse()->getHeader(ApiResponse::HEADER_VARNISH_DEBUG)
            ));
        });

        // Add player 1
        $request = new CreateUserRequest();
        $request->setUserName('New Player');
        $response = $this->apiClient->call($request);
        $apiKey = $response->getHeader(ApiResponse::HEADER_API_KEY);

        // set api key
        $requestConfigListener->setApiKey($apiKey);

        $varnishDebugHeader = $response->getHeader(ApiResponse::HEADER_VARNISH_DEBUG);
        if ($varnishDebugHeader !== null) {
            $this->varnishDebugEnabled = true;
        } else {
            $output->writeln('<question>DEBUG from Varnish is not available - set varnish_debug to true</question>');
        }
        $this->verifyHeader($varnishDebugHeader, self::VARNISH_DEBUG_MISS);


        // Add game 1
        $request = new CreateGameRequest();
        $response = $this->apiClient->call($request);
        $gameId1 = $response->getNewId();
        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_MISS);


        // Add game 2
        $request = new CreateGameRequest();
        $response = $this->apiClient->call($request);
        $gameId2 = $response->getNewId();
        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_MISS);


        // Add player 2
        $request = new CreateUserRequest();
        $request->setUserName('New Player2');
        $response = $this->apiClient->call($request);
        $apiKey2 = $response->getHeader(ApiResponse::HEADER_API_KEY);
        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_MISS);

        // set api key
        $requestConfigListener->setApiKey($apiKey2);

        // Get available games
        $request = new GetGamesRequest();
        $request->setAvailable(true);
        $response = $this->apiClient->call($request);
        $availableGames = $response->getJson();

        $expectedGames = [$gameId1, $gameId2];
        $foundGames = [];
        foreach ($availableGames as $availableGame) {
            if (in_array($availableGame->id, $expectedGames, true)) {
                $foundGames[] = $availableGame->id;
            }
        }
        sort($foundGames);
        if ($foundGames !== $expectedGames) {
            throw new \Exception(
                sprintf('Got games: %s (expected: %s)', implode(',', $foundGames), implode(',', $expectedGames))
            );
        }

        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_MISS);


        // Get available games from Varnish
        $request = new GetGamesRequest();
        $request->setAvailable(true);
        $response = $this->apiClient->call($request);
        $availableGames = $response->getJson();

        $expectedGames = [$gameId1, $gameId2];
        $foundGames = [];
        foreach ($availableGames as $availableGame) {
            if (in_array($availableGame->id, $expectedGames, true)) {
                $foundGames[] = $availableGame->id;
            }
        }
        sort($foundGames);
        if ($foundGames !== $expectedGames) {
            throw new \Exception(
                sprintf('Got games: %s (expected: %s)', implode(',', $foundGames), implode(',', $expectedGames))
            );
        }

        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_HIT);


        // Join gameId2
        $request = new EditGameRequest();
        $request
            ->setGameId($gameId2)
            ->setJoinGame(true)
        ;
        $response = $this->apiClient->call($request);

        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_MISS);
        usleep(self::VARNISH_INVALIDATION_LAG_SLEEP);


        // Get available games
        $request = new GetGamesRequest();
        $request->setAvailable(true);
        $response = $this->apiClient->call($request);
        $availableGames = $response->getJson();

        $expectedGames = [$gameId1];
        $foundGames = [];
        foreach ($availableGames as $availableGame) {
            if (in_array($availableGame->id, $expectedGames, true)) {
                $foundGames[] = $availableGame->id;
            }
        }
        sort($foundGames);
        if ($foundGames !== $expectedGames) {
            throw new \Exception(
                sprintf('Got games: %s (expected: %s)', implode(',', $foundGames), implode(',', $expectedGames))
            );
        }

        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_MISS);


        // Get available games from varnish
        $request = new GetGamesRequest();
        $request->setAvailable(true);
        $response = $this->apiClient->call($request);
        $availableGames = $response->getJson();

        $expectedGames = [$gameId1];
        $foundGames = [];
        foreach ($availableGames as $availableGame) {
            if (in_array($availableGame->id, $expectedGames, true)) {
                $foundGames[] = $availableGame->id;
            }
        }
        sort($foundGames);
        if ($foundGames !== $expectedGames) {
            throw new \Exception(
                sprintf('Got games: %s (expected: %s)', implode(',', $foundGames), implode(',', $expectedGames))
            );
        }

        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_HIT);


        /*
         * Add shot event, add chat event, get all, get shot only, get gt=shoteventid, add shot event, get gt=shoteventid again
         */
        // Add chat
        $request = new CreateEventRequest();
        $request
            ->setGameId($gameId2)
            ->setEventType(EventTypes::TYPES['CHAT'])
            ->setEventValue('Test chat')
        ;
        $response = $this->apiClient->call($request);
        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_MISS);


        // Get all events
        $request = new GetEventsRequest();
        $request
            ->setGameId($gameId2)
        ;
        $response = $this->apiClient->call($request);
        $joinEventId = $response->getJson()[0]->id;
        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_MISS);


        // Get all events from Varnish
        $request = new GetEventsRequest();
        $request->setGameId($gameId2);
        $response = $this->apiClient->call($request);
        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_HIT);


        // Get all join_game events
        $request = new GetEventsRequest();
        $request
            ->setGameId($gameId2)
            ->setType(EventTypes::TYPES['JOIN_GAME'])
        ;
        $response = $this->apiClient->call($request);
        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_MISS);


        // Get all join_game events from Varnish
        $response = $this->apiClient->call($request);
        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_HIT);


        // Get all new events
        $request = new GetEventsRequest();
        $request
            ->setGameId($gameId2)
            ->setGt($joinEventId)
        ;
        $response = $this->apiClient->call($request);
        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_MISS);


        // Get all join_game events from Varnish
        $response = $this->apiClient->call($request);
        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_HIT);


        // Add chat
        $request = new CreateEventRequest();
        $request
            ->setGameId($gameId2)
            ->setEventType(EventTypes::TYPES['CHAT'])
            ->setEventValue('Test chat2')
        ;
        $response = $this->apiClient->call($request);
        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_MISS);
        usleep(self::VARNISH_INVALIDATION_LAG_SLEEP);


        // Get all events
        $request = new GetEventsRequest();
        $request->setGameId($gameId2);
        $response = $this->apiClient->call($request);
        $joinEventId = $response->getJson()[0]->id;
        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_MISS);


        // Get all events from Varnish
        $response = $this->apiClient->call($request);
        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_HIT);


        // Get all join_game events
        $request = new GetEventsRequest();
        $request
            ->setGameId($gameId2)
            ->setType(EventTypes::TYPES['JOIN_GAME'])
        ;
        $response = $this->apiClient->call($request);
        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_MISS);


        // Get all join_game events from Varnish
        $response = $this->apiClient->call($request);
        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_HIT);


        // set api key
        $requestConfigListener->setApiKey($apiKey);

        // Get all join_game events not from Varnish for user1 (caching per user)
        $response = $this->apiClient->call($request);
        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_MISS);


        // Get all new events for user1
        $request = new GetEventsRequest();
        $request
            ->setGameId($gameId2)
            ->setGt($joinEventId)
        ;
        $response = $this->apiClient->call($request);
        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_MISS);


        // Get all new events from Varnish for user1
        $response = $this->apiClient->call($request);
        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_HIT);


        $event = $stopwatch->stop('execute');
        $output->writeln(sprintf('<info>Finished in %s</info>', $event->getDuration() / 1000));
    }

    /**
     * @param string $headerReceived
     * @param string $headerExpected
     * @throws UnexpectedHeaderException
     */
    private function verifyHeader($headerReceived, $headerExpected)
    {
        if ($this->varnishDebugEnabled && $headerReceived !== $headerExpected) {
            throw new UnexpectedHeaderException($headerReceived, $headerExpected);
        }
    }

    /**
     * @param ApiResponse $response
     * @param string $headerExpected
     * @throws UnexpectedHeaderException
     */
    private function verifyHeaderFromResponse(ApiResponse $response, $headerExpected)
    {
        $this->verifyHeader($response->getHeader(ApiResponse::HEADER_VARNISH_DEBUG), $headerExpected);
    }
}

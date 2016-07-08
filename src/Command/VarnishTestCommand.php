<?php

namespace BattleshipsApi\Client\Command;

use BattleshipsApi\Client\Exception\UnexpectedHeaderException;
use BattleshipsApi\Client\Request\ApiRequest;
use BattleshipsApi\Client\Request\RequestDetails;
use BattleshipsApi\Client\Response\ApiResponse;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VarnishTestCommand extends Command
{
    const VARNISH_DEBUG_HIT = 'HIT';
    const VARNISH_DEBUG_MISS = 'MISS';
    // 100ms to prevent calling Varnish before cache is cleared (happens on VMs with no network latency)
    const VARNISH_RACE_CONDITION_SLEEP = 100000;

    /**
     * @var bool
     */
    private $varnishDebugEnabled = false;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        // @todo default API url to be in config
        $this
            ->setName('test:varnish')
            ->setDescription('Runs Varnish test')
            ->addArgument('url', InputArgument::OPTIONAL, 'API url', 'http://battleships-api.dev.lekowski.pl:6081/v1')
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);
        $baseUrl = $input->getArgument('url');
        $apiRequest = new ApiRequest($baseUrl);


        // Add player 1
        $data = new \stdClass();
        $data->name = 'New Player';

        $requestDetails = new RequestDetails('/users', 'POST', $data, 201);
        $response = $apiRequest->call($requestDetails);

        $user1 = new \stdClass();
        $user1->name = $data->name;
        $user1->id = $apiRequest->getNewId($response);
        $user1->apiKey = $response->getHeader(ApiRequest::HEADER_API_KEY);
        $apiRequest->setAuthToken($user1->apiKey);

        $varnishDebugHeader = $response->getHeader(ApiRequest::HEADER_VARNISH_DEBUG);
        if ($varnishDebugHeader !== null) {
            $this->varnishDebugEnabled = true;
        } else {
            $output->writeln('<question>DEBUG from Varnish is not available - set varnish_debug to true</question>');
        }
        $this->verifyHeader($varnishDebugHeader, self::VARNISH_DEBUG_MISS);


        // Add game 1
        $requestDetails = new RequestDetails('/games', 'POST', new \stdClass(), 201);
        $response = $apiRequest->call($requestDetails);

        $gameId1 = $apiRequest->getNewId($response);
        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_MISS);


        // Add game 2
        $requestDetails = new RequestDetails('/games', 'POST', new \stdClass(), 201);
        $response = $apiRequest->call($requestDetails);

        $gameId2 = $apiRequest->getNewId($response);
        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_MISS);


        // Add player 2
        $data = new \stdClass();
        $data->name = 'New Player2';

        $requestDetails = new RequestDetails('/users', 'POST', $data, 201);
        $response = $apiRequest->call($requestDetails);

        $user2 = new \stdClass();
        $user2->name = $data->name;
        $user2->id = $apiRequest->getNewId($response);
        $user2->apiKey = $response->getHeader(ApiRequest::HEADER_API_KEY);

        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_MISS);


        // Get available games
        $apiRequest->setAuthToken($user2->apiKey);
        $requestDetails = new RequestDetails('/games?available=true', 'GET', null, 200);

        $response = $apiRequest->call($requestDetails);
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
        $apiRequest->setAuthToken($user2->apiKey);
        $requestDetails = new RequestDetails('/games?available=true', 'GET', null, 200);

        $response = $apiRequest->call($requestDetails);
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
        $data = new \stdClass();
        $data->joinGame = true;

        $requestDetails = new RequestDetails(sprintf('/games/%s', $gameId2), 'PATCH', $data, 204);
        $response = $apiRequest->call($requestDetails);

        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_MISS);
        usleep(self::VARNISH_RACE_CONDITION_SLEEP);


        // Get available games
        $requestDetails = new RequestDetails('/games?available=true', 'GET', null, 200);

        $response = $apiRequest->call($requestDetails);
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
        $apiRequest->setAuthToken($user2->apiKey);
        $requestDetails = new RequestDetails('/games?available=true', 'GET', null, 200);

        $response = $apiRequest->call($requestDetails);
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
        $data = new \stdClass();
        $data->type = ApiRequest::EVENT_TYPE_CHAT;
        $data->value = 'Test chat';

        $requestDetails = new RequestDetails(sprintf('/games/%s/events', $gameId2), 'POST', $data, 201);
        $response = $apiRequest->call($requestDetails);

        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_MISS);


        // Get all events
        $requestDetails = new RequestDetails(sprintf('/games/%s/events', $gameId2), 'GET', null, 200);
        $response = $apiRequest->call($requestDetails);
        $joinEventId = $response->getJson()[0]->id;

        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_MISS);


        // Get all events from Varnish
        $requestDetails = new RequestDetails(sprintf('/games/%s/events', $gameId2), 'GET', null, 200);
        $response = $apiRequest->call($requestDetails);

        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_HIT);


        // Get all join_game events
        $requestDetails = new RequestDetails(sprintf('/games/%s/events?type=%s', $gameId2, ApiRequest::EVENT_TYPE_JOIN_GAME), 'GET', null, 200);
        $response = $apiRequest->call($requestDetails);

        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_MISS);


        // Get all join_game events from Varnish
        $requestDetails = new RequestDetails(sprintf('/games/%s/events?type=%s', $gameId2, ApiRequest::EVENT_TYPE_JOIN_GAME), 'GET', null, 200);
        $response = $apiRequest->call($requestDetails);

        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_HIT);


        // Get all new events
        $requestDetails = new RequestDetails(sprintf('/games/%s/events?gt=%s', $gameId2, $joinEventId), 'GET', null, 200);
        $response = $apiRequest->call($requestDetails);

        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_MISS);


        // Get all join_game events from Varnish
        $requestDetails = new RequestDetails(sprintf('/games/%s/events?gt=%s', $gameId2, $joinEventId), 'GET', null, 200);
        $response = $apiRequest->call($requestDetails);

        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_HIT);


        // Add chat
        $data = new \stdClass();
        $data->type = ApiRequest::EVENT_TYPE_CHAT;
        $data->value = 'Test chat2';

        $requestDetails = new RequestDetails(sprintf('/games/%s/events', $gameId2), 'POST', $data, 201);
        $response = $apiRequest->call($requestDetails);

        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_MISS);
        usleep(self::VARNISH_RACE_CONDITION_SLEEP);


        // Get all events
        $requestDetails = new RequestDetails(sprintf('/games/%s/events', $gameId2), 'GET', null, 200);
        $response = $apiRequest->call($requestDetails);
        $joinEventId = $response->getJson()[0]->id;

        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_MISS);


        // Get all events from Varnish
        $requestDetails = new RequestDetails(sprintf('/games/%s/events', $gameId2), 'GET', null, 200);
        $response = $apiRequest->call($requestDetails);

        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_HIT);


        // Get all join_game events
        $requestDetails = new RequestDetails(sprintf('/games/%s/events?type=%s', $gameId2, ApiRequest::EVENT_TYPE_JOIN_GAME), 'GET', null, 200);
        $response = $apiRequest->call($requestDetails);

        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_MISS);


        // Get all join_game events not from Varnish
        $requestDetails = new RequestDetails(sprintf('/games/%s/events?type=%s', $gameId2, ApiRequest::EVENT_TYPE_JOIN_GAME), 'GET', null, 200);
        $response = $apiRequest->call($requestDetails);

        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_HIT);


        // Get all join_game events not from Varnish for user1 (caching per user)
        $apiRequest->setAuthToken($user1->apiKey);
        $requestDetails = new RequestDetails(sprintf('/games/%s/events?type=%s', $gameId2, ApiRequest::EVENT_TYPE_JOIN_GAME), 'GET', null, 200);
        $response = $apiRequest->call($requestDetails);

        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_MISS);


        // Get all new events for user1
        $requestDetails = new RequestDetails(sprintf('/games/%s/events?gt=%s', $gameId2, $joinEventId), 'GET', null, 200);
        $response = $apiRequest->call($requestDetails);

        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_MISS);


        // Get all new events from Varnish for user1
        $requestDetails = new RequestDetails(sprintf('/games/%s/events?gt=%s', $gameId2, $joinEventId), 'GET', null, 200);
        $response = $apiRequest->call($requestDetails);

        $this->verifyHeaderFromResponse($response, self::VARNISH_DEBUG_HIT);


        $output->writeln(sprintf('<info>Finished in %s</info>', microtime(true) - $start));
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
        $this->verifyHeader($response->getHeader(ApiRequest::HEADER_VARNISH_DEBUG), $headerExpected);
    }

    /**
     * @param OutputInterface $output
     * @param ApiResponse $response
     */
    private function outputResponse(OutputInterface $output, ApiResponse $response)
    {
        $output->writeln(sprintf('<comment>%s</comment>', print_r($response->getJson(), true)), OutputInterface::VERBOSITY_VERBOSE);
        $output->writeln(sprintf('<comment>%s</comment>', print_r($response->getHeaders(), true)), OutputInterface::VERBOSITY_VERY_VERBOSE);
    }
}

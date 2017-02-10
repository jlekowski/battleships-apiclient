<?php

namespace BattleshipsApi\Client\Command;

use BattleshipsApi\Client\Client\ApiClient;
use BattleshipsApi\Client\Request\ApiRequest;
use BattleshipsApi\Client\Response\ApiResponse;
use BattleshipsApi\Client\Subscriber\LogSubscriber;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Stopwatch\Stopwatch;

class ApiCallCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('api:call')
            ->setDescription('Calls API')
            ->addUsage('--url http://battleships-api.dev.lekowski.pl/v1/users/1 --method GET --key my.4pi.k3y')
            ->addOption('url', 'u', InputOption::VALUE_REQUIRED, 'API request url')
            ->addOption('method', 'm', InputOption::VALUE_REQUIRED, 'HTTP method')
            ->addOption('key', 'k', InputOption::VALUE_OPTIONAL, 'API key')
            ->addOption('data', 'd', InputOption::VALUE_OPTIONAL, 'HTTP data')
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('execute');

        $url = $input->getOption('url');
        $method = $input->getOption('method');
        $apiKey = $input->getOption('key');
        $data = $input->getOption('data');

        // declare ApiClient
        $client = new Client(['timeout' => 2]);
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new LogSubscriber(new ConsoleLogger($output)));
        $apiClient = new ApiClient($client, $dispatcher);

        $apiRequest = new ApiRequest();
        $apiRequest
            ->setUri($url)
            ->setHttpMethod($method)
            ->setApiKey($apiKey)
            ->setData($data)
        ;

        $apiResponse = $apiClient->call($apiRequest);
        $output->writeln('Body:');
        $output->writeln($apiResponse->getBody());
        $output->writeln(sprintf('HTTP Code: %d', $apiResponse->getResponse()->getStatusCode()));
        $this->outputResponse($output, $apiResponse);

        $event = $stopwatch->stop('execute');
        $output->writeln(sprintf('<info>Finished in %d</info>', $event->getDuration()), OutputInterface::VERBOSITY_VERBOSE);
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

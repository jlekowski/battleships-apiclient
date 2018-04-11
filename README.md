[![Build Status](https://travis-ci.org/jlekowski/battleships-apiclient.svg?branch=master)](https://travis-ci.org/jlekowski/battleships-apiclient)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/73cc6cc3-4028-4dde-abcd-41e66dd6f328/mini.png)](https://insight.sensiolabs.com/projects/73cc6cc3-4028-4dde-abcd-41e66dd6f328)

# Battleships (PHP Client library)

## Battleships (sea battle) game - API client in PHP
PHP Client library to communicated with Battleships API.

### DEMO
http://dev.lekowski.pl

### LINKS
* https://github.com/jlekowski/battleships-api - API the library is for
* https://github.com/jlekowski/battleships-offline - offline version
* https://github.com/jlekowski/battleships-webclient - Web Client for API
* https://github.com/jlekowski/battleships - legacy full web version

## === Installation ===
1. Download/clone this repository.
2. Install dev dependencies.
```
composer install --no-dev
```

## === Test ===
1. Install dev dependencies.
```
composer install --dev
```
2. Run unit tests.
```
bin/phpunit
```
3. Run E2E tests.
```
bin/console test:e2e
```
4. Run Varnish tests.
```
bin/console test:varnish
```

## === Usage ===
1. From command line.
```
# create a user
bin/console api:call -vv --url http://battleships-api.dev.lekowski.pl/v1/users --method POST --data '{"name":"John"}'

# using URL (with user id) from `Location` header and key from `Api-Key` header get user details
bin/console api:call --url http://battleships-api.dev.lekowski.pl/v1/users/{userid} --method GET --key {apikey}

# you can also go crazy and both craete a user and get its details in one command
bin/console api:call -vv --url http://battleships-api.dev.lekowski.pl/v1/users --method POST --data '{"name":"John"}' | tee /dev/stderr | grep -E "(Api-Key|Location)\]" -A 2 | grep "[0]" | sed 's/.* => //' | tr '\n' '\t' | awk '{print "bin/console api:call --url " $2 " --method GET --key " $1}' | bash
```
2. In PHP Code (see [E2ETestCommand](src/Command/E2ETestCommand.php) for more examples).
```
$apiClient = BattleshipsApi\Client\Client\ApiClientFactory::build();
# or with full config
$apiClient = BattleshipsApi\Client\Client\ApiClientFactory::build([
    'baseUri' => 'http://battleships-api.dev.lekowski.pl',
    'version' => 1,
    'key' => null,
    'timeout' => null,
    'logger' => null,
    'subscribers' => null,
    'dispatcher' => null
]);

// create user
$request = new BattleshipsApi\Client\Request\User\CreateUserRequest();
$request->setUserName('New Player');

$response = $apiClient->call($request);
$userId = $response->getNewId();
$apiKey = $response->getHeader(BattleshipsApi\Client\Response\ApiResponse::HEADER_API_KEY);

// get user details
$request = new BattleshipsApi\Client\Request\User\GetUserRequest();
$request
    ->setUserId($userId)
    ->setApiKey($apiKey)
;

$response = $apiClient->call($request);
$userDetails = $response->getJson();
```

## === Changelog ===

* version **1.1**
  * Update* request classes start now with Edit* (**backward incompatibility**)
  * ApiRequest class knows subclass's HTTP method, and offers better support for queries and data
  * `composer` accepts Symfony4 components
* version **1.0**
 * Working version of the PHP Client library deployed

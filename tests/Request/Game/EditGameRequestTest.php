<?php

namespace Tests\Request\Game;

use BattleshipsApi\Client\Request\Game\EditGameRequest;
use PHPUnit\Framework\TestCase;

class EditGameRequestTest extends TestCase
{
    /**
     * @var EditGameRequest
     */
    protected $apiRequest;

    public function setUp()
    {
        $this->apiRequest = new EditGameRequest();
    }

    public function testSetGameId()
    {
        // returns itself
        $this->assertEquals($this->apiRequest, $this->apiRequest->setGameId(12));
    }

    public function testSetPlayerShips()
    {
        // returns itself
        $this->assertEquals($this->apiRequest, $this->apiRequest->setPlayerShips([]));
    }

    public function testSetJoinGame()
    {
        // returns itself
        $this->assertEquals($this->apiRequest, $this->apiRequest->setJoinGame(true));
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The required option "uri" is missing.
     */
    public function testResolveThrowsExceptionOnMissingGameId()
    {
        $this->apiRequest->setApiKey('testKey')->resolve();
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The option "playerShips" with value array is invalid.
     */
    public function testResolveThrowsExceptionWhenTooManyShips()
    {
        // 21 masts
        $playerShips = ['A1','E1','A2','D3','E3','F3','J3','H4','J4','A5','B5','C5','D5','J5','H6','B9','E9','F9','B10','H10','A2'];
        $this->apiRequest->setApiKey('testKey')->setJoinGame(true)->setPlayerShips($playerShips)->setGameId(12)->resolve();
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The option "playerShips" with value array is invalid.
     */
    public function testResolveThrowsExceptionWhenNotEnoughShips()
    {
        // 19 masts
        $playerShips = ['A1','E1','A2','D3','E3','F3','J3','H4','J4','A5','B5','C5','D5','J5','H6','B9','E9','F9','B10'];
        $this->apiRequest->setApiKey('testKey')->setPlayerShips($playerShips)->setGameId(12)->resolve();
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The option "playerShips" with value array is invalid.
     */
    public function testResolveThrowsExceptionWhenInvalidCoords()
    {
        // H11 is invalid
        $playerShips = ['A1','E1','A2','D3','E3','F3','J3','H4','J4','A5','B5','C5','D5','J5','H6','B9','E9','F9','B10','H11'];
        $this->apiRequest->setApiKey('testKey')->setPlayerShips($playerShips)->setGameId(12)->resolve();
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The option "joinGame" with value false is invalid.
     */
    public function testResolveThrowsExceptionWhenInvalidJoinGame()
    {
        // H11 is invalid
        $this->apiRequest->setApiKey('testKey')->setJoinGame(false)->setGameId(12)->resolve();
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The required option "apiKey" is missing.
     */
    public function testResolveThrowsExceptionOnMissingApiKey()
    {
        $this->apiRequest->setGameId(12)->resolve();
    }

    public function testSettingRequest()
    {
        // set required options and resolve
        $playerShips = ['A1','E1','A2','D3','E3','F3','J3','H4','J4','A5','B5','C5','D5','J5','H6','B9','E9','F9','B10','H10'];
        $this->apiRequest->setApiKey('testKey')->setJoinGame(true)->setPlayerShips($playerShips)->setGameId(12)->resolve();

        // check http method
        $this->assertEquals('PATCH', $this->apiRequest->getHttpMethod());
        // check uri
        $this->assertEquals('/v1/games/12', $this->apiRequest->getUri());
        // check headers
        $this->assertEquals(['Authorization' => 'Bearer testKey'], $this->apiRequest->getHeaders());
        // check data
        $this->assertEquals(['joinGame' => true, 'playerShips' => $playerShips], $this->apiRequest->getData());
    }

    public function testSettingRequestJoinGame()
    {
        $this->apiRequest->setApiKey('testKey')->setJoinGame(true)->setGameId(12)->resolve();

        // check data
        $this->assertEquals(['joinGame' => true], $this->apiRequest->getData());
    }

    public function testSettingRequestSetShips()
    {
        // set required options and resolve
        $playerShips = ['A1','E1','A2','D3','E3','F3','J3','H4','J4','A5','B5','C5','D5','J5','H6','B9','E9','F9','B10','H10'];
        $this->apiRequest->setApiKey('testKey')->setPlayerShips($playerShips)->setGameId(12)->resolve();

        // check data
        $this->assertEquals(['playerShips' => $playerShips], $this->apiRequest->getData());
    }
}

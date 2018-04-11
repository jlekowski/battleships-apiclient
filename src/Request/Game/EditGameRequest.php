<?php

namespace BattleshipsApi\Client\Request\Game;

use BattleshipsApi\Client\Request\ApiRequest;

class EditGameRequest extends ApiRequest
{
    /* protected */ const DATA = [
        'defined' => ['playerShips', 'joinGame']
    ];

    /**
     * @param int $gameId
     * @return $this
     */
    public function setGameId(int $gameId): self
    {
        return $this->setUri(sprintf('/games/%d', $gameId));
    }

    public function setPlayerShips(array $ships): self
    {
        return $this->setDataParam('playerShips', $ships);
    }

    public function setJoinGame(bool $joinGame): self
    {
        return $this->setDataParam('joinGame', $joinGame);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->dataResolver
                ->setDefined(['playerShips', 'joinGame'])
                ->setAllowedValues('playerShips', function ($value) {
                    // must be array with 20 ships/masts A1-J10
                    return is_array($value) && (count($value) === 20) && (preg_grep('/^[A-J]([1-9]|10)$/', $value) === $value);
                })
                ->setAllowedValues('joinGame', true)
        ;
    }
}

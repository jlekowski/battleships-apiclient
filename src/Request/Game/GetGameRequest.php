<?php

namespace BattleshipsApi\Client\Request\Game;

use BattleshipsApi\Client\Request\ApiRequest;

class GetGameRequest extends ApiRequest
{
    /**
     * @param int $gameId
     * @return $this
     */
    public function setGameId(int $gameId): self
    {
        return $this->setUri(sprintf('/games/%d', $gameId));
    }
}

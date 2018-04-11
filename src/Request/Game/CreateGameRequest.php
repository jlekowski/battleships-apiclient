<?php

namespace BattleshipsApi\Client\Request\Game;

use BattleshipsApi\Client\Request\ApiRequest;

class CreateGameRequest extends ApiRequest
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setUri('/games')
        ;
    }
}

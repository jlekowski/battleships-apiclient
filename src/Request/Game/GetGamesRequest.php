<?php

namespace BattleshipsApi\Client\Request\Game;

use BattleshipsApi\Client\Request\ApiRequest;

class GetGamesRequest extends ApiRequest
{
    /**
     * @param bool $available
     * @return $this
     */
    public function setAvailable(bool $available): self
    {
        return $this->set('uri', sprintf('/games?available=%s', ($available ? 'true' : 'false')));
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->set('httpMethod', 'GET')
        ;
    }
}

<?php

namespace BattleshipsApi\Client\Request\Game;

use BattleshipsApi\Client\Request\ApiRequest;

class GetGamesRequest extends ApiRequest
{
    /* protected */ const QUERY = ['available'];
    /**
     * @param bool $available
     * @return $this
     */
    public function setAvailable(bool $available): self
    {
        return $this->setQueryParam('available', ($available ? 'true' : 'false'));
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setUri('/games')
            ->queryResolver
                ->setRequired('available')
                ->setAllowedValues('available', ['true', 'false'])
        ;
    }
}

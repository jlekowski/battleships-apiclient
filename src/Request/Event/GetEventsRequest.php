<?php

namespace BattleshipsApi\Client\Request\Event;

use BattleshipsApi\Client\Request\ApiRequest;

class GetEventsRequest extends ApiRequest
{
    /* protected */ const QUERY = ['gt', 'type', 'player'];

    /**
     * @param int $gameId
     * @return $this
     */
    public function setGameId(int $gameId): self
    {
        return $this->setUri(sprintf('/games/%d/events', $gameId));
    }

    /**
     * @param int $gt
     * @return $this
     */
    public function setGt(int $gt): self
    {
        return $this->setQueryParam('gt', $gt);
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type): self
    {
        return $this->setQueryParam('type', $type);
    }

    /**
     * @param int $player
     * @return $this
     */
    public function setPlayer(int $player): self
    {
        return $this->setQueryParam('player', $player);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->queryResolver
                ->setAllowedTypes('gt', 'int')
                ->setAllowedValues('type', array_values(EventTypes::TYPES))
                ->setAllowedValues('player', [1, 2])
        ;
    }
}

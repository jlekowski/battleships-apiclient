<?php

namespace BattleshipsApi\Client\Request\Event;

use BattleshipsApi\Client\Request\ApiRequest;

class GetEventRequest extends ApiRequest
{
    /**
     * @var int|null
     */
    private $gameId;

    /**
     * @var int|null
     */
    private $eventId;

    /**
     * @param int $gameId
     * @return $this
     */
    public function setGameId(int $gameId): self
    {
        $this->gameId = $gameId;

        return $this->configureUri();
    }

    /**
     * @param int $eventId
     * @return $this
     */
    public function setEventId(int $eventId): self
    {
        $this->eventId = $eventId;

        return $this->configureUri();
    }

    /**
     * @return $this
     */
    private function configureUri(): self
    {
        if ($this->gameId && $this->eventId) {
            $this->setUri(sprintf('/games/%d/events/%d', $this->gameId, $this->eventId));
        }

        return $this;
    }
}

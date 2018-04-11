<?php

namespace BattleshipsApi\Client\Request\Event;

use BattleshipsApi\Client\Request\ApiRequest;

class CreateEventRequest extends ApiRequest
{
    /* protected */ const DATA = ['type', 'value'];

    /**
     * @param int $gameId
     * @return $this
     */
    public function setGameId(int $gameId): self
    {
        return $this->setUri(sprintf('/games/%d/events', $gameId));
    }

    /**
     * @param string $eventType
     * @return $this
     */
    public function setEventType(string $eventType): self
    {
        return $this->setDataParam('type', $eventType);
    }

    /**
     * @param mixed $eventType
     * @return $this
     */
    public function setEventValue($eventType): self
    {
        return $this->setDataParam('value', $eventType);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->dataResolver
                ->setAllowedValues('type', array_values(EventTypes::TYPES))
        ;
    }
}

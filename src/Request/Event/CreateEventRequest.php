<?php

namespace BattleshipsApi\Client\Request\Event;

use BattleshipsApi\Client\Request\ApiRequest;
use Symfony\Component\OptionsResolver\Options;

class CreateEventRequest extends ApiRequest
{
    /**
     * @param int $gameId
     * @return $this|CreateEventRequest
     */
    public function setGameId(int $gameId): self
    {
        return $this->set('uri', sprintf('/games/%d/events', $gameId));
    }

    /**
     * @param string $eventType
     * @return $this|CreateEventRequest
     */
    public function setEventType(string $eventType): self
    {
        return $this->set('eventType', $eventType);
    }

    /**
     * @param mixed $eventType
     * @return $this|CreateEventRequest
     */
    public function setEventValue($eventType): self
    {
        return $this->set('eventValue', $eventType);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->set('httpMethod', 'POST')
            ->resolver
                ->setRequired(['eventType', 'eventValue'])
//                ->setAllowedValues('eventType', ['chat', 'shot', 'join_game', 'start_game', 'name_update', 'new_game'])
                ->setDefault('requestData', function (Options $options) {
                    return ['type' => $options['eventType'], 'value' => $options['eventValue']];
                })
        ;
    }
}

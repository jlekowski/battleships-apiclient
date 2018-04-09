<?php

namespace BattleshipsApi\Client\Request\Event;

use BattleshipsApi\Client\Request\ApiRequest;
use Symfony\Component\OptionsResolver\Options;

class GetEventRequest extends ApiRequest
{
    /**
     * @param int $gameId
     * @return $this
     */
    public function setGameId(int $gameId): self
    {
        return $this->set('gameId', $gameId);
    }

    /**
     * @param int $eventId
     * @return $this
     */
    public function setEventId(int $eventId): self
    {
        return $this->set('eventId', $eventId);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->set('httpMethod', 'GET')
            ->resolver
                ->setRequired(['gameId', 'eventId'])
                ->setDefault('uri', function (Options $options) {
                    return sprintf('/games/%d/events/%d', $options['gameId'], $options['eventId']);
                })
        ;
    }
}

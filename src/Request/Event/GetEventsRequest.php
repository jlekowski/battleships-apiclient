<?php

namespace BattleshipsApi\Client\Request\Event;

use BattleshipsApi\Client\Request\ApiRequest;
use Symfony\Component\OptionsResolver\Options;

class GetEventsRequest extends ApiRequest
{
    /**
     * @param int $gameId
     * @return $this|GetEventsRequest
     */
    public function setGameId(int $gameId): self
    {
        return $this->set('gameId', $gameId);
    }

    /**
     * @param int $gt
     * @return $this|GetEventsRequest
     */
    public function setGt(int $gt): self
    {
        return $this->set('gt', $gt);
    }

    /**
     * @param string $type
     * @return $this|GetEventsRequest
     */
    public function setType(string $type): self
    {
        return $this->set('type', $type);
    }

    /**
     * @param int $player
     * @return $this|GetEventsRequest
     */
    public function setPlayer(int $player): self
    {
        return $this->set('player', $player);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $filterTypes = ['gt', 'type', 'player'];

        $this
            ->set('httpMethod', 'GET')
            ->resolver
                ->setRequired('gameId')
                ->setDefined($filterTypes)
//                ->setAllowedTypes('gt', 'int')
//                ->setAllowedValues('type', ['chat', 'shot', 'join_game', 'start_game', 'name_update', 'new_game'])
                ->setAllowedValues('player', [1, 2])
                ->setDefault('uri', function (Options $options) use ($filterTypes) {
                    $filters = [];
                    foreach ($filterTypes as $optionKey) {
                        if (isset($options[$optionKey])) {
                            $filters[$optionKey] = $options[$optionKey];
                        }
                    }

                    $uri = sprintf('/games/%d/events?%s', $options['gameId'], http_build_query($filters));

                    return rtrim($uri, '?');
                })
        ;
    }
}

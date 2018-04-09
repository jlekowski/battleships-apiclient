<?php

namespace BattleshipsApi\Client\Request\Game;

use BattleshipsApi\Client\Request\ApiRequest;
use Symfony\Component\OptionsResolver\Options;

class UpdateGameRequest extends ApiRequest
{
    /**
     * @param int $gameId
     * @return $this
     */
    public function setGameId(int $gameId): self
    {
        return $this->set('uri', sprintf('/games/%d', $gameId));
    }

    public function setPlayerShips($ships): self
    {
        return $this->set('playerShips', $ships);
    }

    public function setJoinGame($joinGame): self
    {
        return $this->set('joinGame', $joinGame);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $requestOptions = ['playerShips', 'joinGame'];

        $this
            ->set('httpMethod', 'PATCH')
            ->resolver
                ->setDefined($requestOptions)
                ->setAllowedValues('playerShips', function ($value) {
                    // must be array with 20 ships/masts A1-J10
                    return is_array($value) && (count($value) === 20) && (preg_grep('/^[A-J]([1-9]|10)$/', $value) === $value);
                })
                ->setAllowedValues('joinGame', true)
                ->setDefault('requestData', function (Options $options) use ($requestOptions) {
                    $requestData = [];
                    foreach ($requestOptions as $requestOption) {
                        if (isset($options[$requestOption])) {
                            $requestData[$requestOption] = $options[$requestOption];
                        }
                    }

                    return $requestData ?: null;
                })
        ;
    }
}

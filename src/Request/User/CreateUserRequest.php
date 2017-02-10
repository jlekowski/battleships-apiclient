<?php

namespace BattleshipsApi\Client\Request\User;

use BattleshipsApi\Client\Request\ApiRequest;

class CreateUserRequest extends ApiRequest
{
    /**
     * @param string $name
     * @return $this|CreateUserRequest
     */
    public function setUserName(string $name): self
    {
        return $this->set('requestData', ['name' => $name]);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->set('uri', '/users')
            ->set('httpMethod', 'POST')
            ->set('apiKey', null)
            ->resolver
                ->setRequired('requestData')
        ;
    }
}

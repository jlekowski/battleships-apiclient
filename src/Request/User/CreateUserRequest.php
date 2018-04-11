<?php

namespace BattleshipsApi\Client\Request\User;

use BattleshipsApi\Client\Request\ApiRequest;

class CreateUserRequest extends ApiRequest
{
    /* protected */ const DATA = ['name'];

    /**
     * @param string $name
     * @return $this
     */
    public function setUserName(string $name): self
    {
        return $this->setDataParam('name', $name);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setUri('/users')
            ->setApiKey(null)
        ;
    }
}

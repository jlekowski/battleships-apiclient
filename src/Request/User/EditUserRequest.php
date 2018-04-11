<?php

namespace BattleshipsApi\Client\Request\User;

use BattleshipsApi\Client\Request\ApiRequest;

class EditUserRequest extends ApiRequest
{
    /* protected */ const DATA = ['name'];

    /**
     * @param int $userId
     * @return $this
     */
    public function setUserId(int $userId): self
    {
        return $this->setUri(sprintf('/users/%d', $userId));
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setUserName(string $name): self
    {
        return $this->setDataParam('name', $name);
    }
}

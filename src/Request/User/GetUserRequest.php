<?php

namespace BattleshipsApi\Client\Request\User;

use BattleshipsApi\Client\Request\ApiRequest;

class GetUserRequest extends ApiRequest
{
    /**
     * @param int $userId
     * @return $this
     */
    public function setUserId(int $userId): self
    {
        return $this->setUri(sprintf('/users/%d', $userId));
    }
}

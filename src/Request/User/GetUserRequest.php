<?php

namespace BattleshipsApi\Client\Request\User;

use BattleshipsApi\Client\Request\ApiRequest;

class GetUserRequest extends ApiRequest
{
    /**
     * @param int $userId
     * @return $this|GetUserRequest
     */
    public function setUserId(int $userId): self
    {
        return $this->set('uri', sprintf('/users/%d', $userId));
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->set('httpMethod', 'GET');
    }
}

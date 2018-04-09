<?php

namespace BattleshipsApi\Client\Request\User;

use BattleshipsApi\Client\Request\ApiRequest;

class UpdateUserRequest extends ApiRequest
{
    /**
     * @param int $userId
     * @return $this
     */
    public function setUserId(int $userId): self
    {
        return $this->set('uri', sprintf('/users/%d', $userId));
    }

    /**
     * @param string $name
     * @return $this
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
            ->set('httpMethod', 'PATCH')
            ->resolver
                ->setRequired('requestData')
        ;
    }
}

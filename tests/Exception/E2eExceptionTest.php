<?php

namespace Tests\Exception;

use BattleshipsApi\Client\Exception\E2eException;
use PHPUnit\Framework\TestCase;

class E2eExceptionTest extends TestCase
{
    public function testGetMessage()
    {
        $apiException = new E2eException('Test msg');
        $this->assertEquals('Test msg', $apiException->getMessage());
    }

    public function testGetCode()
    {
        $apiException = new E2eException('Test msg', 123);
        $this->assertEquals(123, $apiException->getCode());
    }

    public function testGetPrevious()
    {
        $previousException = new \Exception();
        $apiException = new E2eException('Test msg', 123, $previousException);
        $this->assertEquals($previousException, $apiException->getPrevious());
    }
}

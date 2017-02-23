<?php

namespace Tests\Exception;

use BattleshipsApi\Client\Exception\UnexpectedHeaderException;
use PHPUnit\Framework\TestCase;

class UnexpectedHeaderExceptionTest extends TestCase
{
    public function testGetMessage()
    {
        $apiException = new UnexpectedHeaderException('Header received', 'Header expected');
        $this->assertEquals('Incorrect header provided: Header received (expected: Header expected)', $apiException->getMessage());
    }

    public function testGetCode()
    {
        $apiException = new UnexpectedHeaderException('Header received', 'Header expected', 123);
        $this->assertEquals(123, $apiException->getCode());
    }

    public function testGetPrevious()
    {
        $previousException = new \Exception();
        $apiException = new UnexpectedHeaderException('Header received', 'Header expected', 123, $previousException);
        $this->assertEquals($previousException, $apiException->getPrevious());
    }
}

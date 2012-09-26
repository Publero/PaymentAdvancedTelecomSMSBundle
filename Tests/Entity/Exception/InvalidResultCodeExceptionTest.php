<?php
namespace Publero\AdvancedTelecomSMSBundle\Tests\Entity\Exception;

use Publero\AdvancedTelecomSMSBundle\Entity\Exception\InvalidResultCodeException;

class InvalidResultCodeExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InvalidResultCodeException
     */
    private $exception;

    /**
     * @var int
     */
    private $testCode = 301;

    public function setUp()
    {
        $this->exception = new InvalidResultCodeException($this->testCode);
    }

    public function testGetResultCode()
    {
        $this->assertEquals($this->testCode, $this->exception->getResultCode());
    }

    public function testGetMessage()
    {
        $this->assertEquals('Error Result code 301 given.', $this->exception->getMessage());
    }
}

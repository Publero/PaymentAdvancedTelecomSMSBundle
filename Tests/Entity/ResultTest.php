<?php
namespace Publero\AdvancedTelecomSMSBundle\Tests\Entity;

abstract class ResultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Publero\AdvancedTelecomSMSBundle\Entity\Result
     */
    private $resultObject;

    public function setUp()
    {
        $this->resultObject = $this->createResultObject();
    }

    /**
     * @return Publero\AdvancedTelecomSMSBundle\Entity\Result
     */
    abstract protected function createResultObject();

    /**
     * @return Publero\AdvancedTelecomSMSBundle\Entity\Result
     */
    public function getResultObject()
    {
        return $this->resultObject;
    }

    public function testGetAndSetCode()
    {
        $result = $this->resultObject;

        $this->assertEmpty($result->getCode());

        $codes = array(1, 0);
        foreach ($codes as $code) {
            $this->assertSame($result, $result->setCode($code));
            $this->assertEquals($code, $result->getCode());
        }
    }
}
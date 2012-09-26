<?php
namespace Publero\AdvancedTelecomSMSBundle\Tests\Entity;

use Publero\AdvancedTelecomSMSBundle\Entity\StartPaymentResult;

class StartPaymentResultTest extends ResultTest
{
    protected function createResultObject()
    {
        return new StartPaymentResult();
    }

    public function testGetMessageTranslationId()
    {
        $result = $this->getResultObject();

        $result->setCode(1);

        $this->assertEquals('start_payment_result.code.ok', $result->getMessageTranslationId());

        $result->setCode(0);
        $subCodes = array(1, 2, 3);
        foreach ($subCodes as $subCode) {
            $result->setSubCode($subCode);
            $this->assertEquals('start_payment_result.subcode.' . $subCode, $result->getMessageTranslationId());
        }
    }

    public function testGetSubCode()
    {
        $this->assertEmpty($this->getResultObject()->getSubCode());
    }

    public function testSetSubCode()
    {
        $startPaymentResult = $this->getResultObject();

        $subCodes = array(11, 61);
        foreach ($subCodes as $subCode) {
            $this->assertSame($startPaymentResult, $startPaymentResult->setSubCode($subCode));
            $this->assertEquals($subCode, $startPaymentResult->getSubCode());
        }
    }

    public function testGetUrl()
    {
        $this->assertEmpty($this->getResultObject()->getUrl());
    }

    public function testSetUrl()
    {
        $startPaymentResult = $this->getResultObject();

        $urls = array('http://www.example.com/', 'http://www.example.com/success');
        foreach ($urls as $url) {
            $this->assertSame($startPaymentResult, $startPaymentResult->setUrl($url));
            $this->assertEquals($url, $startPaymentResult->getUrl());
        }
    }

    public function testToSignatureArray()
    {
        $result = $this->getResultObject();
        $result
            ->setCode(1)
            ->setSubCode(11)
            ->setUrl('http://www.example.com/')
        ;

        $signatureArray = $result->toSignatureArray();
        $this->assertEquals($result->getCode(), $signatureArray[0]);
        $this->assertEquals($result->getSubCode(), $signatureArray[1]);
        $this->assertEquals($result->getUrl(), $signatureArray[2]);
    }
}

<?php
namespace Publero\AdvancedTelecomSMSBundle\Tests\Entity;

use Publero\AdvancedTelecomSMSBundle\Entity\TransactionStatusResult;

class TransactionStatusResultTest extends ResultTest
{
    protected function createResultObject()
    {
        return new TransactionStatusResult();
    }

    public function testGetMessageTranslationId()
    {
        $result = $this->getResultObject();

        $codes = array(1, 2, 3);
        foreach ($codes as $code) {
            $result->setCode($code);
            $this->assertEquals('transaction_status_result.code.' . $code, $result->getMessageTranslationId());
        }
    }

    public function testGetOrderId()
    {
        $this->assertEmpty($this->getResultObject()->getOrderId());
    }

    public function testSetOrderId()
    {
        $transactionStatusResult = $this->getResultObject();

        $orderIds = array(5, 302);
        foreach ($orderIds as $orderId) {
            $this->assertSame($transactionStatusResult, $transactionStatusResult->setOrderId($orderId));
            $this->assertEquals($orderId, $transactionStatusResult->getOrderId());
        }
    }

    public function testGetDescription()
    {
        $this->assertEmpty($this->getResultObject()->getDescription());
    }

    public function testSetDescription()
    {
        $transactionStatusResult = $this->getResultObject();

        $descriptions = array('Error', 'Transaction pending');
        foreach ($descriptions as $description) {
            $this->assertSame($transactionStatusResult, $transactionStatusResult->setDescription($description));
            $this->assertEquals($description, $transactionStatusResult->getDescription());
        }
    }

    public function testToSignatureArray()
    {
        $result = $this->getResultObject();
        $result
            ->setCode(1)
            ->setOrderId(5)
            ->setDescription('Something cool')
        ;

        $signatureArray = $result->toSignatureArray();
        $this->assertEquals($result->getOrderId(), $signatureArray[0]);
        $this->assertEquals($result->getCode(), $signatureArray[1]);
        $this->assertEquals($result->getDescription(), $signatureArray[2]);
    }
}

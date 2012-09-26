<?php
namespace Publero\AdvancedTelecomSMSBundle\Tests\Entity;

use Publero\AdvancedTelecomSMSBundle\Entity\TransactionStatus;

class TransactionStatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TransactionStatus
     */
    private $transactionStatus;

    public function setUp()
    {
        $this->transactionStatus = new TransactionStatus();
    }

    public function testGetOrderId()
    {
        $this->assertEmpty($this->transactionStatus->getOrderId());
    }

    public function testSetOrderId()
    {
        $transactionStatus = $this->transactionStatus;

        $orderIds = array(1, 1000000000);
        foreach($orderIds as $orderId) {
            $this->assertSame($transactionStatus, $transactionStatus->setOrderId($orderId));
            $this->assertEquals($orderId, $transactionStatus->getOrderId());
        }
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testSetOrderIdThrowsExceptionIfOrderIdIsTooLow()
    {
        $this->transactionStatus->setOrderId(0);
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testSetOrderIdThrowsExceptionIfOrderIdIsTooHigh()
    {
        $this->transactionStatus->setOrderId(10000000000);
    }

    public function testGetStatus()
    {
        $this->assertNull($this->transactionStatus->getStatus());
    }

    public function testSetStatus()
    {
        $transactionStatus = $this->transactionStatus;

        $statuses = $transactionStatus->getAvailableStatuses();
        foreach ($statuses as $status) {
            $transactionStatus->setStatus($status);
            $this->assertEquals($status, $transactionStatus->getStatus());
        }
    }

    public function testSetStatusReturnsTransactionStatus()
    {
        $statuses = $this->transactionStatus->getAvailableStatuses();
        $status = current($statuses);
        $transactionStatus = $this->transactionStatus->setStatus($status);

        $this->assertSame($this->transactionStatus, $transactionStatus);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetStatusThrowsExceptionIfInvalidStatusIsGiven()
    {
        $invalidStatus = 'I am invalid status and don\'t argue with me anymore!';

        $this->transactionStatus->setStatus($invalidStatus);
    }

    public function testGetAvailableStatuses()
    {
        $statuses = $this->transactionStatus->getAvailableStatuses();
        $this->assertNotEmpty($statuses);
        foreach ($statuses as $index => $status) {
            $this->assertInternalType('string', $status);
            $this->assertNotEmpty($status);
            $this->assertInternalType('int', $index);
        }
    }

    public function testToSignatureArray()
    {
        $orderId = 5;
        $this->transactionStatus->setOrderId($orderId);

        $this->assertEquals(array(5), $this->transactionStatus->toSignatureArray());
    }

    public function testToRequestArray()
    {
        $orderId = 5;
        $this->transactionStatus->setOrderId($orderId);

        $this->assertEquals(array(5), $this->transactionStatus->toRequestArray());
    }
}

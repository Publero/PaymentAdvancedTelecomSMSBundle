<?php
namespace Publero\AdvancedTelecomSMSBundle\Tests\Entity;

use Publero\AdvancedTelecomSMSBundle\Entity\StartPaymentWithIdent;

class StartPaymentWithIdentTest extends StartPaymentTest
{
    protected function createStartPayment($amount)
    {
        return new StartPaymentWithIdent($amount);
    }

    public function testGetPhoneNumber()
    {
        $this->assertEmpty($this->getStartPayment()->getPhoneNumber());
    }

    public function testSetPhoneNumberAcceptsPlusAnd12Digits()
    {
        $startPayment = $this->getStartPayment();

        $phoneNumber = '+420123456789';
        $payment = $startPayment->setPhoneNumber($phoneNumber);
        $this->assertSame($payment, $startPayment);
        $this->assertEquals($phoneNumber, $startPayment->getPhoneNumber());

        $phoneNumber = '+420 123 456 789';
        $startPayment->setPhoneNumber($phoneNumber);
        $this->assertEquals(str_replace(' ', '', $phoneNumber), $startPayment->getPhoneNumber());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetPhoneNumberThrowsExceptionIfNumberFormatIsInvalid()
    {
        $phoneNumber = '420 123 456 789';
        $this->getStartPayment()->setPhoneNumber($phoneNumber);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetPhoneNumberThrowsExceptionIfNumberFormatIsInvalid2()
    {
        $phoneNumber = '+123 456 789';
        $this->getStartPayment()->setPhoneNumber($phoneNumber);
    }

    public function testGetOperator()
    {
        $this->assertEquals(StartPaymentWithIdent::OPERATOR_TMOBILE, $this->getStartPayment()->getOperator());
    }

    public function testSetOperator()
    {
        $startPayment = $this->getStartPayment();

        $operators = $startPayment->getAvailableOperators();
        foreach ($operators as $operator) {
            $payment = $startPayment->setOperator($operator);
            $this->assertSame($payment, $startPayment);
            $this->assertEquals($operator, $startPayment->getOperator());
        }
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testSetOperatorThrowsExceptionIfGivenOperatorIsNotValid()
    {
        $this->getStartPayment()->setOperator('NOT_AVAILABLE');
    }

    public function testGetAvailableOperators()
    {
        $operators = $this->getStartPayment()->getAvailableOperators();

        $this->assertNotEmpty($operators);
        $this->assertInternalType('array', $operators);
        foreach ($operators as $operator) {
            $this->assertInternalType('string', $operator);
        }
    }

    public function testToSignatureArray()
    {
        $this->fillStartPaymentWithData();

        $startPayment = $this->getStartPayment();
        $data = $startPayment->toSignatureArray();

        $this->assertEquals($startPayment->getOrderId(), $data[0]);
        $this->assertEquals($startPayment->getPhoneNumber(), $data[1]);
        $this->assertEquals($startPayment->getOperator(), $data[2]);
        $this->assertEquals(sprintf('%.2f', $startPayment->getAmount()), $data[3]);
        $this->assertEquals($startPayment->getCurrency(), $data[4]);
        $this->assertEquals($startPayment->getDescription(), $data[5]);
        $this->assertEquals($startPayment->getBackUrl(), $data[6]);
        $this->assertEquals($startPayment->getTarget(), $data[7]);
        $this->assertEquals($startPayment->getChannel(), $data[8]);
    }

    public function testToRequestArray()
    {
        $this->fillStartPaymentWithData();

        $startPayment = $this->getStartPayment();
        $data = $startPayment->toRequestArray();

        $this->assertEquals($startPayment->getOrderId(), $data[0]);
        $this->assertEquals($startPayment->getPhoneNumber(), $data[1]);
        $this->assertEquals($startPayment->getOperator(), $data[2]);
        $this->assertEquals(sprintf('%.2f', $startPayment->getAmount()), $data[3]);
        $this->assertEquals($startPayment->getCurrency(), $data[4]);
        $this->assertEquals($startPayment->getDescription(), $data[5]);
        $this->assertEquals($startPayment->getBackUrl(), $data[6]);
        $this->assertEquals($startPayment->getChannel(), $data[7]);
        $this->assertEquals($startPayment->getTarget(), $data[8]);
    }

    protected function fillStartPaymentWithData()
    {
        parent::fillStartPaymentWithData();

        $this->getStartPayment()
            ->setPhoneNumber('+420 123 456 789')
        ;
    }
}

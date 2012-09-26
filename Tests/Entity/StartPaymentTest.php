<?php
namespace Publero\AdvancedTelecomSMSBundle\Tests\Entity;

use Publero\AdvancedTelecomSMSBundle\Entity\StartPayment;

class StartPaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param StartPayment
     */
    private $startPayment;

    /**
     * @var float
     */
    private $amount = 349.23;

    public function setUp()
    {
        $this->startPayment = $this->createStartPayment($this->amount);
    }

    /**
     * @param int $amount
     * @return StartPayment
     */
    protected function createStartPayment($amount)
    {
        return new StartPayment($amount);
    }

    /**
     * @return StartPayment
     */
    protected function getStartPayment()
    {
        return $this->startPayment;
    }

    public function testConstruct()
    {
        $this->assertEquals(StartPayment::TARGET_SAME, $this->startPayment->getTarget());
        $this->assertEquals(StartPayment::CHANNEL_WEB, $this->startPayment->getChannel());

        $startPayment = new StartPayment($this->amount, StartPayment::CHANNEL_WAP);

        $this->assertEquals(StartPayment::TARGET_SAME, $this->startPayment->getTarget());
        $this->assertEquals(StartPayment::CHANNEL_WAP, $startPayment->getChannel());
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testSetOrderIdThrowsExceptionIfOrderIdIsTooLow()
    {
        $this->startPayment->setOrderId(0);
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testSetOrderIdThrowsExceptionIfOrderIdIsTooHigh()
    {
        $this->startPayment->setOrderId(10000000000);
    }

    public function testGetToArray()
    {
        $orderId = 20;
        $amount = 400.25;
        $description = 'Example Description';
        $backUrl = 'http://example.com/get/me/here/after/the/payment/is/finished';
        $target = StartPayment::TARGET_SAME;
        $channel = StartPayment::CHANNEL_WAP;

        $this->startPayment
            ->setOrderId($orderId)
            ->setAmount($amount)
            ->setDescription($description)
            ->setBackUrl($backUrl)
            ->setTarget($target)
            ->setChannel($channel)
        ;
        $array = $this->startPayment->toArray();

        $this->assertCount(7, $array);
    }

    public function testGetDescription()
    {
        $this->assertEmpty($this->startPayment->getDescription());
    }

    public function testSetDescriptionAcceptsOnlyAlpaNumericalValue()
    {
        $description = 'Hi number 5 How are you doing';

        $this->startPayment->setDescription($description);
        $this->assertEquals($description, $this->startPayment->getDescription());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetDescriptionThrowsExceptionIfDescriptionIsNotAlphaNumerical()
    {
        $this->startPayment->setDescription('a-Z5');
    }

    public function testSetTarget()
    {
        $availableTargets = $this->startPayment->getAvailableTargets();
        foreach ($availableTargets as $target) {
            $this->startPayment->setTarget($target);

            $this->assertEquals($target, $this->startPayment->getTarget());
        }
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testSetTargetToParentOrTopThrowsExceptionIfChannelIsWap()
    {
        $this->startPayment->setChannel(StartPayment::CHANNEL_WAP);
        $this->startPayment->setTarget(StartPayment::TARGET_PARENT);
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testSetTargetThrowsExceptionIfInvalidTargetIsGiven()
    {
        $this->startPayment->setTarget('do_not_exist');
    }

    public function testGetAvailableTargets()
    {
        $availableTargets = $this->startPayment->getAvailableTargets();

        $this->assertNotEmpty($availableTargets);
        $this->assertInternalType('array', $availableTargets);
    }

    public function testSetChannel()
    {
        $availableChannels = $this->startPayment->getAvailableChannels();
        foreach ($availableChannels as $channel) {
            $this->startPayment->setChannel($channel);

            $this->assertEquals($channel, $this->startPayment->getChannel());
        }
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testSetChannelToWapThrowsExceptionIfTargetIsParentOrTop()
    {
        $this->startPayment->setTarget(StartPayment::TARGET_PARENT);
        $this->startPayment->setChannel(StartPayment::CHANNEL_WAP);
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testSetChannelThrowsExceptionIfInvalidChannelIsGiven()
    {
        $this->startPayment->setChannel('do_not_exist');
    }

    public function getGetAvailableChannels()
    {
        $availableChannels = $this->startPayment->getAvailableChannels();

        $this->assertNotEmpty($availableChannels);
        $this->assertInternalType('array', $availableChannels);
    }

    public function testGetCurrency()
    {
        $this->assertEquals('CZK', $this->startPayment->getCurrency());
    }

    public function testToSignatureArray()
    {
        $this->fillStartPaymentWithData();

        $data = $this->startPayment->toSignatureArray();

        $this->assertEquals($this->startPayment->getOrderId(), $data[0]);
        $this->assertEquals(sprintf('%.2f', $this->startPayment->getAmount()), $data[1]);
        $this->assertEquals($this->startPayment->getCurrency(), $data[2]);
        $this->assertEquals($this->startPayment->getDescription(), $data[3]);
        $this->assertEquals($this->startPayment->getBackUrl(), $data[4]);
        $this->assertEquals($this->startPayment->getTarget(), $data[5]);
        $this->assertEquals($this->startPayment->getChannel(), $data[6]);
    }

    public function testToRequestArray()
    {
        $this->fillStartPaymentWithData();

        $data = $this->startPayment->toRequestArray();

        $this->assertEquals($this->startPayment->getOrderId(), $data[0]);
        $this->assertEquals(sprintf('%.2f', $this->startPayment->getAmount()), $data[1]);
        $this->assertEquals($this->startPayment->getCurrency(), $data[2]);
        $this->assertEquals($this->startPayment->getDescription(), $data[3]);
        $this->assertEquals($this->startPayment->getBackUrl(), $data[4]);
        $this->assertEquals($this->startPayment->getChannel(), $data[5]);
        $this->assertEquals($this->startPayment->getTarget(), $data[6]);
    }

    protected function fillStartPaymentWithData()
    {
        $this->startPayment
            ->setOrderId(10)
            ->setAmount($this->amount)
            ->setDescription('This rocks')
            ->setBackUrl('http://www.example.com/')
        ;
    }

    public function testGetAmount()
    {
        $this->assertEquals($this->amount, $this->startPayment->getAmount());
    }

    public function testSetAmount()
    {
        $amounts = array(0.01, 0.1, 1, 10, 100, 1000);
        foreach ($amounts as $amount) {
            $this->startPayment->setAmount($amount);
            $this->assertEquals($amount, $this->startPayment->getAmount());
        }
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testSetAmountThrowsExceptionIfGivenAmountIsBiggerThanThousand()
    {
        $this->startPayment->setAmount(1000.01);
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testSetAmountThrowsExceptionIfGivenAmountIsZero()
    {
        $this->startPayment->setAmount(0);
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testSetAmountThrowsExceptionIfGivenAmountIsNegative()
    {
        $this->startPayment->setAmount(-5);
    }
}

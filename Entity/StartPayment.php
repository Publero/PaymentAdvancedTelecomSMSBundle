<?php
namespace Publero\AdvancedTelecomSMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="publerosms_payment")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"no_operator" = "StartPayment", "operator" = "StartPaymentWithIdent"})
 */
class StartPayment implements ToRequestArrayConvertableInterface
{
    const TARGET_SAME = 'SAME';
    const TARGET_PARENT = 'PARENT';
    const TARGET_TOP = 'TOP';

    const CHANNEL_WEB = 'WEB';
    const CHANNEL_WAP = 'WAP';

    const MAX_ORDER_ID = 10000000000;
    const MIN_ORDER_ID = 1;

    const MAX_AMOUNT = 1000;
    const MIN_AMOUNT = 0;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $orderId;

    /**
     * @var float (4+2)
     *
     * @ORM\Column(name="amount", type="decimal", precision="6", scale="2")
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length="50")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="backurl", type="string", length="256")
     */
    private $backUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="target", type="string", length="8")
     */
    private $target = self::TARGET_SAME;

    /**
     * @var string
     *
     * @ORM\Column(name="channel", type="string", length="4")
     */
    private $channel;

    /**
     * @param float $amount 1234.98 (precision=6, scale=2)
     * @param string $channel One of StartPayment::CHANNEL_ constants
     */
    public function __construct($amount, $channel = self::CHANNEL_WEB)
    {
        $this->setAmount($amount);
        $this->setChannel($channel);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            $this->getOrderId(),
            $this->getAmount(),
            $this->getCurrency(),
            $this->getDescription(),
            $this->getBackUrl(),
            $this->getTarget(),
            $this->getChannel()
        );
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param int $orderId
     * @throws \OutOfRangeException
     * @return StartPayment
     */
    public function setOrderId($orderId)
    {
        if ($orderId >= self::MAX_ORDER_ID || $orderId < self::MIN_ORDER_ID) {
            throw new \OutOfRangeException('Order id must be in interval <1, 10^9)');
        }

        $this->orderId = $orderId;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     * @return StartPayment
     */
    public function setAmount($amount)
    {
        if ($amount > self::MAX_AMOUNT || $amount <= self::MIN_AMOUNT) {
            throw new \OutOfRangeException("Amount $amount is out of range. Allowed amount range is (0.00, 1000.00>");
        }

        $this->amount = $amount;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description Alphanumerical and spaces string
     * @throws \InvalidArgumentException
     * @return StartPayment
     */
    public function setDescription($description)
    {
        if (preg_match('/[^a-zA-Z0-9 ]/', $description)) {
            throw new \InvalidArgumentException('Description must be made of alpha numerical characters and spaces [a-zA-Z0-9 ]. Description given: ' . $description);
        }

        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->backUrl;
    }

    /**
     * @param string $backUrl
     * @return StartPayment
     */
    public function setBackUrl($backUrl)
    {
        $this->backUrl = $backUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param string $target
     * @throws \OutOfBoundsException
     * @return StartPayment
     */
    public function setTarget($target)
    {
        $availableTargets = $this->getAvailableTargets();
        if (!in_array($target, $availableTargets)) {
            throw new \OutOfBoundsException('Target "' . $target . '" is not valid. Use on of following instead: ' . implode(', ', $availableTargets));
        }

        if ($this->getChannel() == self::CHANNEL_WAP && $target !== self::TARGET_SAME) {
            throw new \OutOfBoundsException('Target must be "' . self::TARGET_SAME . '" when channel is set to ' . self::CHANNEL_WAP . '.');
        }

        $this->target = $target;

        return $this;
    }

    /**
     * @return array
     */
    public function getAvailableTargets()
    {
        return array(
            self::TARGET_PARENT,
            self::TARGET_SAME,
            self::TARGET_TOP
        );
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param string $channel
     * @throws \OutOfBoundsException
     * @return StartPayment
     */
    public function setChannel($channel)
    {
        $availableChannels = $this->getAvailableChannels();
        if (!in_array($channel, $availableChannels)) {
            throw new \OutOfBoundsException('Channel "' . $channel . '" is not valid. Use on of following instead: ' . implode(', ', $availableChannels));
        }

        if ($channel == self::CHANNEL_WAP && $this->getTarget() !== self::TARGET_SAME) {
            throw new \OutOfBoundsException('Channel "' . $channel . '" can not be set to this value when target is "' . $this->getTarget() . '".');
        }

        $this->channel = $channel;

        return $this;
    }

    /**
     * @return array
     */
    public function getAvailableChannels()
    {
        return array(
            self::CHANNEL_WAP,
            self::CHANNEL_WEB
        );
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return 'CZK';
    }

    public function toSignatureArray()
    {
        $data = $this->toRequestArray();
        $channel = $data[5];
        $data[5] = $data[6];
        $data[6] = $channel;

        return $data;
    }

    public function toRequestArray()
    {
        return array(
            $this->getOrderId(),
            sprintf('%.2f', $this->getAmount()),
            $this->getCurrency(),
            $this->getDescription(),
            $this->getBackUrl(),
            $this->getChannel(),
            $this->getTarget()
        );
    }
}

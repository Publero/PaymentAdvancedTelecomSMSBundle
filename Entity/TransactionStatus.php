<?php
namespace Publero\AdvancedTelecomSMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="publerosms_transaction")
 */
class TransactionStatus implements ToRequestArrayConvertableInterface
{
    const STATUS_ERROR = 'ERROR';
    const STATUS_ACCEPTED = 'ACCEPTED';
    const STATUS_CANCELED = 'CANCELED';
    const STATUS_EXPIRED = 'EXPIRED';
    const STATUS_REQUESTED = 'REQUESTED';
    const STATUS_PENDING = 'PENDING';
    const STATUS_CREATED = 'CREATED';

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $orderId;

    /**
     * @var string
     * @ORM\Column(name="status", type="string")
     */
    private $status;

    /**
     * @var StartPayment
     *
     * @ORM\OneToOne(targetEntity="StartPayment", orphanRemoval=true, cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="id", referencedColumnName="id")
     */
    private $startPayment;

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
     * @return TransactionStatus
     */
    public function setOrderId($orderId)
    {
        if ($orderId >= 10000000000 || $orderId < 1) {
            throw new \OutOfRangeException('Order id must be in interval <1, 10^9)');
        }

        $this->orderId = $orderId;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status One of TransactionStatus::STATUS_[A-Z]+
     * @throws \InvalidArgumentException
     * @return TransactionStatus
     */
    public function setStatus($status)
    {
        $statuses = $this->getAvailableStatuses();
        if (in_array($status, $statuses)) {
            $this->status = $status;

            return $this;
        }

        throw new \InvalidArgumentException('Invalid status given "' . $status . '" allowed values are: ' . implode(', ', $statuses));
    }

    /**
     * @return StartPayment
     */
    public function getPayment()
    {
        return $this->startPayment;
    }

    /**
     * @param StartPayment $startPayment
     * @return TransactionStatus
     */
    public function setPayment(StartPayment $startPayment)
    {
        $this->startPayment = $startPayment;

        return $this;
    }

    /**
     * @return array
     */
    public function getAvailableStatuses()
    {
        return array(
            0 => self::STATUS_ERROR,
            1 => self::STATUS_ACCEPTED,
            2 => self::STATUS_CANCELED,
            3 => self::STATUS_EXPIRED,
            4 => self::STATUS_REQUESTED,
            5 => self::STATUS_PENDING,
            6 => self::STATUS_CREATED
        );
    }

    public function toSignatureArray()
    {
        return $this->toRequestArray();
    }

    public function toRequestArray()
    {
        return array($this->orderId);
    }
}

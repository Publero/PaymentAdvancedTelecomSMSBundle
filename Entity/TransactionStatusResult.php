<?php
namespace Publero\AdvancedTelecomSMSBundle\Entity;

class TransactionStatusResult extends Result
{
    /**
     * @var int
     */
    private $orderId;

    /**
     * @var string
     */
    private $description;

    public function getMessageTranslationId()
    {
        return 'transaction_status_result.code.' . $this->getCode();
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
     * @return TransactionStatusResult
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

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
     * @param string $description
     * @return TransactionStatusResult
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function toSignatureArray()
    {
        return array(
            $this->getOrderId(),
            $this->getCode(),
            $this->getDescription()
        );
    }
}

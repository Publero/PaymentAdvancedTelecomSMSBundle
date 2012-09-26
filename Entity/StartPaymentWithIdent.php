<?php
namespace Publero\AdvancedTelecomSMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class StartPaymentWithIdent extends StartPayment
{
    const OPERATOR_VODAFONE = 'VF';
    const OPERATOR_O2 = 'O2';
    const OPERATOR_TMOBILE = 'TM';
    const OPERATOR_TEST = 'TEST';

    /**
     * @var string
     *
     * @ORM\Column(name="phonenumber", type="string", length="12")
     */
    private $phoneNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="operator", type="string", length="4")
     */
    private $operator;

    /**
     * @param float $amount 1234.98 (precision=6, scale=2)
     * @param int $channel One of StartPayment::CHANNEL_ constants
     * @param string $operator One of StartPaymentWithIdent::OPERATOR_ constants
     */
    public function __construct($amount, $channel = self::CHANNEL_WEB, $operator = self::OPERATOR_TMOBILE)
    {
        parent::__construct($amount, $channel);
        $this->operator = $operator;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     * @throws \InvalidArgumentException
     * @return StartPaymentWithIdent
     */
    public function setPhoneNumber($phoneNumber)
    {
        $phoneNumber = str_replace(' ', '', $phoneNumber);

        if (!preg_match('/\+[0-9]{12}/', $phoneNumber)) {
            throw new \InvalidArgumentException('Invalid phone number format. Phone number "' . $phoneNumber . '" given');
        }

        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param string $operator
     * @throws \OutOfBoundsException
     * @return StartPaymentWithIdent
     */
    public function setOperator($operator)
    {
        $operators = $this->getAvailableOperators();
        if (!in_array($operator, $operators)) {
            throw new \OutOfBoundsException('Invalid operator given "' . $operator . '". Only these operators are allowed: "' . implode(', ', $operators) . '"');
        }

        $this->operator = $operator;

        return $this;
    }

    /**
     * @return array
     */
    public function getAvailableOperators()
    {
        return array(
            self::OPERATOR_VODAFONE,
            self::OPERATOR_O2,
            self::OPERATOR_TMOBILE,
            self::OPERATOR_TEST
        );
    }

    public function toSignatureArray()
    {
        $data = $this->toRequestArray();
        $channel = $data[7];
        $data[7] = $data[8];
        $data[8] = $channel;

        return $data;
    }

    public function toRequestArray()
    {
        $data = parent::toRequestArray();

        $data[0] = $this->operator;
        array_unshift($data, $this->phoneNumber);
        array_unshift($data, $this->getOrderId());

        return $data;
    }
}

<?php
namespace Publero\AdvancedTelecomSMSBundle\Entity;

use Publero\AdvancedTelecomSMSBundle\Entity\Exception\InvalidResultCodeException;
use Publero\AdvancedTelecomSMSBundle\Connector\SoapConnector;
use Doctrine\ORM\EntityManager;

class PaymentManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var SoapConnector
     */
    private $connector;

    public function __construct(EntityManager $entityManager, SoapConnector $connector)
    {
        $this->entityManager = $entityManager;
        $this->connector = $connector;
    }

    /**
     * @param StartPayment $payment
     * @return StartPaymentResult
     */
    public function createPayment(StartPayment $payment)
    {
        $this->persistAndFlush($payment);

        if ($payment instanceof StartPaymentWithIdent) {
            $result = $this->connector->doStartPaymentWithIdentRequest($payment);
        } else {
            $result = $this->connector->doStartPaymentRequest($payment);
        }

        $transactionStatus = $this->createTransactionStatus($payment);
        $this->persistAndFlush($transactionStatus);

        return $result;
    }

    /**
     * @param StartPayment $payment
     * @return TransactionStatus
     */
    private function createTransactionStatus(StartPayment $payment)
    {
        $transactionStatus = new TransactionStatus();
        $transactionStatus
            ->setStatus(TransactionStatus::STATUS_CREATED)
            ->setPayment($payment)
        ;

        return $transactionStatus;
    }

    /**
     * @param TransactionStatus $status
     * @throws InvalidResultCodeException
     * @return TransactionStatusResult
     */
    public function updateTransactionStatus(TransactionStatus $status)
    {
        $result = $this->connector->doTransactionStatusRequest($status);
        $resultCode = $result->getCode();

        $statuses = $status->getAvailableStatuses();
        if (!isset($statuses[$resultCode]) || !array_key_exists($resultCode, $statuses)) {
            throw new InvalidResultCodeException($resultCode);
        }

        $status->setStatus($statuses[$resultCode]);

        $this->persistAndFlush($status->getPayment());
        $this->persistAndFlush($status);

        return $result;
    }

    /**
     * @param Object $object
     */
    public function persistAndFlush($object)
    {
        $this->entityManager->persist($object);
        $this->entityManager->flush();
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository('PubleroAdvancedTelecomSMSBundle:TransactionStatus');
    }

    /**
     * @return TransactionStatus[]
     */
    public function findOngoingTransactions()
    {
        $ongoingStatuses = array(
            TransactionStatus::STATUS_CREATED,
            TransactionStatus::STATUS_ERROR,
            TransactionStatus::STATUS_REQUESTED,
            TransactionStatus::STATUS_PENDING
        );

        return $this->getRepository()->findBy(array('status' => $ongoingStatuses));
    }
}

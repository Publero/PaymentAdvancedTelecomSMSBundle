<?php
namespace Publero\AdvancedTelecomSMSBundle\Tests\Entity;

use Publero\UtilBundle\TestCase\DatabaseTestCase;
use Publero\AdvancedTelecomSMSBundle\Entity\PaymentManager;
use Publero\AdvancedTelecomSMSBundle\Entity\TransactionStatus;
use Publero\AdvancedTelecomSMSBundle\Entity\TransactionStatusResult;
use Publero\AdvancedTelecomSMSBundle\Entity\StartPayment;
use Publero\AdvancedTelecomSMSBundle\Entity\StartPaymentResult;

class PaymentManagerTest extends DatabaseTestCase
{
    /**
     * @var PaymentManager
     */
    private $manager;

    /**
     * @var Publero\AdvancedTelecomSMSBundle\Connector\SoapConnector
     */
    private $soapConnector;

    /**
     * @var Object
     */
    private $transactions = array();

    public function setUp()
    {
        $this->soapConnector = $this->getSoapConnectorMock();
        $this->manager = new PaymentManager($this->getDoctrine()->getEntityManager(), $this->soapConnector);
    }

    public function tearDown()
    {
        $entityManager = $this->getDoctrine()->getEntityManager();
        foreach ($this->transactions as $transaction) {
            $payment = $transaction->getPayment();
            $entityManager->remove($payment);
            $entityManager->remove($transaction);
        }
        $entityManager->flush();
    }

    /**
     * @return Publero\AdvancedTelecomSMSBundle\Connector\SoapConnector
     */
    private function getSoapConnectorMock()
    {
        $mockClass = 'Publero\AdvancedTelecomSMSBundle\Connector\SoapConnector';
        $mockMethods = array(
            'doStartPaymentRequest',
            'doStartPaymentWithIdentRequest',
            'doTransactionStatusRequest'
        );
        $soapConnector = $this->getMock($mockClass, $mockMethods, array(), '', false);

        return $soapConnector;
    }

    public function testCreatePayment()
    {
        $payment = $this->createPayment();
        $result = $this->createStartPaymentResult();

        $this->soapConnector
            ->expects($this->once())
            ->method('doStartPaymentRequest')
            ->with($this->equalTo($payment))
            ->will($this->returnValue($result))
        ;

        $paymentResult = $this->manager->createPayment($payment);
        $this->assertInstanceOf('Publero\AdvancedTelecomSMSBundle\Entity\StartPaymentResult', $paymentResult);

        $repository = $this->manager->getRepository();
        $transaction = $repository->findOneBy(array('orderId' => $payment->getOrderId()));

        $this->assertEquals(TransactionStatus::STATUS_CREATED, $transaction->getStatus());
        $this->assertEquals($payment->toSignatureArray(), $transaction->getPayment()->toSignatureArray());

        $this->transactions = array($transaction);
    }

    /**
     * @return StartPaymentResult
     */
    private function createStartPaymentResult()
    {
        $result = new StartPaymentResult();
        $result->setCode(1);
        $result->setSubCode(0);
        $result->setUrl('http://example.com');

        return $result;
    }

    public function testUpdateTransactionStatus()
    {
        $payment = $this->createPayment();

        $status = new TransactionStatus();
        $status
            ->setStatus(TransactionStatus::STATUS_CREATED)
            ->setPayment($payment)
        ;
        $result = $this->createTransactionStatusResult();

        $this->soapConnector
            ->expects($this->once())
            ->method('doTransactionStatusRequest')
            ->will($this->returnValue($result))
        ;

        $transactionResult = $this->manager->updateTransactionStatus($status);
        $this->assertInstanceOf('Publero\AdvancedTelecomSMSBundle\Entity\TransactionStatusResult', $transactionResult);

        $repository = $this->manager->getRepository();
        $transaction = $repository->findOneBy(array('orderId' => $status->getOrderId()));

        $this->assertEquals(TransactionStatus::STATUS_ACCEPTED, $transaction->getStatus());
        $this->assertEquals($payment->toSignatureArray(), $transaction->getPayment()->toSignatureArray());

        $this->transactions = array($transaction);
    }

    /**
     * @return TransactionStatuResult
     */
    private function createTransactionStatusResult()
    {
        $result = new TransactionStatusResult();
        $result
            ->setOrderId(1)
            ->setCode(1)
            ->setDescription('Accepted')
        ;

        return $result;
    }

    public function testFindOngoingTransactions()
    {
        $entityManager = $this->getDoctrine()->getEntityManager();
        $transaction = new TransactionStatus();
        $statuses = $transaction->getAvailableStatuses();

        $transactions = array();
        foreach ($statuses as $status) {
             $currentTransaction = clone $transaction;
             $currentTransaction->setStatus($status);
             $currentTransaction->setPayment($this->createPayment());
             $entityManager->persist($currentTransaction);
        }

        $ongoingTransactionsOriginalCount = count($this->manager->findOngoingTransactions());

        $entityManager->flush();

        $ongoingTransactions = $this->manager->findOngoingTransactions();
        $this->assertCount($ongoingTransactionsOriginalCount + 4, $ongoingTransactions);

        $ongoingStatuses = array(
            TransactionStatus::STATUS_CREATED,
            TransactionStatus::STATUS_ERROR,
            TransactionStatus::STATUS_REQUESTED,
            TransactionStatus::STATUS_PENDING
        );
        foreach ($ongoingTransactions as $transaction) {
            $this->assertTrue(in_array($transaction->getStatus(), $ongoingStatuses));
        }

        foreach ($transactions as $transaction) {
            $entityManager->remove($transaction);
        }
        $entityManager->flush();
    }

    /**
     * @return StartPayment
     */
    private function createPayment()
    {
        $payment = new StartPayment(100.25);
        $payment->setDescription('test');
        $payment->setBackUrl('http://example.com');

        return $payment;
    }
}

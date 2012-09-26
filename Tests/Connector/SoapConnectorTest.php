<?php
namespace Publero\AdvancedTelecomSMSBundle\Tests\Connector;

use Publero\UtilBundle\TestCase\ContainerAwareTestCase;
use Symfony\Component\Yaml\Parser;
use Publero\AdvancedTelecomSMSBundle\Signer\ArraySigner;
use Publero\AdvancedTelecomSMSBundle\Connector\SoapConnector;
use Publero\AdvancedTelecomSMSBundle\Entity\StartPaymentWithIdent;
use Publero\AdvancedTelecomSMSBundle\Entity\StartPaymentResult;
use Publero\AdvancedTelecomSMSBundle\Entity\StartPayment;
use Publero\AdvancedTelecomSMSBundle\Entity\TransactionStatus;

class SoapConnectorTest extends ContainerAwareTestCase
{
    /**
     * @var Publero\AdvancedTelecomSMSBundle\Connector\SoapConnector
     */
    private $soapConnector;

    /**
     * @var int
     */
    private $serviceId;

    /**
     * @var string
     */
    private $password;

    public function setUp()
    {
        $this->serviceId = $this->getContainer()->getParameter('publero_advanced_telecom_sms.service_id');
        $this->password = $this->getContainer()->getParameter('publero_advanced_telecom_sms.password');
        $this->soapConnector = $this->getConnector(new ArraySigner($this->password));
    }

    /**
     * Get kernel service container
     * @return SoapConnector
     */
    protected function getConnector(ArraySigner $signer)
    {
        $soapClient = new \SoapClient($this->getMpPortWsdl());

        return new SoapConnector($this->serviceId, $soapClient, $signer);
    }

    private function getMpPortWsdl()
    {
        $fileContents = file_get_contents(__DIR__ . '/../../Resources/config/mpport_url.yml');
        $yaml = new Parser();
        $config = $yaml->parse($fileContents);

        $server = $this->getContainer()->getParameter('publero_advanced_telecom_sms.server');
        $mode = $this->getContainer()->getParameter('publero_advanced_telecom_sms.mode');

        return $config[$server][$mode]['wsdl'];
    }

    /**
     * @param int $orderId
     */
    public function testDoStartPaymentRequest()
    {
        $payment = $this->createTestStartPayment();
        $this->doPaymentRequestsAsserts($payment, array($this->soapConnector, 'doStartPaymentRequest'));
    }

    /**
     * @expectedException Publero\AdvancedTelecomSMSBundle\Connector\Exception\InvalidSignatureException
     */
    public function testDoStartPaymentRequestThrowsExceptionIfSignatureIsInvalid()
    {
        $payment = $this->createTestStartPayment();
        $soapConnector = $this->getConnector(new ArraySigner('Wrong Password'));
        $soapConnector->doStartPaymentRequest($payment);
    }

    /**
     * @return StartPayment
     */
    private function createTestStartPayment()
    {
        $payment = new StartPayment(100);
        $payment
            ->setBackUrl('http://example.com')
            ->setDescription('Example Transaction')
            ->setOrderId(time())
        ;

        return $payment;
    }

    public function testDoStartPaymentWithIdentRequest()
    {
        $payment = $this->createTestStartPaymentWithIdent();
        $this->doPaymentRequestsAsserts($payment, array($this->soapConnector, 'doStartPaymentWithIdentRequest'));
    }

    /**
     * @expectedException Publero\AdvancedTelecomSMSBundle\Connector\Exception\InvalidSignatureException
     */
    public function testDoStartPaymentWithIdentRequestThrowsExceptionIfSignatureIsInvalid()
    {
        $payment = $this->createTestStartPaymentWithIdent();
        $soapConnector = $this->getConnector(new ArraySigner('Wrong Password'));
        $soapConnector->doStartPaymentWithIdentRequest($payment);
    }

    /**
     * @return StartPaymentWithIdent
     */
    private function createTestStartPaymentWithIdent()
    {
        $operator = $this->getContainer()->getParameter('publero_advanced_telecom_sms.server') == 'production' ? StartPaymentWithIdent::OPERATOR_VODAFONE : StartPaymentWithIdent::OPERATOR_TEST;

        $payment = new StartPaymentWithIdent(100);
        $payment
            ->setBackUrl('http://example.com')
            ->setDescription('Example Transaction')
            ->setOrderId(time() * 2)
            ->setPhoneNumber('+420 608 000 000')
            ->setOperator($operator)
        ;

        return $payment;
    }

    /**
     * @param StartPayment $payment
     */
    public function doPaymentRequestsAsserts(StartPayment $payment, $callback)
    {
        // New payment result
        $result = call_user_func($callback, $payment);
        $this->assertEquals(1, $result->getCode());
        $this->assertEquals(0, $result->getSubCode());
        $this->assertNotEmpty($result->getUrl());

        // Already existing payment result
        $result = call_user_func($callback, $payment);
        $this->assertEquals(0, $result->getCode());
        $this->assertEquals(12, $result->getSubCode());
        $this->assertEmpty($result->getUrl());
    }

    public function testDoTransactionStatusRequest()
    {
        $payment = new StartPayment(100);
        $payment
            ->setBackUrl('http://example.com')
            ->setDescription('Example Transaction')
            ->setOrderId(time() * 3)
        ;

        $this->soapConnector->doStartPaymentRequest($payment);

        $status = new TransactionStatus();
        $status->setOrderId($payment->getOrderId());

        $result = $this->soapConnector->doTransactionStatusRequest($status);

        $this->assertEquals(4, $result->getCode());
    }

    /**
     * @expectedException Publero\AdvancedTelecomSMSBundle\Connector\Exception\InvalidSignatureException
     */
    public function testDoTransactionStatusRequestThrowsExceptionIfSignatureIsInvalid()
    {
        $status = new TransactionStatus();
        $status->setOrderId(1);

        $soapConnector = $this->getConnector(new ArraySigner('Wrong Password'));
        $soapConnector->doTransactionStatusRequest($status);
    }

    public function testGetSoapClient()
    {
        $this->assertInstanceOf('\SoapClient', $this->soapConnector->getSoapClient());
    }
}

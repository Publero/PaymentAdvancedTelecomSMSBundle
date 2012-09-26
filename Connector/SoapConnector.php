<?php
namespace Publero\AdvancedTelecomSMSBundle\Connector;

use Publero\AdvancedTelecomSMSBundle\Signer\ArraySigner;
use Publero\AdvancedTelecomSMSBundle\Entity\ToSignatureArrayConvertableInterface;
use Publero\AdvancedTelecomSMSBundle\Entity\ToRequestArrayConvertableInterface;
use Publero\AdvancedTelecomSMSBundle\Connector\Exception\InvalidSignatureException;
use Publero\AdvancedTelecomSMSBundle\Entity\StartPayment;
use Publero\AdvancedTelecomSMSBundle\Entity\StartPaymentWithIdent;
use Publero\AdvancedTelecomSMSBundle\Entity\TransactionStatus;
use Publero\AdvancedTelecomSMSBundle\Entity\StartPaymentResult;
use Publero\AdvancedTelecomSMSBundle\Entity\TransactionStatusResult;

class SoapConnector
{
    /**
     * @var int
     */
    private $serviceId;

    /**
     * @var \SoapClient
     */
    private $soapClient;

    /**
     * @var ArraySigner
     */
    private $signer;

    public function __construct($serviceId, \SoapClient $soapClient, ArraySigner $signer)
    {
        $this->serviceId = $serviceId;
        $this->soapClient = $soapClient;
        $this->signer = $signer;
    }

    /**
     * @param StartPayment $startPayment
     * @return StartPaymentResult
     */
    public function doStartPaymentRequest(StartPayment $startPayment)
    {
        $data = $this->doRequest('StartPayment', $startPayment);

        return $this->createPaymentResultFromData($data);
    }

    /**
     * @param StartPaymentWithIdent $startPaymentWithident
     * @return StartPaymentResult
     */
    public function doStartPaymentWithIdentRequest(StartPaymentWithIdent $startPaymentWithident)
    {
        $data = $this->doRequest('StartPaymentWithIdent', $startPaymentWithident);

        return $this->createPaymentResultFromData($data);
    }

    /**
     * @param \stdClass $data
     * @throws InvalidSignatureException
     * @return StartPaymentResult
     */
    private function createPaymentResultFromData(\stdClass $data)
    {
        $result = new StartPaymentResult();
        $result->setCode($data->RESULTCODE);
        $result->setSubCode($data->SUBRESULTCODE);
        $result->setUrl($data->URL);

        $generatedSignature = $this->generateResultSignature($result);
        if ($data->SIGNATURE != $generatedSignature) {
            throw new InvalidSignatureException('Invalid start payment response signature');
        }

        return $result;
    }

    /**
     * @param TransactionStatus $transactionStatus
     * @return TransactionStatusResult
     */
    public function doTransactionStatusRequest(TransactionStatus $transactionStatus)
    {
        $data = $this->doRequest('TransactionStatus', $transactionStatus);

        $result = new TransactionStatusResult();
        $result->setOrderId($data->ORDERID);
        $result->setCode($data->RESULTCODE);
        $result->setDescription($data->RESULTDESC);

        $generatedSignature = $this->generateResultSignature($result);
        if ($data->SIGNATURE != $generatedSignature) {
            throw new InvalidSignatureException('Invalid transaction status response signature');
        }

        return $result;
    }

    /**
     * @param string $method
     * @param ToRequestArrayConvertableInterface $requestData
     * @return \stdClass
     */
    private function doRequest($method, ToRequestArrayConvertableInterface $requestData)
    {
        $data = $requestData->toRequestArray();
        array_unshift($data, $this->serviceId);
        $data[] = $this->generateSignature($requestData);

        return $this->soapClient->__soapCall($method, $data);
    }

    /**
     * @param ToSignatureArrayConvertableInterface $requestData
     * @return string
     */
    private function generateSignature(ToSignatureArrayConvertableInterface $requestData)
    {
        $signatureData = $requestData->toSignatureArray();
        array_unshift($signatureData, $this->serviceId);

        return $this->signer->generateSignature($signatureData);
    }

    /**
     * @param ToSignatureArrayConvertableInterface $requestData
     * @return string
     */
    private function generateResultSignature(ToSignatureArrayConvertableInterface $requestData)
    {
        $signatureData = $requestData->toSignatureArray();

        return $this->signer->generateSignature($signatureData);
    }

    /**
     * @return \SoapClient
     */
    public function getSoapClient()
    {
        return $this->soapClient;
    }
}

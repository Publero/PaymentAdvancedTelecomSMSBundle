<?php
namespace Publero\AdvancedTelecomSMSBundle\Entity;

class StartPaymentResult extends Result
{
    /**
     * @var int
     */
    private $subCode;

    /**
     * @var string
     */
    private $url;

    public function getMessageTranslationId()
    {
        if ($this->getCode() == 0) {
            return 'start_payment_result.subcode.' . $this->getSubCode();
        }

        return 'start_payment_result.code.ok';
    }

    /**
     * @return int
     */
    public function getSubCode()
    {
        return $this->subCode;
    }

    /**
     * @param int $subCode
     * @return StartpaymentResult
     */
    public function setSubCode($subCode)
    {
        $this->subCode = $subCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return StartPaymentResult
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    public function toSignatureArray()
    {
        return array(
            $this->getCode(),
            $this->getSubCode(),
            $this->getUrl()
        );
    }
}

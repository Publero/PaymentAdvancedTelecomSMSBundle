<?php
namespace Publero\AdvancedTelecomSMSBundle\Entity\Exception;

class InvalidResultCodeException extends \RuntimeException
{
    /**
     * @var int
     */
    private $resultCode;

    /**
     * @param int $resultCode
     */
    public function __construct($resultCode)
    {
        parent::__construct('Error Result code ' . $resultCode . ' given.');
        $this->resultCode = $resultCode;
    }

    /**
     * @return int
     */
    public function getResultCode()
    {
        return $this->resultCode;
    }
}

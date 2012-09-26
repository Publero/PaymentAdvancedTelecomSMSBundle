<?php
namespace Publero\AdvancedTelecomSMSBundle\Entity;

abstract class Result implements ToSignatureArrayConvertableInterface
{
    /**
     * @var int
     */
    private $code;

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $code
     * @return Result
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string translation id
     */
    abstract public function getMessageTranslationId();
}

<?php
namespace Publero\AdvancedTelecomSMSBundle\Entity;

interface ToSignatureArrayConvertableInterface
{
    /**
     * Converts object's values to array which is used to make request signature
     * @return array
     */
    public function toSignatureArray();

}

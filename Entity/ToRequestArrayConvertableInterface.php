<?php
namespace Publero\AdvancedTelecomSMSBundle\Entity;

interface ToRequestArrayConvertableInterface extends ToSignatureArrayConvertableInterface
{
    /**
     * Converts object's values to array which is used to make requests to remote server
     * @return array
     */
    public function toRequestArray();
}

<?php
namespace Publero\AdvancedTelecomSMSBundle\Signer;

class ArraySigner
{
    /**
     * @var string
     */
    private $password;

    /**
     * @param string $password
     * @throws \InvalidArgumentException
     */
    public function __construct($password)
    {
        if (!is_string($password)) {
            throw new \InvalidArgumentException('Password must be string');
        }

        if (empty($password)) {
            throw new \InvalidArgumentException('Password cannot be empty');
        }

        $this->password = $password;
    }

    /**
     * @param array $values
     * @return string
     */
    public function generateSignature(array $values)
    {
        $values[] = $this->getPassword();

        return sha1(implode('|', $values));
    }

    /**
     * @return string
     */
    protected function getPassword()
    {
        return $this->password;
    }
}
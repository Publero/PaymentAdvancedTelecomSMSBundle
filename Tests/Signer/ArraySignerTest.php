<?php
namespace Publero\AdvancedTelecomSMSBundle\Tests\Signer;

use Publero\AdvancedTelecomSMSBundle\Signer\ArraySigner;

class ArraySignerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ArraySigner
     */
    private $signer;

    /**
     * @var string
     */
    private $password = 'heslo001';

    public function setUp()
    {
        $this->signer = new ArraySigner($this->password);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorThrowsInvalidArgumentExceptionIfEmptyPasswordIsGiven()
    {
        new ArraySigner('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorThrowsInvalidArgumentExceptionIfNonStringPasswordIsGiven()
    {
        new ArraySigner(5);
    }

    public function testGenerateStartPaymentSignature()
    {
        $values = array(
            'serviceId' => 1,
            'orderId' => 1286,
            'amount' => '99.00',
            'currency' => 'CZK',
            'description' => 'popisek',
            'backUrl' => 'http://www.example.com',
            'target' => 'SAME',
            'channel' => 'WAP'
        );

        $signature = $this->signer->generateSignature($values);

        $this->assertEquals('bde936d1a7a21968ad85817344cdc59d03b3338a', $signature);
    }

    public function testGenerateReplySignature()
    {
        $values = array(
            'resultCode' => 1,
            'subResultCode' => 0,
            'url' => 'http://www.example.com'
        );

        $signature = $this->signer->generateSignature($values);

        $this->assertEquals('dcf6fe117ba1e284580833e49f009cbf71f9f31b', $signature);
    }

    public function testGenerateTransactionStatusSignature()
    {
        $values = array(
            'serviceId' => 2054,
            'orderId' => 1286
        );

        $signature = $this->signer->generateSignature($values);

        $this->assertEquals('d26aae2e6f1804c90030a9f55d844cc8a93fbf44', $signature);
    }
}
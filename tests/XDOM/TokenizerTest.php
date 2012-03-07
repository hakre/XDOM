<?php
/**
 * <XDOM.header>
 *
 * @php-version 5.2
 */

require_once dirname(__FILE__) . '/../XDOMTests.php';

class XDOM_TokenizerTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $tokenizer = new XDOM_Tokenizer();
        $this->assertInstanceOf('XDOM_Tokenizer', $tokenizer);
    }

    public function testTokens()
    {
        $tokenizer = new XDOM_Tokenizer();
        $this->assertInstanceOf('XDOM_Tokenizer', $tokenizer);

        $this->assertInternalType('array', $tokenizer->getOptions());
        $this->assertInternalType('array', $tokenizer->getDefs());
        $this->assertInternalType('array', $tokenizer->getTokens());
    }

    /**
     * @param string $name
     * @param string $string
     * @param mixed $token
     */
    private function assertToken($name, $string, $token)
    {
        $this->assertInternalType('array', $token);
        $this->assertEquals(array($name, $string), $token);
    }

    public function testTokenizing()
    {
        $subject = '"hello\\" my string"';
        $tokenizer = new XDOM_Tokenizer();
        $token = $tokenizer->tokenAt($subject, 0);
        $this->assertToken('STRING', $subject, $token);

        $tokenNext = $tokenizer->tokenAt($subject, 19);
        $this->assertEquals(null, $tokenNext);
    }

    public function testTokenizingLargest()
    {
        $subject = '*:not(div)';
        $tokenizer = new XDOM_Tokenizer();

        $t1 = $tokenizer->tokenAt($subject, 0);
        $this->assertEquals('*', $t1);

        $t2 = $tokenizer->tokenAt($subject, 1);
        $this->assertToken('NOT', ':not(', $t2);

        $t3 = $tokenizer->tokenAt($subject, 6);
        $this->assertToken('IDENT', 'div', $t3);

        $t4 = $tokenizer->tokenAt($subject, 9);
        $this->assertEquals(')', $t4);

    }
}
<?php
/**
 * <XDOM.header>
 *
 * @php-version 5.2
 */

require_once dirname(__FILE__) . '/../XDOMTests.php';

class XDOM_CssSelectorTest extends PHPUnit_Framework_TestCase
{
    /**
     * data provider
     * @return array
     */
    public function formats()
    {
        $formats = array(
            NULL,
            'a',
        );
        foreach ($formats as &$format) $format = array($format);
        return $formats;
    }

    /**
     * @param $selector
     * @dataProvider formats
     */
    public function testFormats($selector)
    {
        $selector = new XDOM_CssSelector($selector);
        $this->assertInstanceOf('XDOM_CssSelector', $selector);
        $selector->parse();
    }
}
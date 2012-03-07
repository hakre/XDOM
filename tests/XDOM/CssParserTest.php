<?php
/**
 * <XDOM.header>
 *
 * @php-version 5.2
 */

require_once dirname(__FILE__) . '/../XDOMTests.php';

class XDOM_CssParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var XDOM_CssParser
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new XDOM_CssParser;
    }

    protected function tearDown()
    {
    }


    public function provideSelectors()
    {
        $selectors = array(
            // 6.3. Attribute selectors

            // 6.3.1. Attribute presence and value selectors
            // CSS2 introduced four attribute selectors:
            '[att]', # Represents an element with the att attribute, whatever the value of the attribute.
            '[att=val]', # Represents an element with the att attribute whose value is exactly "val".
            '[att~=val]', # Represents an element with the att attribute whose value is a whitespace-separated list of
            # words, one of which is exactly "val". If "val" contains whitespace, it will never represent
            # anything (since the words are separated by spaces). Also if "val" is the empty string, it
            # will never represent anything.
            '[att|=val]', # Represents an element with the att attribute, its value either being exactly "val" or
            # beginning with "val" immediately followed by "-" (U+002D). This is primarily intended to
            # allow language subcode matches (e.g., the hreflang attribute on the a element in HTML) as
            # described in BCP 47 ([BCP47]) or its successor. For lang (or xml:lang) language subcode
            # matching, please see the :lang pseudo-class.
            // Examples:
            'span[class="example"]', 'span[hello="Cleveland"][goodbye="Columbus"]',
            'a[rel~="copyright"]', 'a[href="http://www.w3.org/"]',
            'a[hreflang=fr]', 'a[hreflang|="en"]',

            # @todo unknown / combinator? -> add to test later on
            # The following selectors represent a DIALOGUE element whenever it has one of two different values for an
            # attribute character:
            // 'DIALOGUE[character=romeo] DIALOGUE[character=juliet]',

            // 6.3.2. Substring matching attribute selectors
            // Three additional attribute selectors are provided for matching substrings in the value of an attribute:
            '[att^=val]',
            '[att$=val]',
            '[att*=val]',
            // Examples:
            'object[type^="image/"]', 'a[href$=".html"]', 'p[title*="hello"]',

            // 6.3.3. Attribute selectors and namespaces
            // CSS examples:
            '[foo|att=val]',
            '[*|att]',
            '[|att]',
            '[att]',

            // 6.4. Class selectors
            // CSS examples:
            '*.pastoral', '.pastoral', 'H1.pastoral', 'p.pastoral.marine',

            // 6.5. ID selectors
            // Examples:
            'h1#chapter1', '#chapter1', '*#z98y',

            // 6.6. Pseudo-classes
            'a.external:visited',
            'a:link', 'a:visited', 'a:hover', 'a:active', # 6.6.1.2. The user action pseudo-classes :hover, :active, and :focus
            'a:focus', 'a:focus:hover',
            'p.note:target', '*:target', '*:target::before', # 6.6.2. The target pseudo-class :target
            'html:lang(fr-be)', 'html:lang(de)', # 6.6.3. The language pseudo-class :lang
            // @todo combinator not yet finished
            // ':lang(fr-be) > q', ':lang(de) > q',
            ':enabled', ':disabled', ':checked', ':indeterminate', # 6.6.4. The UI element states pseudo-classes
            ':root', # 6.6.5. Structural pseudo-classes
            'tr:nth-child(2n+1)', 'tr:nth-child(odd)', 'tr:nth-child(2n+0)', 'tr:nth-child(even)', 'p:nth-child(4n+1)', 'p:nth-child(4n+2)',
            ':nth-child(10n-1)', ':nth-child(10n+9)', ':nth-child(10n+-1)'
        );

        $data = array();
        foreach ($selectors as $selector)
        {
            // printf("#%d: %s\n", count($data), $selector);
            $data[] = array($selector);
        }
        return $data;
    }

    /**
     * @param string $selector
     * @dataProvider provideSelectors
     */
    public function testSelectors($selector)
    {
        $this->object->parse($selector);
        $this->assertEquals(strlen($selector), $this->object->key(), sprintf('Less/More for [%s]', $selector));
    }

    public function testParse()
    {
        $parser = $this->object;

        // type_selector, namespace_prefix, element_name, universal
        $parser->parse('*');
        $this->assertEquals(1, $parser->key());
        $parser->parse('div');
        $this->assertEquals(3, $parser->key());
        $parser->parse('|div');
        $this->assertEquals(4, $parser->key());
        $parser->parse('*|div');
        $this->assertEquals(5, $parser->key());
        $parser->parse('ns|div');
        $this->assertEquals(6, $parser->key());
        $parser->parse('ns|*');
        $this->assertEquals(4, $parser->key());
        $parser->parse('*|*');
        $this->assertEquals(3, $parser->key());

        // selectors_group
        $parser->parse('div, div');
        $this->assertEquals(8, $parser->key());
        $parser->parse('div , div');
        $this->assertEquals(9, $parser->key());
    }

    public function testIsolated()
    {
        $parser = $this->object;

        // :lang(de)
        $test = '*:lang(de)';
        $parser->parse($test);
        $this->assertEquals(strlen($test), $parser->key());
    }

    public function testComments()
    {
        $parser = $this->object;

        // skip comments
        $parser->parse('*|/* this div must have a namespace */div');
        $this->assertEquals(41, $parser->key());
    }
}

?>

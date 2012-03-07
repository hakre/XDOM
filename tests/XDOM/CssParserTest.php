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
            // Examples taken from: http://www.w3.org/TR/selectors/#selector-syntax

            // 2. Selectors (Summary table)
            '*', 'E', 'E[foo]', 'E[foo="bar"]', 'E[foo~="bar"]', 'E[foo^="bar"]', 'E[foo$="bar"]', 'E[foo*="bar"]',
            'E[foo|="en"]', 'E:root', 'E:nth-child(n)', 'E:nth-last-child(n)', 'E:nth-of-type(n)',
            'E:nth-last-of-type(n)', 'E:first-child', 'E:last-child', 'E:first-of-type', 'E:last-of-type',
            'E:only-child', 'E:only-of-type', 'E:empty', 'E:link', 'E:visited', 'E:active', 'E:hover', 'E:focus',
            'E:target', 'E:lang(fr)', 'E:enabled', 'E:disabled', 'E:checked', 'E::first-line', 'E::first-letter',
            'E::before', 'E::after', 'E.warning', 'E#myid', 'E:not(s)', 'E F', 'E > F', 'E + F', 'E ~ F',

            // 6.1. Type selector
            'h1',
            'ns|E', '*|E', '|E', 'E', #6.1.1. Type selectors and namespaces
            'foo|h1', 'foo|*', '|h1', '*|h1',

            // 6.2. Universal selector
            '*[hreflang|=en]', '[hreflang|=en]', '*.warning', '.warning', '*#myid', '#myid',
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

            # The following selectors represent a DIALOGUE element whenever it has one of two different values for an
            # attribute character:
            'DIALOGUE[character=romeo] DIALOGUE[character=juliet]',

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
            ':lang(fr-be) > q', ':lang(de) > q',
            ':enabled', ':disabled', ':checked', ':indeterminate', # 6.6.4. The UI element states pseudo-classes
            ':root', # 6.6.5. Structural pseudo-classes
            'tr:nth-child(2n+1)', 'tr:nth-child(odd)', #6.6.5.2. :nth-child() pseudo-class
            'tr:nth-child(2n+0)', 'tr:nth-child(even)', 'p:nth-child(4n+1)', 'p:nth-child(4n+2)',
            ':nth-child(10n-1)', ':nth-child(10n+9)', ':nth-child(10n+-1)',
            'foo:nth-child(0n+5)', 'foo:nth-child(5)',
            'bar:nth-child(1n+0)', 'bar:nth-child(n+0)', 'bar:nth-child(n)', 'bar',
            'tr:nth-child(2n+0)', 'tr:nth-child(2n)',
            ':nth-child( 3n + 1 )', ':nth-child( +3n - 2 )', ':nth-child( -n+ 6)', ':nth-child( +6 )',
            // invalid: (nth has it's own additional grammar)
            ':nth-child(3 n)', ':nth-child(+ 2n)', ':nth-child(+ 2)',
            'html|tr:nth-child(-n+6)', /* represents the 6 first rows of XHTML tables */
            'tr:nth-last-child(-n+2)', 'foo:nth-last-child(odd)', #6.6.5.3. :nth-last-child() pseudo-class
            'img:nth-of-type(2n+1)', 'img:nth-of-type(2n)', #6.6.5.4. :nth-of-type() pseudo-class
            'div > p:first-child', '* > a:first-child', 'a:first-child', #6.6.5.6. :first-child pseudo-class
            'ol > li:last-child', #6.6.5.7. :last-child pseudo-class
            'dl dt:first-of-type', 'tr > td:last-of-type', #6.6.5.8. :first-of-type pseudo-class ...
            'a:only-child', 'a::only-of-type',
            'p:empty',
            # 6.6.7. The negation pseudo-class
            // invalid: ':not(:not(a))', @todo give it's own failing test because grammar does not allow it.
            'button:not([DISABLED])', '*:not(FOO)', 'html|*:not(:link):not(:visited)',
            '*|*:not(*)', '*|*:not(:hover)',

            // 7. Pseudo-elements
            'p::first-line', 'p::first-letter', 'p::first-line',
            '::before', '::after',

            // 8. Combinators
            'h1 em', 'div * p', 'div p *[href]', #8.1. Descendant combinator
            'body > p', 'div ol>li p', #8.2. Child combinators
            'math + p', 'h1.opener + h2', #8.3. Sibling combinators
            'h1 ~ pre', #8.3.2. General sibling combinator

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
        // :lang(fr-be) > q
        // button:not([DISABLED])
        // .warning
        $test = 'div /*test comment*/'; //combinator triggered not avail
        try {
            $parser->parse($test);
            $this->fail();
        } catch (Exception $e) {
            $message = $e->getMessage();
            $this->assertEquals('PARSE ERROR: expect production: [selector] at 0.', $message);
        }
    }

    public function testComments()
    {
        $parser = $this->object;

        // skip comments
        $test = '*|/* this div must have a namespace */div';
        $parser->parse($test);
        $this->assertEquals(strlen($test), $parser->key());

        $test = 'div /* this div has a comment "in" it\'s combinator */ div';
        $parser->parse($test);
        $this->assertEquals(strlen($test), $parser->key());
    }
}
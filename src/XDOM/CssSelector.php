<?php
/**
 * <XDOM.header>
 *
 * @php-version 5.2
 */

/**
 * Css Selector
 */
class XDOM_CssSelector
{
    private $selector;

    /**
     * @param string|null $selector (optional)
     */
    public function __construct($selector = NULL)
    {
        $this->selector = $selector;
    }

    public function parse()
    {
    }
}
<?php
/**
 * <XDOM.header>
 *
 * @php-version 5.2
 */

/**
 * Tokenizer (Tokenizer.lex)
 */
class XDOM_Tokenizer
{
    /**
     * @var array
     */
    protected $options, $defs, $tokens;
    /**
     * @var array
     */
    protected $defsRegex, $tokensRegex;

    public function __construct()
    {
        $file = dirname(__FILE__) . '/Tokenizer.lex';
        $this->parseFile($file);
        if (!count($this->defs)) {
            throw new Exception('No defs.');
        }
        if (!count($this->tokens)) {
            throw new Exception('No tokens.');
        }
        $this->compileRegex();
        $this->validateRegex();
    }

    /**
     * @param string $file
     */
    private function parseFile($file)
    {
        $lines = file($file);
        $this->parseLines($lines);
    }

    /**
     * @param array $lines
     * @throws Exception
     */
    private function parseLines(array $lines)
    {
        $state = 0;
        foreach ($lines as $line)
        {
            $line = rtrim($line);
            if (!strlen($line)) {
                continue;
            }

            switch ($state)
            {
                case 0:
                    if (substr($line, 0, 8) === '%option ') {
                        $this->options[] = substr($line, 8);
                        break;
                    }
                    $state = 1;

                case 1:
                    if ('%%' === $line) {
                        $state = 2;
                        break;
                    }
                    list($name, $pattern) = array_map('trim', explode(' ', $line, 2));
                    if (isset($this->defs[$name])) {
                        throw new Exception(sprintf('{%s} (%s) already defined: %s', $name, $pattern, $this->defs[$name]));
                    }
                    $this->defs[$name] = $pattern;
                    break;

                case 2:
                    if ($p = strpos($line, ' return ', 1)) {
                        list($pattern, $token) = explode(' return ', $line);
                        $pattern = rtrim($pattern);
                        $token = rtrim($token, ';');
                        if (isset($this->tokens[$token])) {
                            throw new Exception(sprintf('Token %s (%s) already defined: %s', $token, $pattern, $this->tokens[$token]));
                        }
                        $this->tokens[$token] = $pattern;
                        break;
                    }

                    if ($p = preg_match('~^(.*?)[ ]+(/\*.*\*/)$~', $line, $match)) {
                        $this->tokens[$match[2]] = $match[1];
                        break;
                    }
                    throw new Exception(sprintf('Unknown: "%s"', $line));

                default:
                    throw new Exception(sprintf('Invalid state: #%d.', $state));
            }
        }
    }

    private function compileRegex()
    {
        $this->expandDefs();
        $this->expandTokens();
    }

    private function validateRegex()
    {
        foreach ($this->tokensRegex as $name => $pattern)
        {
            $r = preg_match("/$pattern/iu", '');
            if ($r === FALSE) {
                $error = ((array)error_get_last()) + array('message' => 'not given');
                throw new UnexpectedValueException(sprintf('Compile failed for %s / %s. Error is %s for expression %s.'
                    , $name, $this->tokens[$name], $error['message'], $pattern));
            }
        }
    }

    private function expandDefs()
    {
        $tokens = $this->defs;
        $expanded = array_combine(array_keys($tokens), array_fill(0, count($tokens), NULL));

        // @todo optimize: (doesn't PCRE optimize this anyway?) character groups don't need any additional grouping, pre-filter tokens therefore
        $i = 0;
        do {
            if ($i++ > 1024) throw new Exception('Overflow.');

            $continue = FALSE;
            foreach ($tokens as $current => $token) {
                $r = preg_match('/{([a-z]+[0-9]?)}/', $token, $match);
                if ($r) {
                    list($mark, $name) = $match;
                    if (!isset($tokens[$name]) && !isset($expanded[$name])) {
                        throw new Exception(sprintf('Not a token to expand: %s.', $name));
                    }
                    $continue = TRUE;
                    if (isset($expanded[$name])) {
                        $tokens[$current] = str_replace($mark, sprintf('(?:%s)', $expanded[$name]), $token);
                        $continue = TRUE;
                    }
                } else {
                    $expanded[$current] = $token;
                    unset($tokens[$current]);
                }
            }
        } while ($continue);

        if (count($tokens)) {
            throw new Exception('Not all defs have been expanded:', print_r($tokens));
        }

        $this->defsRegex = $expanded;
    }

    private function expandTokens()
    {
        $list = $this->tokens;
        foreach ($list as &$v)
        {
            $t = preg_replace('/("([^"]+)")/e', 'preg_quote("$2");', $v);
            while ($r = preg_match('/{([a-z]+[0-9]?)}/i', $t, $match)) {
                list($mark, $name) = $match;
                if (!isset($this->defsRegex[$name])) {
                    throw new Exception(sprintf('{%s} is undefined.', $name));
                }
                $expanded = $this->defsRegex[$name];
                $t = str_replace($mark, sprintf('(?:%s)', $expanded), $t);
            }
            $v = $t;
        }
        unset($v);

        $this->tokensRegex = $list;
    }


    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return array
     */
    public function getDefs()
    {
        return $this->defs;
    }

    /**
     * @return array
     */
    public function getTokens()
    {
        return $this->tokens;
    }


    /**
     * strategy: largest match wins
     *
     * @param string $subject
     * @param int $offset
     * @throws Exception
     * @return string|array|null
     */
    public function tokenAt($subject, $offset)
    {
        if ($offset >= strlen($subject)) {
            return NULL;
        }
        $matchToken = NULL;
        $matchSize = 0;
        foreach ($this->tokensRegex as $name => $pattern)
        {
            $r = preg_match("/$pattern/iu", $subject, $match, PREG_OFFSET_CAPTURE, $offset);
            if ($r === 0) {
                continue;
            }
            if ($match[0][1] !== $offset) {
                continue;
            }
            $size = strlen($match[0][0]);
            if ($size > $matchSize) {
                $matchSize = $size;
                if ($name === '*yytext') {
                    $matchToken = $match[0][0];
                } else {
                    $matchToken = array($name, $match[0][0]);
                }
            }
        }
        return $matchToken;
    }
}
<?php
/**
 * <XDOM.header>
 *
 * @php-version 5.2
 */

require_once(dirname(__FILE__) . '/../src/XDOM.php');


$tokenizer = new XDOM_Tokenizer();

$subjects = array(
    '*|div.class/* not so sure about the hreflang */[hreflang|=en]',
    'div *:not(p) em',
    'div.na\000031me, b',
    'div.na\31 me b',
);

/**
 * using the parser iterator
 */
$parser = new XDOM_CssParser();
foreach ($subjects as $subject)
{
    $parser->setString($subject);
    foreach ($parser as $offset => $value)
    {
        printf('%2d: ', $offset);
        if (is_array($value)) {
            vprintf('<%s> %s', $value);
        } else {
            echo $value;
        }
        echo "\n";
    }
    $next = $parser->key();
    echo "\nFinished parsing: {$next}/", strlen($subject), "\n\n";
}

/*
 * manual token operation:
 */
foreach ($subjects as $subject)
{
    echo "Parsing: $subject\n";
    $offset = 0;
    while ($token = $tokenizer->tokenAt($subject, $offset)) {
        if (is_array($token)) {
            list($name, $string) = $token;
            printf('<%s:%s>', $name, $string);
            $offset += strlen($string);
        } else {
            echo $token;
            $offset += strlen($token);
        }
    }
    echo "\nFinished parsing: $offset/", strlen($subject), "\n\n";
}


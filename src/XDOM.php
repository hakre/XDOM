<?php
/**
 * <XDOM.header>
 *
 * @php-version 5.2
 */

/**
 * XDOM autoloader
 *
 * @param string $className
 */
function XDOMAutoload($className)
{
    static $base;
    $parts = explode('_', $className);
    if ('XDOM' !== $parts[0]) return;

    $base || $base = dirname(__FILE__);

    $path = sprintf('%s/%s.php', $base, implode('/', $parts));
    $real = realpath($path);

    // wincompat
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {

        list($path, $real) = str_replace(DIRECTORY_SEPARATOR, '/', array($path, $real));
    }

    if ($real !== $path) return;

    require_once($path);
}

spl_autoload_register('XDOMAutoload');
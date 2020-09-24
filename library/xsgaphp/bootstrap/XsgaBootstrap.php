<?php
/**
 * Bootstrap.
 *
 * PHP Version 7
 *
 * @package Xsga\Xsga-Php\Library\Bootstrap
 * @author  xsga <parker@xsga.es>
 * @version 1.0.0
 */

use log4php\Logger;

// Set error reporting level.
error_reporting(E_ALL);

/**
 * Error handler.
 *
 * @param integer $errno   Error number.
 * @param string  $errstr  Error message.
 * @param string  $errfile Error file.
 * @param integer $errline Error line.
 *
 * @throws ErrorException Error exception.
 *
 * @return void
 *
 * @access public
*/
function exceptionErrorHandler($errno, $errstr, $errfile, $errline)
{

    // Error exception.
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);

}//end exception_error_handler()


// Register exceptionErrorHandler.
$errorTypes = (E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
set_error_handler('exceptionErrorHandler', $errorTypes);

// Xsga-PHP constants definition.
define('DEBUG_LITERAL', 'debug');
define('TRUE_LITERAL', 'true');

// Load Composer autoloader.
$pathAutoload  = DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
$pathAutoload .= 'vendor'.DIRECTORY_SEPARATOR;

require_once realpath(dirname(__FILE__)).$pathAutoload.'autoload.php';

$pathConfig  = DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
$pathConfig .= 'config'.DIRECTORY_SEPARATOR;

// Load config.
require_once realpath(dirname(__FILE__)).$pathConfig.'settings.php';

// Load Logger configuration.
Logger::configure(realpath(dirname(__FILE__)).$pathConfig.'log4php-app.xml');

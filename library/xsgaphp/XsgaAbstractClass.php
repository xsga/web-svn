<?php
/**
 * XsgaAbstractClass.
 *
 * PHP version 7
 *
 * @package Xsga\Xsga-Php\Library\Mvc\Abstract
 * @author  xsga <parker@xsga.es>
 * @version 1.0.0
 */
 
/**
 * Namespace.
 */
namespace xsgaphp;

/**
 * Import namespaces.
 */
use log4php\Logger;

/**
 * XsgaAbstractClass class.
 *
 * This abstract class defines a generic class pattern.
 */
abstract class XsgaAbstractClass
{
    
    /**
     * Logger.
     * 
     * @var Logger
     * 
     * @access public
     */
    public $logger;
   
    
    /**
     * Constructor.
     * 
     * @access public
     */
    public function __construct()
    {
        // Set logger.
        $this->logger = Logger::getRootLogger();
        
    }//end __construct()
    

}//end XsgaAbstractClass class

<?php
/**
 * WebSVN.
 * 
 * This is a fork by xsga of original WebSVN software.
 * 
 * WebSVN - Subversion repository viewing via the web using PHP.
 * 
 * Copyright (C) 2004-2006 Tim Armes.
 * 
 * This program is free software; you can redistribute it and/or modify 
 * it under the terms of the GNU General Public License as published by 
 * the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, but 
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY 
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License 
 * for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA.
 * 
 * PHP Version 7
 * 
 * @author xsga <xsegales@outlook.com>
 * @version 1.0.0
 */

/**
 * Used classes.
 */
use log4php\Logger;
use app\business\setup\Setup;
use app\business\setup\WebSvnCons;
use app\business\template\Template;

// Start session.
session_start();

// Bootstrap.
$path  = DIRECTORY_SEPARATOR.'..';
$path .= DIRECTORY_SEPARATOR.'library';
$path .= DIRECTORY_SEPARATOR.'xsgaphp';
$path .= DIRECTORY_SEPARATOR.'bootstrap';
$path .= DIRECTORY_SEPARATOR;
require_once realpath(dirname(__FILE__)).$path.'XsgaBootstrap.php';

// Get Logger.
$logger = Logger::getRootLogger();

// Logger.
$logger->debug('New request: '.$_SERVER['REQUEST_URI']);

// Setup app.
$setup = new Setup();
$setup->run();

// Get page request.
$urlArray = explode('/', $_SERVER['REQUEST_URI']);
$pageAndParams = end($urlArray);

// Get page controller.
if (empty($pageAndParams) || $pageAndParams === '?') {
    $page = 'IndexController';
} else {
    $pageAndParamsArray = explode('?', $pageAndParams);
    $page               = $pageAndParamsArray[0];
    $page               = str_replace('.php', '', $page);
    $page               = ucfirst($page).'Controller';
}//end if

$namespace = '\\app\\controller\\';
$class     = $namespace.$page;

try {
    
    // Get controller instance.
    $controller = new $class($setup);
    
} catch (Exception $e) {
    
    // Get error code.
    if (empty($e->getCode())) {
        $code = WebSvnCons::ERROR_500;
    } else {
        $code = $e->getCode();
    }//end if
    
    // Logger.
    $logger->error('ERROR '.$code.': '.$e->getMessage());
    $logger->error($e->__toString());
    
    // Get header.
    switch ($code) {
        
        case WebSvnCons::ERROR_403:
            http_response_code(WebSvnCons::ERROR_403);
            break;
            
        case WebSvnCons::ERROR_404:
            http_response_code(WebSvnCons::ERROR_404);
            break;
            
        default:
            http_response_code(WebSvnCons::ERROR_500);
            break;
        
    }//end switch
    
    // Set error vars.
    $setup->vars['error_id']    = $code;
    $setup->vars['error_title'] = $setup->lang['ERROR'.$code];
    $setup->vars['error_desc']  = $setup->lang['ERROR'.$code.'DESC'];
    $setup->vars['error_gen']   = $setup->lang['ERRORDESC'];
    
    // Render template.
    $template = new Template($setup);
    $template->renderTemplate('error');
    
}//end try

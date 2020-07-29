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
 * Namespace.
 */
namespace app\business\setup;

/**
 * WebSvnCons class.
 */
class WebSvnCons
{
    
    /**
     * HTTP 403 header.
     * 
     * @var string
     * 
     * @access public
     */
    const HTTP_403 = 'HTTP/1.x 403 Forbidden';
    
    /**
     * HTTP 404 header.
     *
     * @var string
     *
     * @access public
     */
    const HTTP_404 = 'HTTP/1.x 404 Not Found';
    
    /**
     * HTTP 500 header.
     *
     * @var string
     *
     * @access public
     */
    const HTTP_500 = 'HTTP/1.x 500 Internal Server Error';
    
    /**
     * Error code 403.
     * 
     * @var integer
     * 
     * @access public
     */
    const ERROR_403 = 403;
    
    /**
     * Error code 404.
     *
     * @var integer
     *
     * @access public
     */
    const ERROR_404 = 404;
    
    /**
     * Error code 500.
     *
     * @var integer
     *
     * @access public
     */
    const ERROR_500 = 500;
    
    /**
     * HTML &.
     *
     * @var string
     *
     * @access public
     */
    const ANDAMP = '&amp;';
    
    /**
     * HTML space.
     * 
     * @var string
     * 
     * @access public
     */
    const ANDNBSP = '&nbsp;';
    
    /**
     * BR tag.
     * 
     * @var string
     * 
     * @access public
     */
    const BR = '<br/>';
    
    /**
     * Download page error.
     * Unable to download resource at path.
     *
     * @var string
     *
     * @access public
     */
    const DL_ERROR_01 = 'Unable to download resource at path: ';
    
    /**
     * Download page error.
     * SVN export failed for. 
     *
     * @var string
     *
     * @access public
     */
    const DL_ERROR_02 = 'SVN export failed for: ';
    
    /**
     * Download page error.
     * Unable to create tar archive.
     *
     * @var string
     *
     * @access public
     */
    const DL_ERROR_03 = 'Unable to create tar archive';
    
    /**
     * Download page error.
     * Unable to call tar command.
     *
     * @var string
     *
     * @access public
     */
    const DL_ERROR_04 = 'Unable to call tar command: ';
    
    /**
     * Download page error.
     * Unable to call tar command. See webserver error log for details.
     *
     * @var string
     *
     * @access public
     */
    const DL_ERROR_05 = 'Unable to call tar command. See webserver error log for details';
    
    /**
     * Download page error.
     * Unable to open file for gz-compression.
     *
     * @var string
     *
     * @access public
     */
    const DL_ERROR_06 = 'Unable to open file for gz-compression';
    
    /**
     * Download page error.
     * Unable to open file.
     *
     * @var string
     *
     * @access public
     */
    const DL_ERROR_07 = 'Unable to open file: ';
    
    
}//end WebSvnCons class.
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
 * @author xsga <parker@xsga.es>
 * @version 1.0.0
 */

/**
 * Namespace.
 */
namespace app\business\diff;

/**
 * Used classes.
 */
use xsgaphp\XsgaAbstractClass;
use app\business\setup\WebSvnCons;

/**
 * ListingHelper class.
 */
class ListingHelper extends XsgaAbstractClass
{
    
    /**
     * Listing array.
     * 
     * @var array
     * 
     * @access public
     */
    public $_listing = array();
    
    /**
     * Index.
     * 
     * @var integer
     * 
     * @access public
     */
    public $_index = 0;
    
    /**
     * Block start.
     * 
     * @var string
     * 
     * @access public
     */
    public $_blockStart = false;
    
    
    /**
     * Add.
     * 
     * @param string  $text1
     * @param integer $lineno1
     * @param string  $class1
     * @param string  $text2
     * @param integer $lineno2
     * @param string  $class2
     * 
     * @return void
     * 
     * @access public
     */
    public function _add($text1, $lineno1, $class1, $text2, $lineno2, $class2)
    {
        
        $listvar = &$this->_listing[$this->_index];
        
        $listvar['rev1diffclass'] = $class1;
        $listvar['rev2diffclass'] = $class2;
        $listvar['rev1line']      = $text1;
        $listvar['rev2line']      = $text2;
        $listvar['rev1lineno']    = $lineno1;
        $listvar['rev2lineno']    = $lineno2;
        $listvar['startblock']    = $this->_blockStart;
        
        $this->_blockStart = false;
        
        $this->_index++;
        
    }//end _add()
    
    
    /**
     * Add deleted line.
     * 
     * @param string  $text
     * @param integer $lineno
     * 
     * @return void
     * 
     * @access public
     */
    public function addDeletedLine($text, $lineno)
    {
        $this->_add($text, $lineno, 'diffdeleted', WebSvnCons::ANDNBSP, '-', 'diffempty');
        
    }//end addDeletedLine()
    
    
    /**
     * Add added line.
     * 
     * @param string  $text
     * @param integer $lineno
     * 
     * @return void
     * 
     * @access public
     */
    public function addAddedLine($text, $lineno)
    {
        $this->_add(WebSvnCons::ANDNBSP, '-', 'diffempty', $text, $lineno, 'diffadded');
        
    }//end addAddedLine()
    
    
    /**
     * Add changed line.
     * 
     * @param string  $text1
     * @param integer $lineno1
     * @param string  $text2
     * @param integer $lineno2
     * 
     * @return void
     * 
     * @access public
     */
    public function addChangedLine($text1, $lineno1, $text2, $lineno2)
    {
        $this->_add($text1, $lineno1, 'diffchanged', $text2, $lineno2, 'diffchanged');
        
    }//end addChangedLine()
    
    
    /**
     * Add line.
     * 
     * Note that $text1 do not need to be equal $text2 if $ignoreWhitespace is true.
     * 
     * @param string  $text1
     * @param integer $lineno1
     * @param string  $text2
     * @param integer $lineno2
     * 
     * @return void
     * 
     * @access public
     */
    public function addLine($text1, $lineno1, $text2, $lineno2)
    {
        $this->_add($text1, $lineno1, 'diff', $text2, $lineno2, 'diff');
        
    }//end addLine()
    
    
    /**
     * Start new block.
     * 
     * @return void
     * 
     * @access public
     */
    public function startNewBlock()
    {
        $this->_blockStart = true;
        
    }//end startNewBlock()
    
    
    /**
     * Get listing.
     * 
     * @return array
     * 
     * @access public
     */
    public function getListing()
    {
        return $this->_listing;
        
    }//end getListing()
    
    
}//end ListingHelper class

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
 * Namespace
 */
namespace app\business\diff;

/**
 * Used classes.
 */
use xsgaphp\XsgaAbstractClass;

/**
 * SensibleLineChanges class.
 * 
 * Class for computing sensibly added/deleted block of lines.
 */
class SensibleLineChanges extends XsgaAbstractClass
{
    
    /**
     * Added.
     * 
     * @var array
     * 
     * @access public
     */
    public $_added = array();
    
    /**
     * Deleted.
     * 
     * @var array
     * 
     * @access public
     */
    public $_deleted = array();
    
    /**
     * Line diff.
     * 
     * @var LineDiff
     * 
     * @access public
     */
    public $_lineDiff = null;
    
    
    /**
     * Constructor.
     * 
     * @param LineDiff $lineDiff
     * 
     * @access public
     */
    public function __construct(LineDiff $lineDiff)
    {
        parent::__construct();
        
        $this->_lineDiff = $lineDiff;
        
    }//end __construct()
    
    
    /**
     * Add deleted line.
     * 
     * @param string  $text
     * @param string  $highlighted_text
     * @param integer $lineno
     * 
     * @return void
     * 
     * @access public
     */
    public function addDeletedLine($text, $highlighted_text, $lineno)
    {
        $this->_deleted[] = array($text, $highlighted_text, $lineno);
        
    }//end addDeletedLine()
    
    
    /**
     * Add added line.
     * 
     * @param string  $text
     * @param string  $highlighted_text
     * @param integer $lineno
     * 
     * @return void
     * 
     * @access public
     */
    function addAddedLine($text, $highlighted_text, $lineno)
    {
        $this->_added[] = array($text, $highlighted_text, $lineno);
        
    }//end addAddedLine
    
    
    /**
     * Compute fast matching.
     * 
     * This function computes simple match - first min(deleted,added) lines are marked as changed
     * it is intended to be run instead of _computeBestMatching if the diff is too big.
     * 
     * @return array
     * 
     * @access public
     */
    public function _computeFastMatching()
    {
        
        $result = array();
        $q      = 0;
        $n      = count($this->_deleted);
        $m      = count($this->_added);
        
        while ($q < $n && $q < $m) {
            $result[] = array($this->_deleted[$q], $this->_added[$q]);
            $q++;
        }//end while
        
        while ($q < $n) {
            $result[] = array($this->_deleted[$q], null);
            $q++;
        }//end while
        
        while ($q < $m) {
            $result[] = array(null, $this->_added[$q]);
            $q++;
        }//end while
        
        return $result;
        
    }//end _computeFastMatching()
    
    
    /**
     * Compute best matching.
     * 
     * Dynamically compute best matching. note that this is O(n*m) * O(line similarity).
     * 
     * @return array
     * 
     * @access public
     */
    public function _computeBestMatching()
    {
        
        $n = count($this->_deleted);
        $m = count($this->_added);
        
        // If the computation will be slow, just run fast algorithm.
        if ($n * $m > 10000) {
            return $this->_computeFastMatching();
        }//end if
        
        // Dyn olds best sum of similarities we can obtain if we match, first $i deleted lines and first $j added lines.
        $dyn = array_fill(0, $n + 1, array_fill(0, $m + 1, 0.0));
        
        // Backlinks, so we can reconstruct best layout easily.
        $back = array_fill(0, $n + 1, array_fill(0, $m + 1, -1));
        
        // If there is no similarity, prefer adding/deleting lines.
        $value_del = 0.1;
        $value_add = 0.1;
        
        // Initialize arrays.
        for ($i = 1; $i <= $n; $i++) {
            $back[$i][0] = 0;
            $dyn[$i][0]  = $value_del * $i;
        }//end for
        
        for ($j = 1; $j <= $m; $j++) {
            $back[0][$j] = 1;
            $dyn[0][$j]  = $value_add * $j;
        }//end for
        
        // Main dynamic programming.
        for ($i = 1; $i <= $n; $i++) {
            
            for ($j = 1; $j <= $m; $j++) {
                
                $best = - 1.0;
                $b    = -1;
                
                if ($dyn[$i - 1][$j] + $value_del >= $best) {
                    $b    = 0;
                    $best = $dyn[$i - 1][$j] + $value_del;
                }//end if
                
                if ($dyn[$i][$j - 1] + $value_add >= $best) {
                    $b    = 1;
                    $best = $dyn[$i][$j - 1] + $value_add;
                }//end fi
                
                $sim = $this->_lineDiff->lineSimilarity($this->_deleted[$i - 1][0], $this->_added[$j - 1][0]);
                
                if ($dyn[$i - 1][$j - 1] + $sim >= $best) {
                    $b    = 2;
                    $best = $dyn[$i - 1][$j - 1] + $sim;
                }//end if
                
                $back[$i][$j] = $b;
                $dyn[$i][$j]  = $best;
                
            }//end for
            
        }//end for
        
        // Compute layout for best result.
        $i      = $n;
        $j      = $m;
        $result = array();
        
        while ($i + $j >= 1) {
            switch($back[$i][$j]) {
                case 2: 
                    array_push($result, array($this->_deleted[$i - 1], $this->_added[$j - 1]));
                    $i--;
                    $j--;
                    break;
                    
                case 1: 
                    array_push($result, array(null, $this->_added[$j - 1]));
                    $j--;
                    break;
                    
                case 0: 
                    array_push($result, array($this->_deleted[$i - 1], null));
                    $i--;
                    break;
                    
                default:
                    assert(false);
            }//end switch
        }//end while
        
        return array_reverse($result);
        
    }//end _computeBestMatching()
    
    
    /**
     * Add computed changes to the listing.
     * 
     * @param ListingHelper $listingHelper
     * @param boolean       $highlighted
     * 
     * @return void
     * 
     * @access public
     */
    public function addChangesToListing(ListingHelper &$listingHelper, $highlighted)
    {
        
        $matching = $this->_computeBestMatching();
        
        foreach ($matching as $change) {
            
            if ($change[1] === null) {
                
                // Deleted -- preserve original highlighted text.
                $listingHelper->addDeletedLine($change[0][1], $change[0][2]);
                
            } else if ($change[0] === null) {
                
                // Added   -- preserve original highlighted text.
                $listingHelper->addAddedLine($change[1][1], $change[1][2]);
                
            } else {
                
                // This is fully changed line, make inline diff.
                $diff = $this->_lineDiff->inlineDiff($change[0][0], $change[0][1], $change[1][0], $change[1][1], $highlighted);
                $listingHelper->addChangedLine($diff[0], $change[0][2], $diff[1], $change[1][2]);
                
            }//end if
            
        }//end foreach
        
        $this->clear();
        
    }//end addChangesToListing()
    
    
    /**
     * Clear.
     * 
     * @return void
     * 
     * @access public
     */
    public function clear()
    {
        $this->_added   = array();
        $this->_deleted = array();
        
    }//end clear()
    
    
}//end SensibleLineChanges class

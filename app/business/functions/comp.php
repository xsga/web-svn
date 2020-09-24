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
 * Used classes.
 */
use app\business\setup\Setup;


/**
 * Check revisions.
 * 
 * @param string $rev Revision.
 * 
 * @return string
 */
function checkRevision($rev)
{
    if (is_numeric($rev) && ((int)$rev > 0)) {
        return $rev;
    }//end if
    
    $rev = strtoupper($rev);
    
    if ($rev === 'HEAD' || $rev === 'PREV' || $rev === 'COMMITTED') {
        return $rev;
    } else {
        return 'HEAD';
    }//end if
            
}//end checkRevision()


/**
 * Clear vars.
 * 
 * @param boolean $ignoreWhitespace
 * @param Setup   $setup
 * @param integer $index
 * 
 * @return void
 */
function clearVars($ignoreWhitespace, Setup $setup, &$index)
{
    if ($ignoreWhitespace && $index > 1) {
        
        $endBlock = false;
        $previous = $index - 1;
        
        if ($setup->listing[$previous]['endpath']) {
            $endBlock = 'newpath';
        } else if ($setup->listing[$previous]['enddifflines']) {
            $endBlock = 'difflines';
        }//end if
        
        if ($endBlock !== false) {
            
            // Check if block ending at previous contains real diff data.
            $i                     = $previous;
            $containsOnlyEqualDiff = true;
            $addedLines            = array();
            $removedLines          = array();
            
            while ($i >= 0 && !$setup->listing[$i - 1][$endBlock]) {
                
                $diffclass = $setup->listing[$i - 1]['diffclass'];
                
                if ($diffclass !== 'diffadded' && $diffclass !== 'diffdeleted' && $addedLines !== $removedLines) {
                    $containsOnlyEqualDiff = false;
                }//end if
                
                if (count($addedLines) > 0 && $addedLines === $removedLines) {
                    $addedLines = array();
                    $removedLines = array();
                }//end if
                
                if ($diffclass === 'diff') {
                    $i--;
                    continue;
                }//end if
                
                if ($diffclass === null) {
                    $containsOnlyEqualDiff = false;
                    break;
                }//end if
                
                if ($diffclass === 'diffdeleted') {
                    
                    if (count($addedLines) <= count($removedLines)) {
                        $containsOnlyEqualDiff = false;
                        break;
                    }//end if
                    
                    array_unshift($removedLines, $setup->listing[$i - 1]['line']);
                    $i--;
                    continue;
                    
                }//end if
                
                if ($diffclass === 'diffadded') {
                    
                    if (count($removedLines) > 0) {
                        $containsOnlyEqualDiff = false;
                        break;
                    }//end if
                    
                    array_unshift($addedLines, $setup->listing[$i - 1]['line']);
                    $i--;
                    continue;
                    
                }//end if
                
                assert(false);
                
            }//end while
            
            if ($containsOnlyEqualDiff) {
                $containsOnlyEqualDiff = $addedLines === $removedLines;
            }//end if
            
            // Remove blocks which only contain diffclass=diff and equal removes and adds.
            if ($containsOnlyEqualDiff) {
                for ($j = $i - 1; $j < $index; $j++) {
                    unset($setup->listing[$j]);
                }
                $index = $i - 1;
            }//end if
            
        }//end if
        
    }//end if
    
    $listvar                 = &$setup->listing[$index];
    $listvar['newpath']      = null;
    $listvar['endpath']      = null;
    $listvar['info']         = null;
    $listvar['diffclass']    = null;
    $listvar['difflines']    = null;
    $listvar['enddifflines'] = null;
    $listvar['properties']   = null;
    
}//end clearVars()

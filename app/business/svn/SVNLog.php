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
namespace app\business\svn;

/**
 * Used classes.
 */
use xsgaphp\XsgaAbstractClass;

/**
 * SVNLog class.
 */
class SVNLog extends XsgaAbstractClass
{
    
    /**
     * Log entries.
     * 
     * @var SVNLogEntry[]
     * 
     * @access public
     */
    public $entries;
    
    /**
     * Current log entry.
     * 
     * @var SVNLogEntry
     * 
     * @access public
     */
    public $curEntry;
    
    /**
     * Path.
     * 
     * @var string
     * 
     * @access public
     */
    public $path = '';
    
    
    /**
     * Return the entry for a given revision.
     * 
     * @param integer $rev Revision.
     * 
     * @return integer
     * 
     * @access public
     */
    public function findEntry($rev)
    {
        foreach ($this->entries as $index => $entry) {
            if ($entry->rev == $rev) {
                return $index;
            }//end if
        }//end foresch
        
    }//end findEntry()
    
    
}//end SVNLog class.

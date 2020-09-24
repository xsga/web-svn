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
 * Set directory timestamp.
 * 
 * @param string  $dir
 * @param integer $timestamp
 * 
 * @return void
 */
function setDirectoryTimestamp($dir, $timestamp)
{
    
    touch($dir, $timestamp);
    
    if (is_dir($dir)) {
        
        // Set timestamp for all contents, recursing into subdirectories
        $handle = opendir($dir);
        
        if ($handle) {
            
            while (($file = readdir($handle)) !== false) {
                
                if ($file === '.' || $file === '..') {
                    continue;
                }//end if
                
                $f = $dir.DIRECTORY_SEPARATOR.$file;
                
                if (is_dir($f)) {
                    setDirectoryTimestamp($f, $timestamp);
                }//end if
                
            }//end if
            
            closedir($handle);
            
        }//end if
        
    }//end if
    
}//end setDirectoryTimestamp()


/**
 * Remove directory.
 * 
 * @param string $dir
 * 
 * @return boolean
 */
function removeDirectory($dir)
{
    if (is_dir($dir)) {
        
        $dir    = rtrim($dir, '/');
        $handle = dir($dir);
        
        while (($file = $handle->read()) !== false) {
            
            if ($file == '.' || $file == '..') {
                continue;
            }//end if
            
            $f = $dir.DIRECTORY_SEPARATOR.$file;
            
            if (!is_link($f) && is_dir($f)) {
                removeDirectory($f);
            } else {
                @unlink($f);
            }//end if
            
        }//end while
        
        $handle->close();
        @rmdir($dir);
        
        return true;
        
    }//end if
    
    return false;
    
}//end removeDirectory()

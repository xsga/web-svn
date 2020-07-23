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
use app\business\setup\Setup;
use app\business\setup\WebSvnCons;

/**
 * Remove URL separator.
 * 
 * @param string $url
 *
 * @return string
 */
function removeURLSeparator($url)
{
    return preg_replace('#(\?|&(amp;)?)$#', '', $url);
    
}//end removeURLSeparator()


/**
 * URL for path.
 * 
 * @param string $fullpath
 * @param Setup  $setup
 * 
 * @return string
 */
function urlForPath($fullpath, Setup $setup)
{
    
    $isDir = $fullpath{strlen($fullpath) - 1} === '/';
    
    if ($isDir) {
        
        if ($setup->config->treeView) {
            
            $id  = $setup->utils->anchorForPath($fullpath, $setup->config->treeView);
            $url = $setup->config->getURL($setup->rep, $fullpath, 'dir').$setup->passRevString.'#'.$id.'" id="'.$id;
            
        } else {
            
            $url = $setup->config->getURL($setup->rep, $fullpath, 'dir').$setup->passRevString;
            
        }//end if
        
    } else {
        
        $url = $setup->config->getURL($setup->rep, $fullpath, 'file').$setup->passRevString;
        
    }//end if
    
    return removeURLSeparator($url);
    
}//end urlForPath()


/**
 * Show directory files.
 * 
 * @param array   $subs
 * @param integer $level
 * @param integer $limit
 * @param integer $index
 * @param Setup   $setup
 *
 * @return array
 */
function showDirFiles(array $subs, $level, $limit, $index, Setup $setup)
{
    
    $path = '';
    
    if (!$setup->config->treeView) {
        $level = $limit;
    }//end if
    
    // TODO: Fix node links to use the path and number of peg revision (if exists).
    // This applies to file detail, and log -- leave the download link as-is.
    for ($n = 0; $n <= $level; $n++) {
        $path .= $subs[$n].'/';
    }//end for
    
    // List each file in the current directory.
    $loop            = 0;
    $last_index      = 0;
    $accessToThisDir = $setup->rep->hasReadAccess($path, false);
    
    // If using flat view and not at the root, create a '..' entry at the top.
    if (!$setup->config->treeView && count($subs) > 2) {
        
        $parentPath = $subs;
        
        unset($parentPath[count($parentPath) - 2]);
        
        $parentPath = implode('/', $parentPath);
        
        if ($setup->rep->hasReadAccess($parentPath, false)) {
            
            $setup->listing[$index]['rowparity'] = $index % 2;
            $setup->listing[$index]['path'] = $parentPath;
            $setup->listing[$index]['filetype'] = 'dir';
            $setup->listing[$index]['filename'] = '..';
            $setup->listing[$index]['fileurl'] = urlForPath($parentPath, $setup);
            $setup->listing[$index]['filelink'] = '<a href="'.$setup->listing[$index]['fileurl'].'">'.$setup->listing[$index]['filename'].'</a>';
            $setup->listing[$index]['level'] = 0;
            $setup->listing[$index]['node'] = 0; // t-node
            $setup->listing[$index]['revision'] = $setup->rev;
            $setup->listing[$index]['revurl'] = $setup->config->getURL($setup->rep, $parentPath, 'revision').'rev='.$setup->rev.WebSvnCons::ANDAMP.'isdir=1';
            $setup->listing[$index]['date'] = $setup->vars['date'];
            $setup->listing[$index]['age'] = $setup->utils->datetimeFormatDuration($setup->lang, time() - strtotime($setup->vars['date']), true, true);
            
            $index++;
            
        }//end if
        
    }//end if
    
    $openDir = false;
    $logList = $setup->svnrep->getList($path, $setup->rev, $setup->peg);
    
    if ($logList) {
        
        $downloadRevAndPeg = $setup->utils->createRevAndPegString($setup->rev, $setup->peg ? $setup->peg : $setup->rev);
        
        foreach ($logList->entries as $entry) {
            
            $isDir = $entry->isdir;
            
            if (!$isDir && $level !== $limit) {
                continue; // Skip any files outside the current directory
            }//end if
            
            $file = $entry->file;
            $isDirString = ($isDir) ? 'isdir=1'.WebSvnCons::ANDAMP : '';
            
            // Only list files/directories that are not designated as off-limits.
            $access = ($isDir) ? $setup->rep->hasReadAccess($path.$file, true) : $accessToThisDir;
            
            if ($access) {
                
                $setup->listing[$index]['rowparity'] = $index % 2;
                
                if ($isDir) {
                    $setup->listing[$index]['filetype'] = ($openDir) ? 'diropen' : 'dir';
                    $openDir = isset($subs[$level + 1]) && (!strcmp($subs[$level + 1].'/', $file) || !strcmp($subs[$level + 1], $file));
                } else {
                    $setup->listing[$index]['filetype'] = strtolower(strrchr($file, '.'));
                    $openDir = false;
                }//end if
                
                $setup->listing[$index]['isDir'] = $isDir;
                $setup->listing[$index]['openDir'] = $openDir;
                $setup->listing[$index]['level'] = ($setup->config->treeView) ? $level : 0;
                $setup->listing[$index]['node'] = 0; // t-node
                $setup->listing[$index]['path'] = $path.$file;
                $setup->listing[$index]['filename'] = $file;
                
                if ($isDir) {
                    $setup->listing[$index]['fileurl'] = urlForPath($path.$file, $setup);
                } else {
                    $setup->passRevString = $setup->utils->createDifferentRevAndPegString($setup->passrev, $setup->peg);
                    $setup->listing[$index]['fileurl'] = urlForPath($path.$file, $setup);
                }//end if
                
                $setup->listing[$index]['filelink'] = '<a href="'.$setup->listing[$index]['fileurl'].'">'.$setup->listing[$index]['filename'].'</a>';
                
                if ($isDir) {
                    $setup->listing[$index]['logurl'] = $setup->config->getURL($setup->rep, $path.$file, 'log').$isDirString.$setup->passRevString;
                } else {
                    $setup->listing[$index]['logurl'] = $setup->config->getURL($setup->rep, $path.$file, 'log').$isDirString.$setup->utils->createDifferentRevAndPegString($setup->passrev, $setup->peg);
                }//end if
                
                if ($setup->config->treeView) {
                    $setup->listing[$index]['compare_box'] = '<input type="checkbox" name="compare[]" value="'.$path.$file.'@'.$setup->passrev.'" onclick="checkCB(this)" />';
                }//end if
                
                if ($setup->config->showLastModInListing()) {
                    
                    $setup->listing[$index]['committime'] = $entry->committime;
                    $setup->listing[$index]['revision'] = $entry->rev;
                    $setup->listing[$index]['author'] = $entry->author;
                    $setup->listing[$index]['age'] = $entry->age;
                    $setup->listing[$index]['date'] = $entry->date;
                    $setup->listing[$index]['revurl'] = $setup->config->getURL($setup->rep, $path.$file, 'revision').$isDirString.$setup->utils->createRevAndPegString($entry->rev, $setup->peg ? $setup->peg : $setup->rev);
                
                }//end if
                
                if ($setup->rep->isDownloadAllowed($path.$file)) {
                    
                    $downloadurl = $setup->config->getURL($setup->rep, $path.$file, 'dl').$isDirString.$downloadRevAndPeg;
                    
                    if ($isDir) {
                        $setup->listing[$index]['downloadurl'] = $downloadurl;
                        $setup->listing[$index]['downloadplainurl'] = '';
                    } else {
                        $setup->listing[$index]['downloadplainurl'] = $downloadurl;
                        $setup->listing[$index]['downloadurl'] = '';
                    }//end if
                    
                } else {
                    
                    $setup->listing[$index]['downloadplainurl'] = '';
                    $setup->listing[$index]['downloadurl'] = '';
                    
                }//end if
                
                $loop++;
                $index++;
                $last_index = $index;
                
                if ($isDir && ($level != $limit) &&
                   (isset($subs[$level + 1]) && (!strcmp($subs[$level + 1].'/', $file) || 
                   !strcmp(htmlentities($subs[$level + 1], ENT_QUOTES).'/', htmlentities($file))))) {
                       
                    $setup->listing = showDirFiles($subs, $level + 1, $limit, $index, $setup);
                    $index = count($setup->listing);
                    
                }//end if
            }//end if
            
        }//end foreach
        
    }//end if
    
    // For an expanded tree, give the last entry an "L" node to close the grouping.
    if ($setup->config->treeView && $last_index !== 0) {
        $setup->listing[$last_index - 1]['node'] = 1; // l-node
    }//end if
    
    return $setup->listing;
    
}//end showDirFiles()


/**
 * Show tree directory.
 * 
 * @param Setup $setup
 *
 * @return number
 */
function showTreeDir(Setup $setup)
{
    
    $subs = explode('/', $setup->path);
    
    // For directory, the last element in the subs is empty.
    // For file, the last element in the subs is the file name.
    // Therefore, it is always count($subs) - 2
    $limit = count($subs) - 2;
    
    for ($n = 0; $n < $limit; $n++) {
        $setup->vars['last_i_node'][$n] = false;
    }//end for
    
    $setup->vars['compare_box'] = ''; // Set blank once in case tree view is not enabled.
    
    return showDirFiles($subs, 0, $limit, 0, $setup);
    
}//end showTreeDir()

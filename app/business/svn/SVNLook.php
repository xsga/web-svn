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
use app\business\setup\Setup;
use xsgaphp\XsgaAbstractClass;

/**
 * SVNLook class.
 */
class SVNLook extends XsgaAbstractClass
{
    
    /**
     * Current tag.
     * 
     * @var string
     * 
     * @access public
     */
    public $curTag = '';
    
    /**
     * Current entry info.
     * 
     * @var SVNInfoEntry
     * 
     * @access public
     */
    public $curInfo = null;
    
    /**
     * Current list.
     * 
     * @var SVNList
     * 
     * @access public
     */
    public $curList = null;
    
    /**
     * Current log.
     * 
     * @var SVNLog
     * 
     * @access public
     */
    public $curLog = null;
    
    /**
     * Setup.
     * 
     * @var Setup
     * 
     * @access public
     */
    public $setup;
        
    
    /**
     * Constructor.
     * 
     * @param Setup $setup
     * 
     * @acces public
     */
    public function __construct(Setup $setup)
    {
        parent::__construct();
        
        $this->setup = $setup;
        
    }//end __construct()
    
    
    /**
     * SVN log entry.
     * 
     * @param SVNLogEntry $a
     * @param SVNLogEntry $b
     * 
     * @return number
     * 
     * @access public
     */
    public function SVNLogEntry_compare($a, $b)
    {
        return strnatcasecmp($a->path, $b->path);
        
    }//end SVNLogEntry()
    
    
    /**
     * Info start element.
     * 
     * @param resource $parser
     * @param string   $name
     * @param array    $attrs
     * 
     * @return void
     * 
     * @access public
     */
    public function infoStartElement($parser, $name, $attrs)
    {
        switch ($name) {
            
            case 'INFO':
                break;
                
            case 'ENTRY':
                
                if (count($attrs)) {
                    
                    while (list($k, $v) = each($attrs)) {
                        
                        if ($k === 'KIND') {
                            $this->curInfo->isdir = ($v == 'dir');
                        } else if ($k === 'REVISION'){
                            $this->curInfo->rev = $v;
                        }//end if
                        
                    }//end while
                    
                }//end if
                
                break;
                
            default:
                $this->curTag = $name;
                break;
                
        }//end switch
        
    }//end infoStartElement()
    
    
    /**
     * Info end element.
     * 
     * @param resource $parser
     * @param string   $name
     * 
     * @return void
     * 
     * @access public
     */
    public function infoEndElement($parser, $name)
    {
        
        if ($name === 'ENTRY' && $this->curInfo->isdir) {
            $this->curInfo->path .= '/';
        }//end switch
        
        $this->curTag = '';
        
    }//end infoEndElement()
    
    
    /**
     * Info character data.
     * 
     * @param resource $parser
     * @param string   $data
     * 
     * @return void
     * 
     * @access public
     */
    public function infoCharacterData($parser, $data)
    {
        
        if ($this->curTag === 'URL') {
            
            $this->curInfo->path = $data;
            
        } else if ($this->curTag === 'ROOT') {
            
            $this->curInfo->path = urldecode(substr($this->curInfo->path, strlen($data)));
            
        }//end if
        
    }//end infoCharacterData()
    
    
    /**
     * List start element.
     * 
     * @param resource $parser
     * @param string   $name
     * @param array    $attrs
     * 
     * @return void
     * 
     * @access public
     */
    public function listStartElement($parser, $name, array $attrs)
    {
        
        switch ($name) {
            
            case 'LIST':
                                
                if (count($attrs)) {
                    while (list($k, $v) = each($attrs)) {
                        if ($k === 'PATH') {
                            $this->curList->path = $v;
                        }//end if
                    }//end while
                }//end if
                
                break;
                
            case 'ENTRY':
                                
                $this->curList->curEntry = new SVNListEntry;
                
                if (count($attrs)) {
                    while (list($k, $v) = each($attrs)) {
                        if ($k === 'KIND') {
                            $this->curList->curEntry->isdir = ($v === 'dir');
                        }//end if
                    }//end while
                }//end if
                
                break;
                
            case 'COMMIT':
                                
                if (count($attrs)) {
                    while (list($k, $v) = each($attrs)) {
                        if ($k === 'REVISION') {
                            $this->curList->curEntry->rev = $v;
                        }//end if
                    }//end while
                }//end if
                break;
                
            default:
                $this->curTag = $name;
                break;
                
        }//end switch
        
    }//end listStartElement()
    
    
    /**
     * List character data.
     * 
     * @param resource $parser
     * @param string   $data
     * 
     * @return void
     * 
     * @access public
     */
    public function listCharacterData($parser, $data)
    {
        
        switch ($this->curTag) {
            
            case 'NAME':
                
                if ($data === false || $data === '') {
                    return;
                }//end if
                
                $this->curList->curEntry->file .= $data;
                
                break;
                
            case 'AUTHOR':
                
                if ($data === false || $data === '') {
                    return;
                }//end if
                
                $data = mb_convert_encoding($data, 'UTF-8', mb_detect_encoding($data));
                    
                $this->curList->curEntry->author .= $data;
                
                break;
                    
            case 'DATE':
                
                $data = trim($data);
                
                if ($data === false || $data === '') {
                    return;
                }//end if
                
                $committime = $this->setup->utils->parseSvnTimestamp($data);
                
                $this->curList->curEntry->committime = $committime;
                $this->curList->curEntry->date       = strftime('%Y-%m-%d %H:%M:%S', $committime);
                $this->curList->curEntry->age        = $this->setup->utils->datetimeFormatDuration($this->setup->lang, max(time() - $committime, 0), true, true);
                
                break;
                
        }//end switch
        
    }//end listCharacterData()
    
    
    /**
     * List end element.
     * 
     * @param resource $parser
     * @param string   $name
     * 
     * @return void
     * 
     * @access public
     */
    public function listEndElement($parser, $name)
    {
        
        if ($name === 'ENTRY') {
            
            if ($this->curList->curEntry->isdir) {
                $this->curList->curEntry->file .= '/';
            }//end if
                
            $this->curList->entries[] = $this->curList->curEntry;
            $this->curList->curEntry  = null;
                
        }//end if
        
        $this->curTag = '';
        
    }//end listEndElement()
    
    
    /**
     * Log start element.
     * 
     * @param resource $parser
     * @param string   $name
     * @param array    $attrs
     * 
     * @return void
     * 
     * @access public
     */
    public function logStartElement($parser, $name, array $attrs)
    {
        
        switch ($name) {
            
            case 'LOGENTRY':
                
                $this->curLog->curEntry       = new SVNLogEntry;
                $this->curLog->curEntry->mods = array();
                $this->curLog->curEntry->path = $this->curLog->path;
                
                if (count($attrs)) {
                    while (list($k, $v) = each($attrs)) {
                        if ($k === 'REVISION') {
                            $this->curLog->curEntry->rev = $v;
                        }//end if
                    }//end while
                }//end if
                
                break;
                
            case 'PATH':
                
                $this->curLog->curEntry->curMod = new SVNMod;
                
                if (count($attrs)) {
                    
                    while (list($k, $v) = each($attrs)) {
                        
                        switch ($k) {
                            case 'ACTION':
                                $this->curLog->curEntry->curMod->action = $v;
                                break;
                                
                            case 'COPYFROM-PATH':
                                $this->curLog->curEntry->curMod->copyfrom = $v;
                                break;
                                
                            case 'COPYFROM-REV':
                                $this->curLog->curEntry->curMod->copyrev = $v;
                                break;
                                
                            case 'KIND':
                                $this->curLog->curEntry->curMod->isdir = ($v == 'dir');
                                break;
                                
                        }//end switch
                        
                    }//end while
                    
                }//end if
                
                $this->curTag = $name;
                break;
                
            default:
                $this->curTag = $name;
                break;
                
        }//end switch
        
    }//end logStartElement()
    
    
    /**
     * Log end element.
     * 
     * @param resource $parser
     * @param string   $name
     */
    public function logEndElement($parser, $name)
    {
        
        switch ($name) {
            case 'LOGENTRY':
                $this->curLog->entries[] = $this->curLog->curEntry;
                break;
                
            case 'PATH':
                $this->curLog->curEntry->mods[] = $this->curLog->curEntry->curMod;
                break;
                
            case 'MSG':
                $this->curLog->curEntry->msg = trim($this->curLog->curEntry->msg);
                break;
        }//end switch
        
        $this->curTag = '';
        
    }//end logEndElement()
    
    
    /**
     * Log character data.
     * 
     * @param resource $parser
     * @param string   $data
     * 
     * @return void
     * 
     * @access public
     */
    public function logCharacterData($parser, $data)
    {
        
        switch ($this->curTag) {
            
            case 'AUTHOR':
                
                if ($data === false || $data === '') {
                    return;
                }//end if
                
                $data = mb_convert_encoding($data, 'UTF-8', mb_detect_encoding($data));
                    
                $this->curLog->curEntry->author .= $data;
                
                break;
                    
            case 'DATE':
                
                $data = trim($data);
                
                if ($data === false || $data === '') {
                    return;
                }//end if
                
                $committime = $this->setup->utils->parseSvnTimestamp($data);
                
                $this->curLog->curEntry->committime = $committime;
                $this->curLog->curEntry->date       = strftime('%Y-%m-%d %H:%M:%S', $committime);
                $this->curLog->curEntry->age        = $this->setup->utils->datetimeFormatDuration($this->setup->lang, max(time() - $committime, 0), true, true);
                
                break;
                
            case 'MSG':
                
                $data = mb_convert_encoding($data, 'UTF-8', mb_detect_encoding($data));
                    
                $this->curLog->curEntry->msg .= $data;
                
                break;
                
            case 'PATH':
                
                $data = trim($data);
                
                if ($data === false || $data === '') {
                    return;
                }//end if
                
                $this->curLog->curEntry->curMod->path .= $data;
                
                // The XML returned when a file is renamed/branched in inconsistent.
                // In the case of a branch, the path doesn't include the leafname.
                // In the case of a rename, it does.    Ludicrous.
                if (!empty($this->curLog->path)) {
                    
                    $pos           = strrpos($this->curLog->path, '/');
                    $this->curpath = substr($this->curLog->path, 0, $pos);
                    $leafname      = substr($this->curLog->path, $pos + 1);
                    
                } else {
                    
                    $this->curpath = '';
                    $leafname      = '';
                    
                }//end if
                
                $this->curMod = $this->curLog->curEntry->curMod;
                
                if ($this->curMod->action === 'A') {
                    
                    if ($data === $this->curLog->path) {
                        
                        // For directories and renames
                        $this->curLog->path = $this->curMod->copyfrom;
                    
                    } else if ($data === $this->curpath || $data === $this->curpath.'/') {
                        
                        // Logs of files that have moved due to branching
                        $this->curLog->path = $this->curMod->copyfrom.'/'.$leafname;
                        
                    } else {
                        
                        $this->curLog->path = str_replace($this->curMod->path, $this->curMod->copyfrom, $this->curLog->path);
                        
                    }//end if
                    
                }//end if
                
                break;
                
        }//end switch
        
    }//end logCharacterData()
    
    
    /**
     * Top level.
     * 
     * @param string $entry
     * 
     * @return boolean
     * 
     * @access public
     */
    public function _topLevel($entry)
    {
        // To be at top level, there must be one space before the entry
        return (strlen($entry) > 1 && $entry{0} === ' ' && $entry{1} !== ' ');
        
    }//end _topLevel()
    
    
    /**
     * Function to sort two given directory entries.
     * Directories go at the top if config option alphabetic is not set.
     * 
     * @param SVNListEntry $e1
     * @param SVNListEntry $e2
     * 
     * @return number
     * 
     * @access public
     */
    public function _listSort($e1, $e2)
    {
        
        $file1 = $e1->file;
        $file2 = $e2->file;
        
        $isDir1 = ($file1{strlen($file1) - 1} == '/');
        $isDir2 = ($file2{strlen($file2) - 1} == '/');
        
        if (!$this->setup->config->isAlphabeticOrder()) {
            
            if ($isDir1 && !$isDir2) {
                return -1;
            }//end if
            
            if ($isDir2 && !$isDir1) {
                return 1;
            }//end if
            
        }//end if
        
        if ($isDir1) {
            $file1 = substr($file1, 0, -1);
        }//end if
        
        if ($isDir2) {
            $file2 = substr($file2, 0, -1);
        }//end if
        
        return strnatcasecmp($file1, $file2);
        
    }//end _listSort()
    
    
    /**
     * Encode path.
     * 
     * @param string $uri
     * 
     * @return string
     * 
     * @access public
     */
    public function encodePath($uri) {
        
        $uri = str_replace(DIRECTORY_SEPARATOR, '/', $uri);
        
        $uri = mb_convert_encoding($uri, 'UTF-8', mb_detect_encoding($uri));
        
        $parts      = explode('/', $uri);
        $partscount = count($parts);
        
        for ($i = 0; $i < $partscount; $i++) {
            // Do not urlencode the 'svn+ssh://' part.
            if ($i != 0 || $parts[$i] !== 'svn+ssh:') {
                $parts[$i] = rawurlencode($parts[$i]);
            }//end if
        }//end for
        
        $uri = implode('/', $parts);
        
        // Quick hack. Subversion seems to have a bug surrounding the use of %3A instead of :
        $uri = str_replace('%3A', ':', $uri);
        
        // Correct for Window share names.
        if ($this->setup->config->serverIsWindows) {
            
            if (substr($uri, 0, 2) === '//') {
                $uri = '\\'.substr($uri, 2, strlen($uri));
            }//end if
            
            if (substr($uri, 0, 10) === 'file://///' ) {
                $uri = 'file:///\\'.substr($uri, 10, strlen($uri));
            }//end if
            
        }//end if
        
        return $uri;
        
    }//end encodePath()
    
    
    /**
     * Equal part.
     * 
     * @param string $str1
     * @param string $str2
     * 
     * @return string
     * 
     * @access public
     */
    public function _equalPart($str1, $str2)
    {
        
        $len1 = strlen($str1);
        $len2 = strlen($str2);
        
        $i = 0;
        while ($i < $len1 && $i < $len2) {
            if (strcmp($str1{$i}, $str2{$i}) != 0) {
                break;
            }//end if
            $i++;
        }//end while
        
        if ($i === 0) {
            return '';
        }//end if
        
        return substr($str1, 0, $i);
        
    }//end _equalPart
    
    
}//end SVNLook class

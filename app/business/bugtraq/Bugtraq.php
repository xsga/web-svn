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
namespace app\business\bugtraq;

/**
 * Used classes.
 */
use app\business\repository\Repository;
use app\business\svn\SVNRepository;
use xsgaphp\XsgaAbstractClass;

/**
 * Class Bugtraq.
 */
class Bugtraq extends XsgaAbstractClass
{
    
    /**
     * Message string.
     * 
     * @var string
     * 
     * @access public
     */
    public $msgstring;
    
    /**
     * URL string.
     * 
     * @var string
     * 
     * @access public
     */
    public $urlstring;
    
    /**
     * Log regular expresion.
     * 
     * @var string
     * 
     * @access public
     */
    public $logregex;
    
    /**
     * Append flag.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $append;
    
    /**
     * First part.
     * 
     * @var string
     * 
     * @access public
     */
    public $firstPart;
    
    /**
     * First part length.
     * 
     * @var integer
     * 
     * @access public
     */
    public $firstPartLen;
    
    /**
     * Last part.
     * 
     * @var string
     * 
     * @access public
     */
    public $lastPart;
    
    /**
     * Last part length.
     * 
     * @var integer
     * 
     * @access public
     */
    public $lastPartLen;
    
    /**
     * Properties founded.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $propsfound = false;
    
    /**
     * Bugstract message.
     * 
     * @var string
     * 
     * @access public
     */
    const BG_MESSAGE = 'bugtraq:message';
    
    /**
     * Bugstract logregex.
     *
     * @var string
     *
     * @access public
     */
    const BG_LOGREGEX = 'bugtraq:logregex';
    
    /**
     * Bugstract URL.
     *
     * @var string
     *
     * @access public
     */
    const BG_URL = 'bugtraq:url';
    
    /**
     * Bugstract append.
     *
     * @var string
     *
     * @access public
     */
    const BG_APPEND = 'bugtraq:append';
    
    /**
     * Bugstraq search pattern.
     * 
     * @var string
     * 
     * @access public
     */
    const BG_SEARCH = '%BUGID%';
    
    
    /**
     * Constructor.
     * 
     * @param Repository    $rep    Repository class instance.
     * @param SVNRepository $svnrep SVNRepository class instance.
     * @param string        $path   Path.
     * 
     * @access public
     */
    public function __construct(Repository $rep, SVNRepository $svnrep, $path)
    {
        parent::__construct();
        
        if ($rep->isBugtraqEnabled()) {
            
            $enoughdata = false;
            
            if (($properties = $rep->getBugtraqProperties()) !== null) {
                
                $this->msgstring = $properties[self::BG_MESSAGE];
                $this->logregex  = $properties[self::BG_LOGREGEX];
                $this->urlstring = $properties[self::BG_URL];
                $this->append    = $properties[self::BG_APPEND];
                
                $enoughdata = true;
                
            } else {
                
                $pos    = strrpos($path, '/');
                $parent = substr($path, 0, $pos + 1);
                
                $this->append = true;
                
                while (!$enoughdata && (strpos($parent, '/') !== false)) {
                    
                    $this->setProperties($svnrep->getProperties($parent), $parent, $svnrep);
                    
                    // Remove the trailing slash.
                    $parent = substr($parent, 0, -1);
                    
                    // Find the last trailing slash.
                    $pos = strrpos($parent, '/');
                    
                    // Find the previous parent directory.
                    $parent = substr($parent, 0, $pos + 1);
                    
                    $enoughdata = ((!empty($this->msgstring) || !empty($this->logregex)) && !empty($this->urlstring));
                    
                }//end while
                
            }//end if
            
            $this->msgstring = trim(@$this->msgstring);
            $this->urlstring = trim(@$this->urlstring);
            
            if ($enoughdata && !empty($this->msgstring)) {
                $this->initPartInfo();
            }//end if
            
            if ($enoughdata) {
                $this->propsfound = true;
            }//end if
            
        }//end if
        
    }//end __construct()
    
    
    /**
     * Set properties.
     * 
     * @param array         $properties
     * @param string        $parent
     * @param SVNRepository $svnrep
     * 
     * @return void
     * 
     * @access private
     */
    private function setProperties(array $properties, $parent, SVNRepository $svnrep)
    {
        if (empty($this->msgstring) && in_array(self::BG_MESSAGE, $properties)) {
            $this->msgstring = $svnrep->getProperty($parent, self::BG_MESSAGE);
        }//end if
        
        if (empty($this->logregex) && in_array(self::BG_LOGREGEX, $properties)) {
            $this->logregex = $svnrep->getProperty($parent, self::BG_LOGREGEX);
        }//end if
        
        if (empty($this->urlstring) && in_array(self::BG_URL, $properties)) {
            $this->urlstring = $svnrep->getProperty($parent, self::BG_URL);
        }//end if
        
        if (in_array(self::BG_APPEND, $properties) && $svnrep->getProperty($parent, self::BG_APPEND) === 'false') {
            $this->append = false;
        }//end if
        
    }//end setMsgstring()
    
    
    /**
     * Init part info.
     * 
     * @return void
     * 
     * @access public
     */
    public function initPartInfo()
    {
        
        if (($bugidpos = strpos($this->msgstring, self::BG_SEARCH)) !== false && strpos($this->urlstring, self::BG_SEARCH) !== false) {
            
            // Get the textual parts of the message string for comparison purposes.
            $this->firstPart    = substr($this->msgstring, 0, $bugidpos);
            $this->firstPartLen = strlen($this->firstPart);
            $this->lastPart     = substr($this->msgstring, $bugidpos + 7);
            $this->lastPartLen  = strlen($this->lastPart);
            
        }//end if
        
    }//end initPartInfo()
    
    
    /**
     * Replace ID's.
     * 
     * @param string $message Message.
     * 
     * @return unknown|string
     */
    public function replaceIDs($message)
    {
        
        if (!$this->propsfound) {
            return $message;
        }//end if
        
        $href = '<a href="';
        
        // First we search for the message string.
        $logmsg  = '';
        $message = rtrim($message);
        
        // Set offset.
        $offset = strrpos($message, "\n");
        
        if ($this->append) {
            
            // Just compare the last line.
            if ($offset !== false) {
                $logmsg  = substr($message, 0, $offset + 1);
                $bugLine = substr($message, $offset + 1);
            } else {
                $bugLine = $message;
            }//end if
            
        } else {
            
            if ($offset !== false) {
                $bugLine = substr($message, 0, $offset);
                $logmsg  = substr($message, $offset);
            } else {
                $bugLine = $message;
            }//end if
            
        }//end if
        
        // Make sure that our line really is an issue tracker message.
        if (isset($this->firstPart) && isset($this->lastPart) 
                && (strncmp($bugLine, $this->firstPart, $this->firstPartLen) === 0) 
                && strcmp(substr($bugLine, -$this->lastPartLen, $this->lastPartLen), $this->lastPart) === 0) {
            
            // Get the issues list
            if ($this->lastPartLen > 0) {
                $issues = substr($bugLine, $this->firstPartLen, -$this->lastPartLen);
            } else {
                $issues = substr($bugLine, $this->firstPartLen);
            }//end if
            
            // Add each reference to the first part of the line.
            $line = $this->firstPart;
            
            while ($pos = strpos($issues, ',')) {
                
                $issue  = trim(substr($issues, 0, $pos));
                $issues = substr($issues, $pos + 1);
                
                $line .= $href.str_replace(self::BG_SEARCH, $issue, $this->urlstring).'">'.$issue.'</a>, ';
                
            }//end while
            
            $line .= $href.str_replace(self::BG_SEARCH, trim($issues), $this->urlstring).'">'.trim($issues).'</a>'.$this->lastPart;
            
            if ($this->append) {
                $message = $logmsg.$line;
            } else {
                $message = $line.$logmsg;
            }//end if
            
        }//end if
        
        // Now replace all other instances of bug IDs that match the regex.
        if ($this->logregex) {
            
            $message      = rtrim($message);
            $line         = '';
            $lines        = explode("\n", $this->logregex);
            $regex_all    = '~'.$lines[0].'~';
            $regex_single = @$lines[1];
            
            if (empty($regex_single)) {
                // If the property only contains one line, then the pattern is only designed
                // to find one issue number at a time.    e.g. [Ii]ssue #?(\d+).    In this case
                // we need to replace the matched issue ID with the link.
                if ($numMatches = preg_match_all($regex_all, $message, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
                    
                    $addedOffset = 0;
                    
                    for ($match = 0; $match < $numMatches; $match++) {
                        
                        $issue        = $matches[$match][1][0];
                        $issueOffset  = $matches[$match][1][1];
                        $issueLink    = $href.str_replace(self::BG_SEARCH, $issue, $this->urlstring).'">'.$issue.'</a>';
                        $message      = substr_replace($message, $issueLink, $issueOffset + $addedOffset, strlen($issue));
                        $addedOffset += strlen($issueLink) - strlen($issue);
                        
                    }//end for
                    
                }//end if
                
            } else {
                // It the property contains two lines, then the first is a pattern for extracting
                // multiple issue numbers, and the second is a pattern extracting each issue
                // number from the multiple match.    e.g. [Ii]ssue #?(\d+)(,? ?#?(\d+))+ and (\d+)
                while (preg_match($regex_all, $message, $matches, PREG_OFFSET_CAPTURE)) {
                    
                    $completeMatch       = $matches[0][0];
                    $completeMatchOffset = $matches[0][1];
                    $replacement         = $completeMatch;
                    
                    if ($numMatches = preg_match_all('~'.$regex_single.'~', $replacement, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
                        
                        $addedOffset = 0;
                        
                        for ($match = 0; $match < $numMatches; $match++) {
                            $issue        = $matches[$match][1][0];
                            $issueOffset  = $matches[$match][1][1];
                            $issueLink    = $href.str_replace(self::BG_SEARCH, $issue, $this->urlstring).'">'.$issue.'</a>';
                            $replacement  = substr_replace($replacement, $issueLink, $issueOffset + $addedOffset, strlen($issue));
                            $addedOffset += strlen($issueLink) - strlen($issue);
                        }//end for
                        
                    }//end if
                    
                    $message = substr_replace($message, $replacement, $completeMatchOffset, strlen($completeMatch));
                    
                }//end while
                
            }//end if
            
        }//end if
        
        return $message;
        
    }//end replaceIDs()
    
    
}//end Bugtraq class


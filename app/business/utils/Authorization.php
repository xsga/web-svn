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
namespace app\business\utils;

/**
 * Used classes.
 */
use app\business\repository\Repository;
use xsgaphp\XsgaAbstractClass;

/**
 * ParentPath class.
 */
class Authorization extends XsgaAbstractClass
{
    
    /**
     * Access cache.
     * 
     * @var array
     * 
     * @access public
     */
    public $accessCache	= array();
    
    /**
     * Access file.
     * 
     * @var string
     * 
     * @access public
     */
    public $accessFile = null;
    
    /**
     * User.
     * 
     * @var string
     * 
     * @access public
     */
    public $user = null;
    
    /**
     * Autz command.
     * 
     * @var string
     * 
     * @access public
     */
    public $authzCommand;
    
    
    /**
     * 
     */
    function __construct($autzCommand)
    {
        
        // Executes parent constructor.
        parent::__construct();
        
        $this->setUsername();
        
        $this->authzCommand = $autzCommand;
        
    }//end __construct()
    
    
    /**
     * Has username.
     * 
     * @return boolean
     * 
     * @access public
     */
    function hasUsername()
    {
        return $this->user !== null;
        
    }//end hasUsername()
    
    
    /**
     * Add access file.
     * 
     * @param string $accessFile
     * 
     * @return void
     * 
     * @access public
     */
    public function addAccessFile($accessFile)
    {
        $this->accessFile = $accessFile;
        
    }//end addAccessFile()
    
    
    /**
     * Set the username from the current http session.
     * 
     * @return void
     * 
     * @access public
     */
    public function setUsername()
    {
        if (isset($_SERVER['REMOTE_USER'])) {
            $this->user = $_SERVER['REMOTE_USER'];
        } else if (isset($_SERVER['REDIRECT_REMOTE_USER'])) {
            $this->user = $_SERVER['REDIRECT_REMOTE_USER'];
        } else if (isset($_SERVER['PHP_AUTH_USER'])) {
            $this->user = $_SERVER['PHP_AUTH_USER'];
        }//end if
        
    }//end setUsername()
    
    
    /**
     * Private function to simplify creation of common SVN authz command string text.
     * 
     * @param string $repo
     * @param string $path
     * @param string $checkSubDirs
     * 
     * @return string
     * 
     * @access private
     */
    private function svnAuthzCommandString($repo, $path, $checkSubDirs = false)
    {
        
        //$cmd         = $this->setup->config->getSvnAuthzCommand();
        $cmd = $this->authzCommand;
        $repoAndPath = '--repository '.quote($repo).' --path '.quote($path);
        $username    = !$this->hasUsername() ? '' : '--username '.quote($this->user);
        $subDirs     = !$checkSubDirs ? '' : '-R';
        $authzFile   = quote($this->accessFile);
        
        return "${cmd} ${repoAndPath} ${username} ${subDirs} ${authzFile}";
        
    }//end svnAuthzCommandString
    
    
    /**
     * Returns true if the user has read access to the given path.
     * 
     * @param string  $repos
     * @param string  $path
     * @param boolean $checkSubDirs
     * 
     * @return boolean
     * 
     * @access public
     */
    public function hasReadAccess($repos, $path, $checkSubDirs = false)
    {
        
        if ($this->accessFile == null) {
            return false;
        }//end if
        
        if ($path === '' || $path[0] !== '/') {
            $path = '/'.$path;
        }//end if
        
        $cmd    = $this->svnAuthzCommandString($repos, $path, $checkSubDirs);
        $result = 'no';
        
        // Access checks might be issued multiple times for the same repos and paths within one and
        // the same request, introducing a lot of overhead because of "svnauthz" especially with
        // many repos under Windows. The easiest way to somewhat optimize it for different scenarios
        // is using a cache.
        $cache         =& $this->accessCache;
        $cached        = isset($cache[$cmd]) ? $cache[$cmd] : null;
        $cachedWhen    = isset($cached) ? $cached['when'] : 0;
        $cachedExpired = (time() - 60) > $cachedWhen;
        
        if ($cachedExpired) {
            // Sorting by "when" should be established somehow to only remove the oldest element
            // instead of an arbitrary first one, which might be the newest added last time.
            if (count($cache) >= 1000) {
                array_shift($cache);
            }//end if
            
            $result      = runCommand($cmd)[0];
            $cache[$cmd] = array('when' => time(), 'result' => $result);
            
        } else {
            $result = $cached['result'];
        }//end if
        
        return $result !== 'no';
        
    }//end hasReadAccess()
    
    
    /**
     * Returns true if the user has read access to the given path and too all subdirectories.
     * 
     * @param string $repos
     * @param string $path
     * 
     * @return boolean
     * 
     * @access public
     */
    public function hasUnrestrictedReadAccess($repos, $path)
    {
        
        return $this->hasReadAccess($repos, $path, true);
    
    }//end hasUnrestrictedReadAccess()
    
    
}//end Authorization class

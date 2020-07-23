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
use xsgaphp\XsgaAbstractClass;

/**
 * Constants.
 */
define('UNDEFINED', 0);
define('ALLOW', 1);
define('DENY', 2);

/**
 * Authentication class.
 */
class Authentication extends XsgaAbstractClass
{
    
    /**
     * Rights.
     * 
     * @var IniFile
     * 
     * @access public
     */
    public $rights;
    
    /**
     * User
     * 
     * @var string
     * 
     * @access public
     */
    public $user = null;
    
    /**
     * User groups.
     * 
     * @var array
     * 
     * @access public
     */
    public $usersGroups = array();
    
    /**
     * Basic realm.
     * 
     * @var string
     * 
     * @access public
     */
    public $basicRealm = 'WebSVN';
    
    
    /**
     * Constructor.
     * 
     * @access public
     */
    public function __construct($basicRealm = false)
    {
        parent::__construct();
        
        $this->rights = new IniFile();
        $this->setUsername();
        
        if ($basicRealm !== false) {
            $this->basicRealm = $basicRealm;
        }//end if
        
    }//end __construct()


    /**
     * Has username.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function hasUsername()
    {
        return $this->user !== null;
        
    }//end hasUsername()
    
    
    /**
     * Get basic realm.
     * 
     * @return string
     * 
     * @access public
     */
    public function getBasicRealm()
    {
        return $this->basicRealm;
        
    }//end getBasicRealm()
    
    
    /**
     * Add access file.
     * 
     * @return void
     * 
     * @access
     */
    public function addAccessFile($accessfile)
    {
        $this->rights->readIniFile($accessfile);
        $this->identifyGroups();
        
    }//end addAccessfile()


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
     * Checks to see which groups and aliases the user belongs to.
     * 
     * @return void
     * 
     * @access public
     */
    public function identifyGroups()
    {
        $this->usersGroups   = array();
        $this->usersGroups[] = '*';

        $aliases = $this->rights->getValues('aliases');
        
        if (is_array($aliases)) {
            foreach ($aliases as $alias => $user) {
                if ($user === strtolower($this->user)) {
                    $this->usersGroups[] = '&'.$alias;
                }//end if
            }//end foreach
        }//end if

        $groups = $this->rights->getValues('groups');
        
        if (is_array($groups)) {
            
            foreach ($groups as $group => $names) {
                
                if (empty($names)) {
                    continue;
                }//end if
                    
                if (in_array(strtolower($this->user), preg_split('/\s*,\s*/', $names))) {
                    $this->usersGroups[] = '@'.$group;
                }//end if

                foreach ($this->usersGroups as $users_group) {
                    if (in_array($users_group, preg_split('/\s*,\s*/', $names))) {
                        $this->usersGroups[] = '@'.$group;
                    }//end if
                }//end foreach
                
            }//end foreach
            
        }//end if
        
    }//end identifyGroups()
    
    
    /**
     * Check if the user is in the given list and return their read status if they are (UNDEFINED, ALLOW or DENY).
     * 
     * @param array  $accessors
     * @param string $user
     * 
     * @return string
     * 
     * @access public
     */
    public function inList(array $accessors, $user)
    {
        $output = UNDEFINED;
        
        foreach ($accessors as $key => $rights) {
            
            if (in_array($key, $this->usersGroups) || strcasecmp($key, $user) === 0) {
                
                if (strpos($rights, 'r') !== false) {
                    return ALLOW;
                } else {
                    $output = DENY;
                }//end if
                
            }//end if
            
        }//end foreach
        
        return $output;
        
    }//end inList()
    
    
    /**
     * Returns true if the user has read access to the given path.
     * 
     * @param string $repos
     * @param string $path
     * @param string $checkSubFolders
     * 
     * @return boolean
     * 
     * @access public
     */
    public function hasReadAccess($repos, $path, $checkSubFolders = false)
    {
        $access = UNDEFINED;
        $repos  = strtolower($repos);
        $path   = strtolower($path);
        
        if ($path === '' || $path{0} !== '/') {
            $path = '/'.$path;
        }//end if

        // If were told to, we should check sub folders of the path to see if there's a read access below this level.
        // This is used to display the folders needed to get to the folder to which read access is granted.
        if ($checkSubFolders) {
            
            $sections = $this->rights->getSections();

            foreach ($sections as $section => $accessers) {
                
                $qualified = $repos.':'.$path;
                $len       = strlen($qualified);
                
                if ($len < strlen($section) && strncmp($section, $qualified, $len) === 0) {
                    $access = $this->inList($accessers, $this->user);
                }//end if

                if ($access !== ALLOW) {
                    $len = strlen($path);
                    if ($len < strlen($section) && strncmp($section, $path, $len) === 0) {
                        $access = $this->inList($accessers, $this->user);
                    }//end if
                }//end if

                if ($access === ALLOW) {
                    break;
                }//end if
            
            }//end foreach
            
        }//end if

        // If we still don't have access, check each subpath of the path until we find an access level.
        if ($access !== ALLOW) {
            
            $access = UNDEFINED;

            if ($path !== '/' && substr($path, -1) === '/') {
                $path = substr($path, 0, -1);
            }//end if
            
            do {
                $accessers = $this->rights->getValues($repos.':'.$path);
                
                if (!empty($accessers)) {
                    $access = $this->inList($accessers, $this->user);
                }//end if
                
                if ($access === UNDEFINED) {
                    $accessers = $this->rights->getValues($path);
                    if (!empty($accessers)) {
                        $access = $this->inList($accessers, $this->user);
                    }//end if
                }//end if

                // If we've not got a match, remove the sub directory and start again.
                if ($access === UNDEFINED) {
                    if ($path === '/') {
                        break;
                    }//end if
                    $path = substr($path, 0, strrpos($path, '/'));
                    if ($path === '') {
                        $path = '/';
                    }//end if
                }//end if

            } while ($access === UNDEFINED && $path !== '');
            
        }//end if

        return $access === ALLOW;
        
    }//end hasReadAccess()
    
    
    /**
     * Returns true if the user has read access to the given path and too all subfolders.
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
        // First make sure that we have full read access at this level.
        if (!$this->hasReadAccess($repos, $path, false)) {
            return false;
        }//end if

        // Now check to see if there is a sub folder that's protected.
        $repos = strtolower($repos);
        $path  = strtolower($path);
        
        if ($path !== '/' && substr($path, -1) === '/') {
            $path = substr($path, 0, -1);
        }//end if
        
        $sections = $this->rights->getSections();
        
        foreach ($sections as $section => $accessers) {
            
            $qualified = $repos.':'.$path;
            $len       = strlen($qualified);
            $access    = UNDEFINED;

            if ($len <= strlen($section) && strncmp($section, $qualified, $len) === 0) {
                $access = $this->inList($accessers, $this->user);
            }//end if

            if ($access !== DENY) {
                $len = strlen($path);
                if ($len <= strlen($section) && strncmp($section, $path, $len) === 0) {
                    $access = $this->inList($accessers, $this->user);
                }//end if
            }//end if

            if ($access === DENY) {
                return false;
            }//end if
            
        }//end foreach

        return true;
        
    }//end hasUnrestrictedReadAccess()


}//end Authentication class.

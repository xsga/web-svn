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
namespace app\business\repository;

/**
 * Used classes.
 */
use app\business\setup\WebSvnConfig;
use xsgaphp\XsgaAbstractClass;
use xsgaphp\exceptions\XsgaException;
use app\business\utils\Authorization;

/**
 * Repository class.
 */
class Repository extends XsgaAbstractClass
{
    
    /**
     * Repository name.
     * 
     * @var string
     * 
     * @access public
     */
    public $name;
    
    /**
     * SVN name.
     * 
     * @var string
     * 
     * @access public
     */
    public $svnName;
    
    /**
     * Path.
     * 
     * @var string
     * 
     * @access public
     */
    public $path;
    
    /**
     * Subpath.
     * 
     * @var string
     * 
     * @access public
     */
    public $subpath;
    
    /**
     * Group.
     * 
     * @var string
     * 
     * @access public
     */
    public $group;
    
    /**
     * Username.
     * 
     * @var string
     * 
     * @access public
     */
    public $username = null;
    
    /**
     * Password.
     * 
     * @var string
     * 
     * @access public
     */
    public $password = null;
    
    /**
     * Client root URL.
     * 
     * @var string
     * 
     * @access public
     */
    public $clientRootURL;
    
    /**
     * Allow download.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $allowDownload;
    
    /**
     * Min download level.
     * 
     * @var integer
     * 
     * @access public
     */
    public $minDownloadLevel;
    
    /**
     * Allowed exceptions.
     * 
     * @var array
     * 
     * @access public
     */
    public $allowedExceptions = array();
    
    /**
     * Disallowed exceptions.
     * 
     * @var array
     * 
     * @access public
     */
    public $disallowedExceptions = array();
    
    /**
     * Show log changes.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $logsShowChanges;
    
    /**
     * Number of spaces.
     * 
     * @var integer
     * 
     * @access public
     */
    public $spaces;
    
    /**
     * Ignore SVN mime types.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $ignoreSvnMimeTypes;
    
    /**
     * Ignore WebSVN content types.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $ignoreWebSVNContentTypes;
    
    /**
     * Bugtraq flag.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $bugtraq;
    
    /**
     * Bugtraq properties.
     * 
     * @var array
     * 
     * @access public
     */
    public $bugtraqProperties;
    
    /**
     * Authentication class.
     * 
     * @var Authorization
     * 
     * @access public
     */
    public $authz = null;
    
    /**
     * Template path.
     * 
     * @var boolean|string
     * 
     * @access public
     */
    public $templatePath = false;
    
    /**
     * WebSvnConfig class.
     * 
     * @var WebSvnConfig
     * 
     * @access public
     */
    public $config;
    
    
    /**
     * Constructor.
     *
     * @param WebSvnConfig $config
     * @param string       $name
     * @param string       $svnName
     * @param string       $serverRootURL
     * @param string       $group
     * @param string       $username
     * @param string       $password
     * @param string       $subpath
     * @param string       $clientRootURL
     *
     * @return void
     */
    public function __construct(WebSvnConfig $config, $name, $svnName, $serverRootURL, $group = null, $username = null, $password = null, $subpath = null, $clientRootURL = null)
    {
        parent::__construct();
        
        $this->name          = $name;
        $this->svnName       = $svnName;
        $this->path          = $serverRootURL;
        $this->subpath       = $subpath;
        $this->group         = $group;
        $this->username      = $username;
        $this->password      = $password;
        $this->clientRootURL = rtrim($clientRootURL, '/');
        $this->config        = $config;
        
    }//end __construct()
    
    
    /**
     * Get display name.
     *
     * @return string
     * 
     * @access public
     */
    public function getDisplayName()
    {
        if (!empty($this->group)) {
            return $this->group.'.'.$this->name;
        }//end if
        
        return $this->name;
        
    }//end getDisplayName()
    
    
    /**
     * SVN credentials.
     * 
     * @return string
     * 
     * @access public
     */
    public function svnCredentials()
    {
        $params = '';
        
        if ($this->username !== null && $this->username !== '') {
            $params .= ' --username '.quote($this->username);
        }//end if
        
        if ($this->password !== null) {
            $params .= ' --password '.quote($this->password);
        }//end if
        
        return $params;
        
    }//end svnCredentials()
    
    
    /**
     * Set log show changes.
     * 
     * @param boolean $enabled Enabled flag.
     * 
     * @return void
     * 
     * @access public
     */
    public function setLogsShowChanges($enabled = true)
    {
        $this->logsShowChanges = $enabled;
        
    }//end setLogsShowChanges()
    
    
    /**
     * Log show changes.
     * 
     * @param boolean $showChangess
     * 
     * @return boolean
     * 
     * @access public
     */
    public function logsShowChanges($showChanges)
    {
        
        if (isset($this->logsShowChanges)){
            return $this->logsShowChanges;
        } else {
            return $showChanges;
        }//end if
        
    }//end logsShowChanges()
    
    
    /**
     * Allowd download.
     * 
     * @return void
     * 
     * @access public
     */
    public function allowDownload() 
    {
        $this->allowDownload = true;
        
    }//end allowDownload()
    
    
    /**
     * Disallow download.
     * 
     * @return void
     * 
     * @access public
     */
    public function disallowDownload()
    {
        $this->allowDownload = false;
        
    }//end disallowDownload()
    
    
    /**
     * Get allow download.
     * 
     * @param boolean $allowDownload Allow download flag.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function getAllowDownload($allowDownload = true)
    {
        
        if (isset($this->allowDownload)) {
            return $this->allowDownload;
        }//end if
        
        return $allowDownload;
        
    }//end getAllowDownload()
    
    
    /**
     * Set min download level.
     * 
     * @param integer $level Level.
     * 
     * @return void
     * 
     * @access public
     */
    public function setMinDownloadLevel($level)
    {
        $this->minDownloadLevel = $level;
        
    }//end setMinDownloadLevel()
    
    
    /**
     * Get min download level.
     * 
     * @return integer
     * 
     * @access public
     */
    public function getMinDownloadLevel()
    {
        
        if (isset($this->minDownloadLevel)) {
            return $this->minDownloadLevel;
        }//end if
        
        return $this->config->getMinDownloadLevel();
        
    }//end getMinDownloadLevel()()
    
    
    /**
     * Add allowed download exceptions.
     * 
     * @param string $path Path.
     * 
     * @return void
     * 
     * @access public
     */
    public function addAllowedDownloadException($path)
    {
        
        if ($path[strlen($path) - 1] !== '/') {
            $path .= '/';
        }//end if
        
        $this->allowedExceptions[] = $path;
        
    }//end addAllowedDownloadException()
    
    
    /**
     * Add disallowed download exceptions.
     * 
     * @param string $path Path.
     * 
     * @return void
     * 
     * @access public
     */
    public function addDisallowedDownloadException($path)
    {
        
        if ($path[strlen($path) - 1] !== '/') {
            $path .= '/';
        }//end if
        
        $this->disallowedExceptions[] = $path;
        
    }//end addDisallowedDownloadException()
    
    
    /**
     * Is download allowed.
     * 
     * @param string $path Path.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function isDownloadAllowed($path)
    {
        
        // Check global download option and access module.
        if (!$this->getAllowDownload() || !$this->hasUnrestrictedReadAccess($path)) {
            return false;
        }//end if
                
        $subs  = explode('/', $path);
        $level = count($subs) - 2;
        
        if ($level >= $this->getMinDownloadLevel()) {
            
            // Level OK, search for disallowed exceptions
            if ($this->config->findException($path, $this->disallowedExceptions) || 
                $this->config->findException($path, $this->config->disallowedExceptions)) {
                
                $out = false;
                
            }//end if
                        
            $out = true;
            
        } else {
            
            // Level not OK, search for disallowed exceptions
            if ($this->config->findException($path, $this->allowedExceptions) ||
                $this->config->findException($path, $this->config->allowedExceptions)) {
                
                $out = true;
                
            }//end if
            
            $out = false;
            
        }//end if
        
        return $out;
        
    }//end isDownloadAllowed()

    
    /**
     * Set template path.
     * 
     * @param string $path Path.
     * 
     * @return void
     * 
     * @access public
     */
    public function setTemplatePath($path)
    {
        $this->templatePath = $path;
        
    }//end setTemplatePath()
    
    
    /**
     * Get template path.
     * 
     * @return string|boolean
     * 
     * @access public
     */
    public function getTemplatePath()
    {
        
        if (!empty($this->templatePath)) {
            return $this->templatePath;
        }//end if
        
        return $this->config->getTemplatePath();
        
    }//end getTemplatePath()

    
    /**
     * Expand tabs by.
     * 
     * @param integer $sp Number of spaces.
     * 
     * @return void
     * 
     * @access public
     */
    public function expandTabsBy($sp)
    {
        $this->spaces = $sp;
        
    }//end expandTabsBy()
    
    
    /**
     * Get expand tabs by.
     * 
     * @return integer
     * 
     * @access public
     */
    public function getExpandTabsBy()
    {
        
        if (isset($this->spaces)) {
            return $this->spaces;
        }//end if
        
        return $this->config->getExpandTabsBy();
        
    }//end getExpandTabsBy()
    
    
    /**
     * Ignore SVN mime types.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function ignoreSvnMimeTypes()
    {
        $this->ignoreSvnMimeTypes = true;
        
    }//end ignoreSvnMimeTypes()
    
    
    /**
     * Use SVN mime types.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function useSvnMimeTypes()
    {
        $this->ignoreSvnMimeTypes = false;
        
    }//end useSvnMimeTypes()
    
    
    /**
     * Get ignore SVN mime types.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function getIgnoreSvnMimeTypes()
    {
        
        if (isset($this->ignoreSvnMimeTypes)) {
            return $this->ignoreSvnMimeTypes;
        }//end if
        
        return $this->config->getIgnoreSvnMimeTypes();
        
    }//end getIgnoreSvnMimeTypes()
    
    
    /**
     * Ignore WebSVN content types.
     * 
     * @return void
     * 
     * @access public
     */
    public function ignoreWebSVNContentTypes()
    {
        $this->ignoreWebSVNContentTypes = true;
        
    }//end ignoreWebSVNContentTypes()
    
    
    /**
     * Use WebSVN content types.
     * 
     * @return void
     * 
     * @access public
     */
    public function useWebSVNContentTypes()
    {
        $this->ignoreWebSVNContentTypes = false;
        
    }//end useWebSVNContentTypes()
    
    
    /**
     * Get ignore WebSVN content types.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function getIgnoreWebSVNContentTypes()
    {
        
        if (isset($this->ignoreWebSVNContentTypes)) {
            return $this->ignoreWebSVNContentTypes;
        }//end if
        
        return $this->config->getIgnoreWebSVNContentTypes();
        
    }//end getIgnoreWebSVNContentTypes()
    

    /**
     * Set bugtraq enabled.
     * 
     * @param boolean $enabled Enabled flag.
     * 
     * @return void
     * 
     * @access public
     */
    public function setBugtraqEnabled($enabled)
    {
        $this->bugtraq = $enabled;
        
    }//end setBugtraqEnabled()
    
    
    /**
     * Is bugtraq enabled.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function isBugtraqEnabled()
    {
        
        if (isset($this->bugtraq)) {
            return $this->bugtraq;
        } else {
            return $this->config->isBugtraqEnabled();
        }//end if
        
    }//end isBugtraqEnabled()
    
    
    /**
     * Set bugtraq properties.
     * 
     * @param array $properties Bugtraq properties.
     * 
     * @return void
     * 
     * @acces public
     */
    public function setBugtraqProperties(array $properties)
    {
        $this->bugtraqProperties = $properties;
        
    }//end setBugtraqProperties()
    
    
    /**
     * Get bugtraq properties.
     * 
     * @return array
     * 
     * @access public
     */
    public function getBugtraqProperties()
    {
        
        if (isset($this->bugtraqProperties)){
            return $this->bugtraqProperties;
        } else {
            return $this->config->getBugtraqProperties();
        }//end if
        
    }//end getBugtraqProperties()

    
    /**
     * Use authentication file.
     * 
     * @param string  $file Filename.
     * 
     * @return void
     * 
     * @access public
     */
    public function useAccessFile($file)
    {
        
        if (is_readable($file)) {
            
            if ($this->auth === null) {
                $this->auth = new Authorization($this->config->getSvnAuthzCommand());
            }//end if
            
            $this->authz->addAccessFile($file);
            
        } else {
            
            // Error message.
            $errorMsg = 'Unable to read access file "'.$file.'"';
            
            // Logger.
            $this->logger->error($errorMsg);
            
            throw new XsgaException($errorMsg);
            
        }//end if
        
    }//end useAccessFile()
    
    
    /**
     * Get authentication.
     * 
     * @return Authorization
     * 
     * @access public
     */
    public function &getAuthz()
    {

        $a = null;
        
        if ($this->authz !== null) {
            $a =& $this->authz;
        } else {
            $a =& $this->config->getAuthz();
        }//end if
        
        return $a;
        
    }//end getAuthz()
    
    
    /**
     * Get path.
     * 
     * @param string $path
     * 
     * @return string
     * 
     * @access public
     */
    public function _getPathWithSubIf($pathWoSub)
    {
        
        if (!$this->subpath) {
            return $pathWoSub;
        }//end if
        
        return '/'.$this->subpath.$pathWoSub;
        
    }//end _getAuthzPath()    
    
    
    /**
     * Has read acces.
     * 
     * @param string  $path         Path.
     * @param boolean $checkSubDirs Subfolders flag.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function hasReadAccess($pathWoSub, $checkSubDirs = false)
    {
        
        $pathInt =  $this->_getPathWithSubIf($pathWoSub);
        $a    =& $this->getAuthz();
        
        if (!empty($a)) {
            return $a->hasReadAccess($this->svnName, $pathInt, $checkSubDirs);
        }//end if
        
        // No access file - free access.
        return true;
        
    }//end hasReadAccess()
    
    
    /**
     * Has log read access.
     * 
     * @param string $path Path.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function hasLogReadAccess($pathWithSub)
    {
        
        $pathInt=  $pathWithSub;
        $a    =& $this->getAuthz();
        
        if (!empty($a)) {
            return $a->hasReadAccess($this->svnName, $pathInt, false);
        }//end if
        
        // No access file - free access.
        return true;
        
    }//end hasLogReadAccess()
    
    
    /**
     * Has unrestricted read access.
     * 
     * @param string $pathWoSub
     * 
     * @return boolean
     * 
     * @access public
     */
    public function hasUnrestrictedReadAccess($pathWoSub)
    {
        
        $pathInt=  $this->_getPathWithSubIf($pathWoSub);
        $a    =& $this->getAuthz();
        
        if (!empty($a)) {
            return $a->hasUnrestrictedReadAccess($this->svnName, $pathInt);
        }//end if
        
        // No access file - free access.
        return true;
        
    }//end hasUnrestrictedReadAccess()
    
    
}//end Repository class.

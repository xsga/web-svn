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
namespace app\business\setup;

/**
 * Used classes.
 */
use app\business\utils\ParentPath;
use app\business\utils\Authentication;
use app\business\repository\Repository;
use xsgaphp\XsgaAbstractClass;

/**
 * WebSvnConfig class.
 */
class WebSvnConfig extends XsgaAbstractClass
{

    /**
     * SVN command prefix.
     * 
     * @var string
     * 
     * @access public
     */
    public $_svnCommandPrefix = '';
    
    /**
     * SVN command path.
     * 
     * @var string
     * 
     * @access public
     */
    public $_svnCommandPath = '';
    
    /**
     * SVN config dir.
     * 
     * @var string
     * 
     * @access public
     */
    public $_svnConfigDir = '';
    
    /**
     * SVN trust server certificate.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $_svnTrustServerCert = false;
    
    /**
     * SVN.
     * 
     * @var string
     * 
     * @access public
     */
    public $svn = '';
    
    /**
     * Diff command.
     * 
     * @var string
     * 
     * @access public
     */
    public $diff = 'diff';
    
    /**
     * Enscript command.
     * 
     * @var string
     * 
     * @access public
     */
    public $enscript = 'enscript -q';
    
    /**
     * Sed command.
     * 
     * @var string
     * 
     * @access public
     */
    public $sed = 'sed';
    
    /**
     * GZip command.
     * 
     * @var string
     * 
     * @access public
     */
    public $gzip = 'gzip';
    
    /**
     * TAR Command.
     * 
     * @var string
     * 
     * @access public
     */
    public $tar = 'tar';
    
    /**
     * ZIP command.
     * 
     * @var string
     * 
     * @access public
     */
    public $zip = 'zip';

    /**
     * Default file download mode.
     * 
     * @var string
     * 
     * @access public
     */
    public $defaultFileDlMode = 'plain';
    
    /**
     * Default folder download mode.
     * 
     * @var string
     * 
     * @access public
     */
    public $defaultFolderDlMode = 'gzip';
    
    /**
     * Valid file download models.
     * 
     * @var array
     * 
     * @access public
     */
    public $validFileDlModes = array( 'gzip', 'zip', 'plain' );
    
    /**
     * Valid folder download modes.
     * 
     * @var array
     * 
     * @access public
     */
    public $validFolderDlModes = array( 'gzip', 'zip' );

    /**
     * Treeview.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $treeView = true;
    
    /**
     * Flat index.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $flatIndex = true;
    
    /**
     * Open tree.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $openTree = false;
    
    /**
     * Alphabetic.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $alphabetic = false;
    
    /**
     * Show last modifications in index.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $showLastModInIndex = true;
    
    /**
     * Show last modifications in listing.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $showLastModInListing = true;
    
    /**
     * Show age instead od date.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $showAgeInsteadOfDate = true;
    
    /**
     * Ignore whitespaces in diff.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $_ignoreWhitespacesInDiff = false;
    
    /**
     * Server is windows.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $serverIsWindows = false;
    
    /**
     * Use enscript.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $useEnscript = false;
    
    /**
     * Use enscript before 1.6.3.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $useEnscriptBefore_1_6_3 = false;
    
    /**
     * Use GeSHi.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $useGeshi = false;
    
    /**
     * Inline mime types.
     * 
     * @var array
     * 
     * @access public
     */
    public $inlineMimeTypes = array();
    
    /**
     * Allow download.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $allowDownload = false;
    
    /**
     * Temporal directory.
     * 
     * @var string
     * 
     * @access public
     */
    public $tempDir = '';
    
    /**
     * Min download level.
     * 
     * @var integer
     * 
     * @access public
     */
    public $minDownloadLevel = 0;
    
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
     * Logs show changes.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $logsShowChanges = false;
    
    /**
     * Number of spaces.
     * 
     * @var integer
     * 
     * @access public
     */
    public $spaces = 8;
    
    /**
     * Bugtraq flag.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $bugtraq = false;
    
    /**
     * Bugtraq properties.
     * 
     * @var array
     * 
     * @access public
     */
    public $bugtraqProperties;
    
    /**
     * Authentication.
     * 
     * @var Authentication
     * 
     * @access public
     */
    public $auth = null;
    
    /**
     * Block robots.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $blockRobots = false;
    
    /**
     * Template paths.
     * 
     * @var array
     * 
     * @access public
     */
    public $templatePaths = array();
    
    /**
     * User template.
     * 
     * @var string
     * 
     * @access public
     */
    public $userTemplate = null;
    
    /**
     * Ignore SVN mime types.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $ignoreSvnMimeTypes = false;
    
    /**
     * Ignore WebSVN content types.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $ignoreWebSVNContentTypes = false;
    
    /**
     * SVN version.
     * 
     * @var string
     * 
     * @access public
     */
    public $subversionVersion = '';
    
    /**
     * SVN major version.
     * 
     * @var string
     * 
     * @access public
     */
    public $subversionMajorVersion = '';
    
    /**
     * SVN minor version.
     * 
     * @var string
     * 
     * @access public
     */
    public $subversionMinorVersion = '';
    
    /**
     * Default language.
     * 
     * @var string
     * 
     * @access public
     */
    public $defaultLanguage = 'en';
    
    /**
     * Ignore accepted languages.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $ignoreAcceptedLanguages = false;
    
    /**
     * Quote.
     * 
     * @var string
     * 
     * @access public
     */
    public $quote = "'";
    
    /**
     * Path separator.
     * 
     * @var string
     * 
     * @access public
     */
    public $pathSeparator = ':';

    /**
     * Repositories.
     * 
     * @var Repository[]
     * 
     * @access public
     */
    public $_repositories = array();

    /**
     * Parent paths.
     * 
     * @var ParentPath[]
     * 
     * @access public
     */
    public $_parentPaths = array();
    
    /**
     * Parent paths loaded.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $_parentPathsLoaded = false;
    
    /**
     * Excluded.
     * 
     * @var array
     * 
     * @access public
     */
    public $_excluded = array();

    
    /**
     * Constructor.
     * 
     * @access public
     */
    public function __construct()
    {
        // Executes parent constructor.
        parent::__construct();
        
        $tmpPath  = realpath(dirname(__FILE__));
        $tmpPath .= DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
        $tmpPath .= 'tmp';
        
        $this->_svnConfigDir = realpath(dirname(__FILE__)).$tmpPath;
        $this->svn           = 'svn --non-interactive --config-dir "'.$tmpPath.'"';
        
    }//end __construct()
    
    
    /**
     * Add repository.
     * 
     * @param string $name          Repository name.
     * @param string $serverRootURL Server root URL.
     * @param string $group         Group.
     * @param string $username      Username.
     * @param string $password      Password.
     * @param string $clientRootURL Client root URL.
     * 
     * @return void.
     * 
     * @access public
     */
    public function addRepository($name, $serverRootURL, $group = null, $username = null, $password = null, $clientRootURL = null)
    {
        $this->addRepositorySubpath($name, $serverRootURL, null, $group, $username, $password, $clientRootURL);
        
    }//end addRepository()
    
    
    /**
     * Add repository subpath.
     * 
     * @param string $name
     * @param string $serverRootURL
     * @param string $subpath
     * @param string $group
     * @param string $username
     * @param string $password
     * @param string $clientRootURL
     * 
     * @return void
     * 
     * @access public
     */
    public function addRepositorySubpath($name, $serverRootURL, $subpath, $group = null, $username = null, $password = null, $clientRootURL = null)
    {
        if (DIRECTORY_SEPARATOR !== '/') {
            
            $serverRootURL = str_replace(DIRECTORY_SEPARATOR, '/', $serverRootURL);
            
            if ($subpath !== null) {
                $subpath = str_replace(DIRECTORY_SEPARATOR, '/', $subpath);
            }//end if
            
        }//end if
        
        $serverRootURL = trim($serverRootURL, '/');
        $svnName       = substr($serverRootURL, strrpos($serverRootURL, '/') + 1);
        
        $this->_repositories[] = new Repository($name, $svnName, $serverRootURL, $group, $username, $password, $subpath, $clientRootURL);
        
    }// end addRepositorySubpath()

    
    /**
     * Parent path.
     * 
     * @param string  $path             Path.
     * @param string  $group            Group.
     * @param bollean $pattern          Pattern flag.
     * @param boolean $skipAlreadyAdded Skip already added flag
     * @param string  $clientRootURL    Client root URL.
     * 
     * @return void
     * 
     * @access public
     */
    public function parentPath($path, $group = null, $pattern = false, $skipAlreadyAdded = true, $clientRootURL = '')
    {
        $this->_parentPaths[] = new ParentPath($this, $path, $group, $pattern, $skipAlreadyAdded, $clientRootURL);
        
    }//end parentPath()
    
    
    /**
     * Add excluded path.
     * 
     * @param string $path Path to exclude.
     * 
     * @return void
     * 
     * @access public
     */
    public function addExcludedPath($path)
    {
        $url = 'file:///'.$path;
        $url = str_replace(DIRECTORY_SEPARATOR, '/', $url);
        
        if ($url{strlen($url) - 1} === '/') {
            $url = substr($url, 0, -1);
        }//end if
        
        $this->_excluded[] = $url;
        
    }//end addExcludedPath()

    
    /**
     * Get repositories.
     * 
     * @return Repository[]
     * 
     * @access public
     */
    public function getRepositories()
    {
        // Lazily load parent paths.
        if ($this->_parentPathsLoaded) {
            return $this->_repositories;
        }//end if

        $this->_parentPathsLoaded = true;

        foreach ($this->_parentPaths as $parentPath) {
            
            $parentRepos = $parentPath->getRepositories();
            
            foreach ($parentRepos as $repo) {
                
                if (!$parentPath->getSkipAlreadyAdded()) {
                    
                    $this->_repositories[] = $repo;
                    
                } else {
                    
                    // We have to check if we already have a repo with the same svn name.
                    $duplicate = false;
                    
                    foreach ($this->_repositories as $knownRepos) {
                        if ($knownRepos->path === $repo->path && $knownRepos->subpath === $repo->subpath) {
                            $duplicate = true;
                            break;
                        }//end if
                    }//end foreach

                    if (!$duplicate && !in_array($repo->path, $this->_excluded, true)) {
                        $this->_repositories[] = $repo;
                    }//end if
                    
                }//end if
                
            }//end foreach
            
        }//end foreach

        return $this->_repositories;
        
    }//end getRepositories()
    
    
    /**
     * Find repository.
     * 
     * @param string $name Repository name.
     * 
     * @return Repository|string
     * 
     * @access public
     */
    public function &findRepository($name)
    {
        // First look in the "normal repositories".
        foreach ($this->_repositories as $index => $rep) {
            
            if (strcmp($rep->getDisplayName(), $name) == 0) {
                $repref =& $this->_repositories[$index];
                return $repref;
            }//end if
            
        }//end foreach

        // Now if the parent repos have not already been loaded check them.
        if (!$this->_parentPathsLoaded) {
            
            foreach ($this->_parentPaths as $parentPath) {
                
                $repref =& $parentPath->findRepository($name);
                
                if ($repref !== null) {
                    $this->_repositories[] = $repref;
                    return $repref;
                }//end if
                
            }//end foreach
            
        }//end if

        // Hack to return a string by reference; value retrieved at setup.php.
        $str   = 'Unable to find repository "'.escape($name).'".';
        $error =& $str;
        
        return $error;
        
    }//end findRepository()
    
    
    /**
     * Set server is windows.
     * 
     * @return void
     * 
     * @access public
     */
    public function setServerIsWindows()
    {
        $this->serverIsWindows = true;

        // On Windows machines, use double quotes around command line parameters.
        $this->quote = '"';

        // On Windows, semicolon separates path entries in a list rather than colon.
        $this->pathSeparator = ';';
        
    }//end setServerIsWindows()
    
    
    /**
     * Use enscript.
     * 
     * @param boolean $before_1_6_3
     * 
     * @access public
     */
    public function useEnscript($before_1_6_3 = false)
    {
        $this->useEnscript             = true;
        $this->useEnscriptBefore_1_6_3 = $before_1_6_3;
        
    }//end useEnscript()
    
    
    /**
     * Get use enscript.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function getUseEnscript()
    {
        return $this->useEnscript;
        
    }//end getUseEnscript()
    
    
    /**
     * Get use enscript before 1.6.3.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function getUseEnscriptBefore_1_6_3()
    {
        return $this->useEnscriptBefore_1_6_3;
        
    }//end getUseEnscriptBefore_1_6_3()
    
    
    /**
     * Use GeSHi.
     * 
     * @return void
     * 
     * @access public
     */
    public function useGeshi()
    {
        $this->useGeshi = true;
        
    }//end useGeshi()
    
    
    /**
     * Get use GeSHi.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function getUseGeshi()
    {
        return $this->useGeshi;
        
    }//end getUseGeshi()
    
    
    /**
     * Specify MIME types to display inline in WebSVN pages.
     * 
     * @param unknown $type
     * 
     * @return void
     * 
     * @access public
     */
    public function addInlineMimeType($type)
    {
        if (!in_array($type, $this->inlineMimeTypes)) {
            $this->inlineMimeTypes[] = $type;
        }//end if
        
    }//end addInlineMimeType()
    
    
    /**
     * Set logs show changes.
     * 
     * @param boolean $enabled Enabled flag.
     * @param number  $myrep
     * 
     * @return void
     * 
     * @access public
     */
    public function setLogsShowChanges($enabled = true, $myrep = 0)
    {
        if (empty($myrep)) {
            $this->logsShowChanges = $enabled;
        } else {
            $repo =& $this->findRepository($myrep);
            $repo->logsShowChanges = $enabled;
        }//end if
        
    }//end setLogsShowChanges()
    
    
    /**
     * Log show changes.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function logsShowChanges()
    {
        return $this->logsShowChanges;
        
    }//end logsShowChanges()

    
    /**
     * Allow download.
     * 
     * @param number $myrep
     * 
     * @return void
     * 
     * @access public
     */
    public function allowDownload($myrep = 0)
    {
        if (empty($myrep)) {
            $this->allowDownload = true;
        } else {
            $repo =& $this->findRepository($myrep);
            $repo->allowDownload();
        }//end if
        
    }//end allowDownload()
    
    
    /**
     * Disallow download.
     * 
     * @param number $myrep
     * 
     * @return void
     * 
     * @access public
     */
    public function disallowDownload($myrep = 0)
    {
        if (empty($myrep)) {
            $this->allowDownload = false;
        } else {
            $repo =& $this->findRepository($myrep);
            $repo->disallowDownload();
        }//end if
        
    }//end disallowDownload()
    
    
    /**
     * Get allow download.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function getAllowDownload()
    {
        return $this->allowDownload;
        
    }//end getAllowDownload()
    
    
    /**
     * Set temporal dir.
     * 
     * @param string $tempDir Temporal directory.
     * 
     * @return void
     * 
     * @access public
     */
    public function setTempDir($tempDir)
    {
        $this->tempDir = $tempDir;
        
    }//end setTempDir()
    
    
    /**
     * Get temporal dir.
     * 
     * @return string
     * 
     * @access public
     */
    public function getTempDir()
    {
        if (empty($this->tempDir)) {
            $this->tempDir = sys_get_temp_dir();
        }//end if
        
        return $this->tempDir;
        
    }//end getTempDir()
    
    
    /**
     * Set min download level.
     * 
     * @param integer $level
     * @param number  $myrep
     * 
     * @return void
     * 
     * @access public
     */
    public function setMinDownloadLevel($level, $myrep = 0)
    {
        if (empty($myrep)) {
            $this->minDownloadLevel = $level;
        } else {
            $repo =& $this->findRepository($myrep);
            $repo->setMinDownloadLevel($level);
        }//end if
        
    }//end setMinDownloadLevel()
    
    
    /**
     * Get min download level.
     * 
     * @return number
     * 
     * @access public
     */
    public function getMinDownloadLevel()
    {
        return $this->minDownloadLevel;
        
    }//end getMinDownloadLevel()
    
    
    /**
     * Add allowed download exceptions.
     * 
     * @param string $path
     * @param number $myrep
     * 
     * @return void
     * 
     * @access public
     */
    public function addAllowedDownloadException($path, $myrep = 0)
    {
        if ($path{strlen($path) - 1} !== '/') {
            $path .= '/';
        }//end if

        if (empty($myrep)) {
            $this->allowedExceptions[] = $path;
        } else {
            $repo =& $this->findRepository($myrep);
            $repo->addAllowedDownloadException($path);
        }//end if
        
    }//end addAllowedDownloadException()
    
    
    /**
     * Add disallowed download exceptions.
     * 
     * @param unknown $path
     * @param number $myrep
     * 
     * @return void
     * 
     * @access public
     */
    public function addDisallowedDownloadException($path, $myrep = 0)
    {
        if ($path{strlen($path) - 1} !== '/') {
            $path .= '/';
        }//end if

        if (empty($myrep)) {
            $this->disallowedExceptions[] = $path;
        } else {
            $repo =& $this->findRepository($myrep);
            $repo->addDisallowedDownloadException($path);
        }//end if
        
    }//end addDisallowedDownloadException()
    
    
    /**
     * Find exception.
     * 
     * @param string $path       Path.
     * @param array  $exceptions Exceptions array.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function findException($path, $exceptions)
    {
        foreach ($exceptions as $exc) {
            if (strncmp($exc, $path, strlen($exc)) == 0) {
                return true;
            }//end if
        }//end foreach

        return false;
        
    }//end findException()
    
    
    /**
     * Get the URL to a path name based on the current config.
     * 
     * @param Repository|string $rep
     * @param string            $path
     * @param string            $op
     * 
     * @return string
     * 
     * @access public
     */
    public function getURL($rep, $path, $op)
    {
        list($base, $params) = $this->getUrlParts($rep, $path, $op);
        
        $url = $base.'?';
        
        foreach ($params as $k => $v) {
            $url .= $k.'='.urlencode($v).WebSvnCons::ANDAMP;
        }//end foreach
        
        return $url;
        
    }//end getURL()
    
    
    /**
     * Get the URL and parameters for a path name based on the current config.
     * 
     * @param Repository|string $rep
     * @param string            $path
     * @param string            $op
     * 
     * @return array
     * 
     * @access public
     */
    public function getUrlParts($rep, $path, $op)
    {
        
        $params = array();

        switch ($op) {
            case 'index':
                $url = '.';
                break;

            case 'dir':
                $url = 'listing.php';
                break;

            case 'revision':
                $url = 'revision.php';
                break;

            case 'file':
                $url = 'filedetails.php';
                break;

            case 'log':
                $url = 'log.php';
                break;

            case 'diff':
                $url = 'diff.php';
                break;

            case 'blame':
                $url = 'blame.php';
                break;

            case 'form':
                $url = 'form.php';
                break;
                
            case 'dl':
                $url = 'dl.php';
                break;

            case 'comp':
                $url = 'comp.php';
                break;
                
            default:
                // Logger.
                $this->logger->error('"'.$op.'" it\'s not a valid option');
                
        }//end switch
            
        if (is_object($rep) && $op !== 'index') {
            $params['repname'] = $rep->getDisplayName();
        }//end if
        
        if (!empty($path)) {
            $params['path'] = $path;
        }//end if

        return array($url, $params);
        
    }//end getUrlParts()
    
    
    /**
     * Set the location of the given path.
     * 
     * @param string $var
     * @param string $path
     * @param string $name
     * @param string $params
     * 
     * @return void
     * 
     * @access public
     */
    public function _setPath(&$var, $path, $name, $params = '')
    {
        if ($path === '') {
            
            // Search in system search path. No check for existence possible.
            $var = $name;
            
        } else {
            
            $lastchar = substr($path, -1, 1);
            $isDir    = ($lastchar === DIRECTORY_SEPARATOR || $lastchar === '/' || $lastchar === '\\');

            if (!$isDir) {
                $path .= DIRECTORY_SEPARATOR;
            }//end if

            if (($this->serverIsWindows && !file_exists($path.$name.'.exe')) || (!$this->serverIsWindows && !file_exists($path.$name))) {
                
                $error_msg = 'Unable to find "'.$name.'" tool at location "'.$path.$name.'"';
                
                // Logger.
                $this->logger->error($error_msg);
                
                echo $error_msg;
                exit;
                
            }//end if

            // On a windows machine we need to put quotes around the entire command to allow for spaces in the path.
            if ($this->serverIsWindows) {
                $var = '"'.$path.$name.'"';
            } else {
                $var = $path.$name;
            }//end if
            
        }//end if

        // Append parameters
        if ($params !== '') {
            $var .= ' '.$params;
        }//end if
        
    }//end _setPath()
    
    
    /**
     * Define directory path to use for --config-dir parameter.
     * 
     * @param unknown $path
     * 
     * @return void
     * 
     * @access public
     */
    public function setSvnConfigDir($path)
    {
        $this->_svnConfigDir = $path;
        $this->_updateSVNCommand();
        
    }//end setSvnConfigDir()
    
    
    /**
     * Define flag to use --trust-server-cert parameter.
     * 
     * @return void
     * 
     * @access public
     */
    public function setTrustServerCert()
    {
        $this->_svnTrustServerCert = true;
        $this->_updateSVNCommand();
        
    }//end setTrustServerCert()
    
    
    /**
     * Define the location of the svn command (e.g. '/usr/bin').
     * 
     * @param string $path
     * 
     * @return void
     * 
     * @access public
     */
    public function setSvnCommandPath($path)
    {
        $this->_svnCommandPath = $path;
        $this->_updateSVNCommand();
        
    }//end setSvnCommandPath()
    
    
    /**
     * Define a prefix to include before every SVN command (e.g. 'arch -i386').
     * 
     * @param string $prefix
     * 
     * @return void
     * 
     * @access public
     */
    public function setSvnCommandPrefix($prefix)
    {
        $this->_svnCommandPrefix = $prefix;
        $this->_updateSVNCommand();
        
    }//end setSvnCommandPrefix()
    
    
    /**
     * Update SVN command.
     * 
     * @return void
     * 
     * @access public
     */
    public function _updateSVNCommand()
    {
        $this->_setPath($this->svn, $this->_svnCommandPath, 'svn', '--non-interactive --config-dir '.$this->_svnConfigDir.($this->_svnTrustServerCert ? ' --trust-server-cert' : ''));
        $this->svn = $this->_svnCommandPrefix.' '.$this->svn;
        
    }//end _updateSVNCommand()
    
    
    /**
     * Get SVN command.
     * 
     * @return string
     * 
     * @access public
     */
    public function getSvnCommand()
    {
        return $this->svn;
        
    }//end getSvnCommand()
    
    
    /**
     * Define the location of the diff command.
     * 
     * @param string $path
     * 
     * @access public
     */
    public function setDiffPath($path)
    {
        $this->_setPath($this->diff, $path, 'diff');
        
    }//end setDiffPath()
    
    
    /**
     * Get diff command.
     * 
     * @return string
     * 
     * @access public
     */
    public function getDiffCommand()
    {
        return $this->diff;
        
    }//end getDiffCommand()

    
    /**
     * Define the location of the enscript command.
     * 
     * @param string $path
     * 
     * @return void
     * 
     * @access public
     */
    public function setEnscriptPath($path)
    {
        $this->_setPath($this->enscript, $path, 'enscript', '-q');
        
    }//end setEnscriptPath()
    
    
    /**
     * Get enscript command.
     * 
     * @return string
     * 
     * @access public
     */
    public function getEnscriptCommand()
    {
        return $this->enscript;
        
    }//end getEnscriptCommand()
    
    
    /**
     * Define the location of the sed command.
     * 
     * @param string $path
     * 
     * @return void
     * 
     * @access public
     */
    public function setSedPath($path)
    {
        $this->_setPath($this->sed, $path, 'sed');
        
    }//end setSedPath()
    
    
    /**
     * Get sed command.
     * 
     * @return string
     * 
     * @access public
     */
    public function getSedCommand()
    {
        return $this->sed;
        
    }//end getSedCommand()
    
    
    /**
     * Define the location of the tar command.
     * 
     * @param string $path
     * 
     * @return void
     * 
     * @access public
     */
    public function setTarPath($path)
    {
        $this->_setPath($this->tar, $path, 'tar');
        
    }//end setTarPath()
    
    
    /**
     * Get tar command.
     * 
     * @return string
     * 
     * @access public
     */
    public function getTarCommand()
    {
        return $this->tar;
        
    }//end getTarCommand()
    
    
    /**
     * Define the location of the GZip command.
     * 
     * @param string $path
     * 
     * @return void
     * 
     * @access public
     */
    public function setGzipPath($path)
    {
        $this->_setPath($this->gzip, $path, 'gzip');
        
    }//end setGzipPath()
    
    
    /**
     * Get gzip command.
     * 
     * @return string
     * 
     * @access public
     */
    public function getGzipCommand()
    {
        return $this->gzip;
        
    }//end getGzipCommand()
    
    
    /**
     * Define the location of the zip command.
     * 
     * @param string $path
     * 
     * @return void
     * 
     * @access public
     */
    public function setZipPath($path)
    {
        $this->_setPath($this->zip, $path, 'zip');
        
    }//end setZipPath()
    
    
    /**
     * Get zip path.
     * 
     * @return string
     * 
     * @access public
     */
    public function getZipPath()
    {
        return $this->zip;
        
    }//end getZipPath()
    
    
    /**
     * Define the default file download mode - one of [gzip, zip, plain].
     * 
     * @param string $dlmode
     * 
     * @return void
     * 
     * @access public
     */
    public function setDefaultFileDlMode($dlmode)
    {
        if (in_array($dlmode, $this->validFileDlModes)) {
            $this->defaultFileDlMode = $dlmode;
        } else {
            
            $error_msg = 'Setting default file download mode to an invalid value "'.$dlmode.'"';
            
            // Logger.
            $this->logger->error($error_msg);
            
            echo $error_msg;
            exit;
            
        }//end if
        
    }//end setDefaultFileDlMode()
    
    
    /**
     * Get default file download mode.
     * 
     * @return string
     * 
     * @access public
     */
    public function getDefaultFileDlMode()
    {
        return $this->defaultFileDlMode;
        
    }//end getDefaultFileDlMode()()
    
    
    /**
     * Define the default folder download mode - one of [gzip, zip].
     * 
     * @param string $dlmode Download mode.
     * 
     * @return void
     * 
     * @access public
     */
    public function setDefaultFolderDlMode($dlmode)
    {
        if (in_array($dlmode, $this->validFolderDlModes)) {
            $this->defaultFolderDlMode = $dlmode;
        } else {
            
            $error_msg = 'Setting default file download mode to an invalid value "'.$dlmode.'"';
            
            // Logger.
            $this->logger->error($error_msg);
            
            echo $error_msg;
            exit;
            
        }//end if
        
    }//end setDefaultFolderDlMode()
    
    
    /**
     * Get default folder download mode.
     * 
     * @return string
     * 
     * @access public
     */
    public function getDefaultFolderDlMode()
    {
        return $this->defaultFolderDlMode;
        
    }//end getDefaultFolderDlMode()
    
    
    /**
     * Add template path.
     * 
     * @param string $path
     * 
     * @return void
     * 
     * @access public
     */
    public function addTemplatePath($path)
    {
        $lastchar = substr($path, -1, 1);
        
        if ($lastchar !== '/' && $lastchar !== '\\') {
            $path .= DIRECTORY_SEPARATOR;
        }//end if

        if (!in_array($path, $this->templatePaths)) {
            $this->templatePaths[] = $path;
        }//end if
        
    }//end addTemplatePath()
    
    
    /**
     * Set template path.
     * 
     * @param string $path
     * @param string $myrep
     * 
     * @return void
     * 
     * @access public
     */
    public function setTemplatePath($path, $myrep = null)
    {
        $lastchar = substr($path, -1, 1);
        
        if ($lastchar !== '/' && $lastchar !== '\\') {
            $path .= DIRECTORY_SEPARATOR;
        }//end if

        if ($myrep !== null) {
            
            // Fixed template for specific repository.
            $repo =& $this->findRepository($myrep);
            $repo->setTemplatePath($path);
            
        } else {
            
            // For backward compatibility.
            if (in_array($path, $this->templatePaths)) {
                array_splice($this->templatePaths, array_search($path, $this->templatePaths), 1);
            }//end if
            
            array_unshift($this->templatePaths, $path);
            
        }//end if
        
    }//end setTemplatePath()
    
    
    /**
     * Get template path.
     * 
     * @return string
     * 
     * @access public
     */
    public function getTemplatePath()
    {
        if (count($this->templatePaths) === 0) {
            
            $error_msg = 'No template path added in config file';
            
            // Logger.
            $this->logger->error($error_msg);
            
            echo $error_msg;
            exit;
            
        }//end if
        
        if ($this->userTemplate !== null) {
            return $this->userTemplate;
        } else {
            return $this->templatePaths[0];
        }//end if
            
    }//end getTemplatePath()
    
    
    /**
     * Set default language,
     * 
     * @param string $language Language.
     * 
     * @return void
     * 
     * @access public
     */
    public function setDefaultLanguage($language)
    {
        $this->defaultLanguage = $language;
        
    }//end setDefaultLanguage()
    
    
    /**
     * Get default language.
     * 
     * @return string
     * 
     * @access public
     */
    public function getDefaultLanguage()
    {
        return $this->defaultLanguage;
        
    }//end getDefaultLanguage()
    
    
    /**
     * Ignore user accepted languages.
     * 
     * @return void
     * 
     * @access public
     */
    public function ignoreUserAcceptedLanguages()
    {
        $this->ignoreAcceptedLanguages = true;
        
    }//end ignoreUserAcceptedLanguages()
    
    
    /**
     * Use accepted languages.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function useAcceptedLanguages()
    {
        return !$this->ignoreAcceptedLanguages;
        
    }//end useAcceptedLanguages()
    
    
    /**
     * Expand tabs by.
     * 
     * @param integer $sp    Number of spaces.
     * @param string  $myrep Repository name.
     * 
     * @return void
     * 
     * @access public
     */
    public function expandTabsBy($sp, $myrep = '')
    {
        if (empty($myrep)) {
            $this->spaces = $sp;
        } else {
            $repo =& $this->findRepository($myrep);
            $repo->expandTabsBy($sp);
        }//end if
        
    }//end expandTabsBy()
    
    
    /**
     * Get expand tabs by.
     * 
     * @return number
     * 
     * @access public
     */
    public function getExpandTabsBy()
    {
        return $this->spaces;
        
    }//end getExpandTabsBy()
    
    
    /**
     * Set bugtraq enabled.
     * 
     * @param boolean $enabled Enabled flag.
     * @param string  $myrep   My repository.
     * 
     * @return void
     * 
     * @access public
     */
    public function setBugtraqEnabled($enabled, $myrep = '')
    {
        if (empty($myrep)) {
            $this->bugtraq = $enabled;
        } else {
            $repo =& $this->findRepository($myrep);
            $repo->setBugtraqEnabled($enabled);
        }//end if
        
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
        return $this->bugtraq;
        
    }//end isBugtraqEnabled()
    
    
    /**
     * Set bugtraq properties.
     * 
     * @param string  $message
     * @param string  $logregex
     * @param string  $url
     * @param boolean $append
     * @param string  $myrep
     * 
     * @return void
     * 
     * @access public
     */
    public function setBugtraqProperties($message, $logregex, $url, $append = true, $myrep = null)
    {
        $properties = array();
        
        $properties['bugtraq:message']  = $message;
        $properties['bugtraq:logregex'] = $logregex;
        $properties['bugtraq:url']      = $url;
        $properties['bugtraq:append']   = (bool)$append;
        
        if ($myrep === null) {
            $this->bugtraqProperties = $properties;
        } else {
            $repo =& $this->findRepository($myrep);
            $repo->setBugtraqProperties($properties);
        }//end if
        
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
        return $this->bugtraqProperties;
        
    }//end getBugtraqProperties()
    
    
    /**
     * Ignore SVN mime types.
     * 
     * @return void
     * 
     * @access public
     */
    public function ignoreSvnMimeTypes()
    {
        $this->ignoreSvnMimeTypes = true;
        
    }//end ignoreSvnMimeTypes()
    
    
    /**
     * Get ignore SVN mime types.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function getIgnoreSvnMimeTypes()
    {
        return $this->ignoreSvnMimeTypes;
        
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
     * Get ignore WebSVN content type.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function getIgnoreWebSVNContentTypes()
    {
        return $this->ignoreWebSVNContentTypes;
        
    }//end getIgnoreWebSVNContentTypes()
    
    
    /**
     * Use authentication file.
     * 
     * @param string $file
     * @param string $myrep
     * @param string $basicRealm
     * 
     * @return void
     * 
     * @access public
     */
    public function useAuthenticationFile($file, $myrep = '', $basicRealm = false)
    {
        if (empty($myrep)) {
            
            if (is_readable($file)) {
                
                if ($this->auth === null) {
                    $this->auth = new Authentication($basicRealm);
                }//end if
                
                $this->auth->addAccessFile($file);
                
            } else {
                
                $error_msg = 'Unable to read authentication file "'.$file.'"';
                
                // Logger.
                $this->logger->error($error_msg);
                
                echo $error_msg;
                exit;
                
            }//end if
            
        } else {
            
            $repo =& $this->findRepository($myrep);
            $repo->useAuthenticationFile($file);
            
        }//end if
        
    }//end useAuthenticationFile()
    
    
    /**
     * Get authentication.
     * 
     * @return Authentication
     * 
     * @access public
     */
    public function &getAuth()
    {
        return $this->auth;
        
    }//end getAuth()
    
    
    /**
     * Are robots blocked.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function areRobotsBlocked()
    {
        return $this->blockRobots;
        
    }//end areRobotsBlocked()
    
    
    /**
     * Set block robots.
     * 
     * @param boolean $value
     * 
     * @access public
     */
    public function setBlockRobots($value = true)
    {
        $this->blockRobots = $value;
        
    }//end setBlockRobots()
    
    
    /**
     * Use tree view.
     * 
     * @return void
     * 
     * @access public
     */
    public function useTreeView()
    {
        $this->treeView = true;
        
    }//end useTreeView()
    
    
    /**
     * Get use tree view.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function getUseTreeView()
    {
        return $this->treeView;
        
    }//end getUseTreeView()
    
    
    /**
     * Use flat view.
     * 
     * @return void
     * 
     * @access public
     */
    public function useFlatView()
    {
        $this->treeView = false;
        
    }//end useFlatView()
    
    
    /**
     * Use tree index.
     * 
     * @param boolean $open
     * 
     * @return void
     * 
     * @access public
     */
    public function useTreeIndex($open)
    {
        $this->flatIndex = false;
        $this->openTree  = $open;
        
    }//end useTreeIndex()
    
    
    /**
     * Get use flat index.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function getUseFlatIndex()
    {
        return $this->flatIndex;
        
    }//end getUseFlatIndex()
    
    
    /**
     * Get open tree.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function getOpenTree()
    {
        return $this->openTree;
        
    }//end getOpenTree()
    
    
    /**
     * Set alphabetic order.
     * 
     * @param boolean $flag
     * 
     * @return void
     * 
     * @access public
     */
    public function setAlphabeticOrder($flag)
    {
        $this->alphabetic = $flag;
        
    }//end setAlphabeticOrder()
    
    
    /**
     * Is alphabetic order.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function isAlphabeticOrder()
    {
        return $this->alphabetic;
        
    }//end isAlphabeticOrder()
    
    
    /**
     * Show last modification in index.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function showLastModInIndex()
    {
        return $this->showLastModInIndex;
        
    }//end showLastModInIndex()
    
    
    /**
     * Set show last modification in index.
     * 
     * @param boolean $show
     * 
     * @return void
     * 
     * @access public
     */
    public function setShowLastModInIndex($show)
    {
        $this->showLastModInIndex = $show;
        
    }//end setShowLastModInIndex()
    
    
    /**
     * Show last modification in listing.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function showLastModInListing()
    {
        return $this->showLastModInListing;
        
    }//end showLastModInListing()
    
    
    /**
     * Set show last modification in listing.
     * 
     * @param boolean $show
     * 
     * @return void
     * 
     * @access public
     */
    public function setShowLastModInListing($show)
    {
        $this->showLastModInListing = $show;
        
    }//end setShowLastModInListing()
    
    
    /**
     * Show age instead of date.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function showAgeInsteadOfDate()
    {
        return $this->showAgeInsteadOfDate;
        
    }//end showAgeInsteadOfDate()
    
    
    /**
     * Set show age instead of date.
     * 
     * @param boolean $show
     * 
     * @return void
     * 
     * @access public
     */
    public function setShowAgeInsteadOfDate($show)
    {
        $this->showAgeInsteadOfDate = $show;
        
    }//end setShowAgeInsteadOfDate
    
    
    /**
     * Get ignore whitespaces in diff.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function getIgnoreWhitespacesInDiff()
    {
        return $this->_ignoreWhitespacesInDiff;
        
    }//end getIgnoreWhitespacesInDiff()
    
    
    /**
     * Set ignore whitespaces in diff.
     * 
     * @param boolean $ignore
     * 
     * @return void
     * 
     * @access public
     */
    public function setIgnoreWhitespacesInDiff($ignore)
    {
        $this->_ignoreWhitespacesInDiff = $ignore;
        
    }//end setIgnoreWhitespacesInDiff()
    
    
    /**
     * Set subversion version.
     * 
     * @param string $subversionVersion
     * 
     * @return void
     * 
     * @access public
     */
    public function setSubversionVersion($subversionVersion)
    {
        $this->subversionVersion = $subversionVersion;
        
    }//end setSubversionVersion
    
    
    /**
     * Get subversion version.
     * 
     * @return string
     * 
     * @access public
     */
    public function getSubversionVersion()
    {
        return $this->subversionVersion;
        
    }//end getSubversionVersion()
    
    
    /**
     * Set subversion major version.
     * 
     * @param string $subversionMajorVersion
     * 
     * @return void
     * 
     * @access public
     */
    public function setSubversionMajorVersion($subversionMajorVersion)
    {
        $this->subversionMajorVersion = $subversionMajorVersion;
        
    }//end setSubversionMajorVersion()
    
    /**
     * Get subversion major version.
     * 
     * @return string
     * 
     * @access public
     */
    public function getSubversionMajorVersion()
    {
        return $this->subversionMajorVersion;
        
    }//end getSubversionMajorVersion()
    
    
    /**
     * Set subversion minor version.
     * 
     * @param string $subversionMinorVersion
     * 
     * @access public
     */
    public function setSubversionMinorVersion($subversionMinorVersion)
    {
        $this->subversionMinorVersion = $subversionMinorVersion;
        
    }//end setSubversionMinorVersion()
    
    
    /**
     * Get subversion minor version.
     * 
     * @return string
     * 
     * @access public
     */
    public function getSubversionMinorVersion()
    {
        return $this->subversionMinorVersion;
        
    }//end getSubversionMinorVersion()
    
    
    /**
     * Sort by group.
     * 
     * This function sorts the repositories by group name. The contents of the group 
     * are left in there original order, which will either be sorted if the group was 
     * added using the parentPath function, or defined for the order in which the 
     * repositories were included in the user's config file.
     *
     * Note that as of PHP 4.0.6 the usort command no longer preserves the order of 
     * items that are considered equal (in our case, part of the same group). The 
     * mergesort function preserves this order.
     * 
     * @return void
     * 
     * @access public
     */
    public function sortByGroup()
    {
        if (!empty($this->_repositories)) {
            $this->mergesort($this->_repositories, array($this, 'cmpGroups'));
        }//end if
        
    }//end sortByGroup()
    
    
    /**
     * Merge sort.
     * 
     * @param array  $array
     * @param string $cmp_function
     * 
     * @return void
     * 
     * @access public
     */
    public function mergesort(&$array, $cmp_function = 'strcmp')
    {
        // Arrays of size < 2 require no action.
        if (count($array) < 2) {
            return;
        }//end if
        
        // Split the array in half.
        $halfway = count($array) / 2;
        $array1  = array_slice($array, 0, $halfway);
        $array2  = array_slice($array, $halfway);
        
        // Recurse to sort the two halves.
        $this->mergesort($array1, $cmp_function);
        $this->mergesort($array2, $cmp_function);
        
        // If all of $array1 is <= all of $array2, just append them.
        if (call_user_func($cmp_function, end($array1), $array2[0]) < 1) {
            $array = array_merge($array1, $array2);
            return;
        }//end if
        
        // Merge the two sorted arrays into a single sorted array.
        $array       = array();
        $array1count = count($array1);
        $array2count = count($array2);
        
        $ptr1 = 0;
        $ptr2 = 0;
        
        while ($ptr1 < $array1count && $ptr2 < $array2count) {
            if (call_user_func($cmp_function, $array1[$ptr1], $array2[$ptr2]) < 1) {
                $array[] = $array1[$ptr1++];
            } else {
                $array[] = $array2[$ptr2++];
            }//end if
        }//end while
        
        // Merge the remainder.
        while ($ptr1 < $array1count) {
            $array[] = $array1[$ptr1++];
        }//end while
        
        while ($ptr2 < $array2count) {
            $array[] = $array2[$ptr2++];
        }//end while
                
    }//end mergesort()
    
    
    /**
     * Compare groups.
     * 
     * @param Repository $a
     * @param Repository $b
     *
     * @return number
     * 
     * @access public
     */
    public function cmpGroups(Repository $a, Repository $b)
    {
        $g = strcasecmp($a->group, $b->group);
        
        if ($g) {
            return $g;
        }//end if
        
        return 0;
        
    }//end cmpGroups()

    
}//end WebSvnConfig class.

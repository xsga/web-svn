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
namespace app\business\utils;

/**
 * Used classes.
 */
use app\business\setup\WebSvnConfig;
use app\business\repository\Repository;
use xsgaphp\XsgaAbstractClass;

/**
 * ParentPath class.
 */
class ParentPath extends XsgaAbstractClass
{

    /**
     * Path.
     * 
     * @var string
     * 
     * @access public
     */
    public $path;
    
    /**
     * Group.
     * 
     * @var string
     * 
     * @access public
     */
    public $group;
    
    /**
     * Pattern.
     * 
     * @var boolean|string
     * 
     * @access public
     */
    public $pattern;
    
    /**
     * Skip already added.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $skipAlreadyAdded;
    
    /**
     * Client root URL.
     * 
     * @var string
     * 
     * @access public
     */
    public $clientRootURL;
    
    /**
     * Config.
     * 
     * @var WebSvnConfig
     * 
     * @access private
     */
    private $config;


    /**
     * Constructor.
     * 
     * @access public
     */
    public function __construct($config, $path, $group = null, $pattern = false, $skipAlreadyAdded = true, $clientRootURL = '')
    {
        parent::__construct();
        
        $this->path = $path;
        $this->group = $group;
        $this->pattern = $pattern;
        $this->skipAlreadyAdded = $skipAlreadyAdded;
        $this->clientRootURL = rtrim($clientRootURL, '/');
        $this->config = $config;
        
    }//end __construct()
    

    /**
     * Find repository.
     * 
     * @param string $name Repository name.
     * 
     * @return Repository|null
     * 
     * @access public
     */
    public function &findRepository($name)
    {
        if ($this->group !== null) {
            
            $prefix = $this->group.'.';
            
            if (substr($name, 0, strlen($prefix)) === $prefix) {
                $name = substr($name, strlen($prefix));
            } else {
                return null;
            }//end if
            
        }//end if
        
        // Is there a directory named $name?
        $fullpath = $this->path.DIRECTORY_SEPARATOR.$name;
        
        if (is_dir($fullpath) && is_readable($fullpath)) {
            
            // And that contains a db directory (in an attempt to not include non svn repositories.
            $dbfullpath = $fullpath.DIRECTORY_SEPARATOR.'db';
            
            if ((is_dir($dbfullpath) && is_readable($dbfullpath)) && ($this->pattern === false || preg_match($this->pattern, $name))) {
                
                $url = $this->config->fileUrlPrefix.$fullpath;
                $url = str_replace(DIRECTORY_SEPARATOR, '/', $url);
                
                if ($url[strlen($url) - 1] === '/') {
                    $url = substr($url, 0, -1);
                }//end if
                
                if (!in_array($url, $this->config->_excluded, true)) {
                    $clientRootURL = ($this->clientRootURL) ? $this->clientRootURL.'/'.$name : '';
                    $rep = new Repository($this->config, $name, $name, $url, $this->group, null, null, null, $clientRootURL);
                    return $rep;
                }//end if
                
            }//end if
            
        }//end if
        
        return null;
        
    }//end findRepository()
    

    /**
     * Get repositories.
     * 
     * @return Repository[]
     * 
     * @access public
     */
    public function &getRepositories()
    {
        $repos  = array();
        $handle = @opendir($this->path);

        if (!$handle) {
            return $repos;
        }//end if

        // For each file.
        while (false !== ($name = readdir($handle))) {
            
            $fullpath = $this->path.DIRECTORY_SEPARATOR.$name;
            
            if ($name[0] !== '.' && is_dir($fullpath) && is_readable($fullpath)) {
                
                $dbfullpath = $fullpath.DIRECTORY_SEPARATOR.'db';
                
                // And that contains a db directory (in an attempt to not include non svn repositories.
                // And matches the pattern if specified.
                if (is_dir($dbfullpath) && is_readable($dbfullpath) && ($this->pattern === false || preg_match($this->pattern, $name))){
                    
                    $url = $this->config->fileUrlPrefix.$fullpath;
                    $url = str_replace(DIRECTORY_SEPARATOR, '/', $url);
                    
                    if ($url[strlen($url) - 1] === '/') {
                        $url = substr($url, 0, -1);
                    }//end if
                    
                    $clientRoot = ($this->clientRootURL) ? $this->clientRootURL.'/'.$name : '';
                    $repos[] = new Repository($this->config, $name, $name, $url, $this->group, null, null, null, $clientRoot);
                    
                }//end if
                
            }//end if
            
        }//end while
        
        closedir($handle);

        // Sort the repositories into alphabetical order.
        if (!empty($repos)) {
            usort($repos, array($this, 'cmpReps'));
        }//end if

        return $repos;
        
    }//end getRepositories()
    
    
    /**
     * Compare repositories.
     * 
     * @param Repository $a Repository one.
     * @param Repository $b Repository two.
     *
     * @return number
     * 
     * @access public
     */
    public function cmpReps(Repository $a, Repository $b)
    {
        
        // First, sort by group.
        $g = strcasecmp($a->group, $b->group);
        
        if ($g) {
            return $g;
        }//end if
        
        // Same group? Sort by name.
        return strcasecmp($a->name, $b->name);
        
    }//end cmpReps()
    

    /**
     * Get skip already added.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function getSkipAlreadyAdded()
    {
        return $this->skipAlreadyAdded;
        
    }//end getSkipAlreadyAdded()
    

}//end ParentPath class.

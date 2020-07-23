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
 * IniFile class.
 */
class IniFile extends XsgaAbstractClass
{
    
    /**
     * Sections.
     * 
     * @var array
     * 
     * @access public
     */
    public $sections;

    
    /**
     * Constructor.
     * 
     * @access public
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->sections = array();
        
    }//end __construct()
    
    
    /**
     * Read ini file.
     * 
     * @param string $name Filename.
     * 
     * @return void
     * 
     * @access public
     */
    public function readIniFile($name)
    {
        // Does not use parse_ini_file function since php 5.3 does not support comment lines starting with #.
        $contents   = file($name);
        $cursection = '';
        $curkey     = '';

        foreach ($contents as $line) {
            
            $line = rtrim($line);
            $str  = ltrim($line);
            
            if (empty($str)) {
                continue;
            }//end if

            // TOTO: remove ' in the next major release to be in line with the svn book.
            if ($str{0} === '#' || $str{0} === "'") {
                continue;
            }//end if

            if ($str !== $line && !empty($cursection) && !empty($curkey)) {
                
                // Line starts with whitespace.
                $this->sections[$cursection][$curkey] .= strtolower($str);
                
            } else if ($str{0} === '[' && $str{strlen($str) - 1} === ']') {
                
                $cursection = strtolower(substr($str, 1, strlen($str) - 2));
                
            } else if (!empty($cursection)) {
                
                if (!isset($this->sections[$cursection])) {
                    $this->sections[$cursection] = array();
                }//end if
                
                list($key, $val) = explode('=', $str, 2);
                $key             = strtolower(trim($key));
                $curkey          = $key;
                
                if ($cursection === 'groups' && isset($this->sections[$cursection][$key])) {
                    $this->sections[$cursection][$key] .= ',' . strtolower(trim($val));
                } else {
                    $this->sections[$cursection][$key] = strtolower(trim($val));
                }//end if
                
            }//end if
            
        }//end foreach
        
    }//end readIniFile()

    
    /**
     * Get sections.
     * 
     * @return array
     * 
     * @access public
     */
    public function &getSections()
    {
        return $this->sections;
        
    }//end getSections()

    
    /**
     * Get values.
     * 
     * @param string $section Section.
     * 
     * @return array
     * 
     * @access public
     */
    public function getValues($section)
    {
        return @$this->sections[strtolower($section)];

    }//end getValues()

    
    /**
     * Get value.
     * 
     * @param string $section Section.
     * @param string $key     Key.
     * 
     * @return string
     * 
     * @access public
     */
    function getValue($section, $key)
    {
        return @$this->sections[strtolower($section)][strtolower($key)];

    }//end getValue()


}//end IniFile class.

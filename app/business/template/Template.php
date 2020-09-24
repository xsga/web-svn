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
namespace app\business\template;

/**
 * Used classes.
 */
use app\business\setup\Setup;
use xsgaphp\XsgaAbstractClass;
use xsgaphp\exceptions\XsgaFileNotFoundException;
use app\business\setup\WebSvnCons;

/**
 * Template class.
 */
class Template extends XsgaAbstractClass
{
    
    /**
     * Ignore flag.
     * 
     * @var boolean
     * 
     * @access private
     */
    private $ignore = false;
    
    /**
     * Stack of previous test results.
     * 
     * @var array
     * 
     * @access private
     */
    private $ignorestack = array();
    
    /**
     * Number of test levels currently ignored.
     * 
     * @var integer
     * 
     * @access private
     */
    private $ignorelevel = 0;
    
    /**
     * Setup.
     * 
     * @var Setup
     * 
     * @access private
     */
    private $setup;
    
    /**
     * Icons.
     * 
     * @var array
     * 
     * @access private
     */
    private $icons;
    
    
    /**
     * Constructor.
     * 
     * @param Setup $setup
     * 
     * @access public
     */
    public function __construct(Setup $setup)
    {
        parent::__construct();
        
        $this->setup = $setup;
        
    }//end __construct()
    
    
    /**
     * Parse command.
     * 
     * @param integer $line
     * @param string $handle
     * 
     * @return boolean
     * 
     * @access public
     */
    public function parseCommand($line, $handle)
    {
        
        // Process content of included file.
        if (strncmp(trim($line), '[websvn-include:', 16) === 0) {
            
            if (!$this->ignore) {
                $line = trim($line);
                $file = substr($line, 16, -1);
                $this->parseTemplate($file);
            }//end if
            
            return true;
            
        }//end if
        
        // Check for test conditions.
        if (strncmp(trim($line), '[websvn-test:', 13) === 0) {
            
            if (!$this->ignore) {
                
                $line = trim($line);
                $var  = substr($line, 13, -1);
                $neg  = ($var[0] === '!');
                
                if ($neg) {
                    $var = substr($var, 1);
                }//end if
                
                if (empty($this->setup->vars[$var]) ^ $neg) {
                    array_push($this->ignorestack, $this->ignore);
                    $this->ignore = true;
                }//end if
                
            } else {
                
                $this->ignorelevel++;
                
            }//end if
            
            return true;
            
        }//end if
        
        if (strncmp(trim($line), '[websvn-else]', 13) === 0) {
            
            if ($this->ignorelevel === 0) {
                $this->ignore = !$this->ignore;
            }//end if
            
            return true;
            
        }//end if
        
        if (strncmp(trim($line), '[websvn-endtest]', 16) === 0) {
            
            if ($this->ignorelevel > 0) {
                $this->ignorelevel--;
            } else {
                $this->ignore = array_pop($this->ignorestack);
            }//end if
            
            return true;
            
        }//end if
        
        if (strncmp(trim($line), '[websvn-getlisting]', 19) === 0) {
            
            if (!$this->ignore) {
                $this->setup->svnrep->listFileContents($this->setup->path, $this->setup->rev, $this->setup->peg);
            }//end if
            
            return true;
            
        }//end if
        
        if (strncmp(trim($line), '[websvn-defineicons]', 19) === 0) {
            
            if (!isset($this->icons)) {
                $this->icons = array();
            }//end if
            
            // Read all the lines until we reach the end of the definition, storing each one.
            if (!$this->ignore) {
                
                while (!feof($handle)) {
                    
                    $line = trim(fgets($handle));
                    
                    if (strncmp($line, '[websvn-enddefineicons]', 22) === 0) {
                        return true;
                    }//end if
                    
                    $eqsign = strpos($line, '=');
                    $match  = substr($line, 0, $eqsign);
                    $def    = substr($line, $eqsign + 1);
                    
                    $this->icons[$match] = $def;
                    
                }//end while
                
            }//end if
            
            return true;
            
        }//end if
        
        if (strncmp(trim($line), '[websvn-icon]', 13) === 0) {
            
            if (!$this->ignore) {
                // The current filetype should be defined my $vars['filetype'].
                if (!empty($this->icons[$this->setup->vars['filetype']])) {
                    echo $this->parseTags($this->icons[$this->setup->vars['filetype']]);
                } else if (!empty($this->icons['*'])) {
                    echo $this->parseTags($this->icons['*']);
                }//end if
            }//end if
            
            return true;
            
        }//end if
        
        if (strncmp(trim($line), '[websvn-treenode]', 17) === 0) {
            
            if (!$this->ignore && !empty($this->icons['i-node']) && !empty($this->icons['t-node']) && !empty($this->icons['l-node'])) {
                
                for ($n = 1; $n < $this->setup->vars['level']; $n++) {
                    if ($this->setup->vars['last_i_node'][$n]) {
                        echo $this->parseTags($this->icons['e-node']);
                    } else {
                        echo $this->parseTags($this->icons['i-node']);
                    }//end if
                }//end for
                
                if ($this->setup->vars['level'] != 0) {
                    if ($this->setup->vars['node'] == 0) {
                        echo $this->parseTags($this->icons['t-node']);
                    } else {
                        echo $this->parseTags($this->icons['l-node']);
                        $this->setup->vars['last_i_node'][$this->setup->vars['level']] = true;
                    }
                }
            }//end if
            
            return true;
            
        }//end if
        
        return false;
        
    }//end parseCommand()
    
    
    /**
     * Parse template.
     * 
     * @param string $file
     * 
     * @return void
     * 
     * @access public
     */
    public function parseTemplate($file)
    {
        
        $template = (($this->setup->rep) ? $this->setup->rep->getTemplatePath() : $this->setup->config->getTemplatePath()).$file;
        
        if (!is_file($template)) {
            
            // Error message.
            $errorMsg = 'No template file found ('.$template.')';
            
            // Logger.
            $this->logger->error($errorMsg);
            
            throw new XsgaFileNotFoundException($errorMsg, WebSvnCons::ERROR_404);
            
        }//end if
        
        $handle       = fopen($template, 'r');
        $inListing    = false;
        $this->ignore = false;
        $listLines    = array();
        
        while (!feof($handle)) {
            $line = fgets($handle);
            
            // Check for the end of the file list.
            if ($inListing) {
                
                if (strcmp(trim($line), '[websvn-endlisting]') === 0) {
                    $inListing = false;
                    
                    // For each item in the list.
                    foreach ($this->setup->listing as $listvars) {
                        
                        // Copy the value for this list item into the $vars array.
                        foreach ($listvars as $id => $value) {
                            $this->setup->vars[$id] = $value;
                        }
                        
                        // Output the list item.
                        foreach ($listLines as $line) {
                            if (!$this->parseCommand($line, $handle) && !$this->ignore) {
                                print $this->parseTags($line);
                            }//end if
                        }//end foreach
                        
                    }//end foreach
                    
                    
                } else if (($this->ignore === false) || (empty($this->ignore))) {
                    $listLines[] = $line;
                }//end if
                
            } else if ($this->parseCommand($line, $handle)) {
                
                continue;
                
            } else {
                
                // Check for the start of the file list.
                if (strncmp(trim($line), '[websvn-startlisting]', 21) === 0) {
                    $inListing = true;
                } else {
                    if (($this->ignore === false) || empty($this->ignore)) {
                        print $this->parseTags($line);
                    }//end if
                }//end if
            
            }//end if
            
        }//end while
        
        fclose($handle);
        
    }//end parseTemplate()
    
    
    /**
     * Parse tags.
     * 
     * @param string $line
     * 
     * @return string
     * 
     * @access public
     */
    public function parseTags($line)
    {
        
        // Replace the language strings.
        while (preg_match('|\[lang:([a-zA-Z0-9_]+)\]|', $line, $matches)) {
            // Make sure that the variable exists.
            if (!isset($this->setup->lang[$matches[1]])) {
                $this->setup->lang[$matches[1]] = '?${matches[1]}?';
            }//end if
            $line = str_replace($matches[0], $this->setup->lang[$matches[1]], $line);
        }//end while
        
        $l = '';
        
        // Replace the websvn variables.
        while (preg_match('|\[websvn:([a-zA-Z0-9_]+)\]|', $line, $matches)) {
            
            // Find beginning.
            $p = strpos($line, $matches[0]);
            
            // Add everything up to beginning.
            if ($p > 0) {
                $l .= substr($line, 0, $p);
            }//end if
            
            // Replace variable (special token, if not exists).
            $l .= isset($this->setup->vars[$matches[1]]) ? $this->setup->vars[$matches[1]]: ('?'.$matches[1].'?');
            
            // Remove allready processed part of line.
            $line = substr($line, $p + strlen($matches[0]));
            
        }//end while
        
        // Rebuild line, add remaining part of line.
        $line = $l.$line;
        
        return $line;
        
    }//end parseTags()
    
    
    /**
     * Render template.
     * 
     * @param string $view
     * 
     * @return void
     * 
     * @access public
     */
    public function renderTemplate($view)
    {
        
        // Set the view in the templates variables.
        $this->setup->vars['template'] = $view;
        
        // Check if we are using a PHP powered template or the standard one
        $path = !empty($this->setup->rep) ? $this->setup->rep->getTemplatePath() : $this->setup->config->getTemplatePath();
        $path = $path . 'template.php';
        
        if (is_readable($path)) {
            $this->setup->vars['templateentrypoint'] = $path;
            $this->executePlainPhpTemplate();
        } else {
            
            $this->parseTemplate('header.tmpl');
            
            flush();
            
            $this->parseTemplate($view . '.tmpl');
            
            if ($view === 'directory' || $view === 'log') {
                print '<script type="text/javascript" src="'.$this->setup->vars['locwebsvnhttp'].'/javascript/compare-checkboxes.js"></script>';
            }//end if
            
            $this->parseTemplate('footer.tmpl');
            
        }//end if
        
    }//end renderTemplate()
    
    
    /**
     * Execute plain PHP template.
     * 
     * @param unknown $vars
     */
    public function executePlainPhpTemplate()
    {
        require_once $this->setup->vars['templateentrypoint'];
        
    }//end executePlainPhpTemplate()
    
    
}
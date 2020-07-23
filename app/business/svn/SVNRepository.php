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
use app\business\repository\Repository;
use app\business\setup\Setup;
use app\business\WebSvnConfig;
use xsgaphp\XsgaAbstractClass;

/**
 * SVNRepository class.
 */
class SVNRepository extends XsgaAbstractClass
{
    
    /**
     * Repository.
     * 
     * @var Repository
     * 
     * @access public
     */
    public $repConfig;
    
    /**
     * Geshi.
     * 
     * @var \GeSHi
     * 
     * @access public
     */
    public $geshi = null;
    
    
    /**
     * WebSvnConfig.
     * 
     * @var WebSvnConfig
     * 
     * @access public
     */
    public $config;
    
    /**
     * SVNLook.
     * 
     * @var SVNLook
     * 
     * @access public
     */
    public $svnLook;
    
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
     * @param Repository $repConfig
     * 
     * @access public
     */
    public function __construct(Setup $setup)
    {
        parent::__construct();
        
        $this->repConfig = $setup->rep;
        $this->config    = $setup->config;
        $this->svnLook   = new SVNLook($setup);
        $this->setup     = $setup;
        
    }//end __construct()
    
    
    /**
     * Distill line-spanning syntax highlighting so that each line can stand alone.
     * (when invoking on the first line, $attributes should be an empty array).
     * Invoked to make sure all open syntax highlighting tags (<font>, <i>, <b>, etc.).
     * 
     * @param string  $line
     * @param array   $attributes
     * 
     * @return mixed
     * 
     * @access public
     */
    public function highlightLine($line, array &$attributes)
    {
        $hline = '';
        
        // Apply any highlighting in effect from the previous line.
        foreach ($attributes as $attr) {
            $hline .= $attr['text'];
        }//end foreach
        
        // Append the new line.
        $hline .= $line;
        
        // Update attributes.
        for ($line = strstr($line, '<'); $line; $line = strstr(substr($line, 1), '<')) {
            
            if (substr($line, 1, 1) === '/') {
                
                // If this closes a tag, remove most recent corresponding opener.
                $tagNamLen = strcspn($line, '> '."\t", 2);
                $tagNam    = substr($line, 2, $tagNamLen);
                
                foreach (array_reverse(array_keys($attributes)) as $k) {
                    if ($attributes[$k]['tag'] === $tagNam) {
                        unset($attributes[$k]);
                        break;
                    }//end if
                }//end foreach
                
            } else {
                
                // If this opens a tag, add it to the list.
                $tagNamLen    = strcspn($line, '> '."\t", 1);
                $tagNam       = substr($line, 1, $tagNamLen);
                $tagLen       = strcspn($line, '>') + 1;
                $attributes[] = array('tag' => $tagNam, 'text' => substr($line, 0, $tagLen));
                
            }//end if
            
        }//end for
        
        // Close any still-open tags.
        foreach (array_reverse($attributes) as $attr) {
            $hline .= '</'.$attr['tag'].'>';
        }//end foreach
        
        return str_replace('[', '&#91;', str_replace(']', '&#93;', $hline));
        
    }//end highlightLine()
    
    
    /**
     * Private function to simplify creation of common SVN command string text.
     * 
     * @param string  $command
     * @param string  $path
     * @param integer $rev
     * @param string  $peg
     * 
     * @return string
     * 
     * @access private
     */
    private function svnCommandString($command, $path, $rev, $peg)
    {
        return $this->config->getSvnCommand().$this->repConfig->svnCredentials().' '.$command.' '.($rev ? '-r '.$rev.' ' : '').quote($this->svnLook->encodePath($this->getSvnPath($path)).'@'.($peg ? $peg : ''));
        
    }//end svnCommandString()
    
    
    /**
     * Private function to simplify creation of enscript command string text.
     * 
     * @param string $path
     * 
     * @return string
     * 
     * @access private
     */
    private function enscriptCommandString($path)
    {
        
        $filename = basename($path);
        $ext = strrchr($path, '.');
        
        $lang = false;
        
        if (array_key_exists($filename, $this->setup->extEnscript)) {
            $lang = $this->setup->extEnscript[$filename];
        } else if (array_key_exists($ext, $this->setup->extEnscript)) {
            $lang = $this->setup->extEnscript[$ext];
        }//end if
        
        $cmd = $this->setup->config->enscript.' --language=html';
        
        if ($lang !== false) {
            $cmd .= ' --color --'.(!$this->setup->config->getUseEnscriptBefore_1_6_3() ? 'highlight' : 'pretty-print').'='.$lang;
        }//end if
        
        $cmd .= ' -o -';
        
        return $cmd;
        
    }//end enscriptCommandString()
    
    
    /**
     * Dump the content of a file to the given filename.
     * 
     * @param string $path
     * @param string $filename
     * @param number $rev
     * @param string $peg
     * @param string $pipe
     * @param string $highlight
     * 
     * @return boolean
     * 
     * @access public
     */
    public function getFileContents($path, $filename, $rev = 0, $peg = '', $pipe = '', $highlight = 'file')
    {
        
        assert ($highlight === 'file' || $highlight === 'no' || $highlight === 'line');
        
        $highlighted = false;
        
        // If there's no filename, just deliver the contents as-is to the use.r
        if ($filename === '') {
            
            $cmd = $this->svnCommandString('cat', $path, $rev, $peg);
            passthruCommand($cmd.' '.$pipe);
            
            return $highlighted;
            
        }//end if
        
        // Get the file contents info.
        $tempname = $filename;
        if ($highlight === 'line') {
            $tempname = $this->setup->utils->tempnamWithCheck($this->setup->config->getTempDir(), '');
        }//end if
        
        $highlighted = true;
        $geshiLang = $this->highlightLanguageUsingGeshi($path);
        
        if ($highlight !== 'no' && $this->config->useGeshi && !empty($geshiLang)) {
            
            $this->applyGeshi($path, $tempname, $geshiLang, $rev, $peg);
            
        } else if ($highlight !== 'no' && $this->setup->config->useEnscript) {
            
            // Get the files, feed it through enscript, then remove the enscript headers using sed.
            // Note that the sed command returns only the part of the file between <PRE> and </PRE>.
            // It's complicated because it's designed not to return those lines themselves.
            $cmd = $this->svnCommandString('cat', $path, $rev, $peg);
            $cmd = quoteCommand($cmd.' | '.$this->enscriptCommandString($path).' | '.$config->sed.' -n '.$this->setup->config->quote.'1,/^<PRE.$/!{/^<\\/PRE.$/,/^<PRE.$/!p;}'.$this->setup->config->quote.' > '.$tempname);
            
        } else {
            
            $highlighted = false;
            $cmd = $this->svnCommandString('cat', $path, $rev, $peg);
            $cmd = quoteCommand($cmd.' > '.quote($filename));
            
        }//end if
        
        if (isset($cmd)) {
            
            // Stderr.
            $descriptorspec = array(2 => array('pipe', 'w'));
            $resource = proc_open($cmd, $descriptorspec, $pipes);
            $error = '';
            
            while (!feof($pipes[2])) {
                $error .= fgets($pipes[2]);
            }//end while
            
            $error = trim($error);
            fclose($pipes[2]);
            proc_close($resource);
            
            if (!empty($error)) {
                error_log($this->setup->lang['BADCMD'].': '.$cmd);
                error_log($error);
                $this->setup->vars['warning'] = nl2br(escape(toOutputEncoding($error)));
            }//end if
            
        }//end if
        
        if ($highlighted && $highlight === 'line') {
            
            // If we need each line independently highlighted (e.g. for diff or blame).
            // then we'll need to filter the output of the highlighter.
            // to make sure tags like <font>, <i> or <b> don't span lines.
            $dst = fopen($filename, 'w');
            
            if ($dst) {
                
                $content = file_get_contents($tempname);
                $content = explode('<br />', $content);
                
                // $attributes is used to remember what highlighting attributes
                // are in effect from one line to the next
                $attributes = array(); // start with no attributes in effect
                
                foreach ($content as $line) {
                    fputs($dst, $this->highlightLine(trim($line), $attributes)."\n");
                }//end foreach
                
                fclose($dst);
                
            }//end if
            
        }//end if
        
        if ($tempname != $filename) {
            @unlink($tempname);
        }//end if
        
        return $highlighted;
        
    }//end getFileContents()
    
    
    /**
     * Check if geshi can highlight the given extension and return the language.
     * 
     * @param string $path
     * 
     * @return string
     * 
     * @access public
     */
    public function highlightLanguageUsingGeshi($path)
    {
        
        $filename = basename($path);
        $ext      = strrchr($path, '.');
        
        if (substr($ext, 0, 1) === '.') {
            $ext = substr($ext, 1);
        }//end if
        
        foreach ($this->setup->extGeshi as $language => $extensions) {
            
            if (in_array($filename, $extensions) || in_array($ext, $extensions)) {
                
                if ($this->geshi === null) {
                    $this->geshi = new \GeSHi();
                }//end if
                
                $this->geshi->set_language($language);
                
                if ($this->geshi->error() === false) {
                    return $language;
                }//end if
                
            }//end if
            
        }//end foreach
        
        return '';
        
    }//end highlightLanguageUsingGeshi()
    
    
    
    /**
     * Perform syntax highlighting using geshi.
     * 
     * @param string $path
     * @param string $filename
     * @param string $language
     * @param string $rev
     * @param string $peg
     * @param string $return
     * 
     * @return void
     * 
     * @access public
     */
    public function applyGeshi($path, $filename, $language, $rev, $peg = '', $return = false)
    {
        
        // Output the file to the filename.
        $cmd            = quoteCommand($this->svnCommandString('cat', $path, $rev, $peg).' > '.quote($filename));
        $descriptorspec = array(2 => array('pipe', 'w')); // stderr
        $resource       = proc_open($cmd, $descriptorspec, $pipes);
        
        $error = '';
        while (!feof($pipes[2])) {
            $error .= fgets($pipes[2]);
        }//end while
        
        $error = trim($error);
        fclose($pipes[2]);
        proc_close($resource);
        
        if (!empty($error)) {
            error_log($this->setup->lang['BADCMD'].': '.$cmd);
            error_log($error);
            $this->setup->vars['warning'] = 'Unable to cat file: '.nl2br(escape(toOutputEncoding($error)));
            return;
        }//end if
        
        $source = file_get_contents($filename);
        if ($this->geshi === null) {
            $this->geshi = new \GeSHi();
        }//end if
        
        $this->geshi->set_source($source);
        $this->geshi->set_language($language);
        $this->geshi->set_header_type(GESHI_HEADER_NONE);
        $this->geshi->set_overall_class('geshi');
        $this->geshi->set_tab_width($this->repConfig->getExpandTabsBy());
        
        if ($return) {
            return $this->geshi->parse_code();
        } else {
            $f = @fopen($filename, 'w');
            fwrite($f, $this->geshi->parse_code());
            fclose($f);
        }//end if
        
    }//end applyGeshi()
    
    
    /**
     * Print the contents of a file without filling up Apache's memory.
     * 
     * @param unknown $path
     * @param number $rev
     * @param string $peg
     * 
     * @return void
     * 
     * @access public
     */
    public function listFileContents($path, $rev = 0, $peg = '')
    {
        
        $geshiLang = $this->highlightLanguageUsingGeshi($path);
        
        if ($this->setup->config->useGeshi && !empty($geshiLang)) {
            
            $tempname = $this->setup->utils->tempnamWithCheck($this->setup->config->getTempDir(), 'wsvn');
            
            if ($tempname !== false) {
                print toOutputEncoding($this->applyGeshi($path, $tempname, $geshiLang, $rev, $peg, true));
                @unlink($tempname);
            }//end if
            
        } else {
            
            $pre = false;
            $cmd = $this->svnCommandString('cat', $path, $rev, $peg);
            
            if ($this->setup->config->useEnscript) {
                
                $cmd .= ' | '.$this->enscriptCommandString($path).' | ';
                $cmd .= $this->setup->config->sed.' -n '.$this->setup->config->quote;
                $cmd .= '/^<PRE.$/,/^<\\/PRE.$/p'.$this->setup->config->quote;
                
            } else {
                
                $pre = true;
                
            }//end if
            
            if ($result = popenCommand($cmd, 'r')) {
                
                if ($pre) {
                    echo '<pre>';
                }//end if
                    
                while (!feof($result)) {
                    
                    $line = fgets($result, 1024);
                    $line = toOutputEncoding($line);
                    
                    if ($pre) {
                        $line = escape($line);
                    }//end if
                    
                    print$this->setup->utils->hardspace($line, $this->repConfig);
                    
                }//end if
                
                if ($pre) {
                    echo '</pre>';
                }//end if
                    
                pclose($result);
                
            }//end if
            
        }//end if
        
    }//end listFileContents()
    
    
    /**
     * Dump the blame content of a file to the given filename.
     * 
     * @param string $path
     * @param string $filename
     * @param number $rev
     * @param string $peg
     * 
     * @return void
     * 
     * @access public
     */
    public function getBlameDetails($path, $filename, $rev = 0, $peg = '')
    {
        
        $cmd            = quoteCommand($this->svnCommandString('blame', $path, $rev, $peg).' > '.quote($filename));
        $descriptorspec = array(2 => array('pipe', 'w')); // stderr
        $resource       = proc_open($cmd, $descriptorspec, $pipes);
        
        $error = '';
        while (!feof($pipes[2])) {
            $error .= fgets($pipes[2]);
        }//end while
        
        $error = trim($error);
        fclose($pipes[2]);
        proc_close($resource);
        
        if (!empty($error)) {
            error_log($this->setup->lang['BADCMD'].': '.$cmd);
            error_log($error);
            $this->setup->vars['warning'] = 'No blame info: '.nl2br(escape(toOutputEncoding($error)));
        }//end if
        
    }//end getBlameDetails()
    
    
    /**
     * Get properties.
     * 
     * @param string $path
     * @param number $rev
     * @param string $peg
     * 
     * @return string[]
     * 
     * @access public
     */
    public function getProperties($path, $rev = 0, $peg = '')
    {
        
        $cmd        = $this->svnCommandString('proplist', $path, $rev, $peg);
        $ret        = runCommand($cmd, $this->setup->lang, true);
        $properties = array();
        
        if (is_array($ret)) {
            foreach ($ret as $line) {
                if (substr($line, 0, 1) === ' ') {
                    $properties[] = ltrim($line);
                }//end if
            }//end foreach
        }//end if
        
        return $properties;
        
    }//end getProperties()
    
    
    /**
     * Get property.
     * 
     * @param string $path
     * @param string $property
     * @param number $rev
     * @param string $peg
     * 
     * @return string
     * 
     * @access public
     */
    public function getProperty($path, $property, $rev = 0, $peg = '')
    {
        
        $cmd = $this->svnCommandString('propget '.$property, $path, $rev, $peg);
        $ret = runCommand($cmd, $this->setup->lang, true);
        
        // Remove the surplus newline.
        if (count($ret)) {
            unset($ret[count($ret) - 1]);
        }//end if
        
        return implode("\n", $ret);
        
    }//end getProperty()
    
    
    /**
     * Exports the directory to the given location.
     * 
     * @param string $path
     * @param string $filename
     * @param number $rev
     * @param string $peg
     * 
     * @return number
     * 
     * @access public
     */
    public function exportRepositoryPath($path, $filename, $rev = 0, $peg = '')
    {
        
        $cmd     = $this->svnCommandString('export', $path, $rev, $peg).' '.quote($filename);
        $retcode = 0;
        
        execCommand($cmd, $retcode);
        
        if ($retcode !== 0) {
            error_log($this->setup->lang['BADCMD'].': '.$cmd);
        }//end if
        
        return $retcode;
        
    }//end exportRepositoryPath()
    
    
    /**
     * Get info.
     * 
     * @param string $path
     * @param number $rev
     * @param string $peg
     * 
     * @return NULL|SVNInfoEntry
     * 
     * @access public
     */
    public function getInfo($path, $rev = 0, $peg = '')
    {
        
        $xml_parser = xml_parser_create('UTF-8');
        xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
        xml_set_element_handler($xml_parser, array($this->svnLook, 'infoStartElement'), array($this->svnLook, 'infoEndElement'));
        xml_set_character_data_handler($xml_parser, array($this->svnLook, 'infoCharacterData'));
        
        // Since directories returned by svn log don't have trailing slashes (:-(), we need to remove.
        // The trailing slash from the path for comparison purposes.
        if ($path{strlen($path) - 1} === '/' && $path !== '/') {
            $path = substr($path, 0, -1);
        }//end if
        
        $this->svnLook->curInfo = new SVNInfoEntry;
        
        // Get the SVN info.
        if ($rev === 0) {
            $headlog = $this->getLog('/', '', '', true, 1);
            if ($headlog && isset($headlog->entries[0])) {
                $rev = $headlog->entries[0]->rev;
            }//end if
        }//end if
        
        $cmd            = quoteCommand($this->svnCommandString('info --xml', $path, $rev, $peg));
        $descriptorspec = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
        $resource       = proc_open($cmd, $descriptorspec, $pipes);
        
        if (!is_resource($resource)) {
            
            $errorMsg = $this->setup->lang['BADCMD'].': <code>'.escape(stripCredentialsFromCommand($cmd)).'</code>';
            
            // Logger.
            $this->logger->error($errorMsg);
            
            echo $errorMsg;
            exit;
            
        }//end if
        
        $handle    = $pipes[1];
        
        while (!feof($handle)) {
            
            $line = fgets($handle);
            
            if (!xml_parse($xml_parser, $line, feof($handle))) {
                
                $errorMsg = sprintf(
                        'XML error: %s (%d) at line %d column %d byte %d'."\n".'cmd: %s',
                        xml_error_string(xml_get_error_code($xml_parser)),
                        xml_get_error_code($xml_parser),
                        xml_get_current_line_number($xml_parser),
                        xml_get_current_column_number($xml_parser),
                        xml_get_current_byte_index($xml_parser),
                        $cmd);
                
                if (xml_get_error_code($xml_parser) !== 5) {
                    // Logger error. Errors can contain sensitive info! don't echo this ~J.
                    $this->logger->error($errorMsg);
                    exit;
                } else {
                    break;
                }//end if
                
            }//end if
        
        }//end while
        
        $error = '';
        while (!feof($pipes[2])) {
            $error .= fgets($pipes[2]);
        }//end while
        
        $error = toOutputEncoding(trim($error));
        
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        
        proc_close($resource);
        xml_parser_free($xml_parser);
        
        if (!empty($error)) {
            
            $error = toOutputEncoding(nl2br(str_replace('svn: ', '', $error)));
            error_log($this->setup->lang['BADCMD'].': '.$cmd);
            error_log($error);
            
            if (strstr($error, 'found format')) {
                $this->setup->vars['error'] = 'Repository uses a newer format than Subversion '.$this->config->getSubversionVersion().' can read. ("'.nl2br(escape(toOutputEncoding(substr($error, strrpos($error, 'Expected'))))).'.")';
            } else if (strstr($error, 'No such revision')) {
                $this->setup->vars['warning'] = 'Revision '.escape($rev).' of this resource does not exist.';
            } else {
                $this->setup->vars['error'] = $this->setup->lang['BADCMD'].': <code>'.escape(stripCredentialsFromCommand($cmd)).'</code><br />'.nl2br(escape(toOutputEncoding($error)));
            }//end if
            
            return null;
            
        }//end if
        
        if ($this->repConfig->subpath !== null) {
            
            if (substr($this->svnLook->curInfo->path, 0, strlen($this->repConfig->subpath) + 1) === '/'. $this->repConfig->subpath) {
                $this->svnLook->curInfo->path = substr($this->svnLook->curInfo->path, strlen($this->repConfig->subpath) + 1);
            } else {
                $this->setup->vars['error'] = 'Info entry does not start with subpath for repository with subpath';
                return null;
            }//end if
            
        }//end if
        
        return $this->svnLook->curInfo;
        
    }//end getInfo()
    
    
    /**
     * Get list.
     * 
     * @param string $path
     * @param number $rev
     * @param string $peg
     * 
     * @return SVNList
     * 
     * @access public
     */
    public function getList($path, $rev = 0, $peg = '')
    {
        
        $xml_parser = xml_parser_create('UTF-8');
        xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
        xml_set_element_handler($xml_parser, array($this->svnLook, 'listStartElement'), array($this->svnLook, 'listEndElement'));
        xml_set_character_data_handler($xml_parser, array($this->svnLook, 'listCharacterData'));
        
        // Since directories returned by svn log don't have trailing slashes (:-(), we need to remove.
        // the trailing slash from the path for comparison purposes.
        if ($path{strlen($path) - 1} === '/' && $path !== '/') {
            $path = substr($path, 0, -1);
        }
        
        $this->svnLook->curList          = new SVNList;
        $this->svnLook->curList->entries = array();
        $this->svnLook->curList->path    = $path;
        
        // Get the list info.
        if ($rev === 0) {
            $headlog = $this->getLog('/', '', '', true, 1);
            if ($headlog && isset($headlog->entries[0])) {
                $rev = $headlog->entries[0]->rev;
            }//end if
        }//end if
        
        $cmd            = quoteCommand($this->svnCommandString('list --xml', $path, $rev, $peg));
        $descriptorspec = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
        $resource       = proc_open($cmd, $descriptorspec, $pipes);
        
        if (!is_resource($resource)) {
            // Logger.
            $this->logger->error('Error running command: '.escape(stripCredentialsFromCommand($cmd)));
            echo $this->setup->lang['BADCMD'].': <code>'.escape(stripCredentialsFromCommand($cmd)).'</code>';
            exit;
        }//end if
        
        $handle = $pipes[1];
        
        while (!feof($handle)) {
            
            $line = fgets($handle);
            
            if (!xml_parse($xml_parser, $line, feof($handle))) {
                
                $errorMsg = sprintf(
                        'XML error: %s (%d) at line %d column %d byte %d'."\n".'cmd: %s',
                        xml_error_string(xml_get_error_code($xml_parser)),
                        xml_get_error_code($xml_parser),
                        xml_get_current_line_number($xml_parser),
                        xml_get_current_column_number($xml_parser),
                        xml_get_current_byte_index($xml_parser),
                        $cmd);
                
                if (xml_get_error_code($xml_parser) != 5) {

                    // Logger. Errors can contain sensitive info! don't echo this ~J.
                    $this->logger->error($errorMsg);
                    exit;
                    
                } else {
                    break;
                }//end if
                
            }//end if
            
        }//end while
        
        $error = '';
        while (!feof($pipes[2])) {
            $error .= fgets($pipes[2]);
        }//end while
        
        $error = toOutputEncoding(trim($error));
        
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        
        proc_close($resource);
        xml_parser_free($xml_parser);
        
        if (!empty($error)) {
            
            $error = toOutputEncoding(nl2br(str_replace('svn: ', '', $error)));
            error_log($this->setup->lang['BADCMD'].': '.$cmd);
            error_log($error);
            
            if (strstr($error, 'found format')) {
                $this->setup->vars['error'] = 'Repository uses a newer format than Subversion '.$this->setup->config->getSubversionVersion().' can read. ("'.nl2br(escape(toOutputEncoding(substr($error, strrpos($error, 'Expected'))))).'.")';
            } else if (strstr($error, 'No such revision')) {
                $this->setup->vars['warning'] = 'Revision '.escape($rev).' of this resource does not exist.';
            } else {
                $this->setup->vars['error'] = $this->setup->lang['BADCMD'].': <code>'.escape(stripCredentialsFromCommand($cmd)).'</code><br />'.nl2br(escape(toOutputEncoding($error)));
            }//end if
            
            return null;
            
        }//end if
        
        // Sort the entries into alphabetical order.
        usort($this->svnLook->curList->entries, array($this->svnLook, '_listSort'));
        
        return $this->svnLook->curList;
        
    }//end getList()
    
    
    /**
     * Get log.
     * 
     * @param string $path
     * @param string $brev
     * @param number $erev
     * @param string $quiet
     * @param number $limit
     * @param string $peg
     * 
     * @return SVNLog
     * 
     * @access public
     */
    public function getLog($path, $brev = '', $erev = 1, $quiet = false, $limit = 2, $peg = '')
    {
        
        $xml_parser = xml_parser_create('UTF-8');
        xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
        xml_set_element_handler($xml_parser, array($this->svnLook, 'logStartElement'), array($this->svnLook, 'logEndElement'));
        xml_set_character_data_handler($xml_parser, array($this->svnLook, 'logCharacterData'));
        
        // Since directories returned by svn log don't have trailing slashes (:-(),
        // we must remove the trailing slash from the path for comparison purposes.
        if ($path !== '/' && $path{strlen($path) - 1} == '/') {
            $path = substr($path, 0, -1);
        }//end if
        
        $this->svnLook->curLog          = new SVNLog;
        $this->svnLook->curLog->entries = array();
        $this->svnLook->curLog->path    = $path;
        
        // Get the log info.
        $effectiveRev = ($brev && $erev ? $brev.':'.$erev : ($brev ? $brev.':1' : ''));
        $effectivePeg = ($peg ? $peg : ($brev ? $brev : ''));
        $cmd          = quoteCommand($this->svnCommandString(
                'log --xml '.($quiet ? '--quiet' : '--verbose'), 
                $path, $effectiveRev, 
                $effectivePeg));
        
        if (($this->config->subversionMajorVersion > 1 || $this->config->subversionMinorVersion >= 2) && $limit !== 0) {
            $cmd .= ' --limit '.$limit;
        }//end if
        
        $descriptorspec = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
        $resource       = proc_open($cmd, $descriptorspec, $pipes);
        
        if (!is_resource($resource)) {
            
            // Logger.
            $this->logger->error('Error running comand: '.escape(stripCredentialsFromCommand($cmd)));
            
            echo $this->setup->lang['BADCMD'].': <code>'.escape(stripCredentialsFromCommand($cmd)).'</code>';
            exit;
            
        }//end if
        
        $handle = $pipes[1];
        
        while (!feof($handle)) {
            
            $line = fgets($handle);
            
            if (!xml_parse($xml_parser, $line, feof($handle))) {
                
                $errorMsg = sprintf('XML error: %s (%d) at line %d column %d byte %d'."\n".'cmd: %s',
                        xml_error_string(xml_get_error_code($xml_parser)),
                        xml_get_error_code($xml_parser),
                        xml_get_current_line_number($xml_parser),
                        xml_get_current_column_number($xml_parser),
                        xml_get_current_byte_index($xml_parser),
                        $cmd);
                
                if (xml_get_error_code($xml_parser) !== 5) {
                    // Logger. Errors can contain sensitive info! don't echo this ~J.
                    $this->logger->error($errorMsg);
                    exit;
                } else {
                    break;
                }//end if
                
            }//end if
            
        }//end while
        
        $error = '';
        while (!feof($pipes[2])) {
            $error .= fgets($pipes[2]);
        }//end while
        
        $error = trim($error);
        
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        
        proc_close($resource);
        
        if (!empty($error)) {
            
            error_log($this->setup->lang['BADCMD'].': '.$cmd);
            error_log($error);
            
            if (strstr($error, 'found format')) {
                $this->setup->vars['error'] = 'Repository uses a newer format than Subversion '.$this->config->getSubversionVersion().' can read. ("'.nl2br(escape(toOutputEncoding(substr($error, strrpos($error, 'Expected'))))).'.")';
            } else if (strstr($error, 'No such revision')) {
                $this->setup->vars['warning'] = 'Revision '.escape($brev).' of this resource does not exist.';
            } else {
                $this->setup->vars['error'] = $this->setup->lang['BADCMD'].': <code>'.escape(stripCredentialsFromCommand($cmd)).'</code><br />'.nl2br(escape(toOutputEncoding($error)));
            }//end if
            
            return null;
            
        }//end if
        
        xml_parser_free($xml_parser);
        
        foreach ($this->svnLook->curLog->entries as $entryKey => $entry) {
            
            $fullModAccess = true;
            $anyModAccess  = (count($entry->mods) == 0);
            $precisePath   = null;
            
            foreach ($entry->mods as $modKey => $mod) {
                
                $access = $this->repConfig->hasReadAccess($mod->path);
                
                if ($access) {
                    
                    $anyModAccess = true;
                    
                    // Find path which is parent of all modification but more precise than $curLogEntry->path.
                    $modpath = $mod->path;
                    
                    if (!$mod->isdir || $mod->action === 'D') {
                        $pos     = strrpos($modpath, '/');
                        $modpath = substr($modpath, 0, $pos + 1);
                    }//end if
                    
                    if (strlen($modpath) === 0 || substr($modpath, -1) !== '/') {
                        $modpath .= '/';
                    }//end if
                    
                    // Compare with current precise path.
                    if ($precisePath === null) {
                        $precisePath = $modpath;
                    } else {
                        $equalPart = $this->svnLook->_equalPart($precisePath, $modpath);
                        if (substr($equalPart, -1) !== '/') {
                            $pos = strrpos($equalPart, '/');
                            $equalPart = substr($equalPart, 0, $pos + 1);
                        }
                        $precisePath = $equalPart;
                    }//end if
                    
                } else {
                    
                    // Hide modified entry when access is prohibited.
                    unset($this->svnLook->curLog->entries[$entryKey]->mods[$modKey]);
                    $fullModAccess = false;
                    
                }//end if
                
                // Fix paths if command was for a subpath repository.
                if ($this->repConfig->subpath !== null) {
                    
                    if (substr($mod->path, 0, strlen($this->repConfig->subpath) + 1) === '/'. $this->repConfig->subpath) {
                        $this->svnLook->curLog->entries[$entryKey]->mods[$modKey]->path = substr($mod->path, strlen($this->repConfig->subpath) + 1);
                    } else {
                        $this->setup->vars['error'] = 'Log entries do not start with subpath for repository with subpath';
                        return null;
                    }//end if
                    
                }//end if
                
            }//end foreach
            
            if (!$fullModAccess) {
                // Hide commit message when access to any of the entries is prohibited.
                $this->svnLook->curLog->entries[$entryKey]->msg = '';
            }//end if
            
            if (!$anyModAccess) {
                // Hide author and date when access to all of the entries is prohibited.
                $this->svnLook->curLog->entries[$entryKey]->author     = '';
                $this->svnLook->curLog->entries[$entryKey]->date       = '';
                $this->svnLook->curLog->entries[$entryKey]->committime = '';
                $this->svnLook->curLog->entries[$entryKey]->age        = '';
            }//end if
            
            if ($precisePath !== null) {
                $this->svnLook->curLog->entries[$entryKey]->precisePath = $precisePath;
            } else {
                $this->svnLook->curLog->entries[$entryKey]->precisePath = $this->svnLook->curLog->entries[$entryKey]->path;
            }//end if
            
        }//end foreach
        
        return $this->svnLook->curLog;
        
    }//end getLog()
    
    
    /**
     * Is file.
     * 
     * @param string $path
     * @param number $rev
     * @param string $peg
     * 
     * @return boolean
     * 
     * @access public
     */
    public function isFile($path, $rev = 0, $peg = '')
    {
        
        $cmd = $this->svnCommandString('info --xml', $path, $rev, $peg);
        
        return strpos(implode(' ', runCommand($cmd, $this->setup->lang, true)), 'kind="file"') !== false;
        
    }//end isFile()
    
    
    /**
     * Get SVN path.
     * 
     * @param string $path
     * 
     * @return string
     * 
     * @access public
     */
    public function getSvnPath($path)
    {
        if ($this->repConfig->subpath === null) {
            return $this->repConfig->path.$path;
        } else {
            return $this->repConfig->path.'/'.$this->repConfig->subpath.$path;
        }//end if
        
    }//end getSvnPath()
    
    
}//end SVNRepository class.

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
use app\business\setup\Setup;
use app\business\setup\WebSvnCons;
use xsgaphp\XsgaAbstractClass;
use xsgaphp\exceptions\XsgaException;

/**
 * Utils class
 */
class Utils extends XsgaAbstractClass
{
    
    
    /**
     * Create path links.
     * 
     * @param Setup  $config Setup.
     * @param string $path   Path.
     * 
     * @return string
     * 
     * @access public
     */
    public function createPathLinks(Setup $setup, $path)
    {
        
        $pathComponents = explode('/', escape($path));
        $count          = count($pathComponents);
        
        // The number of links depends on the last item. It's empty if we're looking at a directory.
        // And non-empty if we're looking at a file.
        if (empty($pathComponents[$count - 1])) {
            $limit = $count - 2;
            $dir   = true;
        } else {
            $limit = $count - 1;
            $dir   = false;
        }//end if
        
        $passRevString = $this->createRevAndPegString($setup->rev, $setup->peg);
        
        $pathSoFar    = '/';
        $pathSoFarURL = $setup->config->getURL($setup->rep, $pathSoFar, 'dir').$passRevString;
        $out          = '<a href="'.$pathSoFarURL.'" class="root"><span>(root)</span></a>/';
        
        for ($n = 1; $n < $limit; $n++) {
            $pathSoFar .= html_entity_decode($pathComponents[$n]).'/';
            $pathSoFarURL = $setup->config->getURL($setup->rep, $pathSoFar, 'dir').$passRevString;
            $out .= '<a href="'.$pathSoFarURL.'#'.$this->anchorForPath($pathSoFar, $setup->config->treeView).'">'.$pathComponents[$n].'</a>/';
        }//end for
        
        if (!empty($pathComponents[$n])) {
            
            $pegrev = ($setup->peg && $setup->peg !== $setup->rev) ? ' <a class="peg" href="'.'?'.escape(str_replace('&peg='.$setup->peg, '', $_SERVER['QUERY_STRING'])).'">@ '.$setup->peg.'</a>' : '';
            
            if ($dir) {
                $out .= '<span class="dir">'.$pathComponents[$n].'/'.$pegrev.'</span>';
            } else {
                $out .= '<span class="file">'.$pathComponents[$n].$pegrev.'</span>';
            }//end if
            
        }//end if
        
        return $out;
        
    }//end createPathLinks()
    
    
    /**
     * Create rev and peg string.
     * 
     * @param integer $rev
     * @param integer $peg
     * 
     * @return string
     * 
     * @access public
     */
    public function createRevAndPegString($rev, $peg)
    {
        $params = array();
        
        if ($rev) {
            $params[] = 'rev='.$rev;
        }//end if
        
        if ($peg) {
            $params[] = 'peg='.$peg;
        }//end if
        
        return implode(WebSvnCons::ANDAMP, $params);
        
    }//end createRevAndPegString()
    
    
    /**
     * Create different rev and peg string.
     * 
     * @param integer $rev
     * @param integer $peg
     * 
     * @return string
     * 
     * @access public
     */
    public function createDifferentRevAndPegString($rev, $peg)
    {
        $params = array();
        
        if ($rev && (!$peg || $rev != $peg)) {
            $params[] = 'rev='.$rev;
        }//end if
        
        if ($peg) {
            $params[] = 'peg='.$peg;
        }//end if
        
        return implode(WebSvnCons::ANDAMP, $params);
        
    }//end createDifferentRevAndPegString()
    
    
    /**
     * Anchor for path.
     * 
     * @param string  $path
     * @param boolean $treeView
     * 
     * @return string
     * 
     * @access public
     */
    public function anchorForPath($path, $treeView)
    {
        // (X)HMTL id/name attribute must be this format: [A-Za-z][A-Za-z0-9-_.:]*
        // MD5 hashes are 32 characters, deterministic, quite collision-resistant,
        // and work for any string, regardless of encoding or special characters.
        if ($treeView) {
            return 'a'.md5($path);
        }//end if
        
        return '';
        
    }//end anchorForPath()
    
    
    /**
     * Create anchor.
     * 
     * @param string $text
     * 
     * @return string|array
     * 
     * @access public
     */
    public function createAnchors($text)
    {
        $ret = $text;
        
        // Match correctly formed URLs that aren't already links.
        $ret = preg_replace('#\b(?<!href=")([a-z]+?)://(\S*)([\w/]+)#i',
                '<a href="\\1://\\2\\3" target="_blank">\\1://\\2\\3</a>',
                $ret);
        
        // Now match anything beginning with www, as long as it's not //www since they were matched above.
        $ret = preg_replace('#\b(?<!//)www\.(\S*)([\w/]+)#i',
                '<a href="http://www.\\1\\2" target="_blank">www.\\1\\2</a>',
                $ret);
        
        // Match email addresses.
        $ret = preg_replace('#\b([\w\-_.]+)@([\w\-.]+)\.(\w{2,})\b#i',
                '<a href="mailto:\\1@\\2.\\3">\\1@\\2.\\3</a>',
                $ret);
        
        return $ret;
        
    }//end createAnchors()
    
    
    /**
     * Get full URL.
     * 
     * @param string $loc
     * 
     * @return string
     * 
     * @access public
     */
    public function getFullURL($loc)
    {
        $protocol = 'http';
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $protocol = $_SERVER['HTTP_X_FORWARDED_PROTO'];
        } else if (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) !== 'off')) {
            $protocol = 'https';
        }//end if
        
        $port = ':'.$_SERVER['SERVER_PORT'];
        if ((':80' === $port && 'http' === $protocol) || (':443' === $port && 'https' === $protocol)) {
            $port = '';
        }//end if
        
        if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
        } else if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        } else if (isset($_SERVER['SERVER_NAME'])) {
            $host = $_SERVER['SERVER_NAME'].$port;
        } else if (isset($_SERVER['SERVER_ADDR'])) {
            $host = $_SERVER['SERVER_ADDR'].$port;
        } else {
            
            // Error message.
            $errorMsg = 'Unable to redirect';
            
            // Logger.
            $this->logger->error($errorMsg);
            
            throw new XsgaException($errorMsg);
            
        }//end if
        
        // Make sure we have a directory to go to.
        if (empty($loc)) {
            $loc = '/';
        } else if ($loc[0] !== '/') {
            $loc = '/'.$loc;
        }//end if
        
        return $protocol . '://' . $host . $loc;
        
    }//end getFullURL()
    
    
    /**
     * XML entities.
     * 
     * @param string $str
     * 
     * @return array
     * 
     * @access public
     */
    public function xmlEntities($str)
    {
        
        $entities = array();
        
        $entities['&']  = WebSvnCons::ANDAMP;
        $entities['<']  = '&lt;';
        $entities['>']  = '&gt;';
        $entities['"']  = '&quot;';
        $entities['\''] = '&apos;';
        
        return str_replace(array_keys($entities), array_values($entities), $str);
        
    }//end xmlentities()
    
    
    /**
     * Hardspace.
     * 
     * @param string     $s
     * @param Repositori $rep
     * 
     * @return string
     * 
     * @access public
     */
    public function hardspace($s, Repository $rep)
    {
        return '<code>'.$this->expandTabs($rep, $s).'</code>';
        
    }//end hardspace()
    
    
    /**
     * Wrap in code tag if it's necessary.
     * 
     * @param string $string
     * @param string $geshi
     * 
     * @return string
     */
    public function wrapInCodeTagIfNecessary($string, $geshi)
    {
        return ($geshi) ? $string : '<code>'.$string.'</code>';
        
    }//end wrapInCodeTagIfNecessary()
    
    
    /**
     * Expands the tabs in a line that may or may not include HTML.
     * Enscript generates code with HTML, so we need to take that into account.
     * 
     * @param Repository $rep      Repository.
     * @param string     $s        Line of possibly HTML-encoded text to expand
     * @param int        $tabwidth Tab width, -1 to use repository's default, 0 to collapse all tabs.
     * 
     * @return string The expanded line.
     * 
     * @access public
     */
    public function expandTabs(Repository $rep, $s, $tabwidth = - 1)
    {
        if ($tabwidth === -1) {
            $tabwidth = $rep->getExpandTabsBy();
        }
        $pos = 0;
        
        // Parse the string into chunks that are either 1 of: HTML tag, tab char, run of any other stuff.
        $chunks = preg_split('/((?:<.+?>)|(?:&.+?;)|(?:\t))/', $s, -1, PREG_SPLIT_DELIM_CAPTURE);
        
        // Count the sizes of the chunks and replace tabs as we go.
        $chunkscount = count($chunks);
        for ($i = 0; $i < $chunkscount; $i++) {
            
            // Make sure we're not dealing with an empty string.
            if (empty($chunks[$i])) {
                continue;
            }//end if
            
            switch ($chunks[$i][0]) {
                case '<':
                    // HTML tag: ignore its width by doing nothing.
                    break;
                    
                case '&':
                    // HTML entity: count its width as 1 char.
                    $pos++;
                    break;
                    
                case "\t":
                    // Tab char: replace it with a run of spaces between length tabwidth and 1.
                    $tabsize = $tabwidth - ($pos % $tabwidth);
                    $chunks[$i] = str_repeat(' ', $tabsize);
                    $pos += $tabsize;
                    break;
                    
                default:
                    // Anything else: just keep track of its width.
                    $pos += strlen($chunks[$i]);
                    break;
                    
            }//end switch
            
        }//end for
        
        // Put the chunks back together and we've got the original line, detabbed.
        return join('', $chunks);
        
    }//end expandTabs()
    
    
    /**
     * Formats a duration of seconds for display.
     * 
     * @param array   $lang        Language literals array.
     * @param integer $seconds     The number of seconds until something.
     * @param string  $nbsp        True if spaces should be replaced by nbsp.
     * @param string  $skipSeconds True if seconds should be omitted.
     * 
     * @return string The formatted duration (e.g. @c "8h    6m    1s").
     * 
     * @access public
     */
    public function datetimeFormatDuration(array $lang, $seconds, $nbsp = false, $skipSeconds = false)
    {
        
        $neg = false;
        if ($seconds < 0) {
            $seconds = 0 - $seconds;
            $neg = true;
        }//end if
        
        $qty   = array();
        $names = array($lang['DAYLETTER'], $lang['HOURLETTER'], $lang['MINUTELETTER']);
        
        $qty[] = (int)($seconds / (60 * 60 * 24));
        $seconds %= 60 * 60 * 24;
        
        $qty[] = (int)($seconds / (60 * 60));
        $seconds %= 60 * 60;
        
        $qty[] = (int)($seconds / 60);
        
        if (!$skipSeconds) {
            $qty[] = (int)($seconds % 60);
            $names[] = $lang['SECONDLETTER'];
        }//end if
        
        $text  = $neg ? '-' : '';
        $any   = false;
        $count = count($names);
        $parts = 0;
        
        for ($i = 0; $i < $count; $i++) {
            // If a "higher valued" time slot had a value or this time slot has a value or this is the very
            // last entry (i.e. all values are 0 and we still want to print seconds).
            if ($any || $qty[$i] > 0 || $i === $count - 1) {
                
                if ($any) {
                    $text .= $nbsp ? '&nbsp;' : ' ';
                }//end if
                
                $text .= $qty[$i].' '.$names[$i];
                
                $any = true;
                $parts++;
                
                if ($parts >= 2) {
                    break;
                }//end if
                
            }//end if
            
        }//end for
        
        return $text;
        
    }//end datetimeFormatDuration()
    
    
    /**
     * Parse SVN timestamp.
     * 
     * @param string $dateString
     * 
     * @return number
     * 
     * @access public
     */
    public function parseSvnTimestamp($dateString)
    {
        // Try the simple approach of a built-in PHP function first.
        $date = strtotime($dateString);
        
        // If the resulting timestamp isn't sane, try parsing manually.
        if ($date <= 0) {
            
            $y  = 0;
            $mo = 0;
            $d  = 0;
            $h  = 0;
            $m  = 0;
            $s  = 0;
            sscanf($dateString, '%d-%d-%dT%d:%d:%d.', $y, $mo, $d, $h, $m, $s);
            
            $mo   = substr('00'.$mo, -2);
            $d    = substr('00'.$d, -2);
            $h    = substr('00'.$h, -2);
            $m    = substr('00'.$m, -2);
            $s    = substr('00'.$s, -2);
            $date = strtotime($y.'-'.$mo.'-'.$d.' '.$h.':'.$m.':'.$s.' GMT');
            
        }//end if
        
        return $date;
        
    }//end parseSvnTimestamp()
    
    
    /**
     * Query builder.
     * 
     * @param object $data
     * @param string $separator
     * @param string $key
     * 
     * @return string
     * 
     * @access public
     */
    public function buildQuery($data, $separator = WebSvnCons::ANDAMP, $key = '')
    {
        if (is_object($data)) {
            $data = get_object_vars($data);
        }//end if
            
        $p = array();
        
        foreach ($data as $k => $v) {
            
            $k = urlencode($k);
            
            if (!empty($key)) {
                $k = $key.'['.$k.']';
            }//end if
                
            if (is_array($v) || is_object($v)) {
                $p[] = $this->buildQuery($v, $separator, $k);
            } else {
                $p[] = $k.'='.urlencode($v);
            }//end if
            
        }//end foreach
        
        return implode($separator, $p);
        
    }//end buildQuery()
    
    
    /**
     * Get username.
     * 
     * @param array   $languages
     * @param string  $default
     * @param string  $userchoice
     * @param boolean $useAcceptedLanguages
     * 
     * @return string
     * 
     * @access public
     */
    public function getUserLanguage($languages, $default, $userchoice, $useAcceptedLanguages)
    {
        
        if (!$useAcceptedLanguages) {
            return $default;
        }//end if
        
        $acceptlangs = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : false;
        if (!$acceptlangs) {
            return $default;
        }//end if
        
        $langs    = array();
        $sublangs = array();
        
        foreach (explode(',', $acceptlangs) as $str) {
            
            $a    = explode(';', $str, 2);
            $lang = trim($a[0]);
            $pos  = strpos($lang, '-');
            
            if ($pos !== false) {
                $sublangs[] = substr($lang, 0, $pos);
            }//end if
                
            $q = 1.0;
            if (count($a) == 2) {
                $v = trim($a[1]);
                if (substr($v, 0, 2) == 'q=') {
                    $q = doubleval(substr($v, 2));
                }//end if
            }//end if
            
            if ($userchoice) {
                $q *= 0.9;
            }//end if
            
            $langs[$lang] = $q;
            
        }//end foreach
        
        foreach ($sublangs as $l) {
            
            if (!isset($langs[$l])) {
                $langs[$l] = 0.1;
            }//end if
            
            if ($userchoice) {
                $langs[$userchoice] = 1.0;
            }//end if
            
        }//end foreach
        
        arsort($langs);
        
        foreach ($langs as $code => $q) {
            if (isset($languages[$code])) {
                return $code;
            }//end if
        }//end foreach
        
        return $default;
        
    }//end getUserLanguage()
    
    
    /**
     * TempnamWithCheck.
     * 
     * @param string $dir
     * @param string $prefix
     * 
     * @return string
     * 
     * @access public
     */
    public function tempnamWithCheck($dir, $prefix)
    {
        $tmp = tempnam($dir, $prefix);
        
        if ($tmp === false) {
            // Logger.
            $this->logger->error('Error creating file');
        }//end if
        
        return $tmp;
        
    }//end tempnamWithCheck()
    
    
}//end Utils class.

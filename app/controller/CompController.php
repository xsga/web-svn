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
namespace app\controller;

/**
 * Used classes.
 */
use app\business\setup\Setup;
use app\business\svn\SVNRepository;
use app\business\setup\WebSvnCons;

/**
 * CompController class
 */
class CompController extends AbstractController
{
    
    
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
        
        // Make sure that we have a repository.
        $this->validatesRepo($setup);
        
        $setup->svnrep = new SVNRepository($setup);
        
        // Retrieve the request information.
        $path1            = @$_REQUEST['compare'][0];
        $path2            = @$_REQUEST['compare'][1];
        $rev1             = (int)@$_REQUEST['compare_rev'][0];
        $rev2             = (int)@$_REQUEST['compare_rev'][1];
        $manualorder      = (@$_REQUEST['manualorder'] === '1');
        $ignoreWhitespace = $setup->config->getIgnoreWhitespacesInDiff();
        
        if (array_key_exists('ignorews', $_REQUEST)) {
            $ignoreWhitespace = (bool)$_REQUEST['ignorews'];
        }//end if
        
        // Some page links put the revision with the path.
        if (strpos($path1, '@')) {
            list($path1, $rev1) = explode('@', $path1);
        } else if (strpos($path1, '@') === 0) {
            // Something went wrong. The path is missing.
            $rev1  = substr($path1, 1);
            $path1 = '/';
        }//end if
        
        if (strpos($path2, '@')) {
            list($path2, $rev2) = explode('@', $path2);
        } else if (strpos($path2, '@') === 0) {
            $rev2  = substr($path2, 1);
            $path2 = '/';
        }//end if
        
        $rev1 = checkRevision($rev1);
        $rev2 = checkRevision($rev2);
        
        // Choose a sensible comparison order unless told not to.
        if (!$manualorder && is_numeric($rev1) && is_numeric($rev2) && $rev1 > $rev2) {
            $temppath = $path1;
            $path1    = $path2;
            $path2    = $temppath;
            $temprev  = $rev1;
            $rev1     = $rev2;
            $rev2     = $temprev;
        }//end if
        
        $setup->vars['rev1url'] = $setup->config->getURL($setup->rep, $path1, 'dir').$setup->utils->createRevAndPegString($rev1, $rev1);
        $setup->vars['rev2url'] = $setup->config->getURL($setup->rep, $path2, 'dir').$setup->utils->createRevAndPegString($rev2, $rev2);
        
        $url = $setup->config->getURL($setup->rep, '', 'comp');
        
        $setup->vars['reverselink'] = '<a href="'.$url.'compare%5B%5D='.urlencode($path2).'@'.$rev2.WebSvnCons::ANDAMP.'compare%5B%5D='.urlencode($path1).'@'.$rev1.WebSvnCons::ANDAMP.'manualorder=1'.($ignoreWhitespace ? WebSvnCons::ANDAMP.'ignorews=1' : '').'">'.$setup->lang['REVCOMP'].'</a>';
        
        $toggleIgnoreWhitespace = '';
        if ($ignoreWhitespace === $setup->config->getIgnoreWhitespacesInDiff()) {
            $toggleIgnoreWhitespace = WebSvnCons::ANDAMP.'ignorews='.($ignoreWhitespace ? '0' : '1');
        }//end if
        
        if (!$ignoreWhitespace) {
            $setup->vars['ignorewhitespacelink'] = '<a href="'.$url.'compare%5B%5D='.urlencode($path1).'@'.$rev1.WebSvnCons::ANDAMP.'compare%5B%5D='.urlencode($path2).'@'.$rev2.($manualorder ? WebSvnCons::ANDAMP.'manualorder=1' : '').$toggleIgnoreWhitespace.'">'.$setup->lang['IGNOREWHITESPACE'].'</a>';
        } else {
            $setup->vars['regardwhitespacelink'] = '<a href="'.$url.'compare%5B%5D='.urlencode($path1).'@'.$rev1.WebSvnCons::ANDAMP.'compare%5B%5D='.urlencode($path2).'@'.$rev2.($manualorder ? WebSvnCons::ANDAMP.'manualorder=1' : '').$toggleIgnoreWhitespace.'">'.$setup->lang['REGARDWHITESPACE'].'</a>';
        }//end if
        
        if ($rev1 === 0) {
            $rev1 = 'HEAD';
        }//end if
        
        if ($rev2 === 0) {
            $rev2 = 'HEAD';
        }//end if
        
        $setup->vars['repname'] = escape($setup->rep->getDisplayName());
        $setup->vars['action']  = $setup->lang['PATHCOMPARISON'];
        
        $hidden  = '<input type="hidden" name="manualorder" value="1" />';
        $hidden .= '<input type="hidden" name="repname" value="'.$setup->repname.'" />';
        
        // Compare form.
        $setup->vars['compare_form']       = '<form method="get" action="'.$url.'" id="compare">'.$hidden;
        $setup->vars['compare_path1input'] = '<input type="text" size="40" name="compare[0]" value="'.escape($path1).'" />';
        $setup->vars['compare_path2input'] = '<input type="text" size="40" name="compare[1]" value="'.escape($path2).'" />';
        $setup->vars['compare_rev1input']  = '<input type="text" size="5" name="compare_rev[0]" value="'.$rev1.'" />';
        $setup->vars['compare_rev2input']  = '<input type="text" size="5" name="compare_rev[1]" value="'.$rev2.'" />';
        $setup->vars['compare_submit']     = '<input name="comparesubmit" type="submit" value="'.$setup->lang['COMPAREPATHS'].'" />';
        $setup->vars['compare_endform']    = '</form>';
        
        // Safe paths are a hack for fixing XSS exploit.
        $setup->vars['path1'] = escape($path1);
        $setup->vars['safepath1'] = escape($path1);
        $setup->vars['path2'] = escape($path2);
        $setup->vars['safepath2'] = escape($path2);
        $setup->vars['rev1'] = $rev1;
        $setup->vars['rev2'] = $rev2;
        
        $history1 = $setup->svnrep->getLog($path1, $rev1, $rev1, false, 1);
        if (!$history1) {
            http_response_code(WebSvnCons::ERROR_404);
            $setup->vars['error'] = $setup->lang['NOPATH'];
        } else {
            $history2 = $setup->svnrep->getLog($path2, $rev2, $rev2, false, 1);
            if (!$history2) {
                http_response_code(WebSvnCons::ERROR_404);
                $setup->vars['error'] = $setup->lang['NOPATH'];
            }//end if
        }//end if
        
        // Set variables used for the more recent of the two revisions.
        $history = ($rev1 >= $rev2 ? $history1 : $history2);
        if ($history && $history->curEntry) {
            $logEntry = $history->curEntry;
            $setup->vars['rev']    = $logEntry->rev;
            $setup->vars['peg']    = $setup->peg;
            $setup->vars['date']   = $logEntry->date;
            $setup->vars['age']    = $setup->utils->datetimeFormatDuration($setup->lang, time() - strtotime($logEntry->date));
            $setup->vars['author'] = $logEntry->author;
            $setup->vars['log']    = $setup->utils->xmlEntities($logEntry->msg);
        } else {
            $setup->vars['warning'] = 'Problem with comparison.';
        }//end if
        
        $noinput = empty($path1) || empty($path2);
        
        // Generate the diff listing.
        $relativePath1 = $path1;
        $relativePath2 = $path2;
        
        // SVN paths.
        $svnpath1 = $setup->svnrep->svnLook->encodepath($setup->svnrep->getSvnPath(str_replace(DIRECTORY_SEPARATOR, '/', $path1)));
        $svnpath2 = $setup->svnrep->svnLook->encodepath($setup->svnrep->getSvnPath(str_replace(DIRECTORY_SEPARATOR, '/', $path2)));
        
        if (!$noinput) {
            $cmd = $setup->config->getSvnCommand().$setup->rep->svnCredentials().' diff '.($ignoreWhitespace ? '-x "-w --ignore-eol-style" ' : '').quote($svnpath1.'@'.$rev1).' '.quote($svnpath2.'@'.$rev2);
        }//end if
        
        $setup->vars['success'] = false;
        
        // TODO: Report warning/error if comparison encounters any problems.
        if (!$noinput && $diff = popenCommand($cmd, 'r')) {
            
            $index                  = 0;
            $indiff                 = false;
            $indiffproper           = false;
            $getLine                = true;
            $node                   = null;
            $bufferedLine           = false;
            $setup->vars['success'] = true;
            
            while (!feof($diff)) {
                
                if ($getLine) {
                    
                    if ($bufferedLine === false) {
                        $bufferedLine = rtrim(fgets($diff), "\r\n");
                    }//end if
                    
                    $newlineR = strpos($bufferedLine, "\r");
                    $newlineN = strpos($bufferedLine, "\n");
                    
                    if ($newlineR === false && $newlineN === false) {
                        $line         = $bufferedLine;
                        $bufferedLine = false;
                    } else {
                        $newline      = ($newlineR < $newlineN ? $newlineR : $newlineN);
                        $line         = substr($bufferedLine, 0, $newline);
                        $bufferedLine = substr($bufferedLine, $newline + 1);
                    }//end if
                    
                }//end if
                
                clearVars($ignoreWhitespace, $setup, $index);
                $getLine = true;
                
                if ($indiff) {
                    
                    // If we're in a diff proper, just set up the line.
                    if ($indiffproper) {
                        
                        if (strlen($line) > 0 && ($line[0] === ' ' || $line[0] === '+' || $line[0] === '-')) {
                            
                            $subline = escape(toOutputEncoding(substr($line, 1)));
                            $subline = rtrim($subline, "\n\r");
                            $subline = ($subline) ? $setup->utils->expandTabs($setup->rep, $subline) : '&nbsp;';
                            
                            $listvar         = &$setup->listing[$index];
                            $listvar['line'] = $subline;
                            
                            switch ($line[0]) {
                                case ' ':
                                    $listvar['diffclass'] = 'diff';
                                    break;
                                    
                                case '+':
                                    $listvar['diffclass'] = 'diffadded';
                                    break;
                                    
                                case '-':
                                    $listvar['diffclass'] = 'diffdeleted';
                                    break;
                                    
                                default:
                                    break;
                                    
                            }//end switch
                            
                            $index++;
                            
                        } else if ($line !== '\ No newline at end of file') {
                            
                            $indiffproper = false;
                            $setup->listing[$index++]['enddifflines'] = true;
                            $getLine = false;
                            
                        }//end if
                        
                        continue;
                        
                    }//end if
                    
                    // Check for the start of a new diff area.
                    if (!strncmp($line, '@@', 2)) {
                        
                        $pos     = strpos($line, '+');
                        $posline = substr($line, $pos);
                        $sline   = 0;
                        $eline   = 0;
                        
                        sscanf($posline, '+%d,%d', $sline, $eline);
                        
                        // Check that this isn't a file deletion.
                        if ($sline === 0 && $eline === 0) {
                            
                            $line = fgets($diff);
                            
                            while ($line[0] === ' ' || $line[0] === '+' || $line[0] === '-') {
                                $line = fgets($diff);
                            }//end while
                            
                            $getLine = false;
                            
                            $setup->listing[$index++]['info'] = $setup->lang['FILEDELETED'];
                            
                        } else {
                            
                            $listvar              = &$setup->listing[$index];
                            $listvar['difflines'] = $line;
                            
                            $sline = 0;
                            $slen  = 0;
                            $eline = 0;
                            $elen  = 0;
                            
                            sscanf($line, '@@ -%d,%d +%d,%d @@', $sline, $slen, $eline, $elen);
                            
                            $listvar['rev1line'] = $sline;
                            $listvar['rev1len']  = $slen;
                            $listvar['rev2line'] = $eline;
                            $listvar['rev2len']  = $elen;
                            
                            $indiffproper = true;
                            
                            $index++;
                            
                        }//end if
                        
                        continue;
                        
                    } else {
                        $indiff = false;
                    }//end if
                    
                }//end if
                
                // Check for a new node entry.
                if (strncmp(trim($line), 'Index: ', 7) === 0) {
                    
                    // End the current node.
                    if ($node) {
                        $setup->listing[$index++]['endpath'] = true;
                        clearVars($ignoreWhitespace, $setup, $index);
                    }//end if
                    
                    $node = trim(toOutputEncoding($line));
                    $node = substr($node, 7);
                    
                    if ($node === '' || $node[0] !== '/') {
                        $node = '/'.$node;
                    }//end if
                    
                    if (substr($path2, -strlen($node)) === $node) {
                        $absnode = $path2;
                    } else {
                        $absnode = $path2;
                        if (substr($absnode, -1) === '/') {
                            $absnode = substr($absnode, 0, -1);
                        }//end if
                        $absnode .= $node;
                    }//end if
                    
                    $listvar            = &$setup->listing[$index];
                    $listvar['newpath'] = escape($absnode);
                    $listvar['fileurl'] = $setup->config->getURL($setup->rep, escape($absnode), 'file').'rev='.$rev2;
                    
                    // Skip past the line of ='s.
                    $line = fgets($diff);
                    
                    // Check for a file addition.
                    $line = fgets($diff);
                    
                    if (strpos($line, '(revision 0)')) {
                        $listvar['info'] = $setup->lang['FILEADDED'];
                    }//end if
                    
                    if (strncmp(trim($line), 'Cannot display:', 15) === 0) {
                        $index++;
                        clearVars($ignoreWhitespace, $setup, $index);
                        $setup->listing[$index++]['info'] = escape(toOutputEncoding($line));
                        continue;
                    }//end if
                    
                    // Skip second file info.
                    $line = fgets($diff);
                    
                    $indiff = true;
                    $index++;
                    
                    continue;
                    
                }//end if
                
                if (strncmp(trim($line), 'Property changes on: ', 21) === 0) {
                    
                    $propnode = trim($line);
                    $propnode = substr($propnode, 21);
                    
                    if ($propnode === '' || $propnode[0] !== '/') {
                        $propnode = '/'.$propnode;
                    }//end if
                    
                    if ($propnode != $node) {
                        
                        if ($node) {
                            $setup->listing[$index++]['endpath'] = true;
                            clearVars($ignoreWhitespace, $setup, $index);
                        }//end if
                        
                        $node = $propnode;
                        $setup->listing[$index++]['newpath'] = escape(toOutputEncoding($node));
                        clearVars($ignoreWhitespace, $setup, $index);
                        
                    }//end if
                    
                    $setup->listing[$index++]['properties'] = true;
                    clearVars();
                    
                    // Skip the row of underscores.
                    $line = fgets($diff);
                    
                    while ($line = trim(fgets($diff))) {
                        
                        if (!strncmp($line, 'Index: ', 7)) {
                            break;
                        }//end if
                        
                        if (!strncmp($line, '##', 2) || $line === '\ No newline at end of file') {
                            continue;
                        }//end if
                        
                        $setup->listing[$index++]['info'] = escape(toOutputEncoding($line));
                        clearVars($ignoreWhitespace, $setup, $index);
                        
                    }//end while
                    
                    $getLine = false;
                    
                    continue;
                    
                }//end if
                
                // Check for error messages.
                if (strncmp(trim($line), 'svn: ', 5) === 0) {
                    $setup->listing[$index++]['info'] = urldecode($line);
                    $setup->vars['success'] = false;
                    continue;
                }//end if
                
                $setup->listing[$index++]['info'] = escape(toOutputEncoding($line));
                
            }//end while
            
            if ($node) {
                clearVars($ignoreWhitespace, $setup, $index);
                $setup->listing[$index++]['endpath'] = true;
            }//end if
            
            if (!$setup->rep->hasUnrestrictedReadAccess($relativePath1) || !$setup->rep->hasUnrestrictedReadAccess($relativePath2, false)) {
                
                // Check every item for access and remove it if read access is not allowed.
                $restricted   = array();
                $inrestricted = false;
                
                foreach ($setup->listing as $i => $item) {
                    
                    if ($item['newpath'] !== null) {
                        $newpath      = $item['newpath'];
                        $inrestricted = !$setup->rep->hasReadAccess($newpath);
                    }//end if
                    
                    if ($inrestricted) {
                        $restricted[] = $i;
                    }//end if
                    
                    if ($item['endpath'] !== null) {
                        $inrestricted = false;
                    }//end if
                    
                }//end foreach
                
                foreach ($restricted as $i) {
                    unset($setup->listing[$i]);
                }//end foreach
                
                $this->validatesAccess($setup);
                
            }//end if
            
            pclose($diff);
            
        }//end if
        
        // Render template.
        $this->renderTemplate($setup, 'compare');
        
    }//end __construct()
    
    
}//end CompController class

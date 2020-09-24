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
namespace app\controller;

/**
 * Used classes.
 */
use app\business\setup\Setup;
use app\business\svn\SVNRepository;
use app\business\setup\WebSvnCons;

/**
 * BlameController class.
 */
class BlameController extends AbstractController
{
    
    
    /**
     * Constructor.
     * 
     * @param Setup $setup Setup instance.
     * 
     * @access public
     */
    public function __construct(Setup $setup)
    {
        
        // Executes parent constructor.
        parent::__construct();
        
        $setup->vars['action'] = $setup->lang['BLAME'];
        
        // Make sure that we have a repository.
        $this->validatesRepo($setup);
        
        $setup->svnrep = new SVNRepository($setup);
        
        // If there's no revision info, go to the lastest revision for this path.
        $history = $setup->svnrep->getLog($setup->path, 'HEAD', 1, false, 2, ($setup->path === '/') ? '' : $setup->peg);
        
        if (!$history) {
            
            unset($setup->vars['error']);
            
            $history = $setup->svnrep->getLog($setup->path, '', '', false, 2, ($setup->path === '/') ? '' : $setup->peg);
            
            if (!$history) {
                http_response_code(WebSvnCons::ERROR_404);
                $setup->vars['error'] = $setup->lang['NOPATH'];
            }//end if
            
        }//end if
        
        $youngest = ($history && isset($history->entries[0])) ? (int)$history->entries[0]->rev : false;
        
        if (empty($setup->rev)) {
            
            $setup->rev = $youngest;
            
        } else {
            
            $history = $setup->svnrep->getLog($setup->path, $setup->rev, '', false, 2, $setup->peg);
            
            if (!$history) {
                http_response_code(WebSvnCons::ERROR_404);
                $setup->vars['error'] = $setup->lang['NOPATH'];
            }//end if
            
        }//end if
        
        if ($setup->path[0] !== '/') {
            $ppath = '/'.$setup->path;
        } else {
            $ppath = $setup->path;
        }//end if
        
        $setup->vars['rev']  = $setup->rev;
        $setup->vars['peg']  = $setup->peg;
        $setup->vars['path'] = escape($ppath);
        
        if (isset($history->entries[0])) {
            $setup->vars['log']    = $setup->utils->xmlEntities($history->entries[0]->msg);
            $setup->vars['date']   = $history->entries[0]->date;
            $setup->vars['age']    = $setup->utils->datetimeFormatDuration($setup->lang, time() - strtotime($history->entries[0]->date));
            $setup->vars['author'] = $history->entries[0]->author;
        }//end if
        
        $setup->vars['pathlinks'] = $setup->utils->createPathLinks($setup, $ppath);
        $setup->passRevString     = $setup->utils->createRevAndPegString($setup->rev, $setup->peg);
        
        if ($setup->rev !== $youngest) {
            $setup->vars['goyoungesturl']  = $setup->config->getURL($setup->rep, $setup->path, 'blame').$setup->utils->createRevAndPegString('', $setup->peg);
            $setup->vars['goyoungestlink'] = '<a href="'.$setup->vars['goyoungesturl'].'"'.($youngest ? ' title="'.$setup->lang['REV'].' '.$youngest.'"' : '').'>'.$setup->lang['GOYOUNGEST'].'</a>';
        }//end if
        
        $revurl = $setup->config->getURL($setup->rep, $setup->path, 'blame');
        
        if ($setup->rev < $youngest) {
            
            $history2 = $setup->svnrep->getLog($setup->path, $setup->rev, $youngest, true, 2, $setup->peg);
            
            if (isset($history2->entries[1])) {
                
                $nextRev = $history2->entries[1]->rev;
                
                if ($nextRev !== $youngest) {
                    $setup->vars['nextrev']    = $nextRev;
                    $setup->vars['nextrevurl'] = $revurl.$setup->utils->createRevAndPegString($nextRev, $setup->peg);
                }//end if
                
            }//end if
            
            unset($setup->vars['error']);
            
        }//end if
        
        if (isset($history->entries[1])) {
            $prevRev                   = $history->entries[1]->rev;
            $setup->vars['prevrev']    = $prevRev;
            $setup->vars['prevrevurl'] = $revurl.$setup->utils->createRevAndPegString($prevRev, $setup->peg);
        }//end if
        
        $setup->vars['revurl']         = $setup->config->getURL($setup->rep, $setup->path, 'revision').$setup->passRevString;
        $setup->vars['revlink']        = '<a href="'.$setup->vars['revurl'].'">'.$setup->lang['LASTMOD'].'</a>';
        $setup->vars['logurl']         = $setup->config->getURL($setup->rep, $setup->path, 'log').$setup->passRevString;
        $setup->vars['loglink']        = '<a href="'.$setup->vars['logurl'].'">'.$setup->lang['VIEWLOG'].'</a>';
        $setup->vars['filedetailurl']  = $setup->config->getURL($setup->rep, $setup->path, 'file').$setup->passRevString;
        $setup->vars['filedetaillink'] = '<a href="'.$setup->vars['filedetailurl'].'">'.$setup->lang['FILEDETAIL'].'</a>';
        
        if ($history === null || count($history->entries) > 1) {
            $setup->vars['diffurl']  = $setup->config->getURL($setup->rep, $setup->path, 'diff').$setup->passRevString;
            $setup->vars['difflink'] = '<a href="'.$setup->vars['diffurl'].'">'.$setup->lang['DIFFPREV'].'</a>';
        }//end if
        
        // Check for binary file type before grabbing blame information.
        $svnMimeType = $setup->svnrep->getProperty($setup->path, 'svn:mime-type', $setup->rev, $setup->peg);
        
        if (!$setup->rep->getIgnoreSvnMimeTypes() && preg_match('~application/*~', $svnMimeType)) {
            
            $setup->vars['warning']    = 'Cannot display blame info for binary file. (svn:mime-type = '.$svnMimeType.')';
            $setup->vars['javascript'] = '';
            
        } else {
            
            // Get the contents of the file.
            $tfname      = $setup->utils->tempnamWithCheck($setup->config->getTempDir(), '');
            $highlighted = $setup->svnrep->getFileContents($setup->path, $tfname, $setup->rev, $setup->peg, '', 'line');
            
            if ($file = fopen($tfname, 'r')) {
                
                // Get the blame info.
                $tbname = $setup->utils->tempnamWithCheck($setup->config->getTempDir(), '');
                
                $setup->svnrep->getBlameDetails($setup->path, $tbname, $setup->rev, $setup->peg);
                
                if ($blame = fopen($tbname, 'r')) {
                    
                    // Create an array of version/author/line.
                    $index     = 0;
                    $seen_rev  = array();
                    $last_rev  = '';
                    $row_class = '';
                    
                    while (!feof($blame) && !feof($file)) {
                        
                        $blameline = fgets($blame);
                        
                        if (!empty($blameline)) {
                            
                            list($revision, $author, $remainder) = sscanf($blameline, '%d %s %s');
                            $empty = !$remainder;
                            
                            $listvar           = &$setup->listing[$index];
                            $listvar['lineno'] = $index + 1;
                            
                            if ($last_rev !== $revision) {
                                
                                $listvar['revision'] = '<a id="l'.$index.'-rev" class="blame-revision" href="'.$setup->config->getURL($setup->rep, $setup->path, 'blame').$setup->utils->createRevAndPegString($revision, $setup->peg ? $setup->peg : $setup->rev).'">'.$revision.'</a>';
                                
                                $seen_rev[$revision] = 1;
                                $row_class = ($row_class == 'light') ? 'dark' : 'light';
                                $listvar['author'] = $author;
                                
                            } else {
                                
                                $listvar['revision'] = '';
                                $listvar['author']   = '';
                                
                            }//end if
                            
                            $listvar['row_class'] = $row_class;
                            $last_rev = $revision;
                            $line = rtrim(fgets($file));
                            
                            if (!$highlighted) {
                                $line = escape(toOutputEncoding($line));
                            }//end if
                            
                            $listvar['line'] = ($empty) ? '&nbsp;' : $setup->utils->wrapInCodeTagIfNecessary($line, $setup->config->getUseGeshi());
                            $index++;
                            
                        }//end if
                        
                    }//end while
                    
                    fclose($blame);
                    
                }//end if
                
                fclose($file);
                @unlink($tbname);
                
            }//end if
            
            @unlink($tfname);
            
            // Build the necessary JavaScript as an array of lines, then join them with \n.
            $javascript   = array();
            $javascript[] = '<script type="text/javascript" src="'.$setup->locwebsvnreal.'/javascript/blame-popup.js"></script>';
            $javascript[] = '<script type="text/javascript">';
            $javascript[] = '/* <![CDATA[ */';
            $javascript[] = 'var rev = new Array();';
            
            // Sort revisions in descending order by key.
            ksort($seen_rev);
            
            if (empty($setup->peg)) {
                $setup->peg = $setup->rev;
            }//end if
            
            if (!isset($setup->vars['warning'])) {
                
                foreach ($seen_rev as $key => $val) {
                    
                    $history = $setup->svnrep->getLog($setup->path, $key, $key, false, 1, $setup->peg);
                    
                    if ($history) {
                        $javascript[] = 'rev['.$key.'] = \'<div class="date">'.$history->curEntry->date.'</div><div class="msg">'.addslashes(preg_replace('/\n/', ' ', $history->curEntry->msg)).'</div>\';';
                    }//end if
                    
                }//end foreach
                
            }//end if
            
            $javascript[] = '/* ]]> */';
            $javascript[] = '</script>';
            
            $setup->vars['javascript'] = implode("\n", $javascript);
            
        }//end if
        
        $this->validatesAccess($setup);
        
        // Render template.
        $this->renderTemplate($setup, 'blame');
        
    }//end __construct()
    
    
}//end BlameController class
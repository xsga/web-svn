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
use app\business\setup\WebSvnCons;
use app\business\svn\SVNRepository;
use app\business\bugtraq\Bugtraq;

/**
 * LogController class.
 */
class LogController extends AbstractController
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
        
        $page  = (int)@$_REQUEST['page'];
        $all   = @$_REQUEST['all'] === '1';
        $isDir = @$_REQUEST['isdir'] === '1' || $path === '' || $path === '/';
        
        if (isset($_REQUEST['showchanges'])) {
            $showchanges = @$_REQUEST['showchanges'] === '1';
        } else {
            $showchanges = $setup->rep->logsShowChanges($setup->config->logsShowChanges());
        }//end if
        
        $search   = trim(@$_REQUEST['search']);
        $dosearch = strlen($search) > 0;
        $words    = preg_split('#\s+#', $search);
        $fromRev  = (int)@$_REQUEST['fr'];
        $startrev = strtoupper(trim(@$_REQUEST['sr']));
        $endrev   = strtoupper(trim(@$_REQUEST['er']));
        $max      = isset($_REQUEST['max']) ? (int)$_REQUEST['max'] : false;
        
        // Max number of results to find at a time.
        $numSearchResults = 20;
        
        if ($search === '') {
            $dosearch = false;
        }//end if
        
        // Normalise the search words.
        foreach ($words as $index => $word) {
            
            $words[$index] = strtolower(removeAccents($word));
            
            // Remove empty string introduced by multiple spaces.
            if (empty($words[$index])) {
                unset($words[$index]);
            }//end if
            
        }//end foreach
        
        if (empty($page)) {
            $page = 1;
        }//end if
        
        // If searching, display all the results.
        if ($dosearch) {
            $all = true;
        }//end if
        
        $maxperpage = 20;
        
        // Make sure that we have a repository.
        if ($setup->rep) {
            
            $setup->svnrep = new SVNRepository($setup);
            
            $history = $setup->svnrep->getLog($setup->path, 'HEAD', '', false, 1, ($setup->path === '/') ? '' : $setup->peg);
            
            if (!$history) {
                
                unset($setup->vars['error']);
                
                $history = $setup->svnrep->getLog($setup->path, '', '', false, 1, ($setup->path === '/') ? '' : $setup->peg);
                
                if (!$history) {
                    header(WebSvnCons::HTTP_404, true, 404);
                    $setup->vars['error'] = $setup->lang['NOPATH'];
                }//end if
                
            }//end if
            
            $youngest = ($history && isset($history->entries[0])) ? (int)$history->entries[0]->rev : 0;
            
            if (empty($startrev)) {
                $startrev = $setup->rev;
            } else if ($startrev !== 'HEAD' && $startrev !== 'BASE' && $startrev !== 'PREV' && $startrev !== 'COMMITTED') {
                $startrev = (int)$startrev;
            }//end if
            
            if (empty($endrev)) {
                $endrev = 1;
            } else if ($endrev !== 'HEAD' && $endrev !== 'BASE' && $endrev !== 'PREV' && $endrev !== 'COMMITTED') {
                $endrev = (int)$endrev;
            }//end if
            
            if (empty($setup->rev)) {
                $setup->rev = $youngest;
            }//end if
            
            if (empty($startrev)) {
                $startrev = $setup->rev;
            }//end if
            
            // Make sure path is prefixed by a /.
            $ppath = $setup->path;
            if ($setup->path === '' || $setup->path{0} !== '/') {
                $ppath = '/'.$setup->path;
            }//end if
            
            $setup->vars['action'] = $setup->lang['LOG'];
            $setup->vars['rev']    = $setup->rev;
            $setup->vars['peg']    = $setup->peg;
            $setup->vars['path']   = escape($ppath);
            
            if ($history && isset($history->entries[0])) {
                $setup->vars['log']    = $setup->utils->xmlEntities($history->entries[0]->msg);
                $setup->vars['date']   = $history->entries[0]->date;
                $setup->vars['age']    = $setup->utils->datetimeFormatDuration($setup->lang, time() - strtotime($history->entries[0]->date));
                $setup->vars['author'] = $history->entries[0]->author;
            }//end if
            
            if ($max === false) {
                $max = ($dosearch) ? 0 : 40;
            } else if ($max < 0) {
                $max = 40;
            }//end if
            
            // TODO: If the rev is less than the head, get the path (may have been renamed!)
            // Will probably need to call `svn info`, parse XML output, and substring a path
            $setup->vars['pathlinks'] = $setup->utils->createPathLinks($setup, $ppath);
            $setup->passRevString     = $setup->utils->createRevAndPegString($setup->rev, $setup->peg);
            $isDirString              = ($isDir) ? 'isdir=1'.WebSvnCons::ANDAMP : '';
            
            unset($setup->queryParams['repname']);
            unset($setup->queryParams['path']);
            
            // Toggle 'showchanges' param for link to switch from the current behavior
            if ($showchanges === $setup->rep->logsShowChanges($setup->config->logsShowChanges())) {
                $setup->queryParams['showchanges'] = (int)!$showchanges;
            } else {
                unset($setup->queryParams['showchanges']);
            }//end if
            
            $setup->vars['changesurl'] = $setup->config->getURL($setup->rep, $setup->path, 'log').$setup->utils->buildQuery($setup->queryParams);
            $setup->vars['changeslink'] = '<a href="'.$setup->vars['changesurl'].'">'.$setup->lang[($showchanges ? 'HIDECHANGED' : 'SHOWCHANGED')].'</a>';
            $setup->vars['showchanges'] = $showchanges;
            
            // Revert 'showchanges' param to propagate the current behavior.
            if ($showchanges === $setup->rep->logsShowChanges($setup->config->logsShowChanges())) {
                unset($setup->queryParams['showchanges']);
            } else {
                $setup->queryParams['showchanges'] = (int)$showchanges;
            }//end if
            
            $setup->vars['revurl'] = $setup->config->getURL($setup->rep, $setup->path, 'revision').$isDirString.$setup->passRevString;
            
            if ($isDir) {
                
                $setup->vars['directoryurl'] = $setup->config->getURL($setup->rep, $setup->path, 'dir').$setup->passRevString.'#'.$setup->utils->anchorForPath($setup->path, $setup->config->treeView);
                $setup->vars['directorylink'] = '<a href="'.$setup->vars['directoryurl'].'">'.$setup->lang['LISTING'].'</a>';
                
            } else {
                
                $setup->vars['filedetailurl'] = $setup->config->getURL($setup->rep, $setup->path, 'file').$setup->passRevString;
                $setup->vars['filedetaillink'] = '<a href="'.$setup->vars['filedetailurl'].'">'.$setup->lang['FILEDETAIL'].'</a>';
                $setup->vars['blameurl'] = $setup->config->getURL($setup->rep, $setup->path, 'blame').$setup->passRevString;
                $setup->vars['blamelink'] = '<a href="'.$setup->vars['blameurl'].'">'.$setup->lang['BLAME'].'</a>';
                $setup->vars['diffurl'] = $setup->config->getURL($setup->rep, $setup->path, 'diff').$setup->passRevString;
                $setup->vars['difflink'] = '<a href="'.$setup->vars['diffurl'].'">'.$setup->lang['DIFFPREV'].'</a>';
                
            }//end if
            
            if ($setup->rev !== $youngest) {
                
                if ($setup->path === '/') {
                    $setup->vars['goyoungesturl'] = $setup->config->getURL($setup->rep, '', 'log').$isDirString;
                } else {
                    $setup->vars['goyoungesturl'] = $setup->config->getURL($setup->rep, $setup->path, 'log').$isDirString.'peg='.($setup->peg ? $setup->peg : $setup->rev);
                }//end if
                
                $setup->vars['goyoungestlink'] = '<a href="'.$setup->vars['goyoungesturl'].'"'.($youngest ? ' title="'.$setup->lang['REV'].' '.$youngest.'"' : '').'>'.$setup->lang['GOYOUNGEST'].'</a>';
                
            }//end if
            
            // We get the bugtraq variable just once based on the HEAD.
            $bugtraq = new Bugtraq($setup->rep, $setup->svnrep, $ppath);
            
            $setup->vars['logsearch_moreresultslink'] = '';
            $setup->vars['pagelinks'] = '';
            $setup->vars['showalllink'] = '';
            
            if ($history) {
                $history = $setup->svnrep->getLog($setup->path, $startrev, $endrev, true, $max, $setup->peg);
                if (empty($history)) {
                    unset($setup->vars['error']);
                    $setup->vars['warning'] = 'Revision '.$startrev.' of this resource does not exist.';
                }//end if
            }//end if
            
            if (!empty($history)) {
                
                // Get the number of separate revisions.
                $revisions = count($history->entries);
                
                if ($all) {
                    
                    $firstrevindex = 0;
                    $lastrevindex = $revisions - 1;
                    $pages = 1;
                    
                } else {
                    
                    // Calculate the number of pages.
                    $pages = floor($revisions / $maxperpage);
                    
                    if (($revisions % $maxperpage) > 0) {
                        $pages++;
                    }//end if
                    
                    if ($page > $pages) {
                        $page = $pages;
                    }//end if
                    
                    // Work out where to start and stop.
                    $firstrevindex = ($page - 1) * $maxperpage;
                    $lastrevindex = min($firstrevindex + $maxperpage - 1, $revisions - 1);
                    
                }//end if
                
                $frev = isset($history->entries[0]) ? $history->entries[0]->rev : false;
                $brev = isset($history->entries[$firstrevindex]) ? $history->entries[$firstrevindex]->rev : false;
                $erev = isset($history->entries[$lastrevindex]) ? $history->entries[$lastrevindex]->rev : false;
                
                $entries = array();
                if ($brev && $erev) {
                    $history = $setup->svnrep->getLog($setup->path, $brev, $erev, false, 0, $setup->peg);
                    if ($history) {
                        $entries = $history->entries;
                    }//end if
                }//end if
                
                $row = 0;
                $index = 0;
                $found = false;
                
                foreach ($entries as $revision) {
                    
                    // Assume a good match
                    $match = true;
                    $thisrev = $revision->rev;
                    
                    // Check the log for the search words, if searching.
                    if ($dosearch) {
                        
                        if ((empty($fromRev) || $fromRev > $thisrev)) {
                            // Turn all the HTML entities into real characters.
                            // Make sure that each word in the search in also in the log
                            foreach ($words as $word) {
                                if (strpos(strtolower(removeAccents($revision->msg)), $word) === false && strpos(strtolower(removeAccents($revision->author)), $word) === false) {
                                    $match = false;
                                    break;
                                }//end if
                            }//end foreach
                            
                            if ($match) {
                                $numSearchResults--;
                                $found = true;
                            }//end if
                            
                        } else {
                            $match = false;
                        }//end if
                        
                    }//end if
                    
                    $thisRevString = $setup->utils->createRevAndPegString($thisrev, ($setup->peg ? $setup->peg : $thisrev));
                    
                    if ($match) {
                        
                        // Add the trailing slash if we need to (svnlook history doesn't return trailing slashes!).
                        $rpath = $revision->path;
                        if (empty($rpath)) {
                            $rpath = '/';
                        } else if ($isDir && $rpath{strlen($rpath) - 1} !== '/') {
                            $rpath .= '/';
                        }//end if
                        
                        $precisePath = $revision->precisePath;
                        if (empty($precisePath)) {
                            $precisePath = '/';
                        } else if ($isDir && $precisePath{strlen($precisePath) - 1} !== '/') {
                            $precisePath .= '/';
                        }//end if
                        
                        // Find the parent path (or the whole path if it's already a directory).
                        $pos = strrpos($rpath, '/');
                        $parent = substr($rpath, 0, $pos + 1);
                        
                        $compareValue = (($isDir) ? $parent : $rpath).'@'.$thisrev;
                        
                        $setup->listing[$index]['compare_box'] = '<input type="checkbox" name="compare[]" value="'.$compareValue.'" onclick="checkCB(this)" />';
                        $url = $setup->config->getURL($setup->rep, $rpath, 'revision').$thisRevString;
                        $setup->listing[$index]['revlink'] = '<a href="'.$url.'">'.$thisrev.'</a>';
                        
                        $url = $setup->config->getURL($setup->rep, $precisePath, ($isDir ? 'dir' : 'file')).$thisRevString;
                        $setup->listing[$index]['revpathlink'] = '<a href="'.$url.'">'.$precisePath.'</a>';
                        $setup->listing[$index]['revpath'] = $precisePath;
                        $setup->listing[$index]['revauthor'] = $revision->author;
                        $setup->listing[$index]['revdate'] = $revision->date;
                        $setup->listing[$index]['revage'] = $revision->age;
                        $setup->listing[$index]['revlog'] = nl2br($bugtraq->replaceIDs($setup->utils->createAnchors($setup->utils->xmlEntities($revision->msg))));
                        $setup->listing[$index]['rowparity'] = $row;
                        $setup->listing[$index]['compareurl'] = $setup->config->getURL($setup->rep, '', 'comp').'compare[]='.$rpath.'@'.($thisrev - 1).WebSvnCons::ANDAMP.'compare[]='.$rpath.'@'.$thisrev;
                        
                        if ($showchanges) {
                            
                            // Aggregate added/deleted/modified paths for display in table
                            $modpaths = array();
                            
                            foreach ($revision->mods as $mod) {
                                $modpaths[$mod->action][] = $mod->path;
                            }//end foreach
                            
                            ksort($modpaths);
                            
                            foreach ($modpaths as $action => $paths) {
                                sort($paths);
                                $modpaths[$action] = $paths;
                            }//end foreach
                            
                            $setup->listing[$index]['revadded'] = (isset($modpaths['A'])) ? implode('<br/>', $modpaths['A']) : '';
                            $setup->listing[$index]['revdeleted'] = (isset($modpaths['D'])) ? implode('<br/>', $modpaths['D']) : '';
                            $setup->listing[$index]['revmodified'] = (isset($modpaths['M'])) ? implode('<br/>', $modpaths['M']) : '';
                            
                        }//end if
                        
                        $row = 1 - $row;
                        $index++;
                        
                    }//end if
                    
                    // If we've reached the search limit, stop here.
                    if (!$numSearchResults) {
                        $url = $setup->config->getURL($setup->rep, $setup->path, 'log').$isDirString.$thisRevString;
                        $setup->vars['logsearch_moreresultslink'] = '<a href="'.$url.WebSvnCons::ANDAMP.'search='.$search.WebSvnCons::ANDAMP.'fr='.$thisrev.'">'.$setup->lang['MORERESULTS'].'</a>';
                        break;
                    }//end if
                    
                }//end foreach
                
                $setup->vars['logsearch_resultsfound'] = true;
                
                if ($dosearch && !$found) {
                    
                    if ($fromRev === 0) {
                        $setup->vars['logsearch_nomatches'] = true;
                        $setup->vars['logsearch_resultsfound'] = false;
                    } else {
                        $setup->vars['logsearch_nomorematches'] = true;
                    }//end if
                    
                } else if ($dosearch && $numSearchResults > 0) {
                    
                    $setup->vars['logsearch_nomorematches'] = true;
                    
                }//end if
                
                // Work out the paging options, create links to pages of results.
                if ($pages > 1) {
                    
                    $prev = $page - 1;
                    $next = $page + 1;
                    
                    unset($setup->queryParams['page']);
                    $logurl = $setup->config->getURL($setup->rep, $setup->path, 'log').$setup->utils->buildQuery($setup->queryParams);
                    
                    if ($page > 1) {
                        $setup->vars['pagelinks'] .= '<a href="'.$logurl.(!$setup->peg && $frev && $prev != 1 ? WebSvnCons::ANDAMP.'peg='.$frev : '').WebSvnCons::ANDAMP.'page='.$prev.'">&larr;'.$setup->lang['PREV'].'</a>';
                    } else {
                        $setup->vars['pagelinks'] .= '<span>&larr;'.$setup->lang['PREV'].'</span>';
                    }//end if
                    
                    for ($p = 1; $p <= $pages; $p++) {
                        if ($p != $page) {
                            $setup->vars['pagelinks'] .= '<a href="'.$logurl.(!$peg && $frev && $p != 1 ? WebSvnCons::ANDAMP.'peg='.$frev : '').WebSvnCons::ANDAMP.'page='.$p.'">'.$p.'</a>';
                        } else {
                            $setup->vars['pagelinks'] .= '<span id="curpage">'.$p.'</span>';
                        }
                    }//end for
                    
                    if ($page < $pages) {
                        $setup->vars['pagelinks'] .= '<a href="'.$logurl.(!$setup->peg && $frev ? WebSvnCons::ANDAMP.'peg='.$frev : '').WebSvnCons::ANDAMP.'page='.$next.'">'.$setup->lang['NEXT'].'&rarr;</a>';
                    } else {
                        $setup->vars['pagelinks'] .= '<span>'.$setup->lang['NEXT'].'&rarr;</span>';
                    }//end if
                    
                    $setup->vars['showalllink'] = '<a href="'.$logurl.WebSvnCons::ANDAMP.'all=1">'.$setup->lang['SHOWALL'].'</a>';
                    
                }//end if
                
            }//end if
            
            // Create form elements for filtering and searching log messages.
            $hidden = '<input type="hidden" name="repname" value="'.$setup->repname.'" />';
            $hidden .= '<input type="hidden" name="path" value="'.$setup->path.'" />';
            
            if ($isDir) {
                $hidden .= '<input type="hidden" name="isdir" value="'.$isDir.'" />';
            }//end if
            
            if ($setup->peg) {
                $hidden .= '<input type="hidden" name="peg" value="'.$setup->peg.'" />';
            }//end if
            
            if ($showchanges !== $setup->rep->logsShowChanges($setup->config->logsShowChanges())) {
                $hidden .= '<input type="hidden" name="showchanges" value="'.$showchanges.'" />';
            }//end if
            
            $setup->vars['logsearch_form'] = '<form method="get" action="'.$setup->config->getURL($setup->rep, $setup->path, 'log').'" id="search">'.$hidden;
            $setup->vars['logsearch_startbox'] = '<input name="sr" size="5" value="'.$startrev.'" />';
            $setup->vars['logsearch_endbox'] = '<input name="er" size="5" value="'.$endrev.'" />';
            $setup->vars['logsearch_maxbox'] = '<input name="max" size="5" value="'.($max == 0 ? 40 : $max).'" />';
            $setup->vars['logsearch_inputbox'] = '<input name="search" value="'.escape($search).'" />';
            $setup->vars['logsearch_showall'] = '<input type="checkbox" name="all" value="1"'.($all ? ' checked="checked"' : '').' />';
            $setup->vars['logsearch_submit'] = '<input type="submit" value="'.$setup->lang['GO'].'" />';
            $setup->vars['logsearch_endform'] = '</form>';
            
            // If a filter is in place, produce a link to clear all filter parameters.
            if ($page !== 1 || $all || $dosearch || $fromRev || $startrev !== $setup->rev || $endrev !== 1 || $max !== 40) {
                $url = $setup->config->getURL($setup->rep, $setup->path, 'log').$isDirString.$setup->passRevString;
                $setup->vars['logsearch_clearloglink'] = '<a href="'.$url.'">'.$setup->lang['CLEARLOG'].'</a>';
            }//end if
            
            // Create form elements for comparing selected revisions.
            $setup->vars['compare_form'] = '<form method="get" action="'.$setup->config->getURL($setup->rep, '', 'comp').'" id="compare">';
            $setup->vars['compare_form'] .= '<input type="hidden" name="repname" value="'.$setup->repname.'" />';
            $setup->vars['compare_submit'] = '<input type="submit" value="'.$setup->lang['COMPAREREVS'].'" />';
            $setup->vars['compare_endform'] = '</form>';
            
            if (!$setup->rep->hasReadAccess($setup->path, false)) {
                $setup->vars['error'] = $setup->lang['NOACCESS'];
                $setup->checkSendingAuthHeader($setup->rep);
            }//end if
            
        } else {
            header(WebSvnCons::HTTP_404, true, 404);
        }//end if
        
        // Render template.
        $this->renderTemplate($setup, 'log');
        
    }//end __construct()
    
    
}//end LogController class

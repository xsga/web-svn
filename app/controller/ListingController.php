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
use app\business\bugtraq\Bugtraq;

/**
 * ListingController class.
 */
class ListingController extends AbstractController
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
        
        // Make sure that we have a repository.
        $this->validatesRepo($setup);
        
        $setup->svnrep = new SVNRepository($setup);
        
        if (!empty($setup->rev)) {
            
            $info = $setup->svnrep->getInfo($setup->path, $setup->rev, $setup->peg);
            
            if ($info) {
                $setup->path = $info->path;
                $setup->peg  = (int)$info->rev;
            }//end if
            
        }//end if
        
        $history = $setup->svnrep->getLog($setup->path, 'HEAD', 1, false, 2, ($setup->path == '/') ? '' : $setup->peg);
        
        if (!$history) {
            
            unset($setup->vars['error']);
            $history = $setup->svnrep->getLog($setup->path, '', '', false, 2, ($setup->path == '/') ? '' : $setup->peg);
            
            if (!$history) {
                http_response_code(WebSvnCons::ERROR_404);
                $setup->vars['error'] = $setup->lang['NOPATH'];
            }//end if
            
        }//end if
        
        $youngest = ($history && isset($history->entries[0])) ? (int)$history->entries[0]->rev : 0;
        
        // Unless otherwise specified, we get the log details of the latest change.
        $lastChangedRev = ($setup->passrev) ? $setup->passrev : $youngest;
        if ($lastChangedRev !== $youngest) {
            $history = $setup->svnrep->getLog($setup->path, $lastChangedRev, 1, false, 2, $setup->peg);
        }//end if
        
        $logEntry = ($history && isset($history->entries[0])) ? $history->entries[0] : 0;
        $headlog  = $setup->svnrep->getLog('/', '', '', true, 1);
        $headrev  = ($headlog && isset($headlog->entries[0])) ? $headlog->entries[0]->rev : 0;
        
        // If we're not looking at a specific revision, get the HEAD revision number (the revision of the rest of the tree display).
        if (empty($setup->rev)) {
            $setup->rev = $headrev;
        }//end if
        
        if ($setup->path === '' || $setup->path[0] !== '/') {
            $ppath = '/'.$setup->path;
        } else {
            $ppath = $setup->path;
        }//end if
        
        $setup->vars['pathlinks'] = $setup->utils->createPathLinks($setup, $ppath);
        $setup->passRevString     = $setup->utils->createRevAndPegString($setup->passrev, $setup->peg);
        $isDirString              = 'isdir=1'.WebSvnCons::ANDAMP;
        
        $revurl       = $setup->config->getURL($setup->rep, $setup->path !== '/' ? $setup->path : '', 'dir');
        $revurlSuffix = $setup->path !== '/' ? '#'.$setup->utils->anchorForPath($setup->path, $setup->config->treeView) : '';
        
        if ($setup->rev < $youngest) {
            
            if ($setup->path === '/') {
                $setup->vars['goyoungesturl'] = $setup->config->getURL($setup->rep, '', 'dir');
            } else {
                $setup->vars['goyoungesturl'] = $setup->config->getURL($setup->rep, $setup->path, 'dir').$setup->utils->createRevAndPegString($youngest, $setup->peg ? $setup->peg: $setup->rev).$revurlSuffix;
            }//end if
            
            $setup->vars['goyoungestlink'] = '<a href="'.$setup->vars['goyoungesturl'].'"'.($youngest ? ' title="'.$setup->lang['REV'].' '.$youngest.'"' : '').'>'.$setup->lang['GOYOUNGEST'].'</a>';
            
            $history2 = $setup->svnrep->getLog($setup->path, $setup->rev, $youngest, true, 2, $setup->peg);
            if (isset($history2->entries[1])) {
                
                $nextRev = $history2->entries[1]->rev;
                
                if ($nextRev !== $youngest) {
                    $setup->vars['nextrev']    = $nextRev;
                    $setup->vars['nextrevurl'] = $revurl.$setup->utils->createRevAndPegString($nextRev, $setup->peg).$revurlSuffix;
                }//end if
                
            }//end if
            
            unset($setup->vars['error']);
            
        }//end if
        
        if (isset($history->entries[1])) {
            $prevRev                   = $history->entries[1]->rev;
            $setup->vars['prevrev']    = $prevRev;
            $setup->vars['prevrevurl'] = $revurl.$setup->utils->createRevAndPegString($prevRev, $setup->peg).$revurlSuffix;
        }//end if
        
        $bugtraq = new Bugtraq($setup->rep, $setup->svnrep, $ppath);
        
        $setup->vars['action']         = '';
        $setup->vars['rev']            = $setup->rev;
        $setup->vars['peg']            = $setup->peg;
        $setup->vars['path']           = escape($ppath);
        $setup->vars['lastchangedrev'] = $lastChangedRev;
        
        if ($logEntry) {
            $setup->vars['date']   = $logEntry->date;
            $setup->vars['age']    = $setup->utils->datetimeFormatDuration($setup->lang, time() - strtotime($logEntry->date));
            $setup->vars['author'] = $logEntry->author;
            $setup->vars['log']    = nl2br($bugtraq->replaceIDs($setup->utils->createAnchors($setup->utils->xmlEntities($logEntry->msg))));
        }//end if
        
        $setup->vars['revurl']  = $setup->config->getURL($setup->rep, ($setup->path == '/' ? '' : $setup->path), 'revision').$isDirString.$setup->passRevString;
        $setup->vars['revlink'] = '<a href="'.$setup->vars['revurl'].'">'.$setup->lang['LASTMOD'].'</a>';
        
        if ($history && count($history->entries) > 1) {
            $setup->vars['compareurl']  = $setup->config->getURL($setup->rep, '', 'comp').'compare[]='.urlencode($history->entries[1]->path).'@'.$history->entries[1]->rev.WebSvnCons::ANDAMP.'compare[]='.urlencode($history->entries[0]->path).'@'.$history->entries[0]->rev;
            $setup->vars['comparelink'] = '<a href="'.$setup->vars['compareurl'].'">'.$setup->lang['DIFFPREV'].'</a>';
        }//end if
        
        $setup->vars['logurl']  = $setup->config->getURL($setup->rep, $setup->path, 'log').$isDirString.$setup->passRevString;
        $setup->vars['loglink'] = '<a href="'.$setup->vars['logurl'].'">'.$setup->lang['VIEWLOG'].'</a>';
        
        // Set up the tarball link.
        if ($setup->rep->isDownloadAllowed($setup->path) && !isset($setup->vars['warning'])) {
            $setup->vars['downloadurl'] = $setup->config->getURL($setup->rep, $setup->path, 'dl').$isDirString.$setup->passRevString;
        }//end if
        
        $setup->vars['compare_form']    = '<form method="get" action="'.$setup->config->getURL($setup->rep, '', 'comp').'" id="compare">';
        $setup->vars['compare_form']   .= '<input type="hidden" name="repname" value="'.$setup->repname.'" />';
        $setup->vars['compare_submit']  = '<input type="submit" value="'.$setup->lang['COMPAREPATHS'].'" />';
        $setup->vars['compare_endform'] = '</form>';
        $setup->vars['showlastmod']     = $setup->config->showLastModInListing();
        
        $setup->listing = showTreeDir($setup);
        
        // Validates access.
        $this->validatesAccess($setup);
        
        $setup->vars['restricted'] = !$setup->rep->hasReadAccess($setup->path);
        
        // Render template.
        $this->renderTemplate($setup, 'directory');
        
    }//end __construct()
    
    
}//end ListingController

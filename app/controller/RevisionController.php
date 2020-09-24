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
use app\business\bugtraq\Bugtraq;
use app\business\setup\WebSvnCons;

/**
 * RevisionController class.
 */
class RevisionController extends AbstractController
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
        
        // Executes parant constructor.
        parent::__construct();
        
        // Make sure that we have a repository.
        $this->validatesRepo($setup);
        
        $setup->svnrep = new SVNRepository($setup);
        
        $ppath = ($setup->path === '' || $setup->path[0] !== '/') ? '/'.$setup->path : $setup->path;
        $setup->vars['pathlinks'] = $setup->utils->createPathLinks($setup, $ppath);
        $setup->passRevString     = $setup->utils->createRevAndPegString($setup->rev, $setup->peg);
        
        // Find the youngest revision containing changes for the given path.
        $history = $setup->svnrep->getLog($setup->path, 'HEAD', 1, true, 2, ($setup->path === '/') ? '' : $setup->peg);
        
        if (!$history) {
            
            unset($setup->vars['error']);
            $history = $setup->svnrep->getLog($setup->path, '', '', true, 2, ($setup->path === '/') ? '' : $setup->peg);
            
            if (!$history) {
                http_response_code(WebSvnCons::ERROR_404);
                $setup->vars['error'] = $setup->lang['NOPATH'];
            }//end if
            
        }//end if
        
        $youngest = ($history && isset($history->entries[0])) ? (int)$history->entries[0]->rev : 0;
        $setup->vars['youngestrev'] = $youngest;
        
        // TODO The "youngest" rev is often incorrect when both path and rev are specified.
        // If a path was last modified at rev M and the URL contains rev N, it uses rev N.
        // Unless otherwise specified, we get the log details of the latest change.
        $lastChangedRev = ($setup->rev) ? $setup->rev : $youngest;
        
        $history = $setup->svnrep->getLog($setup->path, $lastChangedRev, 1, false, 2, $setup->peg, true);
        
        if (!$history) {
            http_response_code(WebSvnCons::ERROR_404);
            $setup->vars['error'] = $setup->lang['NOPATH'];
            
        }//end if
        
        if (empty($setup->rev)) {
            $setup->rev = $lastChangedRev;
        }//end if
        
        // Generate links to newer and older revisions.
        $revurl = $setup->config->getURL($setup->rep, $setup->path, 'revision');
        
        if ($setup->rev < $youngest) {
            
            $setup->vars['goyoungesturl'] = $setup->config->getURL($setup->rep, $setup->path, 'revision');
            $setup->vars['goyoungestlink'] = '<a href="'.$setup->vars['goyoungesturl'].'"'.($youngest ? ' title="'.$setup->lang['REV'].' '.$youngest.'"' : '').'>'.$setup->lang['GOYOUNGEST'].'</a>';
            
            $history2 = $setup->svnrep->getLog($setup->path, $setup->rev, $youngest, true, 2, $setup->peg);
            
            if (isset($history2->entries[1])) {
                
                $nextRev = $history2->entries[1]->rev;
                
                if ($nextRev != $youngest) {
                    $setup->vars['nextrev']    = $nextRev;
                    $setup->vars['nextrevurl'] = $revurl.$setup->utils->createRevAndPegString($nextRev, $setup->path !== '/' ? $setup->peg ? $setup->peg : $setup->rev : '');
                }//end if
                
            }//end if
            
            unset($setup->vars['error']);
            
        }//end if
        
        if (isset($history->entries[1])) {
            
            $prevRev = $history->entries[1]->rev;
            $prevPath = $history->entries[1]->path;
            $setup->vars['prevrev'] = $prevRev;
            $setup->vars['prevrevurl'] = $revurl.$setup->utils->createRevAndPegString($prevRev, $setup->path !== '/' ? ($setup->peg ? $setup->peg : $setup->rev) : '');
            
        }//end if
        
        // Save the entry from which we pull information for the current revision.
        $logEntry = (isset($history->entries[0])) ? $history->entries[0] : null;
        
        $bugtraq = new Bugtraq($setup->rep, $setup->svnrep, $ppath);
        
        $setup->vars['action'] = '';
        $setup->vars['rev']    = $setup->rev;
        $setup->vars['peg']    = $setup->peg;
        $setup->vars['path']   = escape($ppath);
        
        if ($logEntry) {
            $setup->vars['date']   = $logEntry->date;
            $setup->vars['age']    = $setup->utils->datetimeFormatDuration($setup->lang, time() - strtotime($logEntry->date));
            $setup->vars['author'] = $logEntry->author;
            $setup->vars['log']    = nl2br($bugtraq->replaceIDs($setup->utils->createAnchors($setup->utils->xmlEntities($logEntry->msg))));
        }//end if
        
        $isDir = @$_REQUEST['isdir'] === '1' || $setup->path === '' || $setup->path === '/';
        
        $setup->vars['logurl']  = $setup->config->getURL($setup->rep, $setup->path, 'log').$setup->passRevString.($isDir ?  WebSvnCons::ANDAMP.'isdir=1' : '');
        $setup->vars['loglink'] = '<a href="'.$setup->vars['logurl'].'">'.$setup->lang['VIEWLOG'].'</a>';
        
        $dirPath = $isDir ? $setup->path : dirname($setup->path).'/';
        
        $setup->vars['directoryurl']  = $setup->config->getURL($setup->rep, $dirPath, 'dir').$setup->passRevString.'#'.$setup->utils->anchorForPath($dirPath, $setup->config->treeView);
        $setup->vars['directorylink'] = '<a href="'.$setup->vars['directoryurl'].'">'.$setup->lang['LISTING'].'</a>';
        
        if ($setup->path !== $dirPath) {
            $setup->vars['filedetailurl']  = $setup->config->getURL($setup->rep, $setup->path, 'file').$setup->passRevString;
            $setup->vars['filedetaillink'] = '<a href="'.$setup->vars['filedetailurl'].'">'.$setup->lang['FILEDETAIL'].'</a>';
            $setup->vars['blameurl']       = $setup->config->getURL($setup->rep, $setup->path, 'blame').$setup->passRevString;
            $setup->vars['blamelink']      = '<a href="'.$setup->vars['blameurl'].'">'.$setup->lang['BLAME'].'</a>';
        }//end if
        
        $changes = $logEntry ? $logEntry->mods : array();
        if (!is_array($changes)) {
            $changes = array();
        }//end if
        
        usort($changes, array($setup->svnrep->svnLook, 'SVNLogEntry_compare'));
        
        $row = 0;
        
        $prevRevString = $setup->utils->createRevAndPegString($setup->rev - 1, $setup->rev - 1);
        $thisRevString = $setup->utils->createRevAndPegString($setup->rev, $setup->rev);
        
        foreach ($changes as $change) {
            
            $linkRevString = ($change->action === 'D') ? $prevRevString : $thisRevString;
            $isDir         = $change->isdir;
            
            if ($isDir !== null) {
                $isFile = !$isDir;
            } else {
                // NOTE: This is a hack (runs `svn info` on each path) to see if it's a file.
                // `svn log --verbose --xml` should really provide this info, but does only in recent version?
                $lastSeenRev = ($change->action === 'D') ? $setup->rev - 1 : $setup->rev;
                $isFile      = $setup->svnrep->isFile($change->path, $lastSeenRev, $lastSeenRev);
            }//end if
            
            if (!$isFile && $change->path !== '/') {
                $change->path .= '/';
            }//end if
            
            $resourceExisted = $change->action === 'M' || $change->copyfrom;
            
            $setup->listing[] = array(
                    'path'      => escape($change->path),
                    'oldpath'   => $change->copyfrom ? $change->copyfrom.' @ '.$change->copyrev : '',
                    'action'    => $change->action,
                    'added'     => $change->action === 'A',
                    'deleted'   => $change->action === 'D',
                    'modified'  => $change->action === 'M',
                    'detailurl' => $setup->config->getURL($setup->rep, $change->path, ($isFile ? 'file' : 'dir')).$linkRevString,
                    'logurl'    => $setup->config->getURL($setup->rep, $change->path, 'log').$linkRevString.($isFile ? '' : WebSvnCons::ANDAMP.'isdir=1'),
                    'diffurl'   => $resourceExisted ? $setup->config->getURL($setup->rep, $change->path, 'diff').$linkRevString : '',
                    'blameurl'  => $resourceExisted ? $setup->config->getURL($setup->rep, $change->path, 'blame').$linkRevString : '',
                    'rowparity' => $row,
                    'notinpath' => substr($change->path, 0, strlen($setup->path)) != $setup->path,
            );
            
            $row = 1 - $row;
            
        }//end foreach
        
        if (isset($prevRev)) {
            $setup->vars['compareurl']  = $setup->config->getURL($setup->rep, '', 'comp').'compare[]='.urlencode($prevPath).'@'.$prevRev. WebSvnCons::ANDAMP.'compare[]='.urlencode($setup->path).'@'.$setup->rev;
            $setup->vars['comparelink'] = '<a href="'.$setup->vars['compareurl'].'">'.$setup->lang['DIFFPREV'].'</a>';
        }//end if
        
        $this->validatesAccess($setup);
        
        $setup->vars['restricted'] = !$setup->rep->hasReadAccess($setup->path);
        
        // Render template.
        $this->renderTemplate($setup, 'revision');
        
    }//end __construct()
    
    
}//end RevisionController class
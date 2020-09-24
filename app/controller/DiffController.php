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
 * DiffController class.
 */
class DiffController extends AbstractController
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
        
        // Executes parant constructor.
        parent::__construct();
        
        $setup->vars['action'] = $setup->lang['DIFF'];
        $all                   = (@$_REQUEST['all'] === '1');
        $ignoreWhitespace      = $setup->config->getIgnoreWhitespacesInDiff();
        
        if (array_key_exists('ignorews', $_REQUEST)) {
            $ignoreWhitespace = (bool)$_REQUEST['ignorews'];
        }//end if
        
        // Make sure that we have a repository.
        $this->validatesRepo($setup);
        
        $setup->svnrep = new SVNRepository($setup);
        
        // If there's no revision info, go to the lastest revision for this path.
        $history = $setup->svnrep->getLog($setup->path, 'HEAD', 1, true, 2, ($setup->path === '/') ? '' : $setup->peg);
        
        if (!$history) {
            unset($setup->vars['error']);
            $history = $setup->svnrep->getLog($setup->path, '', '', true, 2, ($setup->path === '/') ? '' : $setup->peg);
        }//end if
        
        $youngest = ($history && isset($history->entries[0])) ? (int)$history->entries[0]->rev : false;
        
        if (empty($setup->rev)) {
            $setup->rev = $youngest;
        }//end if
        
        $history = $setup->svnrep->getLog($setup->path, $setup->rev, 1, false, 2, $setup->peg);
        
        if ($setup->path[0] !== '/') {
            $ppath = '/'.$setup->path;
        } else {
            $ppath = $setup->path;
        }//end if
        
        $prevrev = @$history->entries[1]->rev;
        
        $setup->vars['path']    = escape($ppath);
        $setup->vars['rev1']    = $setup->rev;
        $setup->vars['rev2']    = $prevrev;
        $setup->vars['prevrev'] = $prevrev;
        
        if (isset($history->entries[0])) {
            $setup->vars['log']    = $setup->utils->xmlEntities($history->entries[0]->msg);
            $setup->vars['date']   = $history->entries[0]->date;
            $setup->vars['age']    = $setup->utils->datetimeFormatDuration($setup->lang, time() - strtotime($history->entries[0]->date));
            $setup->vars['author'] = $history->entries[0]->author;
            $setup->vars['rev']    = $setup->vars['rev1'] = $history->entries[0]->rev;
            $setup->vars['peg']    = $setup->peg;
        }//end if
        
        $setup->vars['pathlinks'] = $setup->utils->createPathLinks($setup, $ppath);
        $setup->passRevString     = $setup->utils->createRevAndPegString($setup->rev, $setup->peg);
        
        $passIgnoreWhitespace = '';
        if ($ignoreWhitespace !== $setup->config->getIgnoreWhitespacesInDiff()) {
            $passIgnoreWhitespace = WebSvnCons::ANDAMP.'ignorews='.($ignoreWhitespace ? '1' : '0');
        }//end if
        
        if ($setup->rev !== $youngest) {
            $setup->vars['goyoungesturl']  = $setup->config->getURL($setup->rep, $setup->path, 'diff').$setup->utils->createRevAndPegString('', $setup->peg).$passIgnoreWhitespace;
            $setup->vars['goyoungestlink'] = '<a href="'.$setup->vars['goyoungesturl'].'"'.($youngest ? ' title="'.$setup->lang['REV'].' '.$youngest.'"' : '').'>'.$setup->lang['GOYOUNGEST'].'</a>';
        }//end if
        
        $revurl = $setup->config->getURL($setup->rep, $setup->path, 'diff');
        if ($setup->rev < $youngest) {
            
            $history2 = $setup->svnrep->getLog($setup->path, $setup->rev, $youngest, true, 2, $setup->peg ? $setup->peg : 'HEAD');
            
            if (isset($history2->entries[1])) {
                
                $nextRev = $history2->entries[1]->rev;
                
                if ($nextRev !== $youngest) {
                    $setup->vars['nextrev']    = $nextRev;
                    $setup->vars['nextrevurl'] = $revurl.$setup->utils->createRevAndPegString($nextRev, $setup->peg).$passIgnoreWhitespace;
                }//end if
                
            }//end if
            
            unset($setup->vars['error']);
            
        }//end if
        
        if (isset($history->entries[1])) {
            $prevRev                   = $history->entries[1]->rev;
            $setup->vars['prevrev']    = $prevRev;
            $setup->vars['prevrevurl'] = $revurl.$setup->utils->createRevAndPegString($prevRev, $setup->peg).$passIgnoreWhitespace;
        }//end if
        
        // Set vars.
        $setup->vars['revurl']         = $setup->config->getURL($setup->rep, $setup->path, 'revision').$setup->passRevString;
        $setup->vars['revlink']        = '<a href="'.$setup->vars['revurl'].'">'.$setup->lang['LASTMOD'].'</a>';
        $setup->vars['logurl']         = $setup->config->getURL($setup->rep, $setup->path, 'log').$setup->passRevString;
        $setup->vars['loglink']        = '<a href="'.$setup->vars['logurl'].'">'.$setup->lang['VIEWLOG'].'</a>';
        $setup->vars['filedetailurl']  = $setup->config->getURL($setup->rep, $setup->path, 'file').$setup->passRevString;
        $setup->vars['filedetaillink'] = '<a href="'.$setup->vars['filedetailurl'].'">'.$setup->lang['FILEDETAIL'].'</a>';
        $setup->vars['blameurl']       = $setup->config->getURL($setup->rep, $setup->path, 'blame').$setup->passRevString;
        $setup->vars['blamelink']      = '<a href="'.$setup->vars['blameurl'].'">'.$setup->lang['BLAME'].'</a>';
        
        // Check for binary file type before diffing.
        $svnMimeType = $setup->svnrep->getProperty($setup->path, 'svn:mime-type', $setup->rev);
        
        // If no previous revision exists, bail out before diffing.
        if (!$setup->rep->getIgnoreSvnMimeTypes() && preg_match('~application/*~', $svnMimeType)) {
            $setup->vars['warning'] = 'Cannot display diff of binary file. (svn:mime-type = '.$svnMimeType.')';
        } else if (!$prevrev) {
            $setup->vars['noprev'] = 1;
        } else {
            
            $diff = $setup->config->getURL($setup->rep, $setup->path, 'diff').$setup->passRevString;
            
            if ($all) {
                $setup->vars['showcompactlink'] = '<a href="'.$diff.$passIgnoreWhitespace.'">'.$setup->lang['SHOWCOMPACT'].'</a>';
            } else {
                $setup->vars['showalllink'] = '<a href="'.$diff.$passIgnoreWhitespace.WebSvnCons::ANDAMP.'all=1'.'">'.$setup->lang['SHOWENTIREFILE'].'</a>';
            }//end if
            
            $passShowAll = ($all ? WebSvnCons::ANDAMP.'all=1' : '');
            $toggleIgnoreWhitespace = '';
            
            if ($ignoreWhitespace === $setup->config->getIgnoreWhitespacesInDiff()) {
                $toggleIgnoreWhitespace = WebSvnCons::ANDAMP.'ignorews='.($ignoreWhitespace ? '0' : '1');
            }//end if
            
            if ($ignoreWhitespace) {
                $setup->vars['regardwhitespacelink'] = '<a href="'.$diff.$passShowAll.$toggleIgnoreWhitespace.'">'.$setup->lang['REGARDWHITESPACE'].'</a>';
            } else {
                $setup->vars['ignorewhitespacelink'] = '<a href="'.$diff.$passShowAll.$toggleIgnoreWhitespace.'">'.$setup->lang['IGNOREWHITESPACE'].'</a>';
            }//end if
            
            // Get the contents of the two files.
            $newerFile      = $setup->utils->tempnamWithCheck($setup->config->getTempDir(), '');
            $newerFileHl    = $newerFile.'highlight';
            $normalNew      = $setup->svnrep->getFileContents($history->entries[0]->path, $newerFile, $history->entries[0]->rev, $setup->peg, '', 'no');
            $highlightedNew = $setup->svnrep->getFileContents($history->entries[0]->path, $newerFileHl, $history->entries[0]->rev, $setup->peg, '', 'line');
            $olderFile      = $setup->utils->tempnamWithCheck($setup->config->getTempDir(), '');
            $olderFileHl    = $olderFile.'highlight';
            $normalOld      = $setup->svnrep->getFileContents($history->entries[0]->path, $olderFile, $history->entries[1]->rev, $setup->peg, '', 'no');
            $highlightedOld = $setup->svnrep->getFileContents($history->entries[0]->path, $olderFileHl, $history->entries[1]->rev, $setup->peg, '', 'line');
            
            // TODO: Figured out why diffs across a move/rename are currently broken.
            $highlighted = ($highlightedNew && $highlightedOld);
            if ($highlighted) {
                $setup->listing = do_diff($all, $ignoreWhitespace, $highlighted, $newerFile, $olderFile, $newerFileHl, $olderFileHl, $setup);
            } else {
                $setup->listing = do_diff($all, $ignoreWhitespace, $highlighted, $newerFile, $olderFile, null, null, $setup);
            }//end if
            
            // Remove our temporary files.
            @unlink($newerFile);
            @unlink($olderFile);
            @unlink($newerFileHl);
            @unlink($olderFileHl);
            
        }//end if
        
        $this->validatesAccess($setup);
        
        // Render template.
        $this->renderTemplate($setup, 'diff');
        
    }//end __construct()
    
    
}//end DiffController class

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
use xsgaphp\exceptions\XsgaFileNotFoundException;
use app\business\svn\SVNLog;

/**
 * FiledetailsController class.
 */
class FiledetailsController extends AbstractController
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
        
        // Executes parent constructor.
        parent::__construct();
        
        // Make sure that we have a repository.
        $this->validatesRepo($setup);
        
        $setup->svnrep = new SVNRepository($setup);
        
        if ($setup->path[0] !== '/') {
            $ppath = '/'.$setup->path;
        } else {
            $ppath = $setup->path;
        }//end if
        
        $useMime = false;
        
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
            $setup->rev = !$setup->peg ? $youngest : min($setup->peg, $youngest);
        }//end if
        
        $extn = strtolower(strrchr($setup->path, '.'));
        
        // Check to see if the user has requested that this type be zipped and sent to the browser as an attachment.
        $this->getZipped($setup, $history, $extn);
                
        // Check to see if we should serve it with a particular content-type.
        // The content-type could come from an svn:mime-type property on the file, or from the $contentType array in setup.php.
        if (!$setup->rep->getIgnoreSvnMimeTypes()) {
            $svnMimeType = $setup->svnrep->getProperty($setup->path, 'svn:mime-type', $setup->rev);
        }//end if
        
        if (!$setup->rep->getIgnoreWebSVNContentTypes()) {
            $setupContentType = @$setup->contentType[$extn];
        }//end if
        
        // Use the documented priorities when establishing what content-type to use.
        if (!empty($svnMimeType) && $svnMimeType !== 'application/octet-stream') {
            $mimeType = $svnMimeType;
        } else if (!empty($setupContentType)) {
            $mimeType = $setupContentType;
        } else if (!empty($svnMimeType)) {
            $mimeType = $svnMimeType; // Use SVN's default of 'application/octet-stream'
        } else {
            $mimeType = '';
        }//end if
        
        $useMime = ($mimeType) ? @$_REQUEST['usemime'] : false;
        if ($history && !empty($mimeType) && !$useMime) {
            // Save MIME type for later before possibly clobbering.
            $useMime = $mimeType;
            // If a MIME type exists but is set to be ignored, set it to an empty string.
            foreach ($setup->config->inlineMimeTypes as $inlineType) {
                if (preg_match('|'.$inlineType.'|', $mimeType)) {
                    $mimeType = '';
                    break;
                }//end if
            }//end foreach
        }//end if
        
        // If a MIME type is associated with the file, deliver with Content-Type header.
        if ($history && !empty($mimeType) && $setup->rep->hasReadAccess($setup->path)) {
            $base = basename($setup->path);
            header('Content-Type: '.$mimeType);
            header("Content-Disposition: inline; filename*=UTF-8''" . rawurlencode($base));
            $setup->svnrep->getFileContents($setup->path, '', $setup->rev, $setup->peg);
            exit;
        }//end if
        
        // Display the file inline using WebSVN.
        $setup->vars['action'] = '';
        $setup->vars['path']   = escape($ppath);
        
        if (isset($history->entries[0])) {
            $setup->vars['log']    = $setup->utils->xmlEntities($history->entries[0]->msg);
            $setup->vars['date']   = $history->entries[0]->date;
            $setup->vars['age']    = $setup->utils->datetimeFormatDuration($setup->lang, time() - strtotime($history->entries[0]->date));
            $setup->vars['author'] = $history->entries[0]->author;
        }//end if
        
        $setup->vars['pathlinks'] = $setup->utils->createPathLinks($setup, $ppath);
        $setup->passRevString     = $setup->utils->createRevAndPegString($setup->rev, $setup->peg);
        
        if ($setup->rev !== $youngest) {
            $setup->vars['goyoungesturl']  = $setup->config->getURL($setup->rep, $setup->path, 'file').$setup->utils->createRevAndPegString($youngest, $setup->peg);
            $setup->vars['goyoungestlink'] = '<a href="'.$setup->vars['goyoungesturl'].'"'.($youngest ? ' title="'.$setup->lang['REV'].' '.$youngest.'"' : '').'>'.$setup->lang['GOYOUNGEST'].'</a>';
        }//end if
        
        $revurl = $setup->config->getURL($setup->rep, $setup->path, 'file');
        if ($setup->rev < $youngest) {
            $history2 = $setup->svnrep->getLog($setup->path, $setup->rev, $youngest, true, 2, $setup->peg ? $setup->peg : 'HEAD');
            if (isset($history2->entries[1])) {
                $nextRev = $history2->entries[1]->rev;
                if ($nextRev !== $youngest) {
                    $setup->vars['nextrev']    = $nextRev;
                    $setup->vars['nextrevurl'] = $revurl.$setup->utils->createRevAndPegString($nextRev, $setup->peg);
                }//end if
            }//end if
            unset($setup->vars['error']);
        }//end if
        
        $history3 = $setup->svnrep->getLog($setup->path, $setup->rev, 1, true, 2, $setup->peg ? $setup->peg : 'HEAD');
        if (isset($history3->entries[1])) {
            $prevRev                   = $history3->entries[1]->rev;
            $setup->vars['prevrev']    = $prevRev;
            $setup->vars['prevrevurl'] = $revurl.$setup->utils->createRevAndPegString($prevRev, $setup->peg);
        }//end if
        
        unset($setup->vars['error']);
        
        // Set vars.
        $setup->vars['revurl']    = $setup->config->getURL($setup->rep, $setup->path, 'revision').$setup->passRevString;
        $setup->vars['revlink']   = '<a href="'.$setup->vars['revurl'].'">'.$setup->lang['LASTMOD'].'</a>';
        $setup->vars['logurl']    = $setup->config->getURL($setup->rep, $setup->path, 'log').$setup->passRevString;
        $setup->vars['loglink']   = '<a href="'.$setup->vars['logurl'].'">'.$setup->lang['VIEWLOG'].'</a>';
        $setup->vars['blameurl']  = $setup->config->getURL($setup->rep, $setup->path, 'blame').$setup->passRevString;
        $setup->vars['blamelink'] = '<a href="'.$setup->vars['blameurl'].'">'.$setup->lang['BLAME'].'</a>';
        
        if ($history === null || count($history->entries) > 1) {
            $setup->vars['diffurl']  = $setup->config->getURL($setup->rep, $setup->path, 'diff').$setup->passRevString;
            $setup->vars['difflink'] = '<a href="'.$setup->vars['diffurl'].'">'.$setup->lang['DIFFPREV'].'</a>';
        }//end if
        
        if ($setup->rep->isDownloadAllowed($setup->path)) {
            $setup->vars['downloadlurl'] = $setup->config->getURL($setup->rep, $setup->path, 'dl').$setup->passRevString;
            $setup->vars['downloadlink'] = '<a href="'.$setup->vars['downloadlurl'].'">'.$setup->lang['DOWNLOAD'].'</a>';
        }//end if
        
        // Restore preserved value to use for 'mimelink' variable.
        $mimeType = $useMime;
        
        // If there was a MIME type, create a link to display file with that type.
        if ($mimeType && !isset($setup->vars['warning'])) {
            $setup->vars['mimeurl']  = $setup->config->getURL($setup->rep, $setup->path, 'file').'usemime=1'.WebSvnCons::ANDAMP.$setup->passRevString;
            $setup->vars['mimelink'] = '<a href="'.$setup->vars['mimeurl'].'">'.$setup->lang['VIEWAS'].' "'.$mimeType.'"</a>';
        }//end if
        
        $setup->vars['rev'] = escape($setup->rev);
        $setup->vars['peg'] = $setup->peg;
        
        $this->validatesAccess($setup);
        
        $this->validatesFileExists($setup);
        
        // Render template.
        $this->renderTemplate($setup, 'file');
        
    }//end __construct()
    
    
    /**
     * Get zipped.
     * 
     * @param Setup  $setup
     * @param SVNLog $history
     * @param string $extn
     * 
     * @return void
     * 
     * @access private
     */
    private function getZipped(Setup $setup, SVNLog $history, $extn)
    {
        
        // Check to see if the user has requested that this type be zipped and sent to the browser as an attachment.
        if ($history && 
                isset($setup->zipped) && 
                in_array($extn, $setup->zipped) && 
                $setup->rep->hasReadAccess($setup->path)) {
            
            $base = basename($setup->path);
            
            header('Content-Type: application/gzip');
            header('Content-Disposition: attachment; filename='.urlencode($base).'.gz');
            
            // Get the file contents and pipe into gzip. All this without creating a temporary file. Damn clever.
            $setup->svnrep->getFileContents($setup->path, '', $setup->rev, $setup->peg, '| '.$setup->config->gzip.' -n -f');
            
            exit;
            
        }//end if
        
    }//end getZipped()
    
    
    /**
     * Validates file exists.
     * 
     * @param Setup $setup
     * 
     * @throws XsgaFileNotFoundException
     * 
     * @return void
     * 
     * @access private
     */
    private function validatesFileExists(Setup $setup)
    {
        
        if (!$setup->svnrep->isFile($setup->path, $setup->rev, $setup->peg)) {
            
            // Error message.
            $errorMsg = 'File not found';
            
            // Logger.
            $this->logger->error($errorMsg);
            
            throw new XsgaFileNotFoundException($errorMsg, WebSvnCons::ERROR_404);
            
        }//end if
        
    }//end validatesFileExists()
        
    
}//end FiledetailsController class

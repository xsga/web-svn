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
use app\business\setup\WebSvnCons;
use app\business\svn\SVNRepository;
use xsgaphp\exceptions\XsgaFileNotFoundException;
use xsgaphp\exceptions\XsgaException;
use xsgaphp\exceptions\XsgaSecurityException;

/**
 * DlController class.
 */
class DlController extends AbstractController
{
    
    
    /**
     * Constructor.
     * 
     * @param Setup $setup Setup instance.
     * 
     * @throws XsgaFileNotFoundException
     * @throws XsgaException
     * 
     * @access public
     */
    public function __construct(Setup $setup)
    {
        
        // Executes parent constructor.
        parent::__construct();
        
        // Make sure that downloading the specified file/directory is permitted.
        $this->ValidatesDownload($setup);
        
        // Validate repository.
        $this->validatesRepo($setup);
        
        $setup->svnrep = new SVNRepository($setup);
        
        // Fetch information about a revision (if unspecified, the latest) for this path.
        if (empty($setup->rev)) {
            $history = $setup->svnrep->getLog($setup->path, 'HEAD', '', true, 1, $setup->peg);
        } else if ($setup->rev === $setup->peg) {
            $history = $setup->svnrep->getLog($setup->path, '', 1, true, 1, $setup->peg);
        } else {
            $history = $setup->svnrep->getLog($setup->path, $setup->rev, $setup->rev - 1, true, 1, $setup->peg);
        }//end if
        
        $logEntry = ($history) ? $history->entries[0] : null;
        
        if (!$logEntry) {
            
            // Error message.
            $errorMsg = WebSvnCons::DL_ERROR_01.$setup->path;
            
            // Logger.
            $this->logger->error($errorMsg);
            
            throw new XsgaFileNotFoundException($errorMsg, WebSvnCons::ERROR_404);
            
        }//end if
        
        if (empty($setup->rev)) {
            $setup->rev = $logEntry->rev;
        }//end if
        
        // Create a temporary filename to be used for a directory to archive a download.
        // Here we have an unavoidable but highly unlikely to occur race condition.
        $tempDir = $setup->utils->tempnamWithCheck($setup->config->getTempDir(), 'websvn');
        
        @unlink($tempDir);
        mkdir($tempDir);
        
        // Create the name of the directory being archived.
        $archiveName = $setup->path;
        $isDir = (substr($archiveName, -1) === '/');
        
        if ($isDir) {
            $archiveName = substr($archiveName, 0, -1);
        }//end if
        
        $archiveName = basename($archiveName);
        
        if ($archiveName === '') {
            $archiveName = $setup->rep->name;
        }//end if
        
        $plainfilename = $archiveName;
        $archiveName .= '.r'.$setup->rev;
        
        // Export the requested path from SVN repository to the temp directory.
        $svnExportResult = $setup->svnrep->exportRepositoryPath($setup->path, $tempDir.DIRECTORY_SEPARATOR.$archiveName, $setup->rev, $setup->peg);
        
        if ($svnExportResult !== 0) {
            
            // Error message.
            $errorMsg = WebSvnCons::DL_ERROR_02.$archiveName.
            
            // Logger.
            $this->logger->error($errorMsg);
            
            removeDirectory($tempDir);
            
            throw new XsgaException($errorMsg);
            
        }//end if
        
        // For security reasons, disallow direct downloads of filenames that
        // are a symlink, since they may be a symlink to anywhere (/etc/passwd)
        // Deciding whether the symlink is relative and legal within the
        // repository would be nice but seems to error prone at this moment.
        if ( is_link($tempDir.DIRECTORY_SEPARATOR.$archiveName) ) {
            
            // Error message.
            $errorMsg = 'Download of symlinks disallowed: "'.xml_entities($archiveName).'"';
            
            // Logger.
            $this->logger->error($errorMsg);
            
            removeDirectory($tempDir);
            
            throw new XsgaSecurityException($errorMsg);
            
        }//end if
        
        // Set timestamp of exported directory to timestamp of the revision.
        $revDate = $logEntry->date;
        $timestamp = mktime(
                substr($revDate, 11, 2), // hour
                substr($revDate, 14, 2), // minute
                substr($revDate, 17, 2), // second
                substr($revDate, 5, 2),  // month
                substr($revDate, 8, 2),  // day
                substr($revDate, 0, 4)   // year
                );
        setDirectoryTimestamp($tempDir, $timestamp);
        
        // Change to temp directory so that only relative paths are stored in archive.
        $oldcwd = getcwd();
        chdir($tempDir);
        
        if ($isDir) {
            $downloadMode = $setup->config->getDefaultDirectoryDlMode();
        } else {
            $downloadMode = $setup->config->getDefaultFileDlMode();
        }//end if
        
        // $_REQUEST parameter can override dlmode.
        if (!empty($_REQUEST['dlmode'])) {
            
            $downloadMode = $_REQUEST['dlmode'];
            
            if (substr($logEntry->path, -1) === '/') {
                if (!in_array($downloadMode, $setup->config->validDirectoryDlModes)) {
                    $downloadMode = $setup->config->getDefaultDirectoryDlMode();
                }//end if
            } else {
                if (!in_array($downloadMode, $setup->config->validFileDlModes)) {
                    $downloadMode = $setup->config->getDefaultFileDlMode();
                }//end if
            }//end if
            
        }//end if
        
        $downloadArchive = $archiveName;
        
        if ($downloadMode === 'plain') {
            
            $downloadMimeType = 'application/octet-stream';
            
        } else if ($downloadMode === 'zip') {
            
            $downloadMimeType = 'application/zip';
            $downloadArchive .= '.zip';
            
            // Create zip file.
            $cmd = $setup->config->zip.' --symlinks -r '.quote($downloadArchive).' '.quote($archiveName);
            execCommand($cmd, $retcode);
            
            if ($retcode !== 0) {
                $this->logger->error('Unable to call zip command: '.$cmd);
                print 'Unable to call zip command. See webserver error log for details.';
            }//end if
            
        } else {
            
            $downloadMimeType = 'application/gzip';
            $downloadArchive .= '.tar.gz';
            $tarArchive       = $archiveName.'.tar';
            
            // Create the tar file.
            $retcode = 0;
            
            if (class_exists('Archive_Tar')) {
                
                $tar     = new \Archive_Tar($tarArchive);
                $created = $tar->create(array($archiveName));
                
                if (!$created) {
                    
                    $retcode = 1;
                    
                    // Error message.
                    $errorMsg = WebSvnCons::DL_ERROR_03;
                    
                    // Logger.
                    $this->logger->error($errorMsg);
                    
                }//end if
                
            } else {
                
                $cmd = $setup->config->tar.' -cf '.quote($tarArchive).' '.quote($archiveName);
                execCommand($cmd, $retcode);
                
                if ($retcode !== 0) {
                    
                    // Error message.
                    $errorMsg = WebSvnCons::DL_ERROR_04.$cmd;
                    
                    // Logger.
                    $this->logger->error($errorMsg);
                    
                }//end if
                
            }//end if
            
            if ($retcode !== 0) {
                chdir($oldcwd);
                removeDirectory($tempDir);
                
                throw new XsgaException($errorMsg, WebSvnCons::ERROR_500);
                
            }//end if
            
            // Set timestamp of tar file to timestamp of revision.
            touch($tarArchive, $timestamp);
            
            $srcHandle = fopen($tarArchive, 'rb');
            $dstHandle = gzopen($downloadArchive, 'wb');
            
            if (!$srcHandle || !$dstHandle) {
                
                // Error message.
                $errorMsg = WebSvnCons::DL_ERROR_06;
                
                // Logger.
                $this->logger->error($errorMsg);
                
                chdir($oldcwd);
                removeDirectory($tempDir);
                
                throw new XsgaException($errorMsg, WebSvnCons::ERROR_500);
                
            }//end if
            
            while (!feof($srcHandle)) {
                gzwrite($dstHandle, fread($srcHandle, 1024 * 512));
            }//end while
            
            fclose($srcHandle);
            gzclose($dstHandle);
            
        }//end if
        
        // Give the file to the browser.
        if (is_readable($downloadArchive)) {
            
            if ($downloadMode === 'plain') {
                $downloadFilename = $plainfilename;
            } else {
                $downloadFilename = $setup->rep->name.'-'.$downloadArchive;
            }//end if
            
            header('Content-Type: '.$downloadMimeType);
            header('Content-Length: '.filesize($downloadArchive));
            header('Content-Disposition: attachment; filename="'. $downloadFilename .'"');
            
            readfile($downloadArchive);
            
        } else {
            
            chdir($oldcwd);
            removeDirectory($tempDir);
            
            // Error message.
            $errorMsg = WebSvnCons::DL_ERROR_07.$setup->utils->xmlEntities($downloadArchive);
            
            // Logger.
            $this->logger->error($errorMsg);
            
            throw new XsgaFileNotFoundException($errorMsg, WebSvnCons::ERROR_404);
            
        }//end if
        
        chdir($oldcwd);
        removeDirectory($tempDir);
        
    }//end __construct()
    
    
}//end DlController class

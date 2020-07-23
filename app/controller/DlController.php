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

/**
 * DlController class.
 */
class DlController extends AbstractController
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
        
        // Make sure that downloading the specified file/directory is permitted.
        if (!$setup->rep->isDownloadAllowed($setup->path)) {
            
            header(WebSvnCons::HTTP_403, true, 403);
            
            // Logger.
            $logger->error(WebSvnCons::DL_ERROR_01.$setup->path);
            
            print WebSvnCons::DL_ERROR_01.$setup->utils->xmlEntities($setup->path);
            exit;
            
        }//end if
        
        if ($setup->rep) {
            
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
                
                header(WebSvnCons::HTTP_404, true, 404);
                
                // Logger.
                $logger->error(WebSvnCons::DL_ERROR_01.$setup->path);
                
                print WebSvnCons::DL_ERROR_01.$setup->utils->xmlEntities($setup->path);
                exit(0);
                
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
                
                header(WebSvnCons::HTTP_500, true, 500);
                
                // Logger.
                $logger->error(WebSvnCons::DL_ERROR_02.$archiveName);
                
                print WebSvnCons::DL_ERROR_02.'"'.$setup->utils->xmlEntities($archiveName).'".';
                removeDirectory($tempDir);
                exit(0);
                
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
                $downloadMode = $setup->config->getDefaultFolderDlMode();
            } else {
                $downloadMode = $setup->config->getDefaultFileDlMode();
            }//end if
            
            // $_REQUEST parameter can override dlmode.
            if (!empty($_REQUEST['dlmode'])) {
                
                $downloadMode = $_REQUEST['dlmode'];
                
                if (substr($logEntry->path, -1) === '/') {
                    if (!in_array($downloadMode, $setup->config->validFolderDlModes)) {
                        $downloadMode = $setup->config->getDefaultFolderDlMode();
                    }//end if
                } else {
                    if (!in_array($downloadMode, $config->validFileDlModes)) {
                        $downloadMode = $setup->config->getDefaultFileDlMode();
                    }//end if
                }//end if
                
            }//end if
            
            $downloadArchive = $archiveName;
            
            if ($downloadMode == 'plain') {
                
                $downloadMimeType = 'application/octetstream';
                
            } else if ($downloadMode == 'zip') {
                
                $downloadMimeType = 'application/x-zip';
                $downloadArchive .= '.zip';
                
                // Create zip file
                $cmd = $setup->config->zip.' -r '.quote($downloadArchive).' '.quote($archiveName);
                execCommand($cmd, $retcode);
                
                if ($retcode != 0) {
                    error_log('Unable to call zip command: '.$cmd);
                    print 'Unable to call zip command. See webserver error log for details.';
                }//end if
                
            } else {
                
                $downloadMimeType = 'application/x-gzip';
                $downloadArchive .= '.tar.gz';
                $tarArchive = $archiveName.'.tar';
                
                // Create the tar file.
                $retcode = 0;
                
                if (class_exists('ArchiveTar')) {
                    
                    $tar = new ArchiveTar($tarArchive);
                    $created = $tar->create(array($archiveName));
                    
                    if (!$created) {
                        $retcode = 1;
                        header(WebSvnCons::HTTP_500, true, 500);
                        print WebSvnCons::DL_ERROR_03;
                    }//end if
                    
                } else {
                    
                    $cmd = $setup->config->tar.' -cf '.quote($tarArchive).' '.quote($archiveName);
                    execCommand($cmd, $retcode);
                    
                    if ($retcode != 0) {
                        
                        header(WebSvnCons::HTTP_500, true, 500);
                        
                        // Logger.
                        $logger->error(WebSvnCons::DL_ERROR_04.$cmd);
                        
                        print WebSvnCons::DL_ERROR_05;
                        
                    }//end if
                    
                }//end if
                
                if ($retcode != 0) {
                    chdir($oldcwd);
                    removeDirectory($tempDir);
                    exit(0);
                }//end if
                
                // Set timestamp of tar file to timestamp of revision.
                touch($tarArchive, $timestamp);
                
                $srcHandle = fopen($tarArchive, 'rb');
                $dstHandle = gzopen($downloadArchive, 'wb');
                
                if (!$srcHandle || !$dstHandle) {
                    
                    header(WebSvnCons::HTTP_500, true, 500);
                    
                    // Logger.
                    $logger->error(WebSvnCons::DL_ERROR_06);
                    
                    print WebSvnCons::DL_ERROR_06;
                    
                    chdir($oldcwd);
                    removeDirectory($tempDir);
                    exit(0);
                    
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
                
                header(WebSvnCons::HTTP_404, true, 404);
                
                $error_msg = WebSvnCons::DL_ERROR_07.$setup->utils->xmlEntities($downloadArchive);
                
                // Logger.
                $logger->error($error_msg);
                
                print $error_msg;
                
            }//end if
            
            chdir($oldcwd);
            removeDirectory($tempDir);
            
        } else {
            
            header(WebSvnCons::HTTP_404, true, 404);
            
        }//end if
        
    }//end __construct()
    
    
}//end DlController class

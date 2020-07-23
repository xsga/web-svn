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

/*
 * -----------------------------------------------------------------
 * -----------------------------------------------------------------
 * ----- FOLLOW THE INSTRUCTIONS BELOW TO CONFIGURE YOUR SETUP -----
 * -----------------------------------------------------------------
 * -----------------------------------------------------------------
 */

/*
 * ----------------------
 * PLATFORM CONFIGURATION
 * ----------------------
 */

/*
 * SVN WORKING PATH
 * 
 * Configure the path for Subversion to use for --config-dir
 * (e.g. if accepting certificates is required when using repositories via https)
 * 
 * By default: app_root/tmp
 */
//$this->config->setSvnConfigDir('/tmp');

/*
 * SVN LOCATION
 * 
 * Configure these lines if your commands for SVN and DIFF aren't on your path.
 */
//$this->config->setSVNCommandPath('Path/to/svn/command/');
//$this->config->setDiffPath('Path/to/diff/command/');

/*
 * SYNTAX COLOURING PATH
 * 
 * For syntax colouring, if option enabled. Path to Enscript and SED if aren't on your path.
 */
//$this->config->setEnscriptPath('Path/to/enscript/command/');
//$this->config->setSedPath('Path/to/sed/command/');

/*
 * TAR PATH
 * 
 * For delivered tarballs, if option enabled. Path to TAR if isn't on your path.
 */
//$this->config->setTarPath('Path/to/tar/command/');

/*
 * GZIP PATH
 * 
 * For delivered GZIP'd files and tarballs, if option enabled. Path to GZIP if isn't on your path.
 */
//$this->config->setGZipPath('Path/to/gzip/command/');

/*
 * ZIP PATH
 * 
 * Download folder/file zipped. Path to ZIP if isn't on your path.
 */
//$this->config->setZipPath('Path/to/zip/command/');

/*
 * TRUST CERTIFICATE
 * 
 * Uncomment this line to trust server certificates.
 * This may useful if you use self-signed certificates and have no chance to accept the certificate once via cli.
 */
//$this->config->setTrustServerCert();


/*
 * ----------------
 * REPOSITORY SETUP
 * ----------------
 * 
 * There are 2 methods for defining the repositiories available on the system.
 * Either you list them by hand, in which case you can give each one the name of
 * your choice, or you use the parent path function, in which case the name of
 * the directory is used as the repository name.
 *
 * In all cases, you may optionally supply a group name to the repositories.
 * This is useful in the case that you need to separate your projects. Grouped
 * repositories are referred to using the convention GroupName.RepositoryName
 *
 * You may also optionally specify the URL that clients should use to check out
 * a working copy. If used, it must be specified after the group, username, and
 * password; if these arguments are not needed, then pass null instead. Consult
 * the WebSvnConfig class in include/configclass.php for function details.
 *
 * Performance is much better on local repositories (e.g. accessed by file:///).
 * However, you can also provide an interface onto a remote repository. In this
 * case you should supply the username and password needed to access it.
 *
 * To configure the repositories by hand, copy the appropriate line below,
 * uncomment it and replace the name and URL of your repository.
 */

/* 
 * Local repositories (without and with optional group):
 */
//$this->config->addRepository('NameToDisplay', 'URL to repository (e.g. file:///c:/svn/proj)', 'group');

/*
 * Remote repositories (without and with optional group):
 */ 
//$this->config->addRepository('NameToDisplay', 'URL (e.g. http://path/to/rep)', null, 'username', 'password');
//$this->config->addRepository('NameToDisplay', 'URL (e.g. http://path/to/rep)', 'group', 'username', 'password');

/* 
 * Display Part of a repository as if it was a repository.
 * 
 * Local repositories (without and with optional group):
 */
//$this->config->addRepositorySubpath('NameToDisplay', 'URL to repository (e.g. file:///c:/svn/proj)', 'subpath');
//$this->config->addRepositorySubpath('NameToDisplay', 'URL to repository (e.g. file:///c:/svn/proj)', 'subpath', 'group');

/* 
 * Remote repositories (without and with optional group):
 */
//$this->config->addRepositorySubpath('NameToDisplay', 'URL (e.g. http://path/to/rep)', 'subpath', null, 'username', 'password');
//$this->config->addRepositorySubpath('NameToDisplay', 'URL (e.g. http://path/to/rep)', 'subpath', 'group', 'username', 'password');

/* 
 * To use the parent path method (without and with optional group), uncomment the next line
 * and replace the path with your one. You can call the function several times if you have several parent paths.
 * Note that in this case the path is a filesystem path.
 */
$this->config->parentPath('C:\\xavi\\99 - workspace\\svn_repos');

/* 
 * To exclude a repository from being added by the parentPath method uncomment the next line
 * and replace the path with your one. You can call the function several times if you have several paths to exclude.
 */ 
//$this->config->addExcludedPath('Path/to/parent/excludedRep (e.g. c:\\svn\\excludedRep)');

/* 
 * To add only a subset of repositories specified by the parent path you can call the function with a pattern.
 */
//$this->config->parentPath('Path/to/parent (e.g. c:\\svn)', 'group', '/^beginwith/');


/*
 * -------------
 * LOOK AND FEEL
 * -------------
 */

/* 
 * Add custom template paths or comment out templates to modify the list of user selectable templates.
 * The first added template serves as a default.
 */
//$this->config->addTemplatePath($this->locwebsvnreal.'/templates/calm/');

/* 
 * You may also specify a default template by uncommenting and changing the following line as necessary.
 * If no default template is set the first added template is used.
 */
$this->config->setTemplatePath($this->locwebsvnreal.'/templates/calm/');

/* 
 * You may also specify a per repository fixed template by uncommenting and changing the following
 * line as necessary. Use the convention 'groupname.myrep' if your repository is in a group.
 */
//$this->config->setTemplatePath($locwebsvnreal.'/templates/Elegant/', 'myrep');

/* 
 * The index page containing the projects may either be displayed as a flat view (the default),
 * where grouped repositories are displayed as 'GroupName.RepName' or as a tree view.
 * In the case of a tree view, you may choose whether the entire tree is open by default.
 */
//$this->config->useTreeIndex(true);

/* 
 * By default, WebSVN displays a tree view onto the current directory. You can however
 * choose to display a flat view of the current directory only, which may make the display
 * load faster. Uncomment this line if you want that.
 */
//$this->config->useFlatView();

/* 
 * By default, WebSVN displays subfolders first and than the files of a directory,
 * both alphabetically sorted.
 * To use alphabetic order independent iof folders and files uncomment this line.
 */
//$this->config->setAlphabeticOrder(true);

/* 
 * By default, WebSVN displays the information of the last modification
 * (revision, age and author) for each repository in an extra column.
 * To disable that uncomment this line.
 */
//$this->config->setShowLastModInIndex(false);

/* 
 * By default, WebSVN displays the information of the last modification
 * (revision, age and author) for each file and folder in an extra column.
 * To disable that uncomment this line.
 */
//$this->config->setShowLastModInListing(false);

/* 
 * By default, WebSVN displays the age of the last modification.
 * Alternativly the date of the last modification can be shown.
 * To show dates instead of ages uncomment this line.
 */
//$this->config->setShowAgeInsteadOfDate(false);

/* 
 * By default, WebSVN does not ignore whitespaces when showing diffs.
 * To enable ignoring whitespaces in diffs per default uncomment this line.
 */
//$this->config->setIgnoreWhitespacesInDiff(true);


/*
 * --------------
 * LANGUAGE SETUP
 * --------------
 */

/* 
 * Set the default language. If you want English then don't do anything here.
 */
$this->config->setDefaultLanguage('es');

/* 
 * Ignore the user supplied accepted languages to choose reasonable default language.
 * If you want to force the default language - regardless of the client - uncomment the following line.
 */
$this->config->ignoreUserAcceptedLanguages();


/*
 * -------------
 * ACCESS RIGHTS
 * -------------
 */

/* 
 * Uncomment this line if you want to use your Subversion access file to control access
 * rights via WebSVN. For this to work, you'll need to set up the same Apache based authentication
 * to the WebSVN (or wsvn) directory as you have for Subversion itself. More information can be
 * found in install.txt
 */
//$this->config->useAuthenticationFile('/path/to/accessfile'); // Global access file

/* 
 * You may also specify a per repository access file by uncommenting and copying the following
 * line as necessary. Use the convention 'groupname.myrep' if your repository is in a group.
 */
//$this->config->useAuthenticationFile('/path/to/accessfile', 'myrep');

/* 
 * When allowing anonymous access for some repositories and require authentification for others
 * WebSVN can request authentication on-demand. Therefore the optional second/third parameter can be used.
 */

/*
 * Global access file:
 */
//$this->config->useAuthenticationFile('/path/to/accessfile', 'My WebSVN Realm');

/*
 * Access file for myrep:
 */
//$this->config->useAuthenticationFile('/path/to/accessfile', 'myrep', 'My WebSVN Realm');

/* 
 * Uncomment this line if you want to prevent search bots to index the WebSVN pages.
 */
//$this->config->setBlockRobots();


/*
 * ------------
 * FILE CONTENT
 * ------------
 */

/* 
 * You may wish certain file types to be GZIP'd and delieved to the user when clicked apon.
 * This is useful for binary files and the like that don't display well in a browser window!
 * Copy, uncomment and modify this line for each extension to which this rule should apply.
 * (Don't forget the . before the extension. You don't need an index between the []'s).
 * If you'd rather that the files were delivered uncompressed with the associated MIME type,
 * then read below.
 */
//$zipped[] = '.dll';

/* 
 * Subversion controlled files have an svn:mime-type property that can
 * be set on a file indicating its mime type. By default binary files
 * are set to the generic appcliation/octet-stream, and other files
 * don't have it set at all. WebSVN also has a built-in list of
 * associations from file extension to MIME content type. (You can
 * view this list in setup.php).
 *
 * Determining the content-type: By default, if the svn:mime-type
 * property exists and is different from application/octet-stream, it
 * is used. Otherwise, if the built-in list has a contentType entry
 * for the extension of the file, that is used. Otherwise, if the
 * svn:mime-type property exists has the generic binary value of
 * application/octet-stream, the file will be served as a binary
 * file. Otherwise, the file will be brought up as ASCII text in the
 * browser window (although this text may optionally be colourised.
 * See below).
 */

/* 
 * Uncomment this if you want to ignore any svn:mime-type property on your files.
 */
//$this->config->ignoreSvnMimeTypes();

/* 
 * Uncomment this if you want skip WebSVN's custom mime-type handling.
 */
//$this->config->ignoreWebSVNContentTypes();

/* 
 * Following the examples below, you can add new associations, modify
 * the default ones or even delete them entirely (to show them in
 * ASCII via WebSVN).
 */

/*
 * Create a new association.
 */
//$contentType['.c'] = 'text/plain';

/*
 * Modify an existing one.
 */
//$contentType['.doc'] = 'text/plain';

/*
 * Remove a default association.
 */
//unset($contentType['.m']);

/* 
 * If you want to selectively override one or more MIME types to display inline
 * (e.g., the svn:mime-type property is something like text/plain or text/xml, or
 * the file extension matches an entry in $contentType), you can choose to ignore
 * one or more specific MIME types. This approach is finer-grained than ignoring
 * all svn:mime-type properties, and displaying matching files inline such that
 * they are highlighted correctly. (Regular expression matching is used.)
 */
$this->config->addInlineMimeType('text/plain');


/*
 * --------
 * TARBALLS
 * --------
 */

/* 
 * You need tar and gzip installed on your system. Set the paths above if necessary
 *
 * Uncomment the line below to offer a tarball download option across all your repositories.
 */
$this->config->allowDownload();

/* 
 * Set download modes.
 */
$this->config->setDefaultFileDlMode('plain');
$this->config->setDefaultFolderDlMode('gzip');

/* 
 * To change the global option for individual repositories, uncomment and replicate
 * the appropriate line below (replacing 'myrep' with the name of the repository).
 * Use the convention 'groupname.myrep' if your repository is in a group.
 */

/*
 * Specifically allow downloading for 'myrep'.
 */
//$this->config->allowDownload('myrep');

/*
 * Specifically disallow downloading for 'myrep'
 */
//$this->config->disallowDownload('myrep');

/* 
 * You can also choose the minimum directory level from which you'll allow downloading.
 * A value of zero will allow downloading from the root. 1 will allow downloding of directories
 * in the root, etc.
 *
 * If your project is arranged with trunk, tags and branches at the root level, then a value of 2
 * would allow the downloading of directories within branches/tags while disallowing the download
 * of the entire branches or tags directories. This would also stop downloading of the trunk, but
 * see after for path exceptions.
 *
 * Change the line below to set the download level across all your repositories.
 */
$this->config->setMinDownloadLevel(1);

/* 
 * To change the level for individual repositories, uncomment and replicate
 * the appropriate line below (replacing 'myrep' with the name of the repository).
 * Use the convention 'groupname.myrep' if your repository is in a group.
 */
//$this->config->setMinDownloadLevel(2, 'myrep');

/* 
 * Finally, you may add or remove certain directories (and their contents) either globally
 * or on a per repository basis. Uncomment and copy the following lines as necessary. Note
 * that the these are searched in the order than you give them until a match is made (with the
 * exception that all the per repository exceptions are tested before the global ones). This means
 * that you must disallow /a/b/c/ before you allow /a/b/ otherwise the allowed match on /a/b/ will
 * stop any further searching, thereby allowing downloads on /a/b/c/.
 *
 * Global exceptions possibilties:
 */
//$this->config->addAllowedDownloadException('/path/to/allowed/directory/');
//$this->config->addDisAllowedDownloadException('/path/to/disallowed/directory/');

/* 
 * Per repository exception possibilties:
 * Use the convention 'groupname.myrep' if your repository is in a group.
 */
//$this->config->addAllowedDownloadException('/path/to/allowed/directory/', 'myrep');
//$this->config->addDisAllowedDownloadException('/path/to/disallowed/directory/', 'myrep');


/*
 * -------------
 * COLOURISATION
 * -------------
 */

/* 
 * Uncomment this line if you want to use Enscript to colourise your file listings
 *
 * You'll need Enscript version 1.6 or higher AND Sed installed to use this feature.
 * Set the path above.
 *
 * If you have version 1.6.3 or newer use the following line.
 */
//$this->config->useEnscript();

/* 
 * If you have version 1.6.2 or older use the following line.
 */
//$this->config->useEnscript(true);

/* 
 * Enscript need to be told what the contents of a file are so that it can be colourised
 * correctly. WebSVN includes a predefined list of mappings from file extension to Enscript
 * file type (viewable in config/ExtEnscript.php).
 *
 * Here you should add and other extensions not already listed or redefine the default ones. eg:
 */
//$extEnscript['.pas'] = 'pascal';

/* 
 * Note that extensions are case sensitive.
 */

/* 
 * Uncomment this line if you want to use GeSHi to colourise your file listings.
 */
$this->config->useGeshi();

/* GeSHi need to be told what the contents of a file are so that it can be colourised
 * correctly. WebSVN includes a predefined list of mappings from file extension to GeSHi
 * languages (viewable in config/ExtGeshi.php).
 *
 * Here you should add and other extensions not already listed or redefine the default ones. eg:
 */
//$extGeshi['pascal'] = array('p', 'pas');

/* 
 * Note that extensions are case sensitive.
 */


/*
 * -------------------------
 * SHOW CHANGED FILES IN LOG
 * -------------------------
 */

/* 
 * Uncomment this line to show changed files on log.php by default. The normal
 * behavior is to do this only if the "Show changed files" link is clicked. This
 * setting reverses the default action but still allows hiding changed files.
 */
//$this->config->setLogsShowChanges(true);

/* 
 * To override the global setting for individual repositories, uncomment and replicate
 * the appropriate line below (replacing 'myrep' with the name of the repository).
 * Use the convention 'groupname.myrep' if your repository is in a group.
 */
//$this->config->setLogsShowChanges(true,  'myrep');
//$this->config->setLogsShowChanges(false, 'myrep');


/*
 * -------
 * BUGTRAQ
 * -------
 */

/* 
 * Uncomment this line to use bugtraq: properties to show links to your BugTracker
 * from log messages.
 */
//$this->config->setBugtraqEnabled(true);

/* 
 * To override the global setting for individual repositories, uncomment and replicate
 * the appropriate line below (replacing 'myrep' with the name of the repository).
 * Use the convention 'groupname.myrep' if your repository is in a group.
 */
//$this->config->setBugtraqEnabled(true,  'myrep');
//$this->config->setBugtraqEnabled(false, 'myrep');

/* 
 * Usually the information to extract the bugtraq information and generate links are
 * stored in SVN properties starting with 'bugtraq:':
 * namely 'bugtraq:message', 'bugtraq:logregex', 'bugtraq:url' and 'bugtraq:append'.
 * To override the SVN properties globally or for individual repositories, uncomment
 * the appropriate line below (replacing 'myrep' with the name of the repository).
 */
// $this->config->setBugtraqProperties('bug #%BUGID%', 'issues? (\d+)([, ] *(\d+))*'."\n".'(\d+)', 'http://www.example.com/issues/show_bug.cgi?id=%BUGID%', false);
// $this->config->setBugtraqProperties('bug #%BUGID%', 'issues? (\d+)([, ] *(\d+))*'."\n".'(\d+)', 'http://www.example.com/issues/show_bug.cgi?id=%BUGID%', false, 'myrep');


/*
 * -------------
 * MISCELLANEOUS
 * -------------
 */

/* 
 * Comment out this if you don't have the right to use it. Be warned that you may need it however!
 */
set_time_limit(0);

/* 
 * Change the line below to specify a temporary directory other than the one PHP uses.
 */
//$this->config->setTempDir('temp');

/* 
 * Number of spaces to expand tabs to in diff/listing view across all repositories
 */
$this->config->expandTabsBy(8);

/* 
 * To override the global setting for individual repositories, uncomment and replicate
 * the line below (replacing 'myrep' with the name of the repository).
 * Use the convention 'groupname.myrep' if your repository is in a group.
 * 
 * (e.g. Expand Tabs by 3 for repository 'myrep')
 */
//$this->config->expandTabsBy(3, 'myrep');

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
namespace app\business\setup;

/**
 * Used classes.
 */
use app\business\utils\Utils;
use app\business\repository\Repository;
use app\business\svn\SVNRepository;
use xsgaphp\XsgaAbstractClass;

/**
 * Setup class.
 */
class Setup extends XsgaAbstractClass
{
    
    /**
     * Content types.
     * 
     * @var array
     * 
     * @access public
     */
    public $contentType;
    
    /**
     * Enscript extensions.
     * 
     * @var array
     * 
     * @access public
     */
    public $extEnscript;
    
    /**
     * Geshi extensions.
     * 
     * @var array
     * 
     * @access public
     */
    public $extGeshi;
    
    /**
     * WebSvnConfig class instance.
     * 
     * @var WebSvnConfig
     * 
     * @access public
     */
    public $config;
    
    /**
     * Vars.
     * 
     * @var array
     * 
     * @access public
     */
    public $vars;
    
    /**
     * Utils class instance.
     * 
     * @var Utils
     * 
     * @access public
     */
    public $utils;
    
    /**
     * Listings.
     * 
     * @var array
     * 
     * @access public
     */
    public $listing;
    
    /**
     * Language literals.
     * 
     * @var array
     * 
     * @access public
     */
    public $lang;
    
    /**
     * Repository class instance.
     * 
     * @var Repository
     * 
     * @access public
     */
    public $rep;
    
    /**
     * Path to root.
     * 
     * @var string
     * 
     * @access private
     */
    private $rootPath = DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
    
    /**
     * Path to app.
     *
     * @var string
     * 
     * @access private
     */
    private $appPath = DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
    
    /**
     * Revision.
     * 
     * @var integer
     * 
     * @access public
     */
    public $rev;
    
    /**
     * Peg.
     * 
     * @var integer
     * 
     * @access public
     */
    public $peg;
    
    /**
     * Path.
     * 
     * @var string
     * 
     * @access public
     */
    public $path;
    
    /**
     * Pass revision.
     * 
     * @var integer
     * 
     * @access public
     */
    public $passrev;
    
    /**
     * Pass revision string.
     * 
     * @var string
     * 
     * @access public
     */
    public $passRevString;
    
    /**
     * Repository name.
     * 
     * @var string
     * 
     * @access public
     */
    public $repname;
    
    /**
     * SVNRepository class instance.
     * 
     * @var SVNRepository
     * 
     * @access public
     */
    public $svnrep;
    
    /**
     * Local WebSVN real path.
     * 
     * @var string
     * 
     * @access public
     */
    public $locwebsvnreal;
    
    /**
     * Array based flag.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $arrayBased = false;
    
    /**
     * File based flag.
     * 
     * @var boolean
     *
     * @access public
     */
    public $fileBased= false;
    
    /**
     * Query params.
     * 
     * @var array
     * 
     * @access public
     */
    public $queryParams;
    
    
    /**
     * Constructor.
     * 
     * @access public
     */
    public function __construct()
    {
        // Executes parent constructor.
        parent::__construct();
        
        // Set attributes.
        $this->config      = new WebSvnConfig();
        $this->utils       = new Utils();
        $this->vars        = array();
        $this->listing     = array();
        $this->lang        = array();
        $this->contentType = $this->loadContentTypes();
        $this->extEnscript = $this->loadExtEnscript();
        $this->extGeshi    = $this->loadExtGeshi();
        
        // Make sure that the input locale is set up correctly.
        setlocale(LC_ALL, '');
        
        // Assure that a default timezone is set.
        $timezone = @date_default_timezone_get();
        date_default_timezone_set($timezone);
        
    }//end __construct()
    
    
    /**
     * Run setup.
     * 
     * @return void
     * 
     * @access public
     */
    public function run()
    {
        
        // Set server is Windows flag.
        $this->serverIsWindows();
        
        // Load config file.
        $this->loadConfig();
        
        // Initialize an array with all query parameters except language and template.
        $this->queryParams = $_GET + $_POST;
        
        // Setup language.
        $language = $this->setupLanguage();
        
        // Get SVN information.
        $this->getSVNInfo();
        
        // Setup repname and rep.
        $this->setupRep();
        
        // Make sure that the user has set up a repository.
        if ($this->rep === null) {
            
            $this->vars['error'] = $this->lang['SUPPLYREP'];
            
            // Logger.
            $this->logger->error($this->lang['SUPPLYREP']);
            
        } else if (is_string($this->rep)) {
            
            $this->vars['error'] = $this->rep;
            $this->rep = null;
            
        } else {
            
            $this->vars['repurl'] = $this->config->getURL($this->rep, '', 'dir');
            $this->vars['clientrooturl'] = $this->rep->clientRootURL;
            $this->vars['repname'] = escape($this->rep->getDisplayName());
            $this->vars['allowdownload'] = $this->rep->getAllowDownload($this->config->getAllowDownload());
            
        }//end if
        
        // Get template name.
        $template = $this->getTemplate();        
        
        $templates = array();
        // Skip creating template list when selected repository has specific template.
        if ($this->rep === null || $this->rep->templatePath === false) {
            // Get all templates defined in config.php; use last path component as name.
            foreach ($this->config->templatePaths as $pathv) {
                $templates[$pathv] = basename($pathv);
            }//end foreach
            if ($template !== '' && in_array($template, $templates)) {
                $this->config->userTemplate = array_search($template, $templates);
            }//end if
        }//end if
        
        $this->vars['indexurl'] = $this->config->getURL('', '', 'index');
        $this->vars['validationurl'] = $this->utils->getFullURL($_SERVER['SCRIPT_NAME']).'?'.$this->utils->buildQuery($this->queryParams + array('template' => $template, 'language' => $language), '%26');
        $this->vars['version'] = '1.0.0';
        $this->vars['currentyear'] = date('Y');
        
        // To avoid a possible XSS exploit, need to clean up the passed-in path first.
        $this->path = !empty($_REQUEST['path']) ? $_REQUEST['path'] : null;
        if ($this->path === null || $this->path === '') {
            $this->path = '/';
        }//end if
        
        $this->vars['safepath'] = escape($this->path);
        
        // Set operative and peg revisions (if specified) and save passed-in revision.
        $this->rev = (int)@$_REQUEST['rev'];
        $this->peg = (int)@$_REQUEST['peg'];
        
        if ($this->peg === 0) {
            $this->peg = '';
        }//end if
        
        $this->passrev = $this->rev;
        
        // Create revision form.
        $this->createRevisionSelectionForm();
        
        // Set flag if robots should be blocked
        $this->vars['blockrobots'] = $this->config->areRobotsBlocked();
        
        // Set up response headers.
        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Language: '.$language);
        
    }//end run()
    
    
    /**
     * Load config from config.php file.
     * 
     * @return void
     * 
     * @access private
     */
    private function loadConfig()
    {
        $this->locwebsvnreal         = '.';
        $this->vars['locwebsvnhttp'] = '.';
        
        // Get the user's personalised config (requires the locwebsvnhttp stuff above).
        require_once realpath(dirname(__FILE__)).$this->rootPath.'config'.DIRECTORY_SEPARATOR.'config.php';
        
    }//end loadConfig()
    
    
    /**
     * Load available languages array.
     * 
     * @return array
     * 
     * @access private
     */
    private function loadLanguages()
    {
        // Load available languages (populates $languages array).
        require_once realpath(dirname(__FILE__)).$this->appPath.'languages/languages.php';
        return $languages;
        
    }//end loadLanguages()
    
    
    /**
     * Load language literals.
     * 
     * @param string $language
     * 
     * @return array
     * 
     * @access private
     */
    private function loadLanguageLiterals($language)
    {
        require_once realpath(dirname(__FILE__)).$this->appPath.'languages/'.$language.'.php';
        return $lang;
        
    }//end loadLanguageLiterals()
    
    
    /**
     * Setup language.
     * 
     * @return string
     * 
     * @access private
     */
    private function setupLanguage()
    {
        // Get available languages.
        $languages = $this->loadLanguages();
        
        // Get the default language as defined by config.php.
        $defaultLanguage = $this->config->getDefaultLanguage();
        
        // Set default language (english) if default language in config doesn't exists.
        if (!isset($languages[$defaultLanguage])) {
            $defaultLanguage = 'en';
        }//end if
        
        // Determine which language to actually use.
        $language = $this->utils->getUserLanguage($languages, $defaultLanguage, null, $this->config->useAcceptedLanguages());
        
        // Set language code in vars.
        $this->vars['language_code'] = $language;
        
        // Load translated strings.
        $this->lang = $this->loadLanguageLiterals($languages[$language][0]);
        
        return $language;
                
    }//end setupLanguage()
    
    
    /**
     * Setup rep.
     * 
     * @return void
     * 
     * @access private
     */
    private function setupRep()
    {
        // Load repository matching 'repname' parameter (if set) or the default.
        $this->repname = @$_REQUEST['repname'];
        
        if (isset($this->repname)) {
            $this->rep = $this->config->findRepository($this->repname);
        } else {
            $reps = $this->config->getRepositories();
            $this->rep = (isset($reps[0]) ? $reps[0] : null);
        }//end if
        
    }//end setupRep()
    
    
    /**
     * Get template.
     * 
     * If the request specifies a template, store in a permanent/session cookie.
     * Otherwise, check for cookies specifying a particular template.
     * 
     * @return string
     * 
     * @access private
     */
    private function getTemplate()
    {
        $template = '';
        
        if (!empty($_REQUEST['template'])) {
            
            $template = $_REQUEST['template'];
            
            setcookie('storedtemplate', $template, time() + (60 * 60 * 24 * 365 * 10), '/');
            setcookie('storedsesstemplate', $template);
            
        } else if (isset($_COOKIE['storedtemplate'])) {
            
            $template = $_COOKIE['storedtemplate'];
            
        } else if (isset($_COOKIE['storedsesstemplate'])) {
            
            $template = $_COOKIE['storedsesstemplate'];
            
        }//end if
        
        return $template;
        
    }//end getTemplate()
    
    
    /**
     * Load content types.
     * 
     * @return array
     * 
     * @access private
     */
    private function loadContentTypes()
    {
        require_once realpath(dirname(__FILE__)).$this->rootPath.'config/ContentTypes.php';
        return $contentType;
        
    }//end loadContentTypes()
    
    
    /**
     * Load Enscript extensions.
     * 
     * @return array
     * 
     * @access private
     */
    private function loadExtEnscript()
    {
        require_once realpath(dirname(__FILE__)).$this->rootPath.'config/ExtEnscript.php';
        return $extEnscript;
        
    }//end loadExtEnscript()
    
    
    /**
     * Load Geshi extensions.
     * 
     * @return array
     * 
     * @access private
     */
    private function loadExtGeshi()
    {
        require_once realpath(dirname(__FILE__)).$this->rootPath.'config/ExtGeshi.php';
        return $extGeshi;
        
    }//end loadExtGeshi()
    
    
    /**
     * Get SVN information.
     * 
     * @return void
     * 
     * @access private
     */
    private function getSVNInfo()
    {
        // Initialize SVN version information by parsing from command-line output.
        $cmd  = $this->config->getSvnCommand();
        $cmd  = str_replace(array('--non-interactive', '--trust-server-cert'), array('', ''), $cmd);
        $cmd .= ' --version';
        
        // Execute command.
        $ret = runCommand($cmd, $this->lang, false);
        
        if (preg_match('~([0-9]+)\.([0-9]+)\.([0-9]+)~', $ret[0], $matches)) {
            
            // Set config.
            $this->config->setSubversionVersion($matches[0]);
            $this->config->setSubversionMajorVersion($matches[1]);
            $this->config->setSubversionMinorVersion($matches[2]);
            
        }//end if
        
        // Set vars.
        $this->vars['svnversion']           = $this->config->getSubversionVersion();
        $this->vars['showageinsteadofdate'] = $this->config->showAgeInsteadOfDate();
        
    }//end getSVNInfo()
    
    
    /**
     * Set server is Windows flag.
     * 
     * @return void
     * 
     * @access private
     */
    private function serverIsWindows()
    {
        
        if (DIRECTORY_SEPARATOR === '\\') {
            $this->config->setServerIsWindows();
        }//end if
        
        // Define constant.
        define('SERVER_IS_WINDOWS', $this->config->serverIsWindows);
        
    }//end serverIsWindows()
    
    
    /**
     * Function to create the revision selection HTML form.
     * 
     * @param Repository $rep
     * @param string     $path
     * @param string     $rev
     * @param string     $peg
     * 
     * @return void
     * 
     * @access public
     */
    public function createRevisionSelectionForm()
    {
        
        if ($this->rep === null) {
            return;
        }//end if
        
        $params = array();
        
        $params['repname'] = $this->rep->getDisplayName();
        
        if ($this->path === null) {
            $this->path = !empty($_REQUEST['path']) ? $_REQUEST['path'] : null;
            if ($this->path && $this->path != '/') {
                $params['path'] = $this->path;
            }//end if
        }//end if
        
        if ($this->peg || $this->rev) {
            $params['peg'] = ($this->peg ? $this->peg : $this->rev);
        }//end if
            
        $hidden = '';
        foreach ($params as $key => $value) {
            $hidden .= '<input type="hidden" name="'.$key.'" value="'.escape($value).'" />';
        }//end foreach
        
        // The blank "action" attribute makes form link back to the containing page.
        $this->vars['revision_form'] = '<form method="get" action="" id="revision">'.$hidden;
        if ($this->rev === null) {
            $this->rev = (int)@$_REQUEST['rev'];
        }//end if
        
        // Set vars.
        $this->vars['revision_input'] = '<input type="text" size="5" name="rev" placeholder="'.($this->rev ? $this->rev : 'HEAD').'" />';
        $this->vars['revision_submit'] = '<input type="submit" value="'.$this->lang['GO'].'" />';
        $this->vars['revision_endform'] = '</form>';
    
    }//end createRevisionSelectionForm()
    
    
    /**
     * Check sending authenticator header.
     * 
     * @param Repository|boolean $rep
     * 
     * @return void
     * 
     * @access public
     */
    public function checkSendingAuthHeader($rep = false)
    {
        
        $auth = null;
        
        if ($rep) {
            $auth =& $rep->getAuth();
        } else {
            $auth =& $this->config->getAuth();
        }//end if
        
        $loggedin = $auth->hasUsername();
        
        header(WebSvnCons::HTTP_403, true, 403);
        
    }//end checkSendingAuthHeader()
    
    
}//end Setup class.

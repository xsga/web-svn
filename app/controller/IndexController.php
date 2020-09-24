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

/**
 * IndexController class.
 */
class IndexController extends AbstractController
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
        
        // Set vars.
        $setup->vars['action']      = $setup->lang['PROJECTS'];
        $setup->vars['repname']     = '';
        $setup->vars['rev']         = 0;
        $setup->vars['path']        = '';
        $setup->vars['showlastmod'] = $setup->config->showLastModInIndex();
        
        // Sort the repositories by group.
        $setup->config->sortByGroup();
        
        // Get projects.
        $projects = $setup->config->getRepositories();
        
        if (count($projects) === 1 && $projects[0]->hasReadAccess('/')) {
            header('Location: '.str_replace(WebSvnCons::ANDAMP, '', $setup->config->getURL($projects[0], '', 'dir')));
            exit;
        }//end if
        
        // Alternates between every entry, whether it is a group or project.
        $i      = 0;
        $parity = 0;
        
        // The first project (and first of any group) resets this to 0.
        $groupparity = 0;
        $curgroup    = null;
        $groupcount  = 0;
        
        // Create listing of all configured projects (includes groups if they are used).
        foreach ($projects as $project) {
            
            if (!$project->hasReadAccess('/')) {
                continue;
            }//end if
            
            $listvar = &$setup->listing[$i];
            
            // If this is the first project in a group, add an entry for the group.
            if ($curgroup !== $project->group) {
                
                $groupcount++;
                $groupparity = 0;
                $listvar['notfirstgroup'] = !empty($curgroup);
                
                $curgroup = $project->group;
                $listvar['groupname']   = $curgroup;
                $listvar['groupid']     = strtr(base64_encode('grp'.$curgroup), array('+' => '-', '/' => '_', '=' => ''));
                
                // Setting to null because template.php won't unset them.
                $listvar['projectlink'] = null;
                $listvar['projectname'] = null;
                $listvar['projecturl']  = null;
                
                $i++;
                $listvar = &$setup->listing[$i];
                $listvar['groupid'] = null;
                
            }//end if
            
            $listvar['clientrooturl'] = $project->clientRootURL;
            
            // Populate variables for latest modification to the current repository.
            if ($setup->config->showLastModInIndex()) {
                
                $setup->svnrep = new SVNRepository($setup);
                $log           = $setup->svnrep->getLog('/', '', '', true, 1);
                
                if (isset($log->entries[0])) {
                    
                    $head = $log->entries[0];
                    
                    $listvar['revision'] = $head->rev;
                    $listvar['date']     = $head->date;
                    $listvar['age']      = $setup->utils->datetimeFormatDuration($setup->lang, time() - strtotime($head->date));
                    $listvar['author']   = $head->author;
                    
                } else {
                    
                    $listvar['revision'] = 0;
                    $listvar['date']     = '';
                    $listvar['age']      = '';
                    $listvar['author']   = '';
                    
                }//end if
                
            }//end if
            
            // Create project (repository) listing.
            $url  = str_replace(WebSvnCons::ANDAMP, '', $setup->config->getURL($project, '', 'dir'));
            $name = ($setup->config->flatIndex) ? $project->getDisplayName() : $project->name;
            
            $listvar['projectlink'] = '<a href="'.$url.'">'.escape($name).'</a>';
            $listvar['projectname'] = escape($name);
            $listvar['projecturl']  = $url;
            $listvar['rowparity']   = $parity % 2;
            $listvar['groupparity'] = $groupparity % 2;
            $listvar['groupname']   = ($curgroup != null) ? $curgroup : '';
            
            // Increase counters.
            $parity++;
            $groupparity++;
            $i++;
            
        }//end foreach
        
        $this->validatesListing($setup, $projects);
        
        $setup->vars['flatview']   = $setup->config->flatIndex;
        $setup->vars['treeview']   = !$setup->config->flatIndex;
        $setup->vars['opentree']   = $setup->config->openTree;
        $setup->vars['groupcount'] = $groupcount;
        
        // Render template.
        $this->renderTemplate($setup, 'index');
        
    }//end __construct()
    
    
}//end IndexController()
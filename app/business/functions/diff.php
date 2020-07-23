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
 * Used classes.
 */
use app\business\setup\Setup;
use app\business\diff\ListingHelper;
use app\business\diff\SensibleLineChanges;
use app\business\diff\LineDiff;

/**
 * Get space.
 * 
 * @return string
 */
function getSpace()
{
    return '&nbsp;';
    
}//end getSpace()


/**
 * Next line.
 * 
 * @param array|resource $obj
 * @param boolean        $arrayBased
 * @param boolean        $fileBased
 * 
 * @return array|string
 */
function nextLine(&$obj, $arrayBased, $fileBased)
{
    
    if ($arrayBased) {
        return array_shift($obj);
    }//end if
    
    if ($fileBased) {
        return fgets($obj);
    }//end if
    
    return '';
    
}//end nextLine()


/**
 * End of file.
 * 
 * @param array|resource $obj
 * @param boolean        $arrayBased
 * @param boolean        $fileBased
 * 
 * @return boolean
 */
function endOfFile($obj, $arrayBased, $fileBased)
{
    
    if ($arrayBased) {
        return count($obj) === 0;
    }//end if
    
    if ($fileBased) {
        return feof($obj);
    }//end if
    
    return true;
    
}//end endOfFile()


/**
 * Get wrapped line from file.
 * 
 * @param resource $file
 * @param boolean  $is_highlighted
 * @param Setup    $setup
 * 
 * @return boolean|string
 */
function getWrappedLineFromFile($file, $is_highlighted, Setup $setup)
{
    
    $line = fgets($file);
    
    if ($line === false) {
        return false;
    }//end if
    
    $line = toOutputEncoding($line);
    
    if (!$is_highlighted) {
        $line = escape($line);
    }//end if
    
    if (strip_tags($line) === '') {
        $line = getSpace();
    }//end if
    
    return $setup->utils->wrapInCodeTagIfNecessary($line, $setup->config->getUseGeshi());
    
}//end getWrappedLineFromFile()


/**
 * Diff result.
 * 
 * @param boolean        $all
 * @param boolean        $highlighted
 * @param string         $newtname
 * @param string         $oldtname
 * @param array|resource $obj
 * @param boolean        $ignoreWhitespace
 * @param Setup          $setup
 * 
 * @return array
 */
function diff_result($all, $highlighted, $newtname, $oldtname, $obj, $ignoreWhitespace, Setup $setup)
{
    
    $ofile = fopen($oldtname, 'r');
    $nfile = fopen($newtname, 'r');
    
    // Get the first real line.
    $line = nextLine($obj, $setup->arrayBased, $setup->fileBased);
    
    $index = 0;
    $listingHelper = new ListingHelper();
    
    $curoline = 1;
    $curnline = 1;
    
    $sensibleLineChanges = new SensibleLineChanges(new LineDiff($ignoreWhitespace));
    
    while (!endOfFile($obj, $setup->arrayBased, $setup->fileBased)) {
        
        // Get the first line of this range.
        $oline = 0;
        sscanf($line, '@@ -%d', $oline);
        $line = substr($line, strpos($line, '+'));
        $nline = 0;
        sscanf($line, '+%d', $nline);
        
        while ($curoline < $oline || $curnline < $nline) {
            
            if ($curoline < $oline) {
                
                $text1 = getWrappedLineFromFile($ofile, $highlighted, $setup);
                $tmpoline = $curoline;
                $curoline++;
                
            } else {
                
                $tmpoline = '?';
                $text1 = getSpace();
                
            }//end if
            
            if ($curnline < $nline) {
                
                $text2 = getWrappedLineFromFile($nfile, $highlighted, $setup);
                $tmpnline = $curnline;
                $curnline++;
                
            } else {
                
                $tmpnline = '?';
                $text2 = getSpace();
                
            }//end if
            
            if ($all) {
                $listingHelper->addLine($text1, $tmpoline, $text2, $tmpnline);
            }//end if
            
        }//end while
        
        if (!$all && $line !== false) {
            $listingHelper->startNewBlock();
        }//end if
        
        $fin = false;
        
        while (!endOfFile($obj, $setup->arrayBased, $setup->fileBased) && !$fin) {
            
            $line = nextLine($obj, $setup->arrayBased, $setup->fileBased);
            
            if ($line === false || $line === '' || strncmp($line, '@@', 2) === 0) {
                
                $sensibleLineChanges->addChangesToListing($listingHelper, $highlighted);
                $fin = true;
                
            } else {
                
                $mod = $line{0};
                $line = rtrim(substr($line, 1));
                
                switch ($mod) {
                    case '-':
                        $text = getWrappedLineFromFile($ofile, $highlighted, $setup);
                        $sensibleLineChanges->addDeletedLine($line, $text, $curoline);
                        $curoline++;
                        break;
                        
                    case '+':
                        $text = getWrappedLineFromFile($nfile, $highlighted, $setup);
                        $sensibleLineChanges->addAddedLine($line, $text, $curnline);
                        $curnline++;
                        break;
                        
                    default:
                        $sensibleLineChanges->addChangesToListing($listingHelper, $highlighted);
                        
                        $text1 = getWrappedLineFromFile($ofile, $highlighted, $setup);
                        $text2 = getWrappedLineFromFile($nfile, $highlighted, $setup);
                        
                        $listingHelper->addLine($text1, $curoline, $text2, $curnline);
                        
                        $curoline++;
                        $curnline++;
                        
                        break;
                        
                }//end switch
                
            }//end if
            
            if (!$fin) {
                $index++;
            }//end if
            
        }//end while
        
    }//end while
    
    $sensibleLineChanges->addChangesToListing($listingHelper, $highlighted);
    
    // Output the rest of the files.
    if ($all) {
        
        while (!feof($ofile) || !feof($nfile)) {
            
            $noneof = false;
            
            $text1 = getWrappedLineFromFile($ofile, $highlighted, $setup);
            if ($text1 !== false) {
                $tmpoline = $curoline;
                $curoline++;
                $noneof = true;
            } else {
                $tmpoline = '-';
                $text1 = getSpace();
            }//end if
            
            
            $text2 = getWrappedLineFromFile($nfile, $highlighted, $setup);
            if ($text2 !== false) {
                $tmpnline = $curnline;
                $curnline++;
                $noneof = true;
            } else {
                $tmpnline = '-';
                $text2 = getSpace();
            }//end if
            
            if ($noneof) {
                $listingHelper->addLine($text1, $tmpoline, $text2, $tmpnline);
            }//end if
            
        }//end while
        
    }//end if
    
    fclose($ofile);
    fclose($nfile);
    
    return $listingHelper->getListing();
    
}//end diff_result()


/**
 * Command diff.
 * 
 * @param boolean $all
 * @param boolean $ignoreWhitespace
 * @param boolean $highlighted
 * @param string  $newtname
 * @param string  $oldtname
 * @param string  $newhlname
 * @param string  $oldhlname
 * @param Setup   $setup
 * 
 * @return array
 */
function command_diff($all, $ignoreWhitespace, $highlighted, $newtname, $oldtname, $newhlname, $oldhlname, Setup $setup)
{
    
    $context = 5;
    
    if ($all) {
        // Setting the context to 0 makes diff generate the wrong line numbers!.
        $context = 1;
    }//end if
    
    if ($ignoreWhitespace) {
        $whitespaceFlag = ' -w';
    } else {
        $whitespaceFlag = '';
    }//wnd if
    
    // Open a pipe to the diff command with $context lines of context.
    $cmd            = quoteCommand($setup->config->diff.$whitespaceFlag.' -U '.$context.' "'.$oldtname.'" "'.$newtname.'"');
    $descriptorspec = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
    
    $resource = proc_open($cmd, $descriptorspec, $pipes);
    $error    = '';
    
    if (is_resource($resource)) {
        // We don't need to write.
        fclose($pipes[0]);
        
        $diff = $pipes[1];
        
        // Ignore the 3 header lines.
        fgets($diff);
        fgets($diff);
        
        $setup->arrayBased = false;
        $setup->fileBased = true;
        
        if ($highlighted) {
            $listing = diff_result($all, $highlighted, $newhlname, $oldhlname, $diff, $ignoreWhitespace, $setup);
        } else {
            $listing = diff_result($all, $highlighted, $newtname, $oldtname, $diff, $ignoreWhitespace, $setup);
        }//end if
        
        fclose($pipes[1]);
        
        while (!feof($pipes[2])) {
            $error .= fgets($pipes[2]);
        }//end while
        
        $error = toOutputEncoding(trim($error));
        
        if (!empty($error)) {
            $error = '<p>'.$setup->lang['BADCMD'].': <code>'.$cmd.'</code></p><p>'.nl2br($error).'</p>';
        }//end if
        
        fclose($pipes[2]);
        
        proc_close($resource);
        
    } else {
        $error = '<p>'.$setup->lang['BADCMD'].': <code>'.$cmd.'</code></p>';
    }//end if
    
    if (!empty($error)) {
        echo $error;
        
        if (is_resource($resource)) {
            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            
            proc_close($resource);
        }//end if
        
        exit;
        
    }//end if
    
    return $listing;
    
}//end command_diff()


/**
 * Inline diff.
 * 
 * @param boolean $all
 * @param boolean $ignoreWhitespace
 * @param boolean $highlighted
 * @param string  $newtname
 * @param string  $oldtname
 * @param string  $newhlname
 * @param string  $oldhlname
 * @param Setup   $setup
 * 
 * @return array
 */
function inline_diff($all, $ignoreWhitespace, $highlighted, $newtname, $oldtname, $newhlname, $oldhlname, Setup $setup)
{
    
    $context = 5;
    if ($all) {
        // Setting the context to 0 makes diff generate the wrong line numbers!.
        $context = 1;
    }//end if
    
    // Modify error reporting level to suppress deprecated/strict warning "Assigning the return value of new by reference".
    $bckLevel    = error_reporting();
    $removeLevel = 0;
    $modLevel = $bckLevel & (~$removeLevel);
    error_reporting($modLevel);
    
    // Create the diff class.
    $fromLines = file($oldtname);
    $toLines   = file($newtname);
    
    if (!$ignoreWhitespace) {
        
        $diff = new Text_Diff('auto', array($fromLines, $toLines));
        
    } else {
        
        $whitespaces     = array(' ', "\t", "\n", "\r");
        $mappedFromLines = array();
        
        foreach ($fromLines as $k => $line) {
            $line              = rtrim($line, "\n\r");
            $fromLines[$k]     = $line;
            $mappedFromLines[] = str_replace($whitespaces, array(), $line);
        }//end foreach
        
        $mappedToLines = array();
        
        foreach ($toLines as $k => $line) {
            $line = rtrim($line, "\n\r");
            $toLines[$k] = $line;
            $mappedToLines[] = str_replace($whitespaces, array(), $line);
        }//end foreach
        
        $diff = new Text_MappedDiff($fromLines, $toLines, $mappedFromLines, $mappedToLines);
    }//end if
    
    $renderer = new Text_Diff_Renderer_unified(array('leading_context_lines' => $context, 'trailing_context_lines' => $context));
    $rendered = explode("\n", $renderer->render($diff));
    
    // Restore previous error reporting level.
    error_reporting($bckLevel);
    
    $setup->arrayBased = true;
    $setup->fileBased  = false;
    
    if ($highlighted) {
        $listing = diff_result($all, $highlighted, $newhlname, $oldhlname, $rendered, $ignoreWhitespace, $setup);
    } else {
        $listing = diff_result($all, $highlighted, $newtname, $oldtname, $rendered, $ignoreWhitespace, $setup);
    }//end if
    
    return $listing;
    
}//end inline_diff()


/**
 * Do diff.
 * 
 * @param boolean $all
 * @param boolean $ignoreWhitespace
 * @param boolean $highlighted
 * @param string  $newtname
 * @param string  $oldtname
 * @param string  $newhlname
 * @param string  $oldhlname
 * @param Setup   $setup
 * 
 * @return array
 */
function do_diff($all, $ignoreWhitespace, $highlighted, $newtname, $oldtname, $newhlname, $oldhlname, Setup $setup)
{
    
    if (class_exists('Text_Diff')) {
        return inline_diff($all, $ignoreWhitespace, $highlighted, $newtname, $oldtname, $newhlname, $oldhlname, $setup);
    } else {
        return command_diff($all, $ignoreWhitespace, $highlighted, $newtname, $oldtname, $newhlname, $oldhlname, $setup);
    }//end if
    
}//end do_diff()

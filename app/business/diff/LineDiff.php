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
namespace app\business\diff;

/**
 * Used classes.
 */
use xsgaphp\XsgaAbstractClass;

/**
 * LineDiff class.
 * 
 * Default line diffing function.
 */
class LineDiff extends XsgaAbstractClass implements LineDiffInterface
{
    
    /**
     * Ignore white spcae.
     * 
     * @var boolean
     * 
     * @access public
     */
    public $ignoreWhitespace;
    
    
    /**
     * Constructor.
     * 
     * @param boolean $ignoreWhitespace
     * 
     * @access public
     */
    public function __construct($ignoreWhitespace)
    {
        parent::__construct();
        
        $this->ignoreWhitespace = $ignoreWhitespace;
        
    }//end __construct()
    
    
    /**
     * Levenshtein2.
     * 
     * Levenshtein edit distance, on small strings use php function on large strings approximate distance 
     * using words computed by dynamic programming.
     * 
     * @param string $str1 String 1.
     * @param string $str2 String 2.
     * 
     * @return number
     * 
     * @access public
     */
    public function levenshtein2($str1, $str2)
    {
        if (strlen($str1) < 255 && strlen($str2) < 255) {
            return levenshtein($str1, $str2);
        }//end if
        
        $l1 = explode(' ', $str1);
        $l2 = explode(' ', $str2);
        
        $n = count($l1);
        $m = count($l2);
        
        $d = array_fill(0, $n + 1, array_fill(0, $m + 1, 0));
        
        for ($i = 1; $i < $n + 1; $i++) {
            $d[$i][0] = $i;
        }//end for
        
        for ($j = 1; $j < $m + 1; $j++) {
            $d[0][$j] = $j;
        }//end for
        
        for ($i = 1; $i < $n + 1; $i++) {
            for ($j = 1; $j < $m + 1; $j++) {
                $c = ($l1[$i - 1] == $l2[$j - 1]) ? 0 : strlen($l1[$i - 1]) + strlen($l2[$i - 1]);
                $d[$i][$j] = min($d[$i - 1][$j] + 1, $d[$i][$j - 1] + 1, $d[$i - 1][$j - 1] + $c);
            }//end for
        }//end for
        
        return $d[$n][$m];
        
    }//end levenshtein2()
    
    
    /**
     * Line similarity.
     * 
     * {@inheritDoc}
     * @see \app\business\diff\LineDiffInterface::lineSimilarity()
     * 
     * @access public
     */
    public function lineSimilarity($text1, $text2)
    {
        $distance = $this->levenshtein2($text1, $text2);
        
        return max(0.0, 1.0 - $distance / (strlen($text1) + strlen($text2) + 4));
        
    }//end lineSimilarity()
    
    
    /**
     * Tokenize whole line into words.
     * 
     * Note that separators are returned as tokens of length 1 and if $ignoreWhitespace is true,
     * consecutive whitespaces are returned as one token.
     * 
     * @param string  $string
     * @param boolean $highlighted
     * @param boolean $ignoreWhitespace
     * 
     * @return array
     */
    public function tokenize($string, $highlighted, $ignoreWhitespace)
    {
        $html                = array('<' => '>', '&' => ';');
        $whitespaces         = array("\t","\n","\r",' ');
        $separators          = array('.','-','+','*','/','<','>','?','(',')','&','/','{','}','[',']',':',';');
        $data                = array();
        $segment             = '';
        $segmentIsWhitespace = true;
        $count               = strlen($string);
        
        for ($i = 0; $i < $count; $i++) {
            
            $c = $string[$i];
            
            if ($highlighted && array_key_exists($c, $html)) {
                
                if ($segment !== '') {
                    $data[] = $segment;
                }//end if
                
                // Consider html tags and entities as a single token.
                $endchar = $html[$c];
                $segment = $c;
                
                do {
                    $i++;
                    $c = $string[$i];
                    $segment .= $c;
                } while ($c != $endchar && $i < $count - 1);
                
                $data[]              = $segment;
                $segment             = '';
                $segmentIsWhitespace = false;
                
            } else if (in_array($c, $separators) || (!$ignoreWhitespace && in_array($c, $whitespaces))) {
                
                // If it is separator or whitespace and we do not consider consecutive whitespaces.
                if ($segment !== '') {
                    $data[] = $segment;
                }//end if
                
                $data[]              = $c;
                $segment             = '';
                $segmentIsWhitespace = true;
                
            } else if (in_array($c, $whitespaces)) {
                
                // If it is whitespace and we consider consecutive whitespaces as one token.
                if (!$segmentIsWhitespace) {
                    $data[]              = $segment;
                    $segment             = '';
                    $segmentIsWhitespace = true;
                }//end if
                
                $segment .= $c;
                
            } else {
                
                // No separator or whitespace.
                if ($segmentIsWhitespace && $segment !== '') {
                    $data[]  = $segment;
                    $segment = '';
                }//end if
                
                $segment            .= $c;
                $segmentIsWhitespace = false;
                
            }//end if
            
        }//end for
        
        if ($segment !== '') {
            $data[] = $segment;
        }//end if
        
        return $data;
        
    }//end tokenize()
    
    
    /**
     * Inline diff.
     * 
     * {@inheritDoc}
     * @see \app\business\diff\LineDiffInterface::inlineDiff()
     * 
     * @access public
     */
    public function inlineDiff($text1, $highlighted1, $text2, $highlighted2, $highlighted)
    {
        
        $whitespaces = array(' ', "\t", "\n", "\r");
        $do_diff     = true;
        
        if ($text1 === '' || $text2 === '') {
            $do_diff = false;
        }//end if
        
        if ($this->ignoreWhitespace && (str_replace($whitespaces, array(), $text1) === str_replace($whitespaces, array(), $text2))) {
            $do_diff = false;
        }//end if
        
        // Exit gracefully if loading of Text_Diff failed.
        if (!class_exists('Text_Diff') || !class_exists('Text_MappedDiff')) {
            $do_diff = false;
        }//end if
        
        // Return highlighted lines without doing inline diff.
        if (!$do_diff) {
            return array($highlighted1, $highlighted2);
        }//end if
        
        $tokens1 = $this->tokenize($highlighted1, $highlighted, $this->ignoreWhitespace);
        $tokens2 = $this->tokenize($highlighted2, $highlighted, $this->ignoreWhitespace);
        
        if (!$this->ignoreWhitespace) {
            
            $diff = new \Text_Diff('native', array($tokens1, $tokens2));
            
        } else {
            
            // We need to create mapped parts for MappedDiff.
            $mapped1 = array();
            
            foreach ($tokens1 as $token) {
                $mapped1[] = str_replace($whitespaces, array(), $token);
            }//end foreach
            
            $mapped2 = array();
            
            foreach ($tokens2 as $token) {
                $mapped2[] = str_replace($whitespaces, array(), $token);
            }//end foreach
            
            $diff = new \Text_MappedDiff($tokens1, $tokens2, $mapped1, $mapped2);
            
        }//end if
        
        // Now, get the diff and annotate text.
        $edits = $diff->getDiff();
        $line1 = '';
        $line2 = '';
        
        foreach ($edits as $edit) {
            
            if (@is_a($edit, 'Text_Diff_Op_copy')) {
                
                $line1 .= implode('', $edit->orig);
                $line2 .= implode('', $edit->final);
                
            } else if (@is_a($edit, 'Text_Diff_Op_delete')) {
                
                $line1 .= '<del>'.implode('', $edit->orig).'</del>';
                
            } else if (@is_a($edit, 'Text_Diff_Op_add')) {
                
                $line2 .= '<ins>'.implode('', $edit->final).'</ins>';
                
            } else if (@is_a($edit, 'Text_Diff_Op_change')) {
                
                $line1 .= '<del>'.implode('', $edit->orig).'</del>';
                $line2 .= '<ins>'.implode('', $edit->final).'</ins>';
                
            }//end if
            
        }//end foreach
        
        return array($line1, $line2);
        
    }//end inlineDiff()
    
    
}//end LineDiff class


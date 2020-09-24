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
 * RemoveAccents.
 * 
 * Remove all the accents from a string. This function doesn't seem ideal, but expecting everyone to install 'unac' 
 * seems a little excessive as well.
 * 
 * @param string $string String.
 * 
 * @return string
 */
function removeAccents($string)
{
    
    $string = htmlentities($string, ENT_QUOTES, 'ISO-8859-1');
    $string = preg_replace('/&([A-Za-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron);/', '$1', $string);
    
    return $string;
    
}//end removeAccents()

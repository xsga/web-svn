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
use app\business\setup\WebSvnCons;
use log4php\Logger;

/**
 * Detect character encoding.
 * 
 * @param string $str String to detect encoding.
 * 
 * @return string|null
 */
function detectCharacterEncoding($str)
{
    return mb_detect_encoding($str.'a', array('UTF-8', 'ISO-8859-1'));
    
}//end detectCharacterEncoding()


/**
 * Encoding output.
 * 
 * @param string $str String to encode.
 * 
 * @return string
 */
function toOutputEncoding($str)
{
    $enc = detectCharacterEncoding($str);

    if ($enc !== null) {
        
        $str = mb_convert_encoding($str, 'UTF-8', $enc);
    
    } else {
        
        // @see http://w3.org/International/questions/qa-forms-utf-8.html
        $isUtf8 = preg_match('%^(?:
            [\x09\x0A\x0D\x20-\x7E]              # ASCII
            | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
            |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
            | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
            |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
            |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
            | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
            |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
            )*$%xs', $str
        );
        
        if (!$isUtf8) {
            $str = utf8_encode($str);
        }//end if
        
    }//end if

    return $str;
    
}//end toOutputEncoding()


/**
 * Scape a string to output.
 * 
 * @param string $str
 * 
 * @return string|array
 */
function escape($str)
{
    $entities = array();
    
    $entities['&']  = WebSvnCons::ANDAMP;
    $entities['<']  = '&lt;';
    $entities['>']  = '&gt;';
    $entities['"']  = '&quot;';
    $entities['\''] = '&apos;';
    
    return str_replace(array_keys($entities), array_values($entities), $str);

}//end escape()


/**
 * Quote command.
 * 
 * @param string $cmd Command.
 * 
 * @return string
 */
function quoteCommand($cmd)
{
    // On Windows machines, the whole line needs quotes round it so that it's passed to cmd.exe correctly.
    if (SERVER_IS_WINDOWS) {
        $cmd = '"'.$cmd.'"';
    }//end if
    
    return $cmd;
    
}//end quoteCommand()


/**
 * Execute command.
 * 
 * @param string  $cmd
 * @param integer $retcode
 * 
 * @return unknown
 */
function execCommand($cmd, &$retcode)
{
    return @exec($cmd, $tmp, $retcode);
    
}//end execCommand()


/**
 * Open process.
 * 
 * @param string $cmd  Command.
 * @param string $mode Mode.
 * 
 * @return resource
 */
function popenCommand($cmd, $mode)
{
    return popen($cmd, $mode);
    
}//end popenCommand()


/**
 * Passthru command.
 * 
 * @param string $cmd Command.
 * 
 * @return void
 */
function passthruCommand($cmd)
{
    passthru($cmd);

}//end passthruCommand()


/**
 * Run command.
 * 
 * @param string $cmd
 * @param array  $lang
 * @param string $mayReturnNothing
 * 
 * @return string[]
 */
function runCommand($cmd, array $lang, $mayReturnNothing = false)
{
    
    // Get logger.
    $logger = Logger::getRootLogger();
    
    $output = array();
    $err    = false;

    $c = quoteCommand($cmd);

    $descriptorspec = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));

    $resource = proc_open($c, $descriptorspec, $pipes);
    $error    = '';

    if (!is_resource($resource)) {

        // Logger.
        $logger->error('Error running command: '.stripCredentialsFromCommand($cmd));
        echo '<p>'.$lang['BADCMD'].': <code>'.stripCredentialsFromCommand($cmd).'</code></p>';
        exit;
        
    }//end if

    $handle    = $pipes[1];
    $firstline = true;
    
    while (!feof($handle)) {
        
        $line = fgets($handle);
        
        if ($firstline && empty($line) && !$mayReturnNothing) {
            $err = true;
        }//end if

        $firstline = false;
        $output[]  = toOutputEncoding(rtrim($line));
        
    }//end while

    while (!feof($pipes[2])) {
        $error .= fgets($pipes[2]);
    }//end while

    $error = toOutputEncoding(trim($error));

    fclose($pipes[0]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    proc_close($resource);

    if (!$err) {
        return $output;
    } else {
        echo '<p>'.$lang['BADCMD'].': <code>'.stripCredentialsFromCommand($cmd).'</code></p><p>'.nl2br($error).'</p>';
    }//end if
    
}//end runCommand()


/**
 * Strip credentials from command.
 * 
 * @param string $cmd Command.
 * 
 * @return mixed
 */
function stripCredentialsFromCommand($cmd)
{
    $quotingChar  = (SERVER_IS_WINDOWS ? '"' : "'");
    $quotedString = $quotingChar.'([^'.$quotingChar.'\\\\]*(\\\\.[^'.$quotingChar.'\\\\]*)*)'.$quotingChar;
    $patterns     = array('|--username '.$quotedString.' |U', '|--password '.$quotedString.' |U');
    $replacements = array('--username '.quote('***').' ', '--password '.quote('***').' ');
    
    return preg_replace($patterns, $replacements, $cmd, 1);
    
}//end stripCredentialsFromCommand()


/**
 * Quote a string to send to the command line.
 * 
 * @param string $str
 * 
 * @return string
 */
function quote($str)
{
    if (SERVER_IS_WINDOWS) {
        return '"'.$str.'"';
    } else {
        return escapeshellarg($str);
    }//end if
    
}//end quote()

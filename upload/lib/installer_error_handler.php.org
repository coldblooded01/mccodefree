<?php
/*
MCCodes FREE
lib/installer_error_handler.php Rev 1.1.0
Copyright (C) 2005-2012 Dabomstew

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

/**
 * An error handler used to handle PHP errors encountered during installation.
 */

function error_critical($human_error, $debug_error, $action,
        $context = array())
{
    require_once('./installer_head.php'); // in case it hasn't been included
    // Setup a new error
    header('HTTP/1.1 500 Internal Server Error');
    echo '<h1>Installer Error</h1>';
    echo 'A critical error has occurred, and installation has stopped. '
            . 'Below are the details:<br />' . $debug_error . '<br /><br />'
            . '<strong>Action taken:</strong> ' . $action . '<br /><br />';
    if (is_array($context) && count($context) > 0)
    {
        echo '<strong>Context at error time:</strong> ' . '<br /><br />'
                . nl2br(print_r($context, true));
    }
    require_once('./installer_foot.php');
    exit;
}

function error_php($errno, $errstr, $errfile = '', $errline = 0,
        $errcontext = array())
{
    // What's happened?
    // If it's a PHP warning or user error/warning, don't go further - indicates bad code, unsafe
    if ($errno == 2) // E_WARNING
    {
        error_critical('',
                '<strong>PHP Warning:</strong> ' . $errstr . ' (' . $errno
                        . ')', 'Line executed: ' . $errfile . ':' . $errline,
                $errcontext);
    }
    else if ($errno == 4096) // E_RECOVERABLE_ERROR (since 5.2)
    {
        error_critical('',
                '<strong>PHP Recoverable Error:</strong> ' . $errstr . ' ('
                        . $errno . ')',
                'Line executed: ' . $errfile . ':' . $errline, $errcontext);
    }
    else if ($errno == 256) // E_USER_ERROR
    {
        error_critical('',
                '<strong>User Error:</strong> ' . $errstr . ' (' . $errno
                        . ')', 'Line executed: ' . $errfile . ':' . $errline,
                $errcontext);
    }
    else if ($errno == 512) // E_USER_WARNING
    {
        error_critical('',
                '<strong>User Warning:</strong> ' . $errstr . ' (' . $errno
                        . ')', 'Line executed: ' . $errfile . ':' . $errline,
                $errcontext);
    }
    else
    {
        // Only do anything if DEBUG is on, now
        if (DEBUG)
        {
            // Determine the name to display from the error type
            $errname = 'Unknown Error';
            switch ($errno)
            {
            case 8:
                $errname = 'PHP Notice';
                break; // E_NOTICE
            case 1024:
                $errname = 'User Notice';
                break; // E_USER_NOTICE
            case 8192:
                $errname = 'PHP Deprecation Notice';
                break; // E_DEPRECATED [since 5.3]
            case 16384:
                $errname = 'User Deprecation Notice';
                break; // E_USER_DEPRECATED [since 5.3]
            }
            require_once('./installer_head.php'); // in case it hasn't been included
            echo 'A non-critical error has occurred. Page execution will continue. '
                    . 'Below are the details:<br /><strong>' . $errname
                    . '</strong>: ' . $errstr . ' (' . $errno . ')'
                    . '<br /><br />' . '<strong>Line executed</strong>: '
                    . $errfile . ':' . $errline . '<br /><br />';
            if (is_array($errcontext) && count($errcontext) > 0)
            {
                echo '<strong>Context at error time:</strong> '
                        . '<br /><br />' . nl2br(print_r($errcontext, true));
            }
        }
    }
}
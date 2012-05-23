<?php
/*
MCCodes FREE
logout.php Rev 1.1.0
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

session_start();
$sessid = $_SESSION['userid'];
$atk = $_SESSION['attacking'];

if ($_SESSION['attacking'])
{
    print "You lost all your EXP for running from the fight.<br />";
    require "mysql.php";
    global $c;
    mysql_query("UPDATE users SET exp=0 WHERE userid=$sessid", $c);
    $_SESSION['attacking'] == 0;
    session_unset();
    session_destroy();
    die("<a href='login.php'>Continue login...</a>");
}
session_unset();
session_destroy();
header("Location: login.php");


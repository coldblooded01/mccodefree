<?php
/*
MCCodes FREE
staffnotes.php Rev 1.1.0
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
require "global_func.php";
if ($_SESSION['loggedin'] == 0)
{
    header("Location: login.php");
    exit;
}
$userid = $_SESSION['userid'];
require "header.php";
$h = new headers;
$h->startheaders();
include "mysql.php";
global $c;
$is =
        mysql_query(
                "SELECT u.*,us.* FROM users u LEFT JOIN userstats us ON u.userid=us.userid WHERE u.userid=$userid",
                $c) or die(mysql_error());
$ir = mysql_fetch_array($is);
check_level();
$fm = money_formatter($ir['money']);
$cm = money_formatter($ir['crystals'], '');
$lv = date('F j, Y, g:i a', $ir['laston']);
$h->userdata($ir, $lv, $fm, $cm);
$h->menuarea();
if ($ir['user_level'] == 2 || $ir['user_level'] == 3 || $ir['user_level'] == 5)
{
    $q =
            mysql_query(
                    "SELECT staffnotes FROM users WHERE userid={$_POST['ID']}",
                    $c);
    $old = mysql_real_escape_string(mysql_result($q, 0, 0), $c);
    $new = mysql_real_escape_string(stripslashes($_POST['staffnotes']), $c);
    mysql_query(
            "UPDATE users SET staffnotes='{$new}' WHERE userid='{$_POST['ID']}'",
            $c);
    mysql_query(
            "INSERT INTO staffnotelogs VALUES(NULL, $userid, {$_POST['ID']}, "
                    . time() . ", '$old', '{$new}')", $c);
    print 
            "User notes updated!<br />
<a href='viewuser.php?u={$_POST['ID']}'>&gt; Back To Profile</a>";
}
else
{
    print "You violent scum.";
}
$h->endpage();

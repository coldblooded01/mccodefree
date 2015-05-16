<?php
/*
MCCodes FREE
Copyright (C) 2005-2012 Dabomstew
Changes made by John West
updated all the mysql to mysqli. 

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
require "includes/global_func.php";
if ($_SESSION['loggedin'] == 0)
{
    header("Location: login.php");
    exit;
}
$userid = $_SESSION['userid'];
require "header.php";
$h = new headers;
$h->startheaders();
include "includes/mysql.php";
global $c;
$is =
        mysqli_query(
                $c, 
                "SELECT u.*,us.* FROM users u LEFT JOIN userstats us ON u.userid=us.userid WHERE u.userid=$userid") or die(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
$ir = mysqli_fetch_array($is);
check_level();
$fm = money_formatter($ir['money']);
$cm = money_formatter($ir['crystals'], '');
$lv = date('F j, Y, g:i a', $ir['laston']);
$h->userdata($ir, $lv, $fm, $cm);
$h->menuarea();
if ($ir['user_level'] == 2 || $ir['user_level'] == 3 || $ir['user_level'] == 5)
{
    $q =
            mysqli_query(
                    $c, 
                    "SELECT staffnotes FROM users WHERE userid={$_POST['ID']}");
    $old = mysqli_real_escape_string( $c, mysqli_free_result($q));
    $new = mysqli_real_escape_string( $c, stripslashes($_POST['staffnotes']));
    mysqli_query(
            $c, 
            "UPDATE users SET staffnotes='{$new}' WHERE userid='{$_POST['ID']}'");
    mysqli_query( $c, 
            "INSERT INTO staffnotelogs VALUES(NULL, $userid, {$_POST['ID']}, "
                    . time() . ", '$old', '{$new}')");
    print 
            "User notes updated!<br />
<a href='viewuser.php?u={$_POST['ID']}'>&gt; Back To Profile</a>";
}
else
{
    print "You violent scum.";
}
$h->endpage();

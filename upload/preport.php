<?php
/*
MCCodes FREE
preport.php Rev 1.1.0
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
$is = mysqli_query(
    $c,
    "SELECT u.*,us.* FROM users u LEFT JOIN userstats us ON u.userid=us.userid WHERE u.userid=$userid"
) or die(mysqli_error($c));
$ir = mysqli_fetch_array($is);

check_level();
$fm = money_formatter($ir['money']);
$cm = money_formatter($ir['crystals'], '');
$lv = date('F j, Y, g:i a', $ir['laston']);
$h->userdata($ir, $lv, $fm, $cm);
$h->menuarea();
if ($_POST['report'])
{
    $_POST['player'] = abs((int) $_POST['player']);
    $ins_report =
            mysqli_real_escape_string(stripslashes($_POST['report']), $c);
    mysqli_query(
        $c,
        "INSERT INTO preports VALUES(NULL,$userid,{$_POST['player']},'{$ins_report}')"
    ) or die(
        "Your report could not be processed, make sure you have filled out the form entirely."
    );
    print "Report processed!";
}
else
{
    print
            "<h3>Player Report</h3>
Know of a player that's breaking the rules? Don't hesitate to report them. Reports are kept confidential.<br />
<form action='preport.php' method='post'>
Player's ID: <input type='text' name='player' value='{$_GET['ID']}' /><br />
What they've done: <br />
<textarea rows='7' cols='40' name='report'>{$_GET['report']}</textarea><br />
<input type='submit' value='Send Report' /></form>";
}
$h->endpage();

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
        mysqli_query($c,
                "SELECT u.*,us.* FROM users u LEFT JOIN userstats us ON u.userid=us.userid WHERE u.userid=$userid") or die(mysqli_error($c));
$ir = mysqli_fetch_array($is);
$ir['exp_needed'] = ($ir['level'] + 1) * ($ir['level'] + 1) * ($ir['level']
                        + 1);
check_level();
$fm = money_formatter($ir['money']);
$cm = money_formatter($ir['crystals'], '');
$lv = date('F j, Y, g:i a', $ir['laston']);
$h->userdata($ir, $lv, $fm, $cm);
$h->menuarea();
$_GET['delete'] = abs((int) $_GET['delete']);
if ($_GET['delete'])
{
    mysqli_query(
            $c, 
            "DELETE FROM events WHERE evID={$_GET['delete']} AND evUSER=$userid");
    print "<b>Event Deleted</b><br />";
}
print "<b>Latest 10 events</b><br />";
$q =
        mysqli_query($c,
                "SELECT * FROM events WHERE evUSER=$userid ORDER BY evTIME DESC LIMIT 10;");
print
        "<table width=75% border=2> <tr style='background:gray;'> <th>Time</th> <th>Event</th><th>Links</th> </tr>";
while ($r = mysqli_fetch_array($q))
{
    print "<tr><td>" . date('F j Y, g:i:s a', $r['evTIME']);
    if (!$r['evREAD'])
    {
        print "<br /><b>New!</b>";
    }
    print
            "</td><td>{$r['evTEXT']}</td><td><a href='events.php?delete={$r['evID']}'>Delete</a></td></tr>";
}
print "</table>";
mysqli_query($c,"UPDATE events SET evREAD=1 WHERE evUSER=$userid");
$h->endpage();

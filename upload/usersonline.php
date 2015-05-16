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
print "<h3>Users Online</h3>";
$cn = 0;
$q =
        mysqli_query( $c, 
                "SELECT * FROM users WHERE laston>" . (time() - 900)
                        . " ORDER BY laston DESC");
while ($r = mysqli_fetch_array($q))
{
    $la = time() - $r['laston'];
    $unit = "secs";
    if ($la >= 60)
    {
        $la = (int) ($la / 60);
        $unit = "mins";
    }
    if ($la >= 60)
    {
        $la = (int) ($la / 60);
        $unit = "hours";
        if ($la >= 24)
        {
            $la = (int) ($la / 24);
            $unit = "days";
        }
    }
    $cn++;
    print
            "$cn. <a href='viewuser.php?u={$r['userid']}'>{$r['username']}</a> ($la $unit)<br />";
}
$h->endpage();

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
$_GET['to'] = abs((int) $_GET['to']);
if (!$_GET['to'])
{
    print
            "Welcome to the Monorail Station. It costs \$1000 for a ticket.<br />
Where would you like to travel today?<br />";
    $q =
            mysqli_query(
                    $c, 
                    "SELECT * FROM cities WHERE cityid != {$ir['location']} AND cityminlevel <= {$ir['level']}");
    print
            "<table width=75%><tr style='background:gray'><th>Name</th><th>Description</th><th>Min Level</th><th>&nbsp;</th></tr>";
    while ($r = mysqli_fetch_array($q))
    {
        print
                "<tr><td>{$r['cityname']}</td><td>{$r['citydesc']}</td><td>{$r['cityminlevel']}</td><td><a href='monorail.php?to={$r['cityid']}'>Go</a></td></tr>";
    }
    print "</table>";
}
else
{
    if ($ir['money'] < 1000)
    {
        print "You don't have enough money.";
    }
    else if (((int) $_GET['to']) != $_GET['to'])
    {
        print "Invalid city ID";
    }
    else
    {
        $q =
                mysqli_query(
                        $c, 
                        "SELECT * FROM cities WHERE cityid = {$_GET['to']} AND cityminlevel <= {$ir['level']}");
        if (!mysqli_num_rows($q))
        {
            print
                    "Error, this city either does not exist or you cannot go there.";
        }
        else
        {
            mysqli_query(
                    $c, 
                    "UPDATE users SET money=money-1000,location={$_GET['to']} WHERE userid=$userid");
            $r = mysqli_fetch_array($q);
            print
                    "Congratulations, you paid \$1000 and travelled to {$r['cityname']} on the monorail!";
        }
    }
}
$h->endpage();

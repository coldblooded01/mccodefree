<?php
/*
MCCodes FREE
searchname.php Rev 1.1.0
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
//search name
if (!$_GET['name'])
{
    print "Invalid use of file";
}
else
{
    $namebit = mysql_real_escape_string(stripslashes($_GET['name']), $c);
    $q =
            mysql_query(
                    "SELECT * FROM users WHERE username LIKE ('%{$namebit}%')",
                    $c);
    print 
            mysql_num_rows($q)
                    . " players found. <br />
<table><tr style='background-color:gray;'><th>User</th><th>Level</th><th>Money</th></tr>";
    while ($r = mysql_fetch_array($q))
    {
        print 
                "<tr><td><a href='viewuser.php?u={$r['userid']}'>{$r['username']}</a></td><td>{$r['level']}</td><td>\${$r['money']}</td></tr>";
    }
    print "</table>";
}
$h->endpage();

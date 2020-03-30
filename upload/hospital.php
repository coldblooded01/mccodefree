<?php
/*
MCCodes FREE
hospital.php Rev 1.1.0
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
print
        "<h3>Hospital</h3>
<table width='75%' border='2'><tr bgcolor=gray><th>ID</th><th>Name</th <th>Level</th> <th>Time</th><th>Reason</th></tr>";
$q = mysqli_query(
    $c,
    "SELECT u.*,c.* FROM users u WHERE u.hospital > 0 ORDER BY u.hospital DESC"
);
while ($r = mysqli_fetch_array($q))
{
    print
            "\n<tr><td>{$r['userid']}</td><td><a href='viewuser.php?u={$r['userid']}'>{$r['username']}</a> [{$r['userid']}]</td><td>
            {$r['level']}</td><td>{$r['hospital']} minutes</td><td>{$r['hospreason']}</td></tr>";
}
print "</table>";
$h->endpage();

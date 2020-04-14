<?php
/*
MCCodes FREE
usersonline.php Rev 1.1.0
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
require_once(dirname(__FILE__) . "/models/user.php");
$user = User::get($userid);
require "header.php";
$h = new Header();
$h->startheaders();
include "mysql.php";
global $c;

$user->check_level();
$h->userdata($user);
$h->menuarea();
print "<h3>Users Online</h3>";
$cn = 0;
$q = mysqli_query(
    $c,
    "SELECT * FROM users WHERE laston>" . (time() - 900)
        . " ORDER BY laston DESC"
);
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

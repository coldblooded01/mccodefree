<?php
/*
MCCodes FREE
fedjail.php Rev 1.1.0
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
$q = mysqli_query(
    $c,
    "SELECT f.*,u.username,u2.username as jailer FROM fedjail f LEFT JOIN users u ON f.fed_userid=u.userid LEFT JOIN users u2 ON f.fed_jailedby=u2.userid ORDER BY f.fed_days ASC"
);
print
        "<b>Federal Jail</b><br />
If you ever cheat the game your name will become a permanent part of this list...<br />
<table border=1><tr style='background:gray'><th>Who</th><th>Days</th><th>Reason</th><th>Jailer</th></tr>";
while ($r = mysqli_fetch_array($q))
{
    print
            "<tr><td><a href='viewuser.php?u={$r['fed_userid']}'>{$r['username']}</a></td>
<td>{$r['fed_days']} </td><td> {$r['fed_reason']}</td><td><a href='viewuser.php?u={$r['fed_jailedby']}'>{$r['jailer']}</a></td></tr>";
}
print "</table>";
$q = mysqli_query(
    $c,
    "SELECT * FROM users WHERE mailban>0 ORDER BY mailban ASC"
);
print
        "<b>Mail Bann</b></center><br />
If you ever swear or do bad things at your mail, your name will become a permanent part of this list...<br />
<table width=100% border=1><tr style='background:gray'><th>Who</th><th>Days</th><th>Reason</th></tr>";
while ($r = mysqli_fetch_array($q))
{
    print
            "<tr><td><a href='viewuser.php?u={$r['userid']}'>{$r['username']}</a></td>
<td>{$r['mailban']} </td><td> {$r['mb_reason']}</td><td></td></tr>";
}
print "</table>";
$h->endpage();

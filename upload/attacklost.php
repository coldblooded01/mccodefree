<?php
/*
MCCodes FREE
attacklost.php Rev 1.1.0
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
$h->userdata($ir, $lv, $fm, $cm, 0);
$h->menuarea();

$_GET['ID'] == abs((int) $_GET['ID']);
$_SESSION['attacking'] = 0;
$od = mysqli_query($c, "SELECT * FROM users WHERE userid={$_GET['ID']}");
if (mysqli_num_rows($od))
{
    $_SESSION['attacklost'] = 0;
    $r = mysqli_fetch_array($od);
    print "You lost to {$r['username']}";
    $expgain = abs(($ir['level'] - $r['level']) ^ 3);
    $expgainp = $expgain / $ir['exp_needed'] * 100;
    print " and lost $expgainp% EXP!";
    mysqli_query(
        $c,
        "UPDATE users SET exp=exp-$expgain,hospital=40+(rand()*20),hospreason='Lost to <a href=\'viewuser.php?u={$r['userid']}\'>{$r['username']}</a>' WHERE userid=$userid"
    );
    mysqli_query($c, "UPDATE users SET exp=0 WHERE exp<0");
    event_add($r['userid'],
            "<a href='viewuser.php?u=$userid'>{$ir['username']}</a> attacked you and lost.",
            $c);
    $atklog = mysqli_escape_string($c, $_SESSION['attacklog']);
    mysqli_query(
        $c,
        "INSERT INTO attacklogs VALUES(NULL,$userid,{$_GET['ID']},'lost',"
            . time() . ",0,'$atklog');"
    );
}
else
{
    print "You lost to Mr. Non-existant! =O";
}
$h->endpage();

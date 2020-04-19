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
require_once(dirname(__FILE__) . "/models/user.php");
require_once(dirname(__FILE__) . "/models/event.php");
$user = User::get($userid);
require "header.php";
$h = new Header;
$h->startheaders();
include "mysql.php";
global $c;

$user->check_level();
$h->userdata($user, 0);
$h->menuarea();

$_GET['ID'] == abs((int) $_GET['ID']);
$_SESSION['attacking'] = 0;
if (User::exists($_GET['ID']))
{
    $_SESSION['attacklost'] = 0;
    $opponent = User::get($_GET['ID']);
    print "You lost to {$opponent->username}";
    $expgain = abs(($user->level - $opponent->level) ^ 3);
    $expgainp = $expgain / $user->get_exp_needed() * 100;
    print " and lost $expgainp% EXP!";

    mysqli_query(
        $c,
    "UPDATE users SET exp=exp-$expgain,hospital=40+(rand()*20),hospreason='Lost to <a href=\'viewuser.php?u={$opponent->userid}\'>{$opponent->username}</a>' WHERE userid={$userid}"
    );
    mysqli_query($c, "UPDATE users SET exp=0 WHERE exp<0");
    
    Event::add(
        $opponent->userid,
        "<a href=\'viewuser.php?u={$userid}\'>{$user->username}</a> attacked you and lost."
    );
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

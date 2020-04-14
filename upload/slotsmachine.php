<?php
/*
MCCodes FREE
slotsmachine.php Rev 1.1.0
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
$tresder = (int) (rand(100, 999));
$maxbet = $user->level * 250;
$_GET['tresde'] = abs((int) $_GET['tresde']);
if (($_SESSION['tresde'] == $_GET['tresde']) || $_GET['tresde'] < 100)
{
    die(
            "Error, you cannot refresh or go back on the slots, please use a side link to go somewhere else.<br />
<a href='slotsmachine.php?tresde=$tresder'>&gt; Back</a>");
}
$_SESSION['tresde'] = $_GET['tresde'];
$_GET['bet'] = abs((int) $_GET['bet']);
print "<h3>Slots</h3>";
if ($_GET['bet'])
{
    if ($_GET['bet'] > $user->money)
    {
        die(
                "You are trying to bet more than you have.<br />
<a href='slotsmachine.php?tresde=$tresder'>&gt; Back</a>");
    }
    else if ($_GET['bet'] > $maxbet)
    {
        die(
                "You have gone over the max bet.<br />
<a href='slotsmachine.php?tresde=$tresder'>&gt; Back</a>");
    }

    $slot[1] = (int) rand(0, 9);
    $slot[2] = (int) rand(0, 9);
    $slot[3] = (int) rand(0, 9);
    print
            "You place \${$_GET['bet']} into the slot and pull the pole.<br />
You see the numbers: <b>$slot[1] $slot[2] $slot[3]</b><br />
You bet \${$_GET['bet']} ";
    if ($slot[1] == $slot[2] && $slot[2] == $slot[3])
    {
        $won = $_GET['bet'] * 26;
        $gain = $_GET['bet'] * 25;
        print
                "and won \$$won by lining up 3 numbers pocketing you \$$gain extra.";
    }
    else if ($slot[1] == $slot[2] || $slot[2] == $slot[3]
            || $slot[1] == $slot[3])
    {
        $won = $_GET['bet'] * 3;
        $gain = $_GET['bet'] * 2;
        print
                "and won \$$won by lining up 2 numbers pocketing you \$$gain extra.";
    }
    else
    {
        $won = 0;
        $gain = -$_GET['bet'];
        print "and lost it.";
    }
    mysqli_query(
        $c,
        "UPDATE users SET money=money+({$gain}) where userid=$userid"
    );
    $tresder = (int) (rand(100, 999));
    print
            "<br />
<a href='slotsmachine.php?bet={$_GET['bet']}&tresde=$tresder'>&gt; Another time, same bet.</a><br />
<a href='slotsmachine.php?tresde=$tresder'>&gt; I'll continue, but I'm changing my bet.</a><br />
<a href='explore.php'>&gt; Enough's enough, I'm off.</a>";
}
else
{
    print
            "Ready to try your luck? Play today!<br />
The maximum bet for your level is \$$maxbet.<br />
<form action='slotsmachine.php' method='get'>
Bet: \$<input type='text' name='bet' value='5' /><br />
<input type='hidden' name='tresde' value='$tresder' />
<input type='submit' value='Play!!' />
</form>";
}

$h->endpage();

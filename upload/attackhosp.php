<?php
/*
MCCodes FREE
attackhosp.php Rev 1.1.0
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
$h = new Header();
$h->startheaders();
include "mysql.php";
global $c;

$user->check_level();
$h->userdata($user, 0);
$h->menuarea();

$_GET['ID'] = abs((int) $_GET['ID']);
$_SESSION['attacking'] = 0;

if ($_SESSION['attackwon'] != $_GET['ID'])
{
    die("Cheaters don't get anywhere.");
}
if (User::exists($_GET['ID']))
{
    $opponent = User::get($_GET['ID']);
    if ($opponent->hp == 1)
    {
        print "What a cheater you are.";
    }
    else
    {
        print "You beat {$opponent->username} and hospitalized them.";

        Event::add($opponent->userid, "<a href='viewuser.php?u=$userid'>{$user->username}</a> hospitalized you.");

        mysqli_query(
                "UPDATE users SET hp=1,hospital=hospital+80+(rand()*230),hospreason='Hospitalized by <a href=\'viewuser.php?u={$userid}\'>{$ir['username']}</a>' WHERE userid={$r['userid']}",
                $c);
        $atklog = mysql_escape_string($_SESSION['attacklog']);
        mysqli_query(
                "INSERT INTO attacklogs VALUES(NULL,$userid,{$_GET['ID']},'won',"
                        . time() . ",-1,'$atklog');", $c);
        $_SESSION['attackwon'] = 0;
        $bots = array(263, 264, 265, 2477, 2479, 2480, 2481, 0, 0, 0, 0, 0, 0);
        $moneys =
                array(263 => 10000, 264 => 10000, 265 => 15500, 2477 => 80000,
                        2479 => 30000, 2480 => 30000, 2481 => 30000,
                        0 => 100000, 0 => 1400000, 0 => 1400000, 0 => 1400000,
                        0 => 5000000, 0 => 10000000);
        if (in_array($opponent->userid, $bots))
        {
            $qk =
                    mysqli_query(
                            "SELECT * FROM challengesbeaten WHERE userid=$userid AND npcid={$opponent->userid}",
                            $c);
            if (!mysqli_num_rows($qk))
            {
                $gain = $moneys[$opponent->userid];
                mysqli_query(
                        "UPDATE users SET money=money+$gain WHERE userid=$userid",
                        $c);
                mysqli_query(
                        "INSERT INTO challengesbeaten VALUES ($userid,{$opponent->userid})",
                        $c);
                print
                        "<br /><br />Congrats, you have beaten the Challenge BOT {$opponent->username}, you have earnt \$$gain!";
            }
        }
    }
}
else
{
    print "You beat Mr. non-existant!";
}
$h->endpage();

<?php
/*
MCCodes FREE
attackwon.php Rev 1.1.0
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
check_level();

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
    if ($opponent->hp == 1)
    {
        print "What a cheater u are.";
    }
    else
    {
        $stole = (int) (rand($opponent->money / 500, $opponent->money / 20));
        print "You beat {$opponent->username} and stole \$$stole";
        $qe = $opponent->level ^ 3;
        $expgain = rand($qe / 4, $qe / 2);
        $expperc = (int) ($expgain / $user->exp_needed * 100);
        print " and gained $expperc% EXP!";
        mysqli_query(
            $c,
            "UPDATE users SET exp=exp+$expgain,money=money+$stole WHERE userid=$userid"
        );
        mysqli_query(
            $c,
            "UPDATE users SET hp=1,money=money-$stole WHERE userid={$opponent->userid}"
        );
        event_add($opponent->userid,
                "<a href='viewuser.php?u=$userid'>{$username->username}</a> attacked you and stole $stole.",
                $c);
        $atklog = mysqli_escape_string($c, $_SESSION['attacklog']);
        mysqli_query(
            $c,
            "INSERT INTO attacklogs VALUES(NULL,$userid,{$_GET['ID']},'won',"
                . time() . ",$stole,'$atklog');"
        );
        $_SESSION['attackwon'] = 0;
        $bots = array(2477, 2479, 2480, 2481, 263, 264, 265);
        $moneys =
                array(2477 => 80000, 2479 => 30000, 2480 => 30000,
                        2481 => 30000, 263 => 10000, 264 => 10000,
                        265 => 15000, 536 => 100000, 720 => 1400000,
                        721 => 1400000, 722 => 1400000, 585 => 5000000,
                        820 => 10000000);
        if (in_array($opponent->userid, $bots))
        {
            $qk = mysqli_query(
                $c,
                "SELECT * FROM challengesbeaten WHERE userid=$userid AND npcid={$opponent->userid}"
            );
            if (!mysqli_num_rows($qk))
            {
                $gain = $moneys[$opponent->userid];
                mysqli_query(
                    $c,
                    "UPDATE users SET money=money+$gain WHERE userid=$userid"
                );
                mysqli_query(
                    $c,
                    "INSERT INTO challengesbeaten VALUES ($userid,{$opponent->userid})"
                );
                print
                        "<br /><br />Congrats, for beating the Challenge Bot {$opponent->username}, you have earnt \$$gain!";
            }
        }
    }
}
else
{
    print "You beat Mr. non-existant!";
}
$h->endpage();

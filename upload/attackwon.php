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

$_GET['ID'] = abs((int) $_GET['ID']);
$_SESSION['attacking'] = 0;
$od = mysqli_query($c, "SELECT * FROM users WHERE userid={$_GET['ID']}");
if ($_SESSION['attackwon'] != $_GET['ID'])
{
    die("Cheaters don't get anywhere.");
}
if (mysqli_num_rows($od))
{
    $r = mysqli_fetch_array($od);
    if ($r['hp'] == 1)
    {
        print "What a cheater u are.";
    }
    else
    {
        $stole = (int) (rand($r['money'] / 500, $r['money'] / 20));
        print "You beat {$r['username']} and stole \$$stole";
        $qe = $r['level'] * $r['level'] * $r['level'];
        $expgain = rand($qe / 4, $qe / 2);
        $expperc = (int) ($expgain / $ir['exp_needed'] * 100);
        print " and gained $expperc% EXP!";
        mysqli_query(
            $c,
            "UPDATE users SET exp=exp+$expgain,money=money+$stole WHERE userid=$userid"
        );
        mysqli_query(
            $c,
            "UPDATE users SET hp=1,money=money-$stole WHERE userid={$r['userid']}"
        );
        event_add($r['userid'],
                "<a href='viewuser.php?u=$userid'>{$ir['username']}</a> attacked you and stole $stole.",
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
        if (in_array($r['userid'], $bots))
        {
            $qk = mysqli_query(
                $c,
                "SELECT * FROM challengesbeaten WHERE userid=$userid AND npcid={$r['userid']}"
            );
            if (!mysqli_num_rows($qk))
            {
                $gain = $moneys[$r['userid']];
                mysqli_query(
                    $c,
                    "UPDATE users SET money=money+$gain WHERE userid=$userid"
                );
                mysqli_query(
                    $c,
                    "INSERT INTO challengesbeaten VALUES ($userid,{$r['userid']})"
                );
                print
                        "<br /><br />Congrats, for beating the Challenge Bot {$r['username']}, you have earnt \$$gain!";
            }
        }
    }
}
else
{
    print "You beat Mr. non-existant!";
}
$h->endpage();

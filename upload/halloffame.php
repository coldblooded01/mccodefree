<?php
/*
MCCodes FREE
halloffame.php Rev 1.1.0
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
$h->userdata($user);
$h->menuarea();
print
        "<h3>Hall Of Fame</h3>
<table width=75%> <tr> <td><a href='halloffame.php?action=level'>LEVEL</a></td> <td><a href='halloffame.php?action=money'>MONEY</a></td> <td><a href='halloffame.php?action=crystals'>CRYSTALS</a></td> <td><a href='halloffame.php?action=total'>TOTAL STATS</a></td> </tr>
<tr> <td><a href='halloffame.php?action=strength'>STRENGTH</a></td> <td><a href='halloffame.php?action=agility'>AGILITY</a></td> <td><a href='halloffame.php?action=guard'>GUARD</a></td> <td><a href='halloffame.php?action=labour'>LABOUR</a></td> <td><a href='halloffame.php?action=iq'>IQ</a></td> </tr> </table>";
switch ($_GET['action'])
{
case "level":
    hof_level();
    break;
case "money":
    hof_money();
    break;
case "crystals":
    hof_crystals();
    break;
case "total":
    hof_total();
    break;
case "strength":
    hof_strength();
    break;
case "agility":
    hof_agility();
    break;
case "guard":
    hof_guard();
    break;
case "labour":
    hof_labour();
    break;
case "iq":
    hof_iq();
    break;
}

function hof_level()
{
    global $user, $c, $userid;
    print
            "Showing the 20 users with the highest levels<br />
<table width=75%><tr style='background:gray'> <th>Pos</th> <th>User</th> <th>Level</th> </tr>";
    $q = mysqli_query(
        $c,
        "SELECT u.* FROM users u WHERE u.user_level != 0 ORDER BY level DESC,userid ASC LIMIT 20"
    );
    $p = 0;
    while ($r = mysqli_fetch_array($q))
    {
        $p++;
        if ($r['userid'] == $userid)
        {
            $t = "<b>";
            $et = "</b>";
        }
        else
        {
            $t = "";
            $et = "";
        }
        print
                "<tr> <td>$t$p$et</td> <td>$t{$r['username']} [{$r['userid']}]$et</td> <td>$t{$r['level']}$et</td> </tr>";
    }
    print "</table>";
}

function hof_money()
{
    global $c, $userid;
    print
            "Showing the 20 users with the highest amount of money<br />
<table width=75%><tr style='background:gray'> <th>Pos</th> <th>User</th> <th>Money</th> </tr>";
    $q = mysqli_query(
        $c,
        "SELECT u.* FROM users u WHERE u.user_level != 0 ORDER BY money DESC,userid ASC LIMIT 20"
    );
    $p = 0;
    while ($r = mysqli_fetch_array($q))
    {
        $p++;
        if ($r['userid'] == $userid)
        {
            $t = "<b>";
            $et = "</b>";
        }
        else
        {
            $t = "";
            $et = "";
        }
        print
                "<tr> <td>$t$p$et</td> <td>$t{$r['username']} [{$r['userid']}]$et</td> <td>$t\$"
                        . money_formatter($r['money'], '') . "$et</td> </tr>";
    }
    print "</table>";
}

function hof_crystals()
{
    global $c, $userid;
    print
            "Showing the 20 users with the highest amount of crystals<br />
<table width=75%><tr style='background:gray'> <th>Pos</th> <th>User</th> <th>Crystals</th> </tr>";
    $q = mysqli_query(
        $c,
        "SELECT u.* FROM users u WHERE u.user_level != 0 ORDER BY crystals DESC,userid ASC LIMIT 20"
    );
    $p = 0;
    while ($r = mysqli_fetch_array($q))
    {
        $p++;
        if ($r['userid'] == $userid)
        {
            $t = "<b>";
            $et = "</b>";
        }
        else
        {
            $t = "";
            $et = "";
        }
        print
                "<tr> <td>$t$p$et</td> <td>$t{$r['username']} [{$r['userid']}]$et</td> <td>$t"
                        . money_formatter($r['crystals'], '')
                        . "$et</td> </tr>";
    }
    print "</table>";
}

function hof_total()
{
    global $c, $userid;
    print
            "Showing the 20 users with the highest total stats<br />
<table width=75%><tr style='background:gray'> <th>Pos</th> <th>User</th> </tr>";
    $q = mysqli_query(
        $c,
        "SELECT u.* FROM users u LEFT JOIN userstats us ON u.userid=us.userid WHERE u.user_level != 0 ORDER BY (us.strength+us.agility+us.guard+us.labour+us.IQ) DESC,u.userid ASC LIMIT 20"
    );
    $p = 0;
    while ($r = mysqli_fetch_array($q))
    {
        $p++;
        if ($r['userid'] == $userid)
        {
            $t = "<b>";
            $et = "</b>";
        }
        else
        {
            $t = "";
            $et = "";
        }
        print
                "<tr> <td>$t$p$et</td> <td>$t{$r['username']} [{$r['userid']}]$et</td> </tr>";
    }
    print "</table>";
}

function hof_strength()
{
    global $c, $userid;
    print
            "Showing the 20 users with the highest strength<br />
<table width=75%><tr style='background:gray'> <th>Pos</th> <th>User</th> </tr>";
    $q = mysqli_query(
        $c,
        "SELECT u.* FROM users u LEFT JOIN userstats us ON u.userid=us.userid WHERE u.user_level != 0 ORDER BY us.strength DESC,u.userid ASC LIMIT 20"
    );
    $p = 0;
    while ($r = mysqli_fetch_array($q))
    {
        $p++;
        if ($r['userid'] == $userid)
        {
            $t = "<b>";
            $et = "</b>";
        }
        else
        {
            $t = "";
            $et = "";
        }
        print
                "<tr> <td>$t$p$et</td> <td>$t{$r['username']} [{$r['userid']}]$et</td> </tr>";
    }
    print "</table>";
}

function hof_agility()
{
    global $c, $userid;
    print
            "Showing the 20 users with the highest agility<br />
<table width=75%><tr style='background:gray'> <th>Pos</th> <th>User</th> </tr>";
    $q = mysqli_query(
        $c,
        "SELECT u.* FROM users u LEFT JOIN userstats us ON u.userid=us.userid WHERE u.user_level != 0 ORDER BY us.agility DESC,u.userid ASC LIMIT 20"
    );
    $p = 0;
    while ($r = mysqli_fetch_array($q))
    {
        $p++;
        if ($r['userid'] == $userid)
        {
            $t = "<b>";
            $et = "</b>";
        }
        else
        {
            $t = "";
            $et = "";
        }
        print
                "<tr> <td>$t$p$et</td> <td>$t{$r['username']} [{$r['userid']}]$et</td> </tr>";
    }
    print "</table>";
}

function hof_guard()
{
    global $c, $userid;
    print
            "Showing the 20 users with the highest guard<br />
<table width=75%><tr style='background:gray'> <th>Pos</th> <th>User</th> </tr>";
    $q = mysqli_query(
        $c,
        "SELECT u.* FROM users u LEFT JOIN userstats us ON u.userid=us.userid WHERE u.user_level != 0 ORDER BY us.guard DESC,u.userid ASC LIMIT 20"
    );
    $p = 0;
    while ($r = mysqli_fetch_array($q))
    {
        $p++;
        if ($r['userid'] == $userid)
        {
            $t = "<b>";
            $et = "</b>";
        }
        else
        {
            $t = "";
            $et = "";
        }
        print
                "<tr> <td>$t$p$et</td> <td>$t{$r['username']} [{$r['userid']}]$et</td> </tr>";
    }
    print "</table>";
}

function hof_labour()
{
    global $c, $userid;
    print
            "Showing the 20 users with the highest labour<br />
<table width=75%><tr style='background:gray'> <th>Pos</th> <th>User</th> </tr>";
    $q = mysqli_query(
        $c,
        "SELECT u.* FROM users u LEFT JOIN userstats us ON u.userid=us.userid WHERE u.user_level != 0 ORDER BY us.labour DESC,u.userid ASC LIMIT 20"
    );
    $p = 0;
    while ($r = mysqli_fetch_array($q))
    {
        $p++;
        if ($r['userid'] == $userid)
        {
            $t = "<b>";
            $et = "</b>";
        }
        else
        {
            $t = "";
            $et = "";
        }
        print
                "<tr> <td>$t$p$et</td> <td>$t{$r['username']} [{$r['userid']}]$et</td> </tr>";
    }
    print "</table>";
}

function hof_iq()
{
    global $c, $userid;
    print
            "Showing the 20 users with the highest IQ<br />
<table width=75%><tr style='background:gray'> <th>Pos</th> <th>User</th> </tr>";
    $q = mysqli_query(
        $c,
        "SELECT u.* FROM users u LEFT JOIN userstats us ON u.userid=us.userid WHERE u.user_level != 0 ORDER BY us.IQ DESC,u.userid ASC LIMIT 20"
    );
    $p = 0;
    while ($r = mysqli_fetch_array($q))
    {
        $p++;
        if ($r['userid'] == $userid)
        {
            $t = "<b>";
            $et = "</b>";
        }
        else
        {
            $t = "";
            $et = "";
        }
        print
                "<tr> <td>$t$p$et</td> <td>$t{$r['username']} [{$r['userid']}]$et</td> </tr>";
    }
    print "</table>";
}
$h->endpage();

<?php
/*
MCCodes FREE
estate.php Rev 1.1.0
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
$mpq = mysqli_query($c, "SELECT * FROM houses WHERE hWILL={$user->max_will}");
$mp = mysqli_fetch_array($mpq);
$_GET['property'] = abs((int) $_GET['property']);
if ($_GET['property'])
{
    $npq = mysqli_query(
        $c,
        "SELECT * FROM houses WHERE hID={$_GET['property']}"
    );
    $np = mysqli_fetch_array($npq);
    if ($np['hWILL'] < $mp['hWILL'])
    {
        print "You cannot go backwards in houses!";
    }
    else if ($np['hPRICE'] > $user->money)
    {
        print "You do not have enough money to buy the {$np['hrNAME']}.";
    }
    else
    {
        mysqli_query(
            $c,
            "UPDATE users SET money=money-{$np['hPRICE']},will=0,maxwill={$np['hWILL']} WHERE userid=$userid"
        );
        print "Congrats, you bought the {$np['hNAME']} for \${$np['hPRICE']}!";
    }
}
else if (isset($_GET['sellhouse']))
{
    $npq = mysqli_query(
        $c,
        "SELECT * FROM houses WHERE hWILL={$user->max_will}"
    );
    $np = mysqli_fetch_array($npq);
    if ($user->max_will == 100)
    {
        print "You already live in the lowest property!";
    }
    else
    {
        mysqli_query(
            $c,
            "UPDATE users SET money=money+{$np['hPRICE']},will=0,maxwill=100 WHERE userid=$userid"
        );
        print "You sold your {$np['hNAME']} and went back to your shed.";
    }
}
else
{
    print
            "Your current property: <b>{$mp['hNAME']}</b><br />
The houses you can buy are listed below. Click a house to buy it.<br />";
    if ($user->max_will > 100)
    {
        print "<a href='estate.php?sellhouse'>Sell Your House</a><br />";
    }
    $hq = mysqli_query(
        $c,
        "SELECT * FROM houses WHERE hWILL>{$user->max_will} ORDER BY hWILL ASC"
    );
    while ($r = mysqli_fetch_array($hq))
    {
        print
                "<a href='estate.php?property={$r['hID']}'>{$r['hNAME']}</a>&nbsp;&nbsp - Cost: \${$r['hPRICE']}&nbsp;&nbsp - Will Bar: {$r['hWILL']}<br />";
    }
}
$h->endpage();

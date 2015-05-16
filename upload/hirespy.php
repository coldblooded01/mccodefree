<?php
/*
MCCodes FREE
Copyright (C) 2005-2012 Dabomstew
Changes made by John West
updated all the mysql to mysqli. 

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
require "includes/global_func.php";
if ($_SESSION['loggedin'] == 0)
{
    header("Location: login.php");
    exit;
}
$userid = $_SESSION['userid'];
require "header.php";
$h = new headers;
$h->startheaders();
include "includes/mysql.php";
global $c;
$is =
        mysqli_query($c,
                "SELECT u.*,us.* FROM users u LEFT JOIN userstats us ON u.userid=us.userid WHERE u.userid=$userid") or die(mysqli_error($c));
$ir = mysqli_fetch_array($is);
check_level();
$fm = money_formatter($ir['money']);
$cm = money_formatter($ir['crystals'], '');
$lv = date('F j, Y, g:i a', $ir['laston']);
$h->userdata($ir, $lv, $fm, $cm);
$h->menuarea();
if ($ir['user_level'] != 2)
{
    die("");
}
$_POST['ID'] = abs((int) $_POST['ID']);
$_GET['ID'] = abs((int) $_GET['ID']);
if ($_POST['ID'])
{
    $q =
            mysqli_query($c,
                    "SELECT u.*,us.* FROM users u LEFT JOIN userstats us ON u.userid=us.userid WHERE u.userid={$_POST['ID']}");
    $r = mysqli_fetch_array($q);
    if ($r['user_level'] == 2)
    {
        print
                "The spy never came back. It was rumoured he was attacked by {$r['username']} and pushed off a cliff.";
    }
    else
    {
        $payment = $r['level'] * 1000;
        mysqli_query($c,
                "UPDATE users SET money=money-$payment WHERE userid=$userid");
        $exp =
                (int) ($r['exp']
                        / (($r['level'] + 1) * ($r['level'] + 1)
                                * ($r['level'] + 1) * 2) * 100);
        print
                "You have hired a spy to get information on <b>{$r['username']}</b> at the cost of \$$payment. Here is the info he retrieved:<br />
Strength: {$r['strength']}<br />
Agility: {$r['agility']}<br />
Guard: {$r['guard']}<br />
Labour: {$r['labour']}<br />
IQ: {$r['IQ']}<br />
Exp: $exp%<br />
Here is his/her inventory.<br />";
        $inv =
                mysqli_query($c,
                        "SELECT iv.*,i.*,it.* FROM inventory iv LEFT JOIN items i ON iv.inv_itemid=i.itmid LEFT JOIN itemtypes it ON i.itmtype=it.itmtypeid WHERE iv.inv_userid={$r['userid']}");
        if (mysqli_num_rows($inv) == 0)
        {
            print "<b>This person has no items!</b>";
        }
        else
        {
            print
                    "<b>His/her items are listed below.</b><br />
<table width=100%><tr style='background-color:gray;'><th>Item</th><th>Sell Value</th><th>Total Sell Value</th></tr>";
            while ($i = mysqli_fetch_array($inv))
            {
                print "<tr><td>{$i['itmname']}";
                if ($i['inv_qty'] > 1)
                {
                    print "&nbsp;x{$i['inv_qty']}";
                }
                print "</td><td>\${$i['itmsellprice']}</td><td>";
                print "$" . ($i['itmsellprice'] * $i['inv_qty']);
                print "</td></tr>";
            }
            print "</table>";
        }
    }
}
else
{
    $q =
            mysqli_query($c,
                    "SELECT u.*,us.* FROM users u LEFT JOIN userstats us ON u.userid=us.userid WHERE u.userid={$_GET['ID']}");
    if (mysqli_num_rows($q) == 0)
    {
        print "This user does not exist.";
    }
    else
    {
        $r = mysqli_fetch_array($q);
        $payment = $r['level'] * 1000;
        print
                "You are hiring a spy to spy on <b>{$r['username']}</b> at the cost of \$$payment.<br />";
        if ($ir['money'] >= $payment)
        {
            print
                    "<form action='hirespy.php' method='post'><input type='hidden' name='ID' value='{$_GET['ID']}' /><input type='submit' value='Hire' /></form>";
        }
        else
        {
            print "You don't have enough money!";
        }
    }
}

$h->endpage();

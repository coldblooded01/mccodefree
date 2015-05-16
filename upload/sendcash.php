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
        mysqli_query(
                $c, 
                "SELECT u.*,us.* FROM users u LEFT JOIN userstats us ON u.userid=us.userid WHERE u.userid=$userid") or die(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
$ir = mysqli_fetch_array($is);
check_level();
$fm = money_formatter($ir['money']);
$cm = money_formatter($ir['crystals'], '');
$lv = date('F j, Y, g:i a', $ir['laston']);
$h->userdata($ir, $lv, $fm, $cm);
$h->menuarea();
$_GET['ID'] = abs((int) $_GET['ID']);
$_POST['money'] = abs((int) $_POST['money']);
if (!((int) $_GET['ID']))
{
    print "Invalid User ID";
}
else if ($_GET['ID'] == $userid)
{
    print "Haha, what does sending money to yourself do anyway?";
}
else
{
    if ((int) $_POST['money'])
    {
        if ($_POST['money'] > $ir['money'])
        {
            print "Die j00 abuser.";
        }
        else
        {
            mysqli_query(
                    $c, 
                    "UPDATE users SET money=money-{$_POST['money']} WHERE userid=$userid");
            mysqli_query(
                    $c, 
                    "UPDATE users SET money=money+{$_POST['money']} WHERE userid={$_GET['ID']}");
            print "You sent \${$_POST['money']} to ID {$_GET['ID']}.";
            event_add($_GET['ID'],
                    "You received \${$_POST['money']} from {$ir['username']}.",
                    $c);
            $it =
                    mysqli_query(
                            $c, 
                            "SELECT u.*,us.* FROM users u LEFT JOIN userstats us ON u.userid=us.userid WHERE u.userid={$_GET['ID']}") or die(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
            $er = mysqli_fetch_array($it);
            mysqli_query( $c, 
                    "INSERT INTO cashxferlogs VALUES(NULL, $userid, {$_GET['ID']}, {$_POST['money']}, "
                            . time()
                            . ", '{$ir['lastip']}', '{$er['lastip']}')");
        }
    }
    else
    {
        print
                "<h3> Sending Money</h3>
You are sending money to ID: <b>{$_GET['ID']}</b>.
<form action='sendcash.php?ID={$_GET['ID']}' method='post'>
Amnt: <input type='text' name='money' /><br />
<input type='submit' value='Send' /></form>";
        print
                "<h3>Latest 5 Transfers</h3>
<table width=75% border=2> <tr style='background:gray'>  <th>Time</th> <th>User From</th> <th>User To</th> <th>Amount</th> </tr>";
        $q =
                mysqli_query(
                        $c, 
                        "SELECT cx.*,u1.username as sender, u2.username as sent FROM cashxferlogs cx LEFT JOIN users u1 ON cx.cxFROM=u1.userid LEFT JOIN users u2 ON cx.cxTO=u2.userid WHERE cx.cxFROM=$userid ORDER BY cx.cxTIME DESC LIMIT 5")
                or die(
                        ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . "<br />"
                                . "SELECT cx.*,u1.username as sender, u2.username as sent FROM cashxferlogs cx LEFT JOIN users u1 ON cx.cxFROM=u1.userid LEFT JOIN users u2 ON cx.cxTO=u2.userid WHERE cx.cxFROM=$userid ORDER BY cx.cxTIME DESC LIMIT 5");
        while ($r = mysqli_fetch_array($q))
        {
            if ($r['cxFROMIP'] == $r['cxTOIP'])
            {
                $m = "<span style='color:red;font-weight:800'>MULTI</span>";
            }
            else
            {
                $m = "";
            }
            print
                    "<tr> <td>" . date("F j, Y, g:i:s a", $r['cxTIME'])
                            . "</td><td>{$r['sender']} [{$r['cxFROM']}] </td><td>{$r['sent']} [{$r['cxTO']}] </td> <td> \${$r['cxAMOUNT']}</td> </tr>";
        }
        print "</table>";
    }
}
$h->endpage();

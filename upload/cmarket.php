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
                "SELECT u.*,us.* FROM users u LEFT JOIN userstats us ON u.userid=us.userid WHERE u.userid=$userid") or die(((is_object($c)) ? mysqli_error($c) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
$ir = mysqli_fetch_array($is);
check_level();
$fm = money_formatter($ir['money']);
$cm = money_formatter($ir['crystals'], '');
$lv = date('F j, Y, g:i a', $ir['laston']);
$h->userdata($ir, $lv, $fm, $cm);
$h->menuarea();
$_GET['ID'] = abs((int) $_GET['ID']);
print "<h3>Crystal Market</h3>";
switch ($_GET['action'])
{
case "buy":
    crystal_buy();
    break;

case "remove":
    crystal_remove();
    break;

case "add":
    crystal_add();
    break;

default:
    cmarket_index();
    break;
}

function cmarket_index()
{
    global $ir, $c, $userid, $h;
    print
            "<a href='cmarket.php?action=add'>&gt; Add A Listing</a><br /><br />
Viewing all listings...
<table width=75%> <tr style='background:gray'> <th>Adder</th> <th>Qty</th> <th>Price each</th> <th>Price total</th> <th>Links</th> </tr>";
    $q =
            mysqli_query($c,
                    "SELECT cm.*, u.* FROM crystalmarket cm LEFT JOIN users u ON u.userid=cm.cmADDER ORDER BY cmPRICE/cmQTY ASC");
    while ($r = mysqli_fetch_array($q))
    {
        if ($r['cmADDER'] == $userid)
        {
            $link =
                    "<a href='cmarket.php?action=remove&ID={$r['cmID']}'>Remove</a>";
        }
        else
        {
            $link =
                    "<a href='cmarket.php?action=buy&ID={$r['cmID']}'>Buy</a>";
        }
        $each = (int) $r['cmPRICE'] / $r['cmQTY'];
        print
                "\n<tr> <td><a href='viewuser.php?u={$r['userid']}'>{$r['username']}</a> [{$r['userid']}]</td> <td>{$r['cmQTY']}</td> <td> \$"
                        . number_format($each) . "</td> <td>\$"
                        . number_format($r['cmPRICE'])
                        . "</td> <td>[$link]</td> </tr>";
    }
    print "</table>";
}

function crystal_remove()
{
    global $ir, $c, $userid, $h;
    $q =
            mysqli_query($c,
                    "SELECT * FROM crystalmarket WHERE cmID='{$_GET['ID']}' AND cmADDER=$userid");
    if (!mysqli_num_rows($q))
    {
        print
                "Error, either these crystals do not exist, or you are not the owner.<br />
<a href='cmarket.php'>&gt; Back</a>";
        $h->endpage();
        exit;
    }
    $r = mysqli_fetch_array($q);
    mysqli_query($c,
            "UPDATE users SET crystals=crystals+{$r['cmQTY']} where userid=$userid") or die(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    mysqli_query($c,"DELETE FROM crystalmarket WHERE cmID='{$_GET['ID']}'");
    print
            "Crystals removed from market!<br />
<a href='cmarket.php'>&gt; Back</a>";
}

function crystal_buy()
{
    global $ir, $c, $userid, $h;
    $q =
            mysqli_query($c,
                    "SELECT * FROM crystalmarket cm WHERE cmID='{$_GET['ID']}'");
    if (!mysqli_num_rows($q))
    {
        print
                "Error, either these crystals do not exist, or they have already been bought.<br />
<a href='cmarket.php'>&gt; Back</a>";
        $h->endpage();
        exit;
    }
    $r = mysqli_fetch_array($q);
    if ($r['cmPRICE'] > $ir['money'])
    {
        print
                "Error, you do not have the funds to buy these crystals.<br />
<a href='cmarket.php'>&gt; Back</a>";
        $h->endpage();
        exit;
    }
    mysqli_query($c,
            "UPDATE users SET crystals=crystals+{$r['cmQTY']} where userid=$userid") or die(((is_object($c)) ? mysqli_error($c) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    mysqli_query($c,"DELETE FROM crystalmarket WHERE cmID='{$_GET['ID']}'");
    mysqli_query($c,
            "UPDATE users SET money=money-{$r['cmPRICE']} where userid=$userid");
    mysqli_query($c,
            "UPDATE users SET money=money+{$r['cmPRICE']} where userid={$r['cmADDER']}");
    event_add($c,$r['cmADDER'],
            "<a href='viewuser.php?u=$userid'>{$ir['username']}</a> bought your {$r['cmQTY']} crystals from the market for \$"
                    . number_format($r['cmPRICE']) . ".");
    print
            "You bought the {$r['cmQTY']} crystals from the market for \$"
                    . number_format($r['cmPRICE']) . ".";

}

function crystal_add()
{
    global $ir, $c, $userid, $h;
    $_POST['amnt'] = abs((int) $_POST['amnt']);
    $_POST['price'] = abs((int) $_POST['price']);
    if ($_POST['amnt'])
    {
        if ($_POST['amnt'] > $ir['crystals'])
        {
            die(
                    "You are trying to add more crystals to the market than you have.");
        }
        $tp = $_POST['amnt'] * $_POST['price'];
        mysqli_query($c,
                "INSERT INTO crystalmarket VALUES(NULL,{$_POST['amnt']},$userid,$tp)");
        mysqli_query($c,
                "UPDATE users SET crystals=crystals-{$_POST['amnt']} WHERE userid=$userid");
        print
                "Crystals added to market!<br />
<a href='cmarket.php'>&gt; Back</a>";
    }
    else
    {
        print
                "<b>Adding a listing...</b><br /><br />
You have <b>{$ir['crystals']}</b> crystal(s) that you can add to the market.<form action='cmarket.php?action=add' method='post'><table width=50% border=2><tr>
<td>Crystals:</td> <td><input type='text' name='amnt' value='{$ir['crystals']}' /></td></tr><tr>
<td>Price Each:</td> <td><input type='text' name='price' value='200' /></td></tr><tr>
<td colspan=2 align=center><input type='submit' value='Add To Market' /></tr></table></form>";
    }
}
$h->endpage();

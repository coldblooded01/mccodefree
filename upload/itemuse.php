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
//Food
if (!$_GET['ID'])
{
    print "Invalid use of file";
}
else
{
    $i =
            mysqli_query($c,
                    "SELECT iv.*,i.*,it.* FROM inventory iv LEFT JOIN items i ON iv.inv_itemid=i.itmid LEFT JOIN itemtypes it ON i.itmtype=it.itmtypeid WHERE iv.inv_id={$_GET['ID']} AND iv.inv_userid=$userid");
    if (mysqli_num_rows($i) == 0)
    {
        print "Invalid item ID";
    }
    else
    {
        $r = mysqli_fetch_array($i);
        if ($r['itmtypename'] == 'Food')
        {
            $f =
                    mysqli_query($c,
                            "SELECT * FROM food WHERE item_id={$r['itmid']}");
            $fr = mysqli_fetch_array($f);
            mysqli_query($c,
                    "UPDATE inventory SET inv_qty=inv_qty-1 WHERE inv_id={$_GET['ID']}");
            mysqli_query($c,"DELETE FROM inventory WHERE inv_qty=0");
            mysqli_query($c,
                    "UPDATE users SET energy=energy+{$fr['energy']} WHERE userid=$userid");
            mysqli_query($c,
                    "UPDATE users SET energy=maxenergy WHERE energy > maxenergy");
            print
                    "You cram a {$r['itmname']} into your mouth. You feel a bit of energy coming back to you.";
        }
        else if ($r['itmtypename'] == 'Medical')
        {
            $f =
                    mysqli_query($c,
                            "SELECT * FROM medical WHERE item_id={$r['itmid']}");
            $fr = mysqli_fetch_array($f);
            mysqli_query($c,
                    "UPDATE inventory SET inv_qty=inv_qty-1 WHERE inv_id={$_GET['ID']}");
            mysqli_query($c,"DELETE FROM inventory WHERE inv_qty=0");
            mysqli_query($c,
                    "UPDATE users SET hp=hp+{$fr['health']} WHERE userid=$userid");
            mysqli_query($c,"UPDATE users SET hp=maxhp WHERE hp > maxhp");
            if ($r['itmname'] == 'Full Restore')
            {
                mysqli_query($c,
                        "UPDATE users SET energy=maxenergy,will=maxwill,brave=maxbrave WHERE userid=$userid");
            }
            if ($r['itmname'] == 'Will Potion')
            {
                mysqli_query($c,
                        "UPDATE users SET will=maxwill WHERE userid=$userid");
            }
            print
                    "You spray a {$r['itmname']} into your mouth. You feel a bit of health coming back to you.";
        }
        else
        {
            print "You cannot use this item.";
        }
    }
}
$h->endpage();

<?php
/*
MCCodes FREE
itemuse.php Rev 1.1.0
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
$is =
        mysql_query(
                "SELECT u.*,us.* FROM users u LEFT JOIN userstats us ON u.userid=us.userid WHERE u.userid=$userid",
                $c) or die(mysql_error());
$ir = mysql_fetch_array($is);
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
            mysql_query(
                    "SELECT iv.*,i.*,it.* FROM inventory iv LEFT JOIN items i ON iv.inv_itemid=i.itmid LEFT JOIN itemtypes it ON i.itmtype=it.itmtypeid WHERE iv.inv_id={$_GET['ID']} AND iv.inv_userid=$userid",
                    $c);
    if (mysql_num_rows($i) == 0)
    {
        print "Invalid item ID";
    }
    else
    {
        $r = mysql_fetch_array($i);
        if ($r['itmtypename'] == 'Food')
        {
            $f =
                    mysql_query(
                            "SELECT * FROM food WHERE item_id={$r['itmid']}",
                            $c);
            $fr = mysql_fetch_array($f);
            mysql_query(
                    "UPDATE inventory SET inv_qty=inv_qty-1 WHERE inv_id={$_GET['ID']}",
                    $c);
            mysql_query("DELETE FROM inventory WHERE inv_qty=0", $c);
            mysql_query(
                    "UPDATE users SET energy=energy+{$fr['energy']} WHERE userid=$userid");
            mysql_query(
                    "UPDATE users SET energy=maxenergy WHERE energy > maxenergy");
            print
                    "You cram a {$r['itmname']} into your mouth. You feel a bit of energy coming back to you.";
        }
        else if ($r['itmtypename'] == 'Medical')
        {
            $f =
                    mysql_query(
                            "SELECT * FROM medical WHERE item_id={$r['itmid']}",
                            $c);
            $fr = mysql_fetch_array($f);
            mysql_query(
                    "UPDATE inventory SET inv_qty=inv_qty-1 WHERE inv_id={$_GET['ID']}",
                    $c);
            mysql_query("DELETE FROM inventory WHERE inv_qty=0", $c);
            mysql_query(
                    "UPDATE users SET hp=hp+{$fr['health']} WHERE userid=$userid");
            mysql_query("UPDATE users SET hp=maxhp WHERE hp > maxhp");
            if ($r['itmname'] == 'Full Restore')
            {
                mysql_query(
                        "UPDATE users SET energy=maxenergy,will=maxwill,brave=maxbrave WHERE userid=$userid",
                        $c);
            }
            if ($r['itmname'] == 'Will Potion')
            {
                mysql_query(
                        "UPDATE users SET will=maxwill WHERE userid=$userid",
                        $c);
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

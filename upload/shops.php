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
$_GET['shop'] = abs((int) $_GET['shop']);
if (!$_GET['shop'])
{
    print "You begin looking through town and you see a few shops.<br />";
    $q =
            mysqli_query(
                    $c, 
                    "SELECT * FROM shops WHERE shopLOCATION={$ir['location']}");
    print
            "<table width=85%><tr style='background: gray;'><th>Shop</th><th>Description</th></tr>";
    while ($r = mysqli_fetch_array($q))
    {
        print
                "<tr><td><a href='shops.php?shop={$r['shopID']}'>{$r['shopNAME']}</a></td><td>{$r['shopDESCRIPTION']}</td></tr>";
    }
    print "</table>";
}
else
{
    $sd = mysqli_query( $c, "SELECT * FROM shops WHERE shopID={$_GET['shop']}");
    if (mysqli_num_rows($sd))
    {
        $shopdata = mysqli_fetch_array($sd);
        if ($shopdata['shopLOCATION'] == $ir['location'])
        {
            print
                    "Browsing items at <b>{$shopdata['shopNAME']}...</b><br />
<table><tr style='background: gray;'><th>Item</th><th>Description</th><th>Price</th><th>Sell Price</th><th>Buy</th></tr>";
            $qtwo =
                    mysqli_query(
                            $c, 
                            "SELECT si.*,i.*,it.* FROM shopitems si LEFT JOIN items i ON si.sitemITEMID=i.itmid LEFT JOIN itemtypes it ON i.itmtype=it.itmtypeid WHERE si.sitemSHOP={$_GET['shop']} ORDER BY i.itmtype ASC, i.itmbuyprice ASC, i.itmname ASC") or die(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
            $lt = "";
            while ($r = mysqli_fetch_array($qtwo))
            {
                if ($lt != $r['itmtypename'])
                {
                    $lt = $r['itmtypename'];
                    print
                            "\n<tr style='background: gray;'><th colspan=5>{$lt}</th></tr>";
                }
                print
                        "\n<tr><td>{$r['itmname']}</td><td>{$r['itmdesc']}</td><td>\${$r['itmbuyprice']}</td><td>\${$r['itmsellprice']}</td><td><form action='itembuy.php?ID={$r['itmid']}' method='post'>Qty: <input type='text' name='qty' value='1' /><input type='submit' value='Buy' /></form></td></tr>";
            }
            print "</table>";
        }
        else
        {
            print "You are trying to access a shop in another city!";
        }
    }
    else
    {
        print "You are trying to access an invalid shop!";
    }
}
$h->endpage();

<?php
/*
MCCodes FREE
inventory.php Rev 1.1.0
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

$inv = mysqli_query(
    $c,
    "SELECT iv.*,i.*,it.* FROM inventory iv LEFT JOIN items i ON iv.inv_itemid=i.itmid LEFT JOIN itemtypes it ON i.itmtype=it.itmtypeid WHERE iv.inv_userid={$userid} ORDER BY i.itmtype ASC, i.itmname ASC"
);
if (mysqli_num_rows($inv) == 0)
{
    print "<b>You have no items!</b>";
}
else
{
    print
            "<b>Your items are listed below.</b><br />
<table width=100%><tr style='background-color:gray;'><th>Item</th><th>Sell Value</th><th>Total Sell Value</th><th>Links</th></tr>";
    $lt = "";
    while ($i = mysqli_fetch_array($inv))
    {
        if ($lt != $i['itmtypename'])
        {
            $lt = $i['itmtypename'];
            print
                    "\n<tr style='background: gray;'><th colspan=4>{$lt}</th></tr>";
        }
        print "<tr><td>{$i['itmname']}";
        if ($i['inv_qty'] > 1)
        {
            print "&nbsp;x{$i['inv_qty']}";
        }
        print "</td><td>\${$i['itmsellprice']}</td><td>";
        print "$" . ($i['itmsellprice'] * $i['inv_qty']);
        print
                "</td><td>[<a href='iteminfo.php?ID={$i['itmid']}'>Info</a>] [<a href='itemsend.php?ID={$i['inv_id']}'>Send</a>] [<a href='itemsell.php?ID={$i['inv_id']}'>Sell</a>] [<a href='imadd.php?ID={$i['inv_id']}'>Add To Market</a>]";
        if ($i['itmtypename'] == 'Food' || $i['itmtypename'] == 'Medical')
        {
            print " [<a href='itemuse.php?ID={$i['inv_id']}'>Use</a>]";
        }
        if ($i['itmname'] == 'Nuclear Bomb')
        {
            print " [<a href='nuclearbomb.php'>Use</a>]";
        }
        print "</td></tr>";
    }
    print "</table>";
}
$h->endpage();

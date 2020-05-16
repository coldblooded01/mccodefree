<?php
/*
MCCodes FREE
iteminfo.php Rev 1.1.0
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
require_once(dirname(__FILE__) . "/models/item.php");
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
//look up item
$_GET['ID'] = abs((int) $_GET['ID']);
$itmid = $_GET['ID'];
if (!$itmid || !Item::exists($itmid))
{
    print "Invalid item ID";
}
else
{
    $item = Item::get($itmid);

    print "<table width=75%><tr style='background: gray;'><th colspan=2><b>Looking up info on {$item->name}</b></th></table>
<table width=75%><tr bgcolor=#dfdfdf><th colspan=2>The <b>{$item->name}</b> is a/an {$item->item_type->name} Item - <b>{$item->description}</b></th></table><br />
<table width=75%><tr style='background: gray;'><th colspan=2>Item Info</th></tr><tr style='background:gray'><th>Item Buy Price</th><th>Item Sell Price</th></tr><tr><td>";
    if ($item->buy_price)
    {
        print money_formatter($item->buy_price);
    }
    else
    {
        print "N/A";
    }
    print "</td><td>";
    if ($item->sell_price)
    {
        print money_formatter($item->sell_price);
    }
    else
    {
        print "N/A</td></tr></table>";
    }

}
$h->endpage();

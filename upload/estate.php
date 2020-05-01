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
$user_house = $user->get_house();
$_GET['property'] = abs((int) $_GET['property']);
if ($_GET['property'])
{
    $house_on_sale = House::get($_GET['property']);
    if ($house_on_sale->will < $user_house->will)
    {
        print "You cannot go backwards in houses!";
    }
    else if ($house_on_sale->price > $user->money)
    {
        print "You do not have enough money to buy the {$house_on_sale->name}.";
    }
    else
    {
        $user->buy_house($house_on_sale);
        print "Congrats, you bought the {$house_on_sale->name} for \${$house_on_sale->price}!";
    }
}
else if (isset($_GET['sellhouse']))
{
    if ($user->max_will == 100)
    {
        print "You already live in the lowest property!";
    }
    else
    {
        $user->sell_house();
        print "You sold your {$user_house->name} and went back to your shed.";
    }
}
else
{
    print
            "Your current property: <b>{$user_house->name}</b><br />
The houses you can buy are listed below. Click a house to buy it.<br />";
    if ($user->max_will > 100)
    {
        print "<a href='estate.php?sellhouse'>Sell Your House</a><br />";
    }
    $houses = House::filter_by_will_gt($user->max_will, 'hWILL');
    foreach($houses as $house) {
        print "<a href='estate.php?property={$house->id}'>{$house->name}</a>&nbsp;&nbsp - Cost: \${$house->price}&nbsp;&nbsp - Will Bar: {$house->will}<br />";
    }
}
$h->endpage();

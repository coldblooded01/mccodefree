<?php
/*
MCCodes FREE
itembuy.php Rev 1.1.0
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
$_GET['ID'] = abs((int) $_GET['ID']);
$_POST['qty'] = abs((int) $_POST['qty']);
if (!$_GET['ID'] || !$_POST['qty'])
{
    print "Invalid use of file";
}
else if ($_POST['qty'] <= 0)
{
    print
            "You have been added to the delete list for trying to cheat the game.";
}
else
{
    if (!Item::exists($_GET['ID']))
    {
        print "Invalid item ID";
    }
    else
    {
        $itemd = Item::get($_GET['ID']);
        if ($user->money < $itemd->buy_price * $_POST['qty'])
        {
            print "You don't have enough money to buy this item!";
            $h->endpage();
            exit;
        }
        if (!$itemd->is_buyable())
        {
            print "This item can't be bought!";
            $h->endpage();
            exit;
        }
        $price = ($itemd->buy_price * $_POST['qty']);
        mysqli_query(
            $c,
            "INSERT INTO inventory VALUES(NULL,{$itemd->id},$userid,{$_POST['qty']});"
        );
        mysqli_query(
            $c,
            "UPDATE users SET money=money-$price WHERE userid=$userid"
        );
        mysqli_query(
            $c,
            "INSERT INTO itembuylogs VALUES (NULL, $userid, {$itemd->id}, $price, {$_POST['qty']}, "
                . time()
                . ", '{$user->username} bought {$_POST['qty']} {$itemd->name}(s) for {$price}')"
        );
        print "You bought {$_POST['qty']} {$itemd->name}(s) for \$$price";
    }
}
$h->endpage();

<?php
/*
MCCodes FREE
imadd.php Rev 1.1.0
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

check_level();
$h->userdata($user);
$h->menuarea();
$_GET['ID'] = abs((int) $_GET['ID']);
$_GET['price'] = abs((int) $_GET['price']);
if ($_GET['price'])
{
    $q = mysqli_query(
        $c,
        "SELECT iv.*,i.* FROM inventory iv LEFT JOIN items i ON iv.inv_itemid=i.itmid WHERE inv_id={$_GET['ID']} and inv_userid=$userid"
    );
    if (mysqli_num_rows($q) == 0)
    {
        print "Invalid Item ID";
    }
    else
    {
        $r = mysqli_fetch_array($q);
        mysqli_query(
            $c,
            "INSERT INTO itemmarket VALUES(NULL,'{$r['inv_itemid']}',$userid,{$_GET['price']})"
        );
        mysqli_query(
            $c,
            "UPDATE inventory SET inv_qty=inv_qty-1 WHERE inv_id={$_GET['ID']}"
        );
        mysqli_query($c, "DELETE FROM inventory WHERE inv_qty=0");
        mysqli_query(
            $c,
            "INSERT INTO imarketaddlogs VALUES ( '', {$r['inv_itemid']}, {$_GET['price']}, {$r['inv_id']}, $userid, "
                . time()
                . ", '{$user->username} added a {$r['itmname']} to the itemmarket for \${$_GET['price']}')"
        );
        print "Item added to market.";
    }
}
else
{
    $q = mysqli_query(
        $c,
        "SELECT * FROM inventory WHERE inv_id={$_GET['ID']} and inv_userid=$userid"
    );
    if (mysqli_num_rows($q) == 0)
    {
        print "Invalid Item ID";
    }
    else
    {
        $r = mysqli_fetch_array($q);
        print
                "Adding an item to the item market...
<form action='imadd.php' method='get'>
<input type='hidden' name='ID' value='{$_GET['ID']}' />
Price: \$<input type='text' name='price' value='0' /><br />
<input type='submit' value='Add' /></form>";
    }
}
$h->endpage();

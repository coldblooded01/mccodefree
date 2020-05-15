<?php
/*
MCCodes FREE
itemmarket.php Rev 1.1.0
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
require_once(dirname(__FILE__) . "/models/event.php");
$user = User::get($userid);
require "header.php";
$h = new Header();
$h->startheaders();
include "mysql.php";
global $c;
$user->check_level();
$h->userdata($user);
$h->menuarea();
print "<h3>Item Market</h3>";
switch ($_GET['action'])
{
case "buy":
    item_buy();
    break;

case "gift1":
    item_gift1();
    break;

case "gift2":
    item_gift2();
    break;

case "remove":
    item_remove();
    break;

default:
    imarket_index();
    break;
}

function imarket_index()
{
    global $user, $c, $userid, $h;
    print
            "Viewing all listings...
<table width=75%> <tr style='background:gray'> <th>Adder</th> <th>Item</th> <th>Price</th> <th>Links</th> </tr>";
    $q = mysqli_query(
        $c,
        "SELECT im.*, i.*, u.*,it.* FROM itemmarket im LEFT JOIN items i ON im.imITEM=i.itmid LEFT JOIN users u ON u.userid=im.imADDER LEFT JOIN itemtypes it ON i.itmtype=it.itmtypeid ORDER BY i.itmtype, i.itmname ASC"
    );
    $lt = "";
    while ($r = mysqli_fetch_array($q))
    {
        if ($lt != $r['itmtypename'])
        {
            $lt = $r['itmtypename'];
            print
                    "\n<tr style='background: gray;'><th colspan=4>{$lt}</th></tr>";
        }
        if ($r['imADDER'] == $userid)
        {
            $link =
                    "[<a href='itemmarket.php?action=remove&ID={$r['imID']}'>Remove</a>]";
        }
        else
        {
            $link =
                    "[<a href='itemmarket.php?action=buy&ID={$r['imID']}'>Buy</a>] [<a href='itemmarket.php?action=gift1&ID={$r['imID']}'>Gift</a>]";
        }
        print
                "\n<tr> <td><a href='viewuser.php?u={$r['userid']}'>{$r['username']}</a> [{$r['userid']}]</td> <td>{$r['itmname']}</td> <td>\$"
                        . number_format($r['imPRICE'])
                        . "</td> <td>[<a href='iteminfo.php?ID={$r['itmid']}'>Info</a>] $link</td> </tr>";
    }
    print "</table>";
}

function item_remove()
{
    global $user, $c, $userid, $h;
    $q =
            mysqli_query(
                    "SELECT im.*,i.* FROM itemmarket im LEFT JOIN items i ON im.imITEM=i.itmid WHERE imID={$_GET['ID']} AND imADDER=$userid",
                    $c);
    if (!mysqli_num_rows($q))
    {
        print
                "Error, either this item does not exist, or you are not the owner.<br />
<a href='itemmarket.php'>&gt; Back</a>";
        $h->endpage();
        exit;
    }
    $r = mysqli_fetch_array($q);
    mysqli_query(
        $c,
        "INSERT INTO inventory VALUES(NULL,{$r['imITEM']},$userid,1)"
    ) or die(mysqli_error($c));
    $i = mysqli_insert_id($c);
    mysqli_query($c, "DELETE FROM itemmarket WHERE imID={$_GET['ID']}");
    mysqli_query(
        $c,
        "INSERT INTO imremovelogs VALUES(NULL, {$r['imITEM']}, {$r['imADDER']}, $userid, {$r['imID']}, $i, "
            . time()
            . ", '{$user->username} removed a {$r['itmname']} from the item market.')"
    );
    print
            "Item removed from market!<br />
<a href='itemmarket.php'>&gt; Back</a>";
}

function item_buy()
{
    global $user, $c, $userid, $h;
    $q = mysqli_query(
        $c,
        "SELECT * FROM itemmarket im LEFT JOIN items i ON i.itmid=im.imITEM WHERE imID={$_GET['ID']}"
    );
    if (!mysqli_num_rows($q))
    {
        print
                "Error, either this item does not exist, or it has already been bought.<br />
<a href='itemmarket.php'>&gt; Back</a>";
        $h->endpage();
        exit;
    }
    $r = mysqli_fetch_array($q);
    if ($r['imPRICE'] > $user->money)
    {
        print
                "Error, you do not have the funds to buy this item.<br />
<a href='itemmarket.php'>&gt; Back</a>";
        $h->endpage();
        exit;
    }
    mysqli_query(
        $c,
        "INSERT INTO inventory VALUES(NULL,{$r['imITEM']},$userid,1)"
    ) or die(mysqli_error($c));
    $i = mysqli_insert_id($c);
    mysqli_query(
        $c,
        "DELETE FROM itemmarket WHERE imID={$_GET['ID']}"
    );
    mysqli_query(
        $c,
        "UPDATE users SET money=money-{$r['imPRICE']} where userid=$userid"
    );
    mysqli_query(
        $c,
        "UPDATE users SET money=money+{$r['imPRICE']} where userid={$r['imADDER']}"
    );
    Event::add(
        $r['imADDER'],
        "<a href='viewuser.php?u=$userid'>{$user->username}</a> bought your {$r['itmname']} item from the market for \$"
            . number_format($r['imPRICE']) . "."
    );
    mysqli_query(
        $c,
        "INSERT INTO imbuylogs VALUES(NULL, {$r['imITEM']}, {$r['imADDER']}, $userid,  {$r['imPRICE']}, {$r['imID']}, $i, "
            . time()
            . ", '{$user->username} bought a {$r['itmname']} from the item market for \${$r['imPRICE']} from user ID {$r['imADDER']}')"
    );
    print
            "You bought the {$r['itmname']} from the market for \$"
                    . number_format($r['imPRICE']) . ".";

}

function item_gift1()
{
    global $user, $c, $userid, $h;
    $q = mysqli_query(
        $c,
        "SELECT * FROM itemmarket im LEFT JOIN items i ON i.itmid=im.imITEM WHERE imID={$_GET['ID']}"
    );
    if (!mysqli_num_rows($q))
    {
        print
                "Error, either this item does not exist, or it has already been bought.<br />
<a href='itemmarket.php'>&gt; Back</a>";
        $h->endpage();
        exit;
    }
    $r = mysqli_fetch_array($q);
    if ($r['imPRICE'] > $user->money)
    {
        print
                "Error, you do not have the funds to buy this item.<br />
<a href='itemmarket.php'>&gt; Back</a>";
        $h->endpage();
        exit;
    }
    print
            "Buying the <b>{$r['itmname']}</b> for \$"
                    . number_format($r['imPRICE'])
                    . " as a gift...<br />
<form action='itemmarket.php?action=gift2' method='post'>
<input type='hidden' name='ID' value='{$_GET['ID']}' />
User to give gift to: " . user_dropdown($c, 'user')
                    . "<br />
<input type='submit' value='Buy Item and Send Gift' /></form>";
}

function item_gift2()
{
    global $user, $c, $userid, $h;
    $q = mysqli_query(
        $c,
        "SELECT * FROM itemmarket im LEFT JOIN items i ON i.itmid=im.imITEM WHERE imID={$_POST['ID']}"
    );
    if (!mysqli_num_rows($q))
    {
        print
                "Error, either this item does not exist, or it has already been bought.<br />
<a href='itemmarket.php'>&gt; Back</a>";
        $h->endpage();
        exit;
    }
    $r = mysqli_fetch_array($q);
    if ($r['imPRICE'] > $user->money)
    {
        print
                "Error, you do not have the funds to buy this item.<br />
<a href='itemmarket.php'>&gt; Back</a>";
        $h->endpage();
        exit;
    }
    mysqli_query(
        $c,
        "INSERT INTO inventory VALUES(NULL,{$r['imITEM']},{$_POST['user']},1)"
    ) or die(mysqli_error($c));
    $i = mysqli_insert_id($c);
    mysqli_query($c, "DELETE FROM itemmarket WHERE imID={$_POST['ID']}");
    mysqli_query(
        $c,
        "UPDATE users SET money=money-{$r['imPRICE']} where userid=$userid"
    );
    mysqli_query(
        $c,
        "UPDATE users SET money=money+{$r['imPRICE']} where userid={$r['imADDER']}"
    );
    Event::add(
        $r['imADDER'],
        "<a href='viewuser.php?u=$userid'>{$user->username}</a> bought your {$r['itmname']} item from the market for \$"
            . number_format($r['imPRICE']) . "."
    );
    Event::add(
        $_POST['user'],
        "<a href='viewuser.php?u=$userid'>{$user->username}</a> bought you a {$r['itmname']} from the item market as a gift."
    );

    $u = mysqli_query($c, "SELECT * FROM users WHERE userid={$_POST['user']}");
    $uname = mysqli_data_seek($u, 1);
    mysqli_query(
        $c,
        "INSERT INTO imbuylogs VALUES(NULL, {$r['imITEM']}, {$r['imADDER']}, $userid,  {$r['imPRICE']}, {$r['imID']}, $i, "
            . time()
            . ", '{$user->username} bought a {$r['itmname']} from the item market for \${$r['imPRICE']} from user ID {$r['imADDER']} as a gift for $uname [{$_POST['user']}]')"
    );
    print
            "You bought the {$r['itmname']} from the market for \$"
                    . number_format($r['imPRICE'])
                    . " and sent the gift to $uname.";

}
$h->endpage();

<?php
/*
MCCodes FREE
friendslist.php Rev 1.1.0
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
require_once(dirname(__FILE__) . "/models/user.php");
$user = User::get($userid);

$user->check_level();
$h->userdata($user);
$h->menuarea();
if (!$user->is_donator())
{
    die("This feature is for donators only.");
}
print "<h3>Friends List</h3>";
switch ($_GET['action'])
{
case "add":
    add_friend();
    break;

case "remove":
    remove_friend();
    break;

case "ccomment":
    change_comment();
    break;

default:
    friends_list();
    break;
}

function friends_list()
{
    global $c, $userid;
    print
            "<a href='friendslist.php?action=add'>&gt; Add a Friend</a><br />
These are the people on your friends list. ";
    $q_y = mysqli_query($c, "SELECT * FROM friendslist WHERE fl_ADDED=$userid");
    print
            mysqli_num_rows($q_y)
                    . " people have added you to their list.<br />
Most liked: [";
    $q2r = mysqli_query(
        $c,
        "SELECT u.username,count( * ) as cnt,fl.fl_ADDED FROM friendslist fl LEFT JOIN users u on fl.fl_ADDED=u.userid GROUP BY fl.fl_ADDED ORDER BY cnt DESC LIMIT 5"
    ) or die(mysqli_error($c));
    $r = 0;
    while ($r2r = mysqli_fetch_array($q2r))
    {
        $r++;
        if ($r > 1)
        {
            print " | ";
        }
        print
                "<a href='viewuser.php?u={$r2r['fl_ADDED']}'>{$r2r['username']}</a>";
    }
    print
            "]<br />
<table width=90%><tr style='background:gray'> <th>ID</th> <th>Name</th> <th>Mail</th> <th>Send Cash</th> <th>Remove</th> <th>Comment</th> <th>Change Comment</th> <th>Online?</th></tr>";
    $q = mysqli_query(
        $c,
        "SELECT fl.*,u.* FROM friendslist fl LEFT JOIN users u ON fl.fl_ADDED=u.userid WHERE fl.fl_ADDER=$userid ORDER BY u.username ASC"
    );
    while ($r = mysqli_fetch_array($q))
    {
        if ($r['laston'] >= time() - 15 * 60)
        {
            $on = "<font color=green><b>Online</b></font>";
        }
        else
        {
            $on = "<font color=red><b>Offline</b></font>";
        }
        $d = "";
        if ($r['donatordays'])
        {
            $r['username'] = "<font color=red>{$r['username']}</font>";
            $d =
                    "<img src='donator.gif' alt='Donator: {$r['donatordays']} Days Left' title='Donator: {$r['donatordays']} Days Left' />";
        }
        if (!$r['fl_COMMENT'])
        {
            $r['fl_COMMENT'] = "N/A";
        }
        print
                "<tr> <td>{$r['userid']}</td> <td><a href='viewuser.php?u={$r['userid']}'>{$r['username']}</a> $d</td> <td><a href='mailbox.php?action=compose&ID={$r['userid']}'>Mail</a></td> <td><a href='sendcash.php?ID={$r['userid']}'>Send Cash</a></td> <td><a href='friendslist.php?action=remove&f={$r['fl_ID']}'>Remove</a></td> <td>{$r['fl_COMMENT']}</td> <td><a href='friendslist.php?action=ccomment&f={$r['fl_ID']}'>Change</a></td> <td>$on</td></tr>";
    }
}

function add_friend()
{
    global $c, $userid;
    $_POST['ID'] = abs((int) $_POST['ID']);
    $_POST['comment'] = mysqli_real_escape_string(
        $c,
        nl2br(
            htmlentities(
                stripslashes($_POST['comment']),
                ENT_QUOTES, 
                'ISO-8859-1'
            )
        )
    );

    if ($_POST['ID'])
    {
        $qc = mysqli_query(
            $c,
            "SELECT * FROM friendslist WHERE fl_ADDER=$userid AND fl_ADDED={$_POST['ID']}"
        );
        $q = mysqli_query(
            $c,
            "SELECT * FROM users WHERE userid={$_POST['ID']}"
        );
        if (mysqli_num_rows($qc))
        {
            print "You cannot add the same person twice.";
        }
        else if ($userid == $_POST['ID'])
        {
            print
                    "You cannot be so lonely that you have to try and add yourself.";
        }
        else if (mysqli_num_rows($q) == 0)
        {
            print "Oh no, you're trying to add a ghost.";
        }
        else
        {
            mysqli_query(
                $c,
                "INSERT INTO friendslist VALUES(NULL, $userid, {$_POST['ID']}, '{$_POST['comment']}')"
            ) or die(mysqli_error($c));
            $r = mysqli_fetch_array($q);
            print
                    "{$r['username']} was added to your friends list.<br />
<a href='friendslist.php'>&gt; Back</a>";
        }
    }
    else
    {
        $_GET['ID'] =
                (isset($_GET['ID']) && is_numeric($_GET['ID']))
                        ? abs(intval($_GET['ID'])) : '';
        print
                "Adding a friend!<form action='friendslist.php?action=add' method='post'>
Friend's ID: <input type='text' name='ID' value='{$_GET['ID']}' /><br />
Comment (optional): <br />
<textarea name='comment' rows='7' cols='40'></textarea><br />
<input type='submit' value='Add Friend' /></form>";
    }

}

function remove_friend()
{
    global $c, $userid;
    mysqli_query(
        $c,
        "DELETE FROM friendslist WHERE fl_ID={$_GET['f']} AND fl_ADDER=$userid"
    );
    print
            "Friends list entry removed!<br />
<a href='friendslist.php'>&gt; Back</a>";
}

function change_comment()
{
    global $c, $userid;
    $_POST['f'] = abs((int) $_POST['f']);
    $_POST['comment'] = mysqli_real_escape_string(
        $c,
        nl2br(
            htmlentities(
                stripslashes($_POST['comment']),
                ENT_QUOTES,
                'ISO-8859-1'
            )
        )
    );
    if ($_POST['comment'])
    {
        mysqli_query(
            $c,
            "UPDATE friendslist SET fl_COMMENT='{$_POST['comment']}' WHERE fl_ID={$_POST['f']} AND fl_ADDER=$userid"
        );
        print
                "Comment for friend changed!<br />
<a href='friendslist.php'>&gt; Back</a>";
    }
    else
    {
        $_GET['f'] = abs((int) $_GET['f']);
        $q = mysqli_query(
            $c,
            "SELECT * FROM friendslist WHERE fl_ID={$_GET['f']} AND fl_ADDER=$userid"
        );
        if (mysqli_num_rows($q))
        {
            $r = mysqli_fetch_array($q);
            $comment = str_replace('<br />', "\n", $r['fl_COMMENT']);
            print
                    "Changing a comment.<form action='friendslist.php?action=ccomment' method='post'>
<input type='hidden' name='f' value='{$_GET['f']}' /><br />
Comment: <br />
<textarea rows='7' cols='40' name='comment'>$comment</textarea><br />
<input type='submit' value='Change Comment' /></form>";
        }
        else
        {
            print "Stop trying to edit comments that aren't yours.";
        }
    }
}

$h->endpage();

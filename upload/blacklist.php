<?php
/*
MCCodes FREE
blacklist.php Rev 1.1.0
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
require_once(dirname(__FILE__) . "/models/blacklist_record.php");
$user = User::get($userid);
require "header.php";
$h = new Header();
$h->startheaders();
include "mysql.php";
global $c;

$user->check_level();
$h->userdata($user);
$h->menuarea();
if (!$user->is_donator())
{
    die("This feature is for donators only.");
}
print "<h3>Black List</h3>";
switch ($_GET['action'])
{
case "add":
    add_enemy();
    break;

case "remove":
    remove_enemy();
    break;

case "ccomment":
    change_comment();
    break;

default:
    black_list();
    break;
}

function black_list()
{
    global $user;
    print 
            "<a href='blacklist.php?action=add'>&gt; Add an Enemy</a><br />
These are the people on your black list. ";
    $others_blacklist = BlacklistRecord::filter_by_added($user);
    print 
            count($others_blacklist)
                    . " people have added you to their list.<br />Most hated: [";
    $r = 0;
    foreach (BlacklistRecord::filter_most_hated() as $mh_user)
    {
        $r++;
        if ($r > 1)
        {
            print " | ";
        }
        print 
                "<a href='viewuser.php?u={$mh_user->userid}'>{$mh_user->username}</a>";
    }
    print 
            "]
<table width=90%><tr style='background:gray'> <th>ID</th> <th>Name</th> <th>Mail</th> <th>Attack</th> <th>Remove</th> <th>Comment</th> <th>Change Comment</th> <th>Online?</th></tr>";
    $blacklist = BlacklistRecord::filter_by_adder($user->userid);
    
    foreach ($blacklist as $bl_record)
    {
        $added_user = $bl_record->added_user;
        if ($added_user->last_time_online >= time() - 15 * 60)
        {
            $on = "<font color=green><b>Online</b></font>";
        }
        else
        {
            $on = "<font color=red><b>Offline</b></font>";
        }
        $d = "";
        $formatted_comment = $bl_record->comment;
        $formatted_username = $added_user->username;
        if ($added_user->donatordays)
        {
            $formatted_username = "<font color=red>{$added_user->username}</font>";
            $d =
                    "<img src='donator.gif' alt='Donator: {$added_user->donatordays} Days Left' title='Donator: {$added_user->donatordays} Days Left' />";
        }
        if (!$bl_record->comment)
        {
            $formatted_comment = "N/A";
        }
        print 
                "<tr> <td>{$added_user->userid}</td> <td><a href='viewuser.php?u={$added_user->userid}'>{$formatted_username}</a> $d</td> <td><a href='mailbox.php?action=compose&ID={$added_user->userid}'>Mail</a></td> <td><a href='attack.php?ID={$added_user->userid}'>Attack</a></td> <td><a href='blacklist.php?action=remove&f={$bl_record->id}'>Remove</a></td> <td>{$formatted_comment}</td> <td><a href='blacklist.php?action=ccomment&f={$bl_record->id}'>Change</a></td> <td>$on</td></tr>";
    }
}

function add_enemy()
{
    global $user, $c;
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
        $enemy_id = $_POST['ID'];
        $enemy = User::get($enemy_id);
        if (BlacklistRecord::is_user_in_blacklist($user, $enemy))
        {
            print "You cannot add the same person twice.";
        }
        else if ($user->userid == $enemy_id)
        {
            print 
                    "You cannot be so lonely that you have to try and add yourself.";
        }
        else if (!User::exists($enemy_id))
        {
            print "Oh no, you're trying to add a ghost.";
        }
        else
        {
            $comment = $_POST['comment'];
            BlacklistRecord::add($user, $enemy, $comment);
            print 
                    "{$enemy->username} was added to your black list.<br />
<a href='blacklist.php'>&gt; Back</a>";
        }
    }
    else
    {
        $_GET['ID'] =
                (isset($_GET['ID']) && is_numeric($_GET['ID']))
                        ? abs(intval($_GET['ID'])) : '';
        print 
                "Adding an enemy!<form action='blacklist.php?action=add' method='post'>
Enemy's ID: <input type='text' name='ID' value='{$_GET['ID']}' /><br />
Comment (optional): <br />
<textarea name='comment' rows='7' cols='40'></textarea><br />
<input type='submit' value='Add Enemy' /></form>";
    }

}

function remove_enemy()
{
    global $user;
    BlacklistRecord::remove($_GET['f'], $user->userid);
    print 
            "Black list entry removed!<br />
<a href='blacklist.php'>&gt; Back</a>";
}

function change_comment()
{
    global $user, $c;
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
        BlacklistRecord::edit_comment($_POST['f'], $user->userid, $_POST['comment']);
        print 
                "Comment for enemy changed!<br />
<a href='blacklist.php'>&gt; Back</a>";
    }
    else
    {
        $_GET['f'] = abs((int) $_GET['f']);
        
        if (BlacklistRecord::check_adder($_GET['f'], $user->userid))
        {
            $bl_record = BlacklistRecord::get($_GET['f']);
            $comment = str_replace('<br />', "\n", $bl_record->comment);
            print 
                    "Changing a comment.<form action='blacklist.php?action=ccomment' method='post'>
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

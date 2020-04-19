<?php
/*
MCCodes FREE
staffnotes.php Rev 1.1.0
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
if ($user->user_level == 2 || $user->user_level == 3 || $user->user_level == 5)
{
    $q = mysqli_query(
        $c,
        "SELECT staffnotes FROM users WHERE userid={$_POST['ID']}"
    );
    $old = mysqli_real_escape_string($c, mysqli_data_seek($q, 0));
    $new = mysqli_real_escape_string($c, stripslashes($_POST['staffnotes']));
    mysqli_query(
        $c,
        "UPDATE users SET staffnotes='{$new}' WHERE userid='{$_POST['ID']}'"
    );
    mysqli_query(
        $c,
        "INSERT INTO staffnotelogs VALUES(NULL, $userid, {$_POST['ID']}, "
            . time() . ", '$old', '{$new}')"
    );
    print 
            "User notes updated!<br />
<a href='viewuser.php?u={$_POST['ID']}'>&gt; Back To Profile</a>";
}
else
{
    print "You violent scum.";
}
$h->endpage();

<?php
/*
MCCodes FREE
willpdone.php Rev 1.1.0
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
if ($_GET['action'] == "cancel")
{
    print "You have cancelled your donation. Please donate later...";
}
else if ($_GET['action'] == "done")
{
    if (!$_GET['tx'])
    {
        die("Get a life.");
    }
    $quantity = mysqli_real_escape_string(
        $c,
        stripslashes($_GET['quantity'])
    );
    mysqli_query(
        $c,
        "INSERT INTO willplogs VALUES(NULL,$userid," . time()
            . ",'{$quantity}');"
    );
    if ($_GET['quantity'] == 'one')
    {
        $q = 1;
    }
    else if ($_GET['quantity'] == 'five')
    {
        $q = 5;
    }
    else
    {
        echo 'Stop cheating!';
        $h->endpage();
        exit;
    }
    mysqli_query($c, "INSERT INTO inventory VALUES(NULL,34,$userid,$q)");
    print 
            "Your will potions have been credited, if you are cheating, we will jail you.";
}
$h->endpage();

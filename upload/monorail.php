<?php
/*
MCCodes FREE
monorail.php Rev 1.1.0
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
$_GET['to'] = abs((int) $_GET['to']);
if (!$_GET['to'])
{
    print
            "Welcome to the Monorail Station. It costs \$1000 for a ticket.<br />
Where would you like to travel today?<br />";
    $q = mysqli_query(
        $c,
        "SELECT * FROM cities WHERE cityid != {$user->location} AND cityminlevel <= {$user->level}"
    );
    print
            "<table width=75%><tr style='background:gray'><th>Name</th><th>Description</th><th>Min Level</th><th>&nbsp;</th></tr>";
    while ($r = mysqli_fetch_array($q))
    {
        print
                "<tr><td>{$r['cityname']}</td><td>{$r['citydesc']}</td><td>{$r['cityminlevel']}</td><td><a href='monorail.php?to={$r['cityid']}'>Go</a></td></tr>";
    }
    print "</table>";
}
else
{
    if ($user->money < 1000)
    {
        print "You don't have enough money.";
    }
    else if (((int) $_GET['to']) != $_GET['to'])
    {
        print "Invalid city ID";
    }
    else
    {
        $q = mysqli_query(
            $c,
            "SELECT * FROM cities WHERE cityid = {$_GET['to']} AND cityminlevel <= {$user->level}"
        );
        if (!mysqli_num_rows($q))
        {
            print
                    "Error, this city either does not exist or you cannot go there.";
        }
        else
        {
            mysqli_query(
                $c,
                "UPDATE users SET money=money-1000,location={$_GET['to']} WHERE userid=$userid"
            );
            $r = mysqli_fetch_array($q);
            print
                    "Congratulations, you paid \$1000 and travelled to {$r['cityname']} on the monorail!";
        }
    }
}
$h->endpage();

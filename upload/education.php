<?php
/*
MCCodes FREE
education.php Rev 1.1.0
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
print "<h3>Schooling</h3>";
if ($user->course > 0)
{
    $cd = mysqli_query($c, "SELECT * FROM courses WHERE crID={$user->course}");
    $coud = mysqli_fetch_array($cd);
    print
            "You are currently doing the {$coud['crNAME']}, you have {$user->cdays} days remaining.";
}
else
{
    if ($_GET['cstart'])
    {
        $_GET['cstart'] = abs((int) $_GET['cstart']);
        //Verify.
        $cd = mysqli_query(
            $c,
            "SELECT * FROM courses WHERE crID={$_GET['cstart']}"
        );
        if (mysqli_num_rows($cd) == 0)
        {
            print "You are trying to start a non-existant course!";
        }
        else
        {
            $coud = mysqli_fetch_array($cd);
            $cdo = mysqli_query(
                $c,
                "SELECT * FROM coursesdone WHERE userid=$userid AND courseid={$_GET['cstart']}"
            );
            if ($user->money < $coud['crCOST'])
            {
                print "You don't have enough money to start this course.";
                $h->endpage();
                exit;
            }
            if (mysqli_num_rows($cdo) > 0)
            {
                print "You have already done this course.";
                $h->endpage();
                exit;
            }
            mysqli_query(
                $c,
                "UPDATE users SET course={$_GET['cstart']},cdays={$coud['crDAYS']},money=money-{$coud['crCOST']} WHERE userid=$userid"
            );
            print
                    "You have started the {$coud['crNAME']}, it will take {$coud['crDAYS']} days to complete.";
        }
    }
    else
    {
        //list courses
        print "Here is a list of available courses.";
        $q = mysqli_query($c, "SELECT * FROM courses");
        print
                "<br /><table width=75%><tr style='background:gray;'><th>Course</th><th>Description</th><th>Cost</th><th>Take</th></tr>";
        while ($r = mysqli_fetch_array($q))
        {
            $cdo = mysqli_query(
                $c,
                "SELECT * FROM coursesdone WHERE userid=$userid AND courseid={$r['crID']}"
            );
            if (mysqli_num_rows($cdo))
            {
                $do = "<i>Done</i>";
            }
            else
            {
                $do = "<a href='education.php?cstart={$r['crID']}'>Take</a>";
            }
            print
                    "<tr><td>{$r['crNAME']}</td><td>{$r['crDESC']}</td><td>\${$r['crCOST']}</td><td>$do</td></tr>";
        }
        print "</table>";
    }
}
$h->endpage();

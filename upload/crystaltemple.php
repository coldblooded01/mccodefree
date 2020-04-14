<?php
/*
MCCodes FREE
crystaltemple.php Rev 1.1.0
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
if (!$_GET['spend'])
{
    print
            "Welcome to the crystal temple!<br />
You have <b>{$user->crystals}</b> crystals.<br />
What would you like to spend your crystals on?<br />
<br />
<a href='crystaltemple.php?spend=refill'>Energy Refill - 12 Crystals</a><br />
<a href='crystaltemple.php?spend=IQ'>IQ - 5 IQ per crystal</a><br />
<a href='crystaltemple.php?spend=money'>Money - \$200 per crystal</a><br />";
}
else
{
    if ($_GET['spend'] == 'refill')
    {
        if ($user->crystals < 12)
        {
            print "You don't have enough crystals!";
        }
        else if ($user->energy == $user->max_energy)
        {
            print "You already have full energy.";
        }
        else
        {
            mysqli_query(
                $c,
                "UPDATE users SET energy=maxenergy,crystals=crystals-12 WHERE userid=$userid"
            );
            print "You have paid 12 crystals to refill your energy bar.";
        }
    }
    else if ($_GET['spend'] == 'IQ')
    {
        print
                "Type in the amount of crystals you want to swap for IQ.<br />
You have <b>{$user->crystals}</b> crystals.<br />
One crystal = 5 IQ.<form action='crystaltemple.php?spend=IQ2' method='post'><input type='text' name='crystals' /><br /><input type='submit' value='Swap' /></form>";
    }
    else if ($_GET['spend'] == 'IQ2')
    {
        $_POST['crystals'] = (int) $_POST['crystals'];
        if ($_POST['crystals'] <= 0 || $_POST['crystals'] > $user->crystals)
        {
            print
                    "Error, you either do not have enough crystals or did not fill out the form.<br />
<a href='crystaltemple.php?spend=IQ'>Back</a>";
        }
        else
        {
            $iqgain = $_POST['crystals'] * 5;
            mysqli_query(
                $c,
                "UPDATE users SET crystals=crystals-{$_POST['crystals']} WHERE userid=$userid"
            );
            mysqli_query(
                $c,
                "UPDATE userstats SET IQ=IQ+$iqgain WHERE userid=$userid"
            );
            print "You traded {$_POST['crystals']} crystals for $iqgain IQ.";
        }
    }
    else if ($_GET['spend'] == 'money')
    {
        print
                "Type in the amount of crystals you want to swap for \$\$\$.<br />
You have <b>{$user->crystals}</b> crystals.<br />
One crystal = \$200.<form action='crystaltemple.php?spend=money2' method='post'><input type='text' name='crystals' /><br /><input type='submit' value='Swap' /></form>";
    }
    else if ($_GET['spend'] == 'money2')
    {
        $_POST['crystals'] = (int) $_POST['crystals'];
        if ($_POST['crystals'] <= 0 || $_POST['crystals'] > $user->crystals)
        {
            print
                    "Error, you either do not have enough crystals or did not fill out the form.<br />
<a href='crystaltemple.php?spend=money'>Back</a>";
        }
        else
        {
            $iqgain = $_POST['crystals'] * 200;
            mysqli_query(
                $c,
                "UPDATE users SET crystals=crystals-{$_POST['crystals']},money=money+$iqgain WHERE userid=$userid"
            );
            print "You traded {$_POST['crystals']} crystals for \$$iqgain.";
        }
    }
}

$h->endpage();

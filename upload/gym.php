<?php
/*
MCCodes FREE
gym.php Rev 1.1.0
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
$out = "";
$_GET['times'] = abs((int) $_GET['times']);
if (isset($_GET['train']))
{
    if ($_GET['train'] != "strength" && $_GET['train'] != "agility"
            && $_GET['train'] != "guard" && $_GET['train'] != "labour")
    {
        $h->userdata($user);
        $h->menuarea();
        die("Abusers aren't allowed.");
    }
    $tgain = 0;
    for ($i = 1; $i <= $_GET['times'] && $user->energy > 0; $i++)
    {
        if ($user->energy > 0)
        {
            $gain =
                    rand(1, 3) / rand(800, 1000) * rand(800, 1000)
                            * (($user->will + 20) / 150);
            $tgain += $gain;
            if ($_GET['train'] == "IQ")
            {
                $gain /= 100;
            }
            $user->aliases[$_GET['train']] += $gain;
            $egain = $gain / 10;
            $ts = $user->aliases[$_GET['train']];
            $st = $_GET['train'];

            mysqli_query(
                $c,
                "UPDATE userstats SET $st=$st+" . $gain
                    . " WHERE userid=$userid"
                ) or die(
                    "UPDATE userstats SET $st=$st+$gain,energy=energy-1,exp=exp+$egain WHERE userid=$userid<br />"
                        . mysqli_error($c)
                );
            $wu = (int) (rand(1, 3));
            if ($user->will >= $wu) {
                $user->will -= $wu;
                mysqli_query(
                    $c,
                    "UPDATE users SET energy=energy-1,exp=exp+$egain,will=will-$wu WHERE userid=$userid"
                );
            } else {
                $user->will = 0;
                mysqli_query(
                    $c,
                    "UPDATE users SET energy=energy-1,exp=exp+$egain,will=0 WHERE userid=$userid"
                );
            }
            $user->energy -= 1;
            $user->exp += $egain;

        } else {
            $out = "You do not have enough energy to train.";
        }
    }
    $stat = $user->aliases[$st];
    $i--;
    $out =
            "You begin training your $st.<br />
You have gained $tgain $st by training it $i times.<br />
You now have $stat $st and {$user->energy} energy left.<br /><br />";

}
else
{
    $out = "<h3>Gym: Main Lobby<h3>";
}
$h->userdata($user);
$h->menuarea();
print $out;
print
        "Enter the amount of times you wish to train and choose the stat to train.<br />
You can train up to {$user->energy} times.<br /><form action='gym.php' method='get'>
<input type='text' name='times' value='1' /><select type='dropdown' name='train'>
<option value='strength'>Strength</option>
<option value='agility'>Agility</option>
<option value='labour'>Labour</option>
<option value='guard'>Guard</option></select><br />
<input type='submit' value='Train!' /></form>";

$h->endpage();

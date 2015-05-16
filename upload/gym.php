<?php
/*
MCCodes FREE
Copyright (C) 2005-2012 Dabomstew
Changes made by John West
updated all the mysql to mysqli. 

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
require "includes/global_func.php";
if ($_SESSION['loggedin'] == 0)
{
    header("Location: login.php");
    exit;
}
$userid = $_SESSION['userid'];
require "header.php";
$h = new headers;
$h->startheaders();
include "includes/mysql.php";
global $c;
$is =
        mysqli_query($c,
                "SELECT u.*,us.* FROM users u LEFT JOIN userstats us ON u.userid=us.userid WHERE u.userid=$userid") or die(mysqli_error($c));
$ir = mysqli_fetch_array($is);
check_level();
$fm = money_formatter($ir['money']);
$cm = money_formatter($ir['crystals'], '');
$lv = date('F j, Y, g:i a', $ir['laston']);
$out = "";
$_GET['times'] = abs((int) $_GET['times']);
if (isset($_GET['train']))
{
    if ($_GET['train'] != "strength" && $_GET['train'] != "agility"
            && $_GET['train'] != "guard" && $_GET['train'] != "labour")
    {
        $h->userdata($ir, $lv, $fm, $cm);
        $h->menuarea();
        die("Abusers aren't allowed.");
    }
    $tgain = 0;
    for ($i = 1; $i <= $_GET['times'] && $ir['energy'] > 0; $i++)
    {
        if ($ir['energy'] > 0)
        {
            $gain =
                    rand(1, 3) / rand(800, 1000) * rand(800, 1000)
                            * (($ir['will'] + 20) / 150);
            $tgain +=(int) $gain;
            if ($_GET['train'] == "IQ")
            {
                $gain /= 100;
            }
            $ir[$_GET['train']] += $gain;
            $egain = $gain / 10;
            $ts = $ir[$_GET['train']];
            $st = $_GET['train'];

            mysqli_query($c,
                    "UPDATE userstats SET $st=$st+" . $gain
                            . " WHERE userid=$userid")
                    or die(
                            "UPDATE userstats SET $st=$st+$gain,energy=energy-1,exp=exp+$egain WHERE userid=$userid<br />"
                                    . ((is_object($c)) ? mysqli_error($c) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
            $wu = (int) (rand(1, 3));
            if ($ir['will'] >= $wu)
            {
                $ir['will'] -= $wu;
                mysqli_query($c,
                        "UPDATE users SET energy=energy-1,exp=exp+$egain,will=will-$wu WHERE userid=$userid");
            }
            else
            {
                $ir['will'] = 0;
                mysqli_query($c,
                        "UPDATE users SET energy=energy-1,exp=exp+$egain,will=0 WHERE userid=$userid");
            }
            $ir['energy'] -= 1;
            $ir['exp'] += $egain;

        }
        else
        {
            $out = "You do not have enough energy to train.";
        }
    }
    $stat = (int)$ir[$st];
    $i--;
    $out =
            "You begin training your $st.<br />
You have gained $tgain $st by training it $i times.<br />
You now have $stat $st and {$ir['energy']} energy left.<br /><br />";

}
else
{
    $out = "<h3>Gym: Main Lobby<h3>";
}
$h->userdata($ir, $lv, $fm, $cm);
$h->menuarea();
print $out;
print
        "Enter the amount of times you wish to train and choose the stat to train.<br />
You can train up to {$ir['energy']} times.<br /><form action='gym.php' method='get'>
<input type='text' name='times' value='1' /><select type='dropdown' name='train'>
<option value='strength'>Strength</option>
<option value='agility'>Agility</option>
<option value='labour'>Labour</option>
<option value='guard'>Guard</option></select><br />
<input type='submit' value='Train!' /></form>";

$h->endpage();

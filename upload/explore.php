<?php
/*
MCCodes FREE
explore.php Rev 1.1.0
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

require_once(dirname(__FILE__) . "/models/setting.php");
$GAME_NAME = Setting::get('GAME_NAME')->value;

check_level();
$h->userdata($user);
$h->menuarea();
$tresder = (int) rand(100, 999);
print
        "<b>You begin exploring the area you're in, you see a bit that interests you.</b><br />
<table width=75%><tr height=100><td valign=top>
<u>Market Place</u><br />
<a href='shops.php'>Shops</a><br />
<a href='itemmarket.php'>Item Market</a><br />
<a href='cmarket.php'>Crystal Market</a></td>
<td valign=top>
<u>Serious Money Makers</u><br />
<a href='monorail.php'>Travel Agency</a><br />
<a href='estate.php'>Estate Agent</a><br />
<a href='bank.php'>City Bank</a></td>
<td valign=top>";
if ($user->location == 5)
{
    print
            "<u>Cyber State</u><br />
<a href='cyberbank.php'>Cyber Bank</a><br />";
}
print
        "</td><td valign=top>
<u>Dark Side</u><br />
<a href='fedjail.php'>Federal Jail</a><br />
<a href='slotsmachine.php?tresde=$tresder'>Slots Machine</a><br />
<a href='roulette.php?tresde=$tresder'>Roulette</a></td></tr><tr height=100>
<td valign=top>";
if ($user->location == 5)
{
    print
            "<u>Cyber Casino</u><br />
<a href='slotsmachine3.php'>Super Slots</a><br />";
}
print
        "</td><td valign=top>
<u>Statistics Dept</u><br />
<a href='userlist.php'>User List</a><br />
<a href='stafflist.php'>{$GAME_NAME} Staff</a><br />
<a href='halloffame.php'>Hall of Fame</a><br />
<a href='stats.php'>Game Stats</a><br />
<a href='usersonline.php'>Users Online</a></td><td valign=top>&nbsp;</td><td valign=top>
<u>Mysterious</u><br />
<a href='crystaltemple.php'>Crystal Temple</a><br />";
if ($user->location == 4)
{
    print "<a href='battletent.php'>Battle Tent</a><br />";
}
$game_url = determine_game_urlbase();
print
        "</td></tr></table><br /><br />This is your referal link: http://{$game_url}/register.php?REF=$userid <br />
Every signup from this link earns you two valuable crystals!";
$h->endpage();

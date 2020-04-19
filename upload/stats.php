<?php
/*
MCCodes FREE
stats.php Rev 1.1.0
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
// Basic Stats (all users)
$q = mysqli_query(
    $c,
    "SELECT COUNT(`userid`) AS `c_users`,
            SUM(`money`) AS `s_money`,
            SUM(`crystals`) AS `s_crystals`
        FROM `users`"
);
$mem_info = mysqli_fetch_assoc($q);
$membs = $mem_info['c_users'];
$total = $mem_info['s_money'];
$avg = (int) ($total / ($membs > 1 ? $membs : 1));
$totalc = $mem_info['s_crystals'];
$avgc = (int) ($totalc / ($membs > 1 ? $membs : 1));
mysqli_free_result($q);
$q = mysqli_query(
    $c,
    "SELECT COUNT(`userid`) AS `c_users`,
            SUM(`bankmoney`) AS `s_bank`
        FROM `users`
        WHERE `bankmoney` > -1"
);
$bank_info = mysqli_fetch_assoc($q);
$banks = $bank_info['c_users'];
$totalb = $bank_info['s_bank'];
$avgb = (int) ($totalb / ($banks > 0 ? $banks : 1));
mysqli_free_result($q);
$q = mysqli_query(
    $c,
    "SELECT COUNT(`userid`)
        FROM `users`
        WHERE `gender` = 'Male'"
);
$male = mysqli_data_seek($q, 0);
mysqli_free_result($q);
$q = mysqli_query(
    $c,
    "SELECT COUNT(`userid`)
        FROM `users`
        WHERE `gender` = 'Female'"
);
$fem = mysqli_data_seek($q, 0);
mysqli_free_result($q);

$q = mysqli_query($c, "SELECT SUM(`inv_qty`) FROM `inventory`");
$totali =(int) mysqli_data_seek($q, 0);
mysqli_free_result($q);
$q = mysqli_query($c, "SELECT COUNT(`mail_id`) FROM `mail`");
$mail = mysqli_data_seek($q, 0);
mysqli_free_result($q);
$events = Event::count_total_events();
echo "<h3>Country Statistics</h3>
You step into the Statistics Department and login to the service. You see some stats that interest you.<br />
<table width='75%' cellspacing='1' class='table'>
	<tr>
		<th>Users</th>
		<th>Money and Crystals</th>
	</tr>
	<tr>
		<td>
			There are currently $membs {$set['game_name']} players,
                $male males and $fem females.
        </td>
        <td>
			Amount of cash in circulation: " . money_formatter($total)
        . ". <br />
			The average player has: " . money_formatter($avg)
        . ". <br />
			Amount of cash in banks: " . money_formatter($totalb)
        . ". <br />
			Amount of players with bank accounts: $banks<br />
			The average player has in their bank accnt: "
        . money_formatter($avgb)
        . ". <br />
			Amount of crystals in circulation: "
        . money_formatter($totalc, "")
        . ". <br />
			The average player has: " . money_formatter($avgc, "")
        . " crystals.
        </td>
    </tr>
	<tr>
		<th>Mails/Events</th>
		<th>Items</th>
	</tr>
	<tr>
		<td>
			" . money_formatter($mail, "") . " mails and "
        . money_formatter($events, "")
        . " events have been sent.
        </td>
        <td>
			There are currently " . money_formatter($totali, "")
        . " items in circulation.
        </td>
    </tr>
 </table>";
$h->endpage();

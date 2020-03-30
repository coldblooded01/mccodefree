<?php
/*
MCCodes FREE
stafflist.php Rev 1.1.0
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
require "header.php";
$h = new headers;
$h->startheaders();
include "mysql.php";
global $c;
$is = mysqli_query(
	$c,
    "SELECT u.*,us.* FROM users u LEFT JOIN userstats us ON u.userid=us.userid WHERE u.userid=$userid"
) or die(mysqli_error($c));
$ir = mysqli_fetch_array($is);

check_level();
$fm = money_formatter($ir['money']);
$cm = money_formatter($ir['crystals'], '');
$lv = date('F j, Y, g:i a', $ir['laston']);
$h->userdata($ir, $lv, $fm, $cm);
$h->menuarea();
$staff = array();
$q = mysqli_query(
    $c,
    "SELECT `userid`, `laston`, `username`, `level`, `money`, `user_level`
        FROM `users`
        WHERE `user_level` IN(2, 3, 4, 5)
        ORDER BY `userid` ASC"
);
while ($r = mysqli_fetch_assoc($q))
{
    $staff[$r['userid']] = $r;
}
mysqli_free_result($q);
echo '
<b>Admins</b>
<br />
<table width="75%" cellspacing="1" cellpadding="1" border="1">
		<tr style="background: gray;">
			<th>User</th>
			<th>Level</th>
			<th>Money</th>
			<th>Last Seen</th>
			<th>Status</th>
		</tr>
   ';

foreach ($staff as $r)
{
    if ($r['user_level'] == 2)
    {
        $on =
                ($r['laston'] >= ($_SERVER['REQUEST_TIME'] - 900))
                        ? '<span style="color: green;">Online</span>'
                        : '<span style="color: green;">Offline</span>';
        echo '
		<tr>
			<td><a href="viewuser.php?u=' . $r['userid'] . '">'
                . $r['username'] . '</a> [' . $r['userid'] . ']</td>
			<td>' . $r['level'] . '</td>
			<td>' . money_formatter($r['money'], '$') . '</td>
			<td>' . date("F j, Y, g:i:s a", $r['laston']) . '</td>
			<td>' . $on . '</td>
		</tr>
   		';
    }
}
echo '</table>

<b>Secretaries</b>
<br />
<table width="75%" cellspacing="1" cellpadding="1" border="1">
		<tr style="background: gray;">
			<th>User</th>
			<th>Level</th>
			<th>Money</th>
			<th>Last Seen</th>
			<th>Status</th>
		</tr>
   ';
foreach ($staff as $r)
{
    if ($r['user_level'] == 3)
    {
        $on =
                ($r['laston'] >= ($_SERVER['REQUEST_TIME'] - 900))
                        ? '<span style="color: green;">Online</span>'
                        : '<span style="color: green;">Offline</span>';
        echo '
		<tr>
			<td><a href="viewuser.php?u=' . $r['userid'] . '">'
                . $r['username'] . '</a> [' . $r['userid'] . ']</td>
			<td>' . $r['level'] . '</td>
			<td>' . money_formatter($r['money'], '$') . '</td>
			<td>' . date("F j, Y, g:i:s a", $r['laston']) . '</td>
			<td>' . $on . '</td>
		</tr>
   		';
    }
}
echo '</table>

<b>Assistants</b>
<br />
<table width="75%" cellspacing="1" cellpadding="1" border="1">
		<tr style="background: gray;">
			<th>User</th>
			<th>Level</th>
			<th>Money</th>
			<th>Last Seen</th>
			<th>Status</th>
		</tr>
   ';
foreach ($staff as $r)
{
    if ($r['user_level'] == 5)
    {
        $on =
                ($r['laston'] >= ($_SERVER['REQUEST_TIME'] - 900))
                        ? '<span style="color: green;">Online</span>'
                        : '<span style="color: green;">Offline</span>';
        echo '
		<tr>
			<td><a href="viewuser.php?u=' . $r['userid'] . '">'
                . $r['username'] . '</a> [' . $r['userid'] . ']</td>
			<td>' . $r['level'] . '</td>
			<td>' . money_formatter($r['money'], '$') . '</td>
			<td>' . date("F j, Y, g:i:s a", $r['laston']) . '</td>
			<td>' . $on . '</td>
		</tr>
   		';
    }
}
echo '</table>

<b>IRC Ops</b>
<br />
<table width="75%" cellspacing="1" cellpadding="1" border="1">
		<tr style="background: gray;">
			<th>User</th>
			<th>Level</th>
			<th>Money</th>
			<th>Last Seen</th>
			<th>Status</th>
		</tr>
   ';
foreach ($staff as $r)
{
    if ($r['user_level'] == 4)
    {
        $on =
                ($r['laston'] >= ($_SERVER['REQUEST_TIME'] - 900))
                        ? '<span style="color: green;">Online</span>'
                        : '<span style="color: green;">Offline</span>';
        echo '
		<tr>
			<td><a href="viewuser.php?u=' . $r['userid'] . '">'
                . $r['username'] . '</a> [' . $r['userid'] . ']</td>
			<td>' . $r['level'] . '</td>
			<td>' . money_formatter($r['money'], '$') . '</td>
			<td>' . date("F j, Y, g:i:s a", $r['laston']) . '</td>
			<td>' . $on . '</td>
		</tr>
   		';
    }
}
echo '</table>';
$h->endpage();

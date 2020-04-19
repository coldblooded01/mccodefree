<?php
/*
MCCodes FREE
index.php Rev 1.1.0
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
$fm = money_formatter($user->money);
$cm = money_formatter($user->crystals, '');
$h->userdata($user);
$h->menuarea();

print "<h3>General Info:</h2>";
$exp = (int) ($user->exp / $user->exp_needed * 100);
print
        "<table><tr><td><b>Name:</b> {$user->username}</td><td><b>Crystals:</b> {$cm}</td></tr><tr>
<td><b>Level:</b> {$user->level}</td>
<td><b>Exp:</b> {$exp}%</td></tr><tr>
<td><b>Money:</b> $fm</td>
<td><b>HP:</b> {$user->hp}/{$user->max_hp}</td></tr>
<tr><td><b>Property:</b> {$user->get_house()['hNAME']}</td></tr></table>";
print "<hr><h3>Stats Info:</h3>";
$ts = $user->user_stats->strength + $user->user_stats->agility
        + $user->user_stats->guard + $user->user_stats->labour
        + $user->user_stats->iq;
$formatted_stats = [];
$formatted_stats['strank'] = get_rank($user->user_stats->strength, 'strength');
$formatted_stats['agirank'] = get_rank($user->user_stats->agility, 'agility');
$formatted_stats['guarank'] = get_rank($user->user_stats->guard, 'guard');
$formatted_stats['labrank'] = get_rank($user->user_stats->labour, 'labour');
$formatted_stats['IQrank'] = get_rank($user->user_stats->iq, 'IQ');
$tsrank = get_rank($ts, 'strength+agility+guard+labour+IQ');
$formatted_stats['strength'] = number_format($user->user_stats->strength);
$formatted_stats['agility'] = number_format($user->user_stats->agility);
$formatted_stats['guard'] = number_format($user->user_stats->guard);
$formatted_stats['labour'] = number_format($user->user_stats->labour);
$formatted_stats['IQ'] = number_format($user->user_stats->iq);
$ts = number_format($ts);

print
        "<table><tr><td><b>Strength:</b> {$formatted_stats['strength']} [Ranked: {$formatted_stats['strank']}]</td><td><b>Agility:</b> {$formatted_stats['agility']} [Ranked: {$formatted_stats['agirank']}]</td></tr>
<tr><td><b>Guard:</b> {$formatted_stats['guard']} [Ranked: {$formatted_stats['guarank']}]</td><td><b>Labour:</b> {$formatted_stats['labour']} [Ranked: {$formatted_stats['labrank']}]</td></tr>
<tr><td><b>IQ: </b> {$formatted_stats['IQ']} [Ranked: {$formatted_stats['IQrank']}]</td><td><b>Total stats:</b> {$ts} [Ranked: $tsrank]</td></tr></table>";
$h->endpage();

<?php
/*
MCCodes FREE
voting.php Rev 1.1.0
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
require "mysql.php";
require "global_func.php";
require_once(dirname(__FILE__) . "/models/setting.php");
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

global $c;

check_level();
$h->userdata($user);
$h->menuarea();
print
        "<h3>Voting</h3>
Here you may vote for {$GAME_NAME} at various RPG toplists and be rewarded.<br />
<a href='http://apexwebgaming.com/in/498'>Vote at APEX (no reward)</a><br />
<a href='votetwg.php'>Vote at TWG (20% energy restore)</a><br />
<a href='votetrpg.php'>Vote at TOPRPG (\$300)</a>";

$h->endpage();

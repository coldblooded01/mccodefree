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
                "SELECT u.*,us.* FROM users u LEFT JOIN userstats us ON u.userid=us.userid WHERE u.userid=$userid") or die(((is_object($c)) ? mysqli_error($c) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
$ir = mysqli_fetch_array($is);
check_level();
$fm = money_formatter($ir['money']);
$cm = money_formatter($ir['crystals'], '');
$lv = date('F j, Y, g:i a', $ir['laston']);
$h->userdata($ir, $lv, $fm, $cm);
$h->menuarea();
print
        <<<EOF
<h1>test game Rules and Regulations</h1>
<ol>
<li>Players are only allowed to have one account, owning two or more accounts will result in all accounts being jailed,
if you are on the same IP as another player, mail staff and let them know.</li>
<li>You are responsible for whatever happens on your account, don't give out your password to anyone.</li>
<li>Children play this game, so keep it PG-13. Mild swearing will be permitted, but F-bombing, sexual vulgarities
or excessive swearing will result in some time in Fed until you clean up your act.</li>

<li>Profile images with nudity, profanity, or otherwise offensive images will be removed, and may result in jail time.</li>
<li>We understand that you play other games, but do not advertise them here. You get 1 warning, afterwards its Fed time.</li>
<li>Do not spam the staff's mailbox, if you have a problem, message one of us once. They will deal with your problem in a timely
manner, but do not mail them repeatedly, or mail multiple staff members.</li>
<li>Do not harrass other players, use common sense on this one, if you don't know when your crossing the line from fantasy into
harrassment, assume that you are harrassing the other player. This will not be tolerated and will result in a stiff punishment.</li>
<li>Scamming will not be tolerated in any manner. Any attempt to scam anyone will result in being jailed for a long long time.</li>
<li>If a member of staff is bothering you for any unfair or just plain, weird reason, mail admin [1]</li>
<li>Common sense rules are not posted here, if you can't determine the difference between what is ok, and what is not, you should
consider not interacting with other people until you do understand.</li>
<li>These rules are subject to change without notice, check them from time to time, as ignorance will not be accepted as an excuse.</li>
</ol>
EOF;
$h->endpage();

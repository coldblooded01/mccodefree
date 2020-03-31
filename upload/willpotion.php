<?php
/*
MCCodes FREE
willpotion.php Rev 1.1.0
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
require_once "models/setting.php";
$PAYPAL = Setting::get('PAYPAL')->value;
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
$game_url = determine_game_urlbase();
print
        <<<EOF
<h3>Will Potions</h3>

Buy will potions today! They restore 100% will.<br />
<b>Buy One:</b> (\$1)<br />
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="business" value="{$PAYPAL}">
<input type="hidden" name="item_name" value="Will Potion for ($userid) (1)">
<input type="hidden" name="amount" value="1.00">
<input type="hidden" name="no_shipping" value="1">
<input type="hidden" name="return" value="http://{$game_url}/willpdone.php?action=done&quantity=one">
<input type="hidden" name="cancel_return" value="http://{$game_url}/willpdone.php?action=cancel">
<input type="hidden" name="cn" value="Your Player ID">
<input type="hidden" name="currency_code" value="USD">
<input type="hidden" name="tax" value="0">
<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but21.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
</form>
<b>Buy Five:</b> (\$4.50)<br />
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="business" value="{$PAYPAL}">
<input type="hidden" name="item_name" value="Will Potion for ($userid) (5)">
<input type="hidden" name="amount" value="4.50">
<input type="hidden" name="no_shipping" value="1">
<input type="hidden" name="return" value="http://{$game_url}/willpdone.php?action=done&quantity=five">
<input type="hidden" name="cancel_return" value="http://{$game_url}/willpdone.php?action=cancel">
<input type="hidden" name="cn" value="Your Player ID">
<input type="hidden" name="currency_code" value="USD">
<input type="hidden" name="tax" value="0">
<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but21.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
</form>
EOF;
$h->endpage();

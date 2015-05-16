<?php
/*
MCCodes FREE
crons/cron_fivemins.php Rev 1.1.0
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

require_once(dirname(__FILE__) . "/../includes/mysql.php");
require_once(dirname(__FILE__) . "/../includes/global_func.php");
$cron_code = '023c36285d4f518c15b1f23aab76d50a';
if ($argc == 2)
{
    if ($argv[1] != $cron_code)
    {
        exit;
    }
}
else if (!isset($_GET['code']) || $_GET['code'] !== $cron_code)
{
    exit;
}
// update for all users
$allusers_query =
        "UPDATE `users`
        SET `brave` = LEAST(`brave` + ((`maxbrave` / 10) + 0.5), `maxbrave`),
        `hp` = LEAST(`hp` + (`maxhp` / 3), `maxhp`),
        `will` = LEAST(`will` + 10, `maxwill`)";
mysqli_query($c,$allusers_query);
//enerwill update
$en_nd_query =
        "UPDATE `users`
        SET `energy` = LEAST(`energy` + (`maxenergy` / 12.5), `maxenergy`)
        WHERE `donatordays` = 0";
$en_don_query =
        "UPDATE `users`
        SET `energy` = LEAST(`energy` + (`maxenergy` / 6), `maxenergy`)
        WHERE `donatordays` > 0";
mysqli_query($c,$en_nd_query, $c);
mysqli_query($c,$en_don_query, $c);

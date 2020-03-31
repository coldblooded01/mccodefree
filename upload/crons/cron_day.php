<?php
/*
MCCodes FREE
crons/cron_day.php Rev 1.1.0
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

require_once(dirname(__FILE__) . "/../mysql.php");
require_once(dirname(__FILE__) . "/../global_func.php");
require_once(dirname(__FILE__) . "/../models/setting.php");


$cron_code = Setting::get('CRON_CODE')->value;
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
mysqli_query($c, "UPDATE `fedjail` SET `fed_days` = `fed_days` - 1");
$q = mysqli_query($c, "SELECT * FROM `fedjail` WHERE `fed_days` <= 0");
$ids = array();
while ($r = mysqli_fetch_assoc($q))
{
    $ids[] = $r['fed_userid'];
}
mysqli_free_result($q);
if (count($ids) > 0)
{
    mysqli_query(
        $c,
        "UPDATE `users` SET `fedjail` = 0 WHERE `userid` IN("
            . implode(",", $ids) . ")"
    );
}
mysqli_query($c, "DELETE FROM `fedjail` WHERE `fed_days` <= 0");
$user_update_query =
        "UPDATE `users` SET 
         `daysold` = `daysold` + 1,
         `mailban` = `mailban` - IF(`mailban` > 0, 1, 0),
         `donatordays` = `donatordays` - IF(`donatordays` > 0, 1, 0),
         `cdays` = `cdays` - IF(`course` > 0, 1, 0),
         `bankmoney` = `bankmoney` + IF(`bankmoney` > 0, `bankmoney` / 50, 0),
         `cybermoney` = `cybermoney` + IF(`cybermoney` > 0, `cybermoney` / 100 * 7, 0)";
mysqli_query($c, $user_update_query);
$q = mysqli_query(
    $c,
    "SELECT `userid`, `course` FROM `users` WHERE `cdays` <= 0 AND `course` > 0"
);
$course_cache = array();
while ($r = mysqli_fetch_assoc($q))
{
    if (!array_key_exists($r['course'], $course_cache))
    {
        $cd = mysqli_query(
            $c,
            "SELECT `crSTR`, `crGUARD`, `crLABOUR`, `crAGIL`, `crIQ`, `crNAME`
                FROM `courses`
                WHERE `crID` = {$r['course']}"
        );
        $coud = mysqli_fetch_assoc($cd);
        mysqli_free_result($cd);
        $course_cache[$r['course']] = $coud;
    }
    else
    {
        $coud = $course_cache[$r['course']];
    }
    $userid = $r['userid'];
    mysqli_query(
        $c,
        "INSERT INTO `coursesdone` VALUES({$r['userid']}, {$r['course']})"
    );
    $upd = "";
    $ev = "";
    if ($coud['crSTR'] > 0)
    {
        $upd .= ", us.strength = us.strength + {$coud['crSTR']}";
        $ev .= ", {$coud['crSTR']} strength";
    }
    if ($coud['crGUARD'] > 0)
    {
        $upd .= ", us.guard = us.guard + {$coud['crGUARD']}";
        $ev .= ", {$coud['crGUARD']} guard";
    }
    if ($coud['crLABOUR'] > 0)
    {
        $upd .= ", us.labour = us.labour + {$coud['crLABOUR']}";
        $ev .= ", {$coud['crLABOUR']} labour";
    }
    if ($coud['crAGIL'] > 0)
    {
        $upd .= ", us.agility = us.agility + {$coud['crAGIL']}";
        $ev .= ", {$coud['crAGIL']} agility";
    }
    if ($coud['crIQ'] > 0)
    {
        $upd .= ", us.IQ = us.IQ + {$coud['crIQ']}";
        $ev .= ", {$coud['crIQ']} IQ";
    }
    $ev = substr($ev, 1);
    mysqli_query(
        $c,
        "UPDATE `users` AS `u`
            INNER JOIN `userstats` AS `us` ON `u`.`userid` = `us`.`userid`
            SET `u`.`course` = 0{$upd}
            WHERE `u`.`userid` = {$userid}"
    );
    event_add($userid,
            "Congratulations, you completed the {$coud['crNAME']} and gained {$ev}!",
            $c);
}
mysqli_free_result($q);
mysqli_query("TRUNCATE TABLE `votes`", $c);

<?php
/*
MCCodes FREE
header.php Rev 1.1.0
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

if (strpos($_SERVER['PHP_SELF'], "header.php") !== false)
{
    exit;
}

include "mysql.php";
require_once(dirname(__FILE__) . "/models/setting.php");
require_once(dirname(__FILE__) . "/models/user.php");
require_once(dirname(__FILE__) . "/models/ad.php");

class Header
{

    function startheaders()
    {
        $GAME_NAME = Setting::get('GAME_NAME')->value;
        echo <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="css/game.css" type="text/css" rel="stylesheet" />
<title>{$GAME_NAME}</title>
</head>
<body style='background-color: #C3C3C3;'>

EOF;
    }

    function userdata($user, $dosessh = 1)
    {
        global $c;
        $fm = money_formatter($user->money);
        $cm = money_formatter($user->crystals, '');
        $lv = $user->get_last_visit();
        $GAME_NAME = Setting::get('GAME_NAME')->value;
        $ip = mysqli_real_escape_string($c, $_SERVER['REMOTE_ADDR']);
        mysqli_query($c,
                "UPDATE users SET laston=" . time()
                        . ",lastip='$ip' WHERE userid=$user->userid");
        if (!$user->email)
        {
            die(
                    "<body>Your account may be broken. Please mail help@yourgamename.com stating your username and player ID.");
        }
        if ($dosessh && isset($_SESSION['attacking']))
        {
            if ($_SESSION['attacking'] > 0)
            {
                print "You lost all your EXP for running from the fight.";
                mysqli_query($c, "UPDATE users SET exp=0 WHERE userid=$user->userid");
                $_SESSION['attacking'] = 0;
            }
        }
        $enperc = (int) ($user->energy / $user->max_energy * 100);
        $wiperc = (int) ($user->will / $user->max_will * 100);
        $experc = (int) ($user->exp / $user->get_exp_needed() * 100);
        $brperc = (int) ($user->brave / $user->max_brave * 100);
        $hpperc = (int) ($user->hp / $user->max_hp * 100);
        $enopp = 100 - $enperc;
        $wiopp = 100 - $wiperc;
        $exopp = 100 - $experc;
        $bropp = 100 - $brperc;
        $hpopp = 100 - $hpperc;
        $d = "";
        $u = $user->username;
        if ($user->donatordays)
        {
            $u = "<font color=red>{$user->username}</font>";
            $d =
                    "<img src='donator.gif' alt='Donator: {$user->username} Days Left' title='Donator: {$user->donatordays} Days Left' />";
        }
        print
                "
<table width=100%><tr><td><img src='logo.png'></td>
<td><b>Name:</b> {$u} [{$user->userid}] $d<br />
<b>Money:</b> {$fm}<br />
<b>Level:</b> {$user->level}<br />
<b>Crystals:</b> {$user->crystals}<br />
[<a href='logout.php'>Emergency Logout</a>]</td><td>
<b>Energy:</b> {$enperc}%<br />
<img src=bargreen.gif width=$enperc height=10><img src=barred.gif width=$enopp height=10><br />
<b>Will:</b> {$wiperc}%<br />
<img src=bargreen.gif width=$wiperc height=10><img src=barred.gif width=$wiopp height=10><br />
<b>Brave:</b> {$user->brave}/{$user->max_brave}<br />
<img src=bargreen.gif width=$brperc height=10><img src=barred.gif width=$bropp height=10><br />
<b>EXP:</b> {$experc}%<br />
<img src=bargreen.gif width=$experc height=10><img src=barred.gif width=$exopp height=10><br />
<b>Health:</b> {$hpperc}%<br />
<img src=bargreen.gif width=$hpperc height=10><img src=barred.gif width=$hpopp height=10></td></tr></table></div><center><b><u><a href='voting.php'>Vote for {$GAME_NAME} on various gaming sites and be rewarded!</a></u></b></center><br />
<center><b><u><a href='donator.php'>Donate to {$GAME_NAME}, it's only \$3 and gets you a lot of benefits!</a></u></b></center><br />
                ";
        $ad = Ad::get_random();
        if ($ad->id)
        {
            print
                    "<center><a href='ad.php?ad={$ad->id}'><img src='{$ad->img}' alt='Paid Advertisement' /></a></center><br />";
            $ad->view();
        }
        print "<table width=100%><tr><td width=20% valign='top'>
";
        if ($user->fedjail)
        {
            $q =
                    mysqli_query(
                            $c,
                            "SELECT * FROM fedjail WHERE fed_userid=$user->userid"
                    );
            $r = mysqli_fetch_array($q);
            die(
                    "<b><font color=red size=+1>You have been put in the {$GAME_NAME} Federal Jail for {$r['fed_days']} day(s).<br />
Reason: {$r['fed_reason']}</font></b></body></html>");
        }
        if (file_exists('ipbans/' . $ip))
        {
            die(
                    "<b><font color=red size=+1>Your IP has been banned, there is no way around this.</font></b></body></html>");
        }
    }

    function menuarea()
    {
        include "mainmenu.php";
        global $user, $c;
        print "</td><td valign='top'>
";
    }

    function endpage()
    {
        $GAME_OWNER = Setting::get('GAME_OWNER')->value;
        $year = date('Y');
        print
                "</td></tr></table>
        <div style='font-style: italic; text-align: center'>
      		Powered by codes made by Dabomstew. Copyright &copy; {$year} {$GAME_OWNER}.
    	</div>
        </body>
		</html>";
    }
}

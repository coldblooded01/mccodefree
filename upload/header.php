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

if (strpos($_SERVER['PHP_SELF'], "header.php") !== false)
{
    exit;
}

class headers
{

    function startheaders()
    {
        global $ir;
        echo <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="css/game.css" type="text/css" rel="stylesheet" />
<title>test game</title>
</head>
<body style='background-color: #C3C3C3;'>

EOF;
    }

    function userdata($ir, $lv, $fm, $cm, $dosessh = 1)
    {
        global $c, $userid;
        $ip = mysqli_real_escape_string($c, $_SERVER['REMOTE_ADDR']);
        mysqli_query($c,
                "UPDATE users SET laston=" . time()
                        . ",lastip='$ip' WHERE userid=$userid");
        if (!$ir['email'])
        {
            die(
                    "<body>Your account may be broken. Please mail help@yourgamename.com stating your username and player ID.");
        }
        if ($dosessh && isset($_SESSION['attacking']))
        {
            if ($_SESSION['attacking'] > 0)
            {
                print "You lost all your EXP for running from the fight.";
                mysqli_query("UPDATE users SET exp=0 WHERE userid=$userid", $c);
                $_SESSION['attacking'] = 0;
            }
        }
        $enperc = (int) ($ir['energy'] / $ir['maxenergy'] * 100);
        $wiperc = (int) ($ir['will'] / $ir['maxwill'] * 100);
        $experc = (int) ($ir['exp'] / $ir['exp_needed'] * 100);
        $brperc = (int) ($ir['brave'] / $ir['maxbrave'] * 100);
        $hpperc = (int) ($ir['hp'] / $ir['maxhp'] * 100);
        $enopp = 100 - $enperc;
        $wiopp = 100 - $wiperc;
        $exopp = 100 - $experc;
        $bropp = 100 - $brperc;
        $hpopp = 100 - $hpperc;
        $d = "";
        $u = $ir['username'];
        if ($ir['donatordays'])
        {
            $u = "<font color=red>{$ir['username']}</font>";
            $d =
                    "<img src='images/donator.gif' alt='Donator: {$ir['donatordays']} Days Left' title='Donator: {$ir['donatordays']} Days Left' />";
        }
        print
                "
<table width=100%><tr><td><img src='images/logo.png'></td>
<td><b>Name:</b> {$u} [{$ir['userid']}] $d<br />
<b>Money:</b> {$fm}<br />
<b>Level:</b> {$ir['level']}<br />
<b>Crystals:</b> {$ir['crystals']}<br />
[<a href='logout.php'>Emergency Logout</a>]</td><td>
<b>Energy:</b> {$enperc}%<br />
<img src=images/bargreen.gif width=$enperc height=10><img src=images/barred.gif width=$enopp height=10><br />
<b>Will:</b> {$wiperc}%<br />
<img src=images/bargreen.gif width=$wiperc height=10><img src=images/barred.gif width=$wiopp height=10><br />
<b>Brave:</b> {$ir['brave']}/{$ir['maxbrave']}<br />
<img src=images/bargreen.gif width=$brperc height=10><img src=images/barred.gif width=$bropp height=10><br />
<b>EXP:</b> {$experc}%<br />
<img src=images/bargreen.gif width=$experc height=10><img src=images/barred.gif width=$exopp height=10><br />
<b>Health:</b> {$hpperc}%<br />
<img src=images/bargreen.gif width=$hpperc height=10><img src=images/barred.gif width=$hpopp height=10></td></tr></table></div><center><b><u><a href='voting.php'>Vote for test game on various gaming sites and be rewarded!</a></u></b></center><br />
<center><b><u><a href='donator.php'>Donate to test game, it's only \$3 and gets you a lot of benefits!</a></u></b></center><br />
                ";
        $q = mysqli_query($c, "SELECT * FROM ads ORDER BY rand() LIMIT 1");
        if (mysqli_num_rows($q))
        {
            $r = mysqli_fetch_array($q);
            print
                    "<center><a href='ad.php?ad={$r['adID']}'><img src='{$r['adIMG']}' alt='Paid Advertisement' /></a></center><br />";
            mysqli_query(
                    "UPDATE ads SET adVIEWS=adVIEWS+1 WHERE adID={$r['adID']}",
                    $c);
        }
        print "<table width=100%><tr><td width=20% valign='top'>
";
        if ($ir['fedjail'])
        {
            $q =
                    mysqli_query(
                            "SELECT * FROM fedjail WHERE fed_userid=$userid",
                            $c);
            $r = mysqli_fetch_array($q);
            die(
                    "<b><font color=red size=+1>You have been put in the test game Federal Jail for {$r['fed_days']} day(s).<br />
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
        global $ir, $c;
        print "</td><td valign='top'>
";
    }

    function endpage()
    {
        $year = date('Y');
        print
                "</td></tr></table>
        <div style='font-style: italic; text-align: center'>
      		Powered by codes made by Dabomstew. Copyright &copy; {$year} coming soon.
    	</div>
        </body>
		</html>";
    }
}

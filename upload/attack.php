<?php
/*
MCCodes FREE
attack.php Rev 1.1.0
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
$h->userdata($user, 0);
$_GET['ID'] == (int) $_GET['ID'];
if (!$_GET['ID'])
{
    print "<font color='red'>WTF you doing, bro?</font></b>";
    $h->endpage();
    exit;
}
else if ($_GET['ID'] == $userid)
{
    print "<font color='red'><b>Only the crazy attack themselves.</font></b>";
    $h->endpage();
    exit;
}

if (!User::exists($_GET['ID']))
{
    print
            "<b><font color='red'>This player does not exist.</font></b><br />
<a href='index.php'>&gt; Back</a>";
    $h->endpage();
    $_SESSION['attacking'] = 0;
    exit;
}
$opponent = User::get($_GET['ID']);
if ($opponent->is_unconscious())
{
    print
            "<b><font color='red'>This player is unconscious.</font></b><br />
<a href='index.php'>&gt; Back</a>";
    $h->endpage();
    $_SESSION['attacking'] = 0;
    exit;
}
else if ($opponent->is_in_hospital() and !$user->is_in_hospital())
{
    print
            "<font color='red'><b>This player is in hospital.</b><font><br />
<a href='index.php'>&gt; Back</a>";
    $h->endpage();
    $_SESSION['attacking'] = 0;
    exit;
}
else if ($user->is_in_hospital())
{
    print
            "<b><font color='red'>You can not attack while in hospital.</font></b><br />
<a href='hospital.php'>&gt; Back</a>";
    $h->endpage();
    $_SESSION['attacking'] = 0;
    exit;
}
print "<table width=100%><tr><td colspan=2 align=center>";
if ($_GET['wepid'])
{
    if ($_SESSION['attacking'] == 0)
    {
        if ($user->has_energy_to_attack())
        {
            $user->spend_attack_energy();
            $_SESSION['attacklog'] = "";
        }
        else
        {
            print
                    "<font color='red'><b>You can only attack someone when you have 50% energy</font></b>";
            $h->endpage();
            exit;
        }
    }
    $_SESSION['attacking'] = 1;
    $_GET['wepid'] = (int) $_GET['wepid'];
    $_GET['nextstep'] = (int) $_GET['nextstep'];
    //damage
    $qr =
            mysqli_query(
                $c,
                "SELECT * FROM inventory WHERE inv_itemid={$_GET['wepid']} and inv_userid=$userid"
            );
    if (mysqli_num_rows($qr) == 0)
    {
        print
                "<font color='red'>Stop trying to abuse a game bug. You can lose all your EXP for that.</font></b><br />
<a href='index.php'>&gt; Home</a>";
        $user->exp_penalty();
        die("");
    }
    $qo = mysqli_query(
        $c,
        "SELECT i.*,w.* FROM items i LEFT JOIN weapons w ON i.itmid=w.item_id WHERE w.item_id={$_GET['wepid']}"
    );
    $r1 = mysqli_fetch_array($qo);
    $mydamage =
            (int) (($r1['damage'] * $user->user_stats->strength / $opponent->user_stats->guard)
                    * (rand(8000, 12000) / 10000));
    $hitratio = min(50 * $user->user_stats->agility / $opponent->user_stats->agility, 95);
    if (rand(1, 100) <= $hitratio)
    {
        $q3 = mysqli_query(
            $c,
            "SELECT a.Defence FROM inventory iv LEFT JOIN items i ON iv.inv_itemid = i.itmid LEFT JOIN armour a ON i.itmid=a.item_ID WHERE i.itmtype=7 AND iv.inv_userid={$_GET['ID']} ORDER BY rand()"
        );
        if (mysqli_num_rows($q3))
        {
            $mydamage -= mysqli_data_seek($q3, 0);
        }
        if ($opponent->hp - $mydamage == 1)
        {
            $mydamage += 1;
        }
        $opponent->damage($mydamage);
        
        print
                "<font color=red>{$_GET['nextstep']}. Using your {$r1['itmname']} you hit {$opponent->username} doing $mydamage damage ({$opponent->hp})</font><br />\n";
        $_SESSION['attacklog'] .=
                "<font color=red>{$_GET['nextstep']}. Using his {$r1['itmname']} {$user->username} hit {$opponent->username} doing $mydamage damage ({$opponent->hp})</font><br />\n";
    }
    else
    {
        print
                "<font color=red>{$_GET['nextstep']}. You tried to hit {$opponent->username} but missed ({$opponent->hp})</font><br />\n";
        $_SESSION['attacklog'] .=
                "<font color=red>{$_GET['nextstep']}. {$user->username} tried to hit {$opponent->username} but missed ({$opponent->hp})</font><br />\n";
    }
    if ($opponent->hp <= 0)
    {
        $_SESSION['attackwon'] = $_GET['ID'];
        $opponent->kill();
        print
                "<form action='attackleave.php?ID={$_GET['ID']}' method='post'><input type='submit' value='Leave Them' /></form>
<form action='attackmug.php?ID={$_GET['ID']}' method='post'><input type='submit' value='Mug Them'></form>
<form action='attackhosp.php?ID={$_GET['ID']}' method='post'><input type='submit' value='Hospitalize Them'></form>";
    }
    else
    {
        //choose opp gun
        $eq = mysqli_query(
            $c,
            "SELECT iv.*,i.*,w.* FROM inventory iv LEFT JOIN items i ON iv.inv_itemid=i.itmid LEFT JOIN weapons w ON iv.inv_itemid=w.item_id WHERE iv.inv_userid={$_GET['ID']} AND ( i.itmtype=3 OR i.itmtype=4 )"
        );
        if (mysqli_num_rows($eq) == 0)
        {
            $wep = "Fists";
            $dam =
                    (int) ((((int) ($opponent->strength / 100)) + 1)
                            * (rand(8000, 12000) / 10000));
        }
        else
        {
            $cnt = 0;
            while ($r = mysqli_fetch_array($eq))
            {
                $enweps[] = $r;
                $cnt++;
            }
            $weptouse = rand(0, $cnt - 1);
            $wep = $enweps[$weptouse]['itmname'];
            $dam =
                    (int) (($enweps[$weptouse]['damage'] * $opponent->user_stats->strength
                            / $user->user_stats->guard) * (rand(8000, 12000) / 10000));
        }
        $hitratio = min(50 * $opponent->user_stats->agility / $user->user_stats->agility, 95);
        if ($opponent->userid == 1)
        {
            $hitratio = 100;
        }
        if (rand(1, 100) <= $hitratio)
        {
            $q3 = mysqli_query(
                $c,
                "SELECT a.Defence FROM inventory iv LEFT JOIN items i ON iv.inv_itemid = i.itmid LEFT JOIN armour a ON i.itmid=a.item_ID WHERE i.itmtype=7 AND iv.inv_userid=$userid ORDER BY rand()"
            );
            if (mysqli_num_rows($q3))
            {
                $dam -= mysqli_data_seek($q3, 0, 0);
            }
            $user->damage($dam);
            $ns = $_GET['nextstep'] + 1;
            print
                    "<font color=blue>{$ns}. Using his $wep {$opponent->username} hit you doing $dam damage ({$user->hp})</font><br />\n";
            $_SESSION['attacklog'] .=
                    "<font color=blue>{$ns}. Using his $wep {$opponent->username} hit {$user->username} doing $dam damage ({$user->hp})</font><br />\n";
        }
        else
        {
            $ns = $_GET['nextstep'] + 1;
            print
                    "<font color=blue>{$ns}. {$opponent->username} tried to hit you but missed ({$user->hp})</font><br />\n";
            $_SESSION['attacklog'] .=
                    "<font color=blue>{$ns}. {$opponent->username} tried to hit {$user->username} but missed ({$user->hp})</font><br />\n";
        }
        if ($user->hp <= 0)
        {
            $user->kill();
            print
                    "<form action='attacklost.php?ID={$_GET['ID']}' method='post'><input type='submit' value='Continue' />";
        }
    }
}
else if (!$opponent->has_energy_to_attack())
{
    print "You can only attack those who have at least 1/2 their max health";
    $h->endpage();
    exit;
}
else if (!$user->has_energy_to_attack())
{
    print "You can only attack someone when you have 50% energy";
    $h->endpage();
    exit;
}
else if ($user->location != $opponent->location)
{
    print "You can only attack someone in the same location!";
    $h->endpage();
    exit;
}
else
{
}
print "</td></tr>";
if ($user->hp <= 0 || $opponent->hp <= 0)
{
    print "</table>";
}
else
{
    print
            "<tr><td>Your Health: {$user->hp}/{$user->max_hp}</td><td>Opponents Health: {$opponent->hp}/{$opponent->max_hp}</td></tr>";
    $mw = mysqli_query(
        $c,
        "SELECT iv.*,i.* FROM inventory iv LEFT JOIN items i ON iv.inv_itemid=i.itmid WHERE iv.inv_userid=$userid AND (i.itmtype = 3 || i.itmtype = 4)"
    );
    print "<tr><td colspan=2 align='center'>Attack with:<br />";
    while ($r = mysqli_fetch_array($mw))
    {
        if (!$_GET['nextstep'])
        {
            $ns = 1;
        }
        else
        {
            $ns = $_GET['nextstep'] + 2;
        }
        print
                "<a href='attack.php?nextstep=$ns&amp;ID={$_GET['ID']}&amp;wepid={$r['itmid']}'>{$r['itmname']}</a><br />";
    }
    print "</table>";
}
$h->endpage();

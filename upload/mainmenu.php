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

if (strpos($_SERVER['PHP_SELF'], "mainmenu.php") !== false)
{
    exit;
}
global $c, $ir;
if (!$ir['hospital'])
{
    print
            "<a href='index.php'>Home</a><br />
<a href='inventory.php'>Items</a><br />
<a href='explore.php'>Explore</a><br />
<a href='events.php'>";
    $d =
            mysqli_query($c,
                    "SELECT COUNT(*) as cnt FROM events WHERE evUSER={$ir['userid']} AND evREAD=0") or die(((is_object($c)) ? mysqli_error($c) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    $r = mysqli_fetch_array($d);
    if ($r['cnt'] > 0)
    {
        print "<b>Events ({$r['cnt']})</b>";
    }
    else
    {
        print "Events (0)";
    }
    print "</a><br />
<a href='mailbox.php'>";
    $d2 =
            mysqli_query($c,
                    "SELECT COUNT(*) as cnt FROM mail WHERE mail_to={$ir['userid']} AND mail_read=0") or die(mysqli_error($c));
    $r = mysqli_fetch_array($d2);
    if ($r['cnt'] > 0)
    {
        print "<b>Mail ({$r['cnt']})</b>";
    }
    else
    {
        print "Mail (0)";
    }
    print
            "</a><br />
<a href='gym.php'>Gym</a><br />
<a href='criminal.php'>Crimes</a><br />
<a href='education.php'>Local School</a><br />
<a href='monopaper.php'>Announcements</a><br />
<a href='search.php'>Search</a><br />
<a href='advsearch.php'>Advanced Search</a><br />";
    if ($ir['user_level'] > 1)
    {
        print "<hr />
<b>Staff Only</b><br />\n";
        if ($ir['user_level'] < 6 and $ir['user_level'] != 4)
        {
            print "<a href='new_staff.php'>Staff Panel</a><br />\n";
        }
    }
    if ($ir['user_level'] > 1)
    {
        print "<hr /><b>Staff Online:</b><br />";
        $q =
                mysqli_query($c,
                        "SELECT * FROM users WHERE laston > " . (time() - 900)
                                . " AND user_level>1 ORDER BY userid ASC");
        while ($r = mysqli_fetch_array($q))
        {
            $la = time() - $r['laston'];
            $unit = "secs";
            if ($la >= 60)
            {
                $la = (int) ($la / 60);
                $unit = "mins";
            }
            if ($la >= 60)
            {
                $la = (int) ($la / 60);
                $unit = "hours";
                if ($la >= 24)
                {
                    $la = (int) ($la / 24);
                    $unit = "days";
                }
            }
            print
                    "<a href='viewuser.php?u={$r['userid']}'>{$r['username']}</a> ($la $unit)<br />";
        }
    }
    if ($ir['donatordays'])
    {
        print
                "<hr />
<b>Donators Only</b><br />
<a href='friendslist.php'>Friends List</a><br />
<a href='blacklist.php'>Black List</a>";
    }
    print
            "<hr />
<a href='preferences.php'>Preferences</a><br />
<a href='preport.php'>Player Report</a><br />
<a href='helptutorial.php'>Help Tutorial</a><br />
<a href='gamerules.php'>Game Rules</a><br />
<a href='viewuser.php?u={$ir['userid']}'>My Profile</a><br />
<a href='logout.php'>Logout</a><br /><br />
Time is now<br />
            ";
    echo date('F j, Y') . "<br />" . date('g:i:s a');
}
else
{
    print
            "<a href='index.php'>Home</a><br />
<a href='inventory.php'>Items</a><br />
<a href='events.php'>";
    $d =
            mysqli_query($c,
                    "SELECT COUNT(*) as cnt FROM events WHERE evUSER={$ir['userid']} AND evREAD=0") or die(((is_object($c)) ? mysqli_error($c) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    $r = mysqli_fetch_array($d);
    if ($r['cnt'] > 0)
    {
        print "<b>Events ({$r['cnt']})</b>";
    }
    else
    {
        print "Events (0)";
    }
    print "</a><br />
<a href='mailbox.php'>";
    $d2 =
            mysqli_query($c,
                    "SELECT COUNT(*) as cnt FROM mail WHERE mail_to={$ir['userid']} AND mail_read=0") or die(((is_object($c)) ? mysqli_error($c) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    $r = mysqli_fetch_array($d2);
    if ($r['cnt'] > 0)
    {
        print "<b>Mail ({$r['cnt']})</b>";
    }
    else
    {
        print "Mail (0)";
    }
    print
            "</a><br />
<a href='monopaper.php'>Announcements</a><br />
<a href='search.php'>Search</a><br />";
    if ($ir['user_level'] > 1)
    {
        print "<hr />
<b>Staff Only</b><br />";
        if ($ir['user_level'] < 6 and $ir['user_level'] != 4)
        {
            print "<a href='new_staff.php'>Staff Panel</a><br />\n";
        }
    }
    if ($ir['user_level'] > 1)
    {
        print "<hr /><b>Staff Online:</b><br />";
        $q =
                mysqli_query($c,
                        "SELECT * FROM users WHERE laston>(" . time()
                                . "-15*60) AND user_level>1 ORDER BY userid ASC");
        while ($r = mysqli_fetch_array($q))
        {
            $la = time() - $r['laston'];
            $unit = "secs";
            if ($la >= 60)
            {
                $la = (int) ($la / 60);
                $unit = "mins";
            }
            if ($la >= 60)
            {
                $la = (int) ($la / 60);
                $unit = "hours";
                if ($la >= 24)
                {
                    $la = (int) ($la / 24);
                    $unit = "days";
                }
            }
            print
                    "<a href='viewuser.php?u={$r['userid']}'>{$r['username']}</a> ($la $unit)<br />";
        }
    }
    if ($ir['donatordays'])
    {
        print
                "<hr />
<b>Donators Only</b><br />
<a href='friendslist.php'>Friends List</a><br />
<a href='blacklist.php'>Black List</a>";
    }
    print
            "<hr />
<a href='preferences.php'>Preferences</a><br />
<a href='preport.php'>Player Report</a><br />
<a href='helptutorial.php'>Help Tutorial</a><br />
<a href='gamerules.php'>Game Rules</a><br />
<a href='viewuser.php?u={$ir['userid']}'>My Profile</a><br />
<a href='logout.php'>Logout</a><br /><br />
Time is now<br />
            ";
    echo date('F j, Y') . "<br />" . date('g:i:s a');
}

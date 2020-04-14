<?php
/*
MCCodes FREE
events.php Rev 1.1.0
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
require_once(dirname(__FILE__) . "/models/event.php");
$user = User::get($userid);
require "header.php";
$h = new Header();
$h->startheaders();
include "mysql.php";
global $c;

$user->check_level();
$h->userdata($user);
$h->menuarea();
$_GET['delete'] = abs((int) $_GET['delete']);
if ($_GET['delete'])
{
    Event::delete_event($_GET['delete'], $userid);
    print "<b>Event Deleted</b><br />";
}
print "<b>Latest 10 events</b><br />";
$events = Event::get_events_for_user($userid);
print
        "<table width=75% border=2> <tr style='background:gray;'> <th>Time</th> <th>Event</th><th>Links</th> </tr>";
foreach ($events as $event)
{
    print "<tr><td>" . date('F j Y, g:i:s a', $event->time);
    if ($event->is_new())
    {
        print "<br /><b>New!</b>";
    }
    print
            "</td><td>{$event->text}</td><td><a href='events.php?delete={$event->id}'>Delete</a></td></tr>";
}
print "</table>";
Event::mark_all_as_read($userid);
$h->endpage();

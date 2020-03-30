<?php
/*
MCCodes FREE
mailban.php Rev 1.1.0
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

if (!isset($_GET['userid']) || !is_numeric($_GET['userid']))
{
    exit;
}
require "global_func.php";
$location =
        'http://' . determine_game_urlbase()
                . '/new_staff.php?action=mailform&XID=' . $_GET['userid'];
header('Location: ' . $location);
exit;

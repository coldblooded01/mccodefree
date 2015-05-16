<?php
/*
MCCodes FREE
index.php Rev 1.1.1
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

require "includes/mysql.php";
$_GET['ad'] = abs(@intval($_GET['ad']));
mysqli_query($c,"UPDATE ads SET adCLICKS=adCLICKS+1 WHERE adID='{$_GET['ad']}'");
$q = mysqli_query($c,"SELECT adURL FROM ads WHERE adID='{$_GET['ad']}'");
if (mysqli_num_rows($q) > 0)
{
    header("Location: " . mysqli_free_result($q));
}
else
{
    die("Invalid ad.");
}

<?php
/*
MCCodes FREE
authenticate.php Rev 1.1.0
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

require_once("mysql.php");
require_once(dirname(__FILE__) . "/models/setting.php");

$GAME_NAME = Setting::get('GAME_NAME')->value;

if ($_POST['username'] == "" || $_POST['password'] == "")
{
    die(
            "<h3>{$GAME_NAME}Error</h3>
You did not fill in the login form!<br />
<a href=login.php>&gt; Back</a>");
}

require "global_func.php";


$username =
        (array_key_exists('username', $_POST) && is_string($_POST['username']))
                ? $_POST['username'] : '';
$password =
        (array_key_exists('password', $_POST) && is_string($_POST['password']))
                ? $_POST['password'] : '';
if (empty($username) || empty($password))
{
    die(
            "<h3>{$GAME_NAME} Error</h3>
	You did not fill in the login form!<br />
	<a href='login.php'>&gt; Back</a>");
}
$form_username = mysqli_real_escape_string($c, stripslashes($username));
$raw_password = stripslashes($password);
$uq =
        mysqli_query($c,
                "SELECT `userid`, `userpass`, `pass_salt`
                 FROM `users`
                 WHERE `login_name` = '$form_username'");
if (mysqli_num_rows($uq) == 0)
{
    die(
            "<h3>{$GAME_NAME} Error</h3>
	Invalid username or password!<br />
	<a href='login.php'>&gt; Back</a>");
}
else
{
    $mem = mysqli_fetch_assoc($uq);
    $login_failed = false;
    // Pass Salt generation: autofix
    if (empty($mem['pass_salt']))
    {
        if (md5($raw_password) != $mem['userpass'])
        {
            $login_failed = true;
        }
        $salt = generate_pass_salt();
        $enc_psw = encode_password($mem['userpass'], $salt, true);
        $e_salt = mysqli_real_escape_string($c, $salt); // in case of changed salt function
        $e_encpsw = mysqli_real_escape_string($c, $enc_psw); // ditto for password encoder
        mysqli_query(
                $c,
                "UPDATE `users`
        		 SET `pass_salt` = '{$e_salt}', `userpass` = '{$e_encpsw}'
        		 WHERE `userid` = {$mem['userid']}");
    }
    else
    {
        $login_failed =
                !(verify_user_password($raw_password, $mem['pass_salt'],
                        $mem['userpass']));
    }
    if ($login_failed)
    {
        die(
                "<h3>{$GAME_NAME} Error</h3>
		Invalid username or password!<br />
		<a href='login.php'>&gt; Back</a>");
    }
    if ($mem['userid'] == 1 && file_exists('./installer.php'))
    {
        die(
                "<h3>{$GAME_NAME} Error</h3>
                The installer still exists! You need to delete installer.php immediately.<br />
                <a href='login.php'>&gt; Back</a>");
    }
    session_regenerate_id();
    $_SESSION['loggedin'] = 1;
    $_SESSION['userid'] = $mem['userid'];
    $loggedin_url = 'http://' . determine_game_urlbase() . '/loggedin.php';
    header("Location: {$loggedin_url}");
    exit;
}


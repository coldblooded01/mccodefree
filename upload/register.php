<?php
/*
MCCodes FREE
register.php Rev 1.1.0
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
require "mysql.php";
require "global_func.php";
require_once(dirname(__FILE__) . "/models/event.php");
require_once(dirname(__FILE__) . "/models/setting.php");
$GAME_NAME = Setting::get('GAME_NAME')->value;
print 
        <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="css/game.css" type="text/css" rel="stylesheet" />
<title>{$GAME_NAME}</title>
</head>
<body onload="getme();" bgcolor="#C3C3C3">
<img src="logo.png" alt="Your Game Logo" />
<br />
EOF;
$ip = ($_SERVER['REMOTE_ADDR']);
if (file_exists('ipbans/' . $ip))
{
    die(
            "<b><span style='color: red; font-size: 120%'>
            Your IP has been banned, there is no way around this.
            </span></b>
            </body></html>");
}
if ($_POST['username'])
{
    $sm = 100;
    if ($_POST['promo'] == "Your Promo Code Here")
    {
        $sm += 100;
    }
    $username = $_POST['username'];
    $username = mysqli_real_escape_string(
        $c,
        htmlentities(
            stripslashes($username),
            ENT_QUOTES,
            'ISO-8859-1'
        )
    );
    $q = mysqli_query($c, "SELECT * FROM users WHERE username='{$username}'");
    if (mysqli_num_rows($q))
    {
        print "Username already in use. Choose another.";
    }
    else if ($_POST['password'] != $_POST['cpassword'])
    {
        print "The passwords did not match, go back and try again.";
    }
    else
    {
        $_POST['ref'] = abs((int) $_POST['ref']);
        $ip = $_SERVER['REMOTE_ADDR'];
        if ($_POST['ref'])
        {
            $q = mysqli_query(
                $c,
                "SELECT `lastip`
                    FROM `users`
                    WHERE `userid` = {$_POST['ref']}"
            );
            if (mysqli_num_rows($q) == 0)
            {
                mysqli_free_result($q);
                echo "Referrer does not exist.<br />
				&gt; <a href='register.php'>Back</a>";
                die('</body></html>');
            }
            $rem_IP = mysqli_data_seek($q, 0, 0);
            mysqli_free_result($q);
            if ($rem_IP == $ip)
            {
                echo "No creating referral multies.<br />
				&gt; <a href='register.php'>Back</a>";
                die('</body></html>');
            }
        }
        mysqli_query(
            $c,
            "INSERT INTO users (username, login_name, userpass, level, money, crystals, donatordays, user_level, energy, maxenergy, will, maxwill, brave, maxbrave, hp, maxhp, location, gender, signedup, email, bankmoney, lastip) VALUES( '{$username}', '{$username}', md5('{$_POST['password']}'), 1, $sm, 0, 0, 1, 12, 12, 100, 100, 5, 5, 100, 100, 1, 'Male', "
                . time() . ", '{$_POST['email']}', -1, '$ip')"
        );
        $i = mysqli_insert_id($c);
        mysqli_query(
            $c,
            "INSERT INTO userstats VALUES ($i, 10, 10, 10, 10, 10)"
        );

        if ($_POST['ref'])
        {
            mysqli_query(
                $c,
                "UPDATE `users`
                    SET `crystals` = `crystals` + 2
                    WHERE `userid` = {$_POST['ref']}");
            Event::add(
                $_POST['ref'],
                "For refering $username to the game, you have earnt 2 valuable crystals!"
            );
            $e_rip = mysqli_real_escape_string($c, $rem_IP);
            $e_oip = mysqli_real_escape_string($c, $ip);
            mysqli_query(
                $c,
                "INSERT INTO `referals`
                    VALUES(NULL, {$_POST['ref']}, $i, " . time()
                    . ", '{$e_rip}', '$e_oip')"
            );
        }
        print 
                "You have signed up, enjoy the game.<br />
&gt; <a href='login.php'>Login</a>";
    }
}
else
{
    $gref = abs((int) $_GET['REF']);
    $fref = $gref ? $gref : '';
    echo <<<EOF
    <h3>
      {$GAME_NAME} Registration
    </h3>
    <form action="register.php" method="post">
      Username: <input type="text" name="username" /><br />
      Password: <input type="password" name="password" /><br />
      Confirm Password: <input type="password" name="cpassword" /><br />
      Email: <input type="text" name="email" /><br />
      Promo Code: <input type="text" name="promo" /><br />
      <input type="hidden" name="ref" value='{$fref}' />
      <input type="submit" value="Submit" />
    </form><br />
    &gt; <a href='login.php'>Go Back</a>
EOF;
}
print "</body></html>";

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
require "global_func.php";
require_once(dirname(__FILE__) . "/models/event.php");
require_once(dirname(__FILE__) . "/models/referral.php");
require_once(dirname(__FILE__) . "/models/setting.php");
require_once(dirname(__FILE__) . "/models/user.php");
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
    $starting_money = 100;
    if ($_POST['promo'] == "Your Promo Code Here")
    {
        $starting_money += 100;
    }
    $username = $_POST['username'];
    $username = mysql_escape(
        htmlentities(
            stripslashes($username),
            ENT_QUOTES,
            'ISO-8859-1'
        )
    );
    $error = "";

    if (User::exists_by_username($username))
    {
        $error = "Username already in use. Choose another.<br/>&gt; <a href='register.php'>Back</a>";
    }
    else if ($_POST['password'] != $_POST['cpassword'])
    {
        $error = "The passwords did not match, go back and try again.<br/>&gt; <a href='register.php'>Back</a>";
    }
    else
    {
        $ref_id = abs((int) $_POST['ref']);
        $ip = $_SERVER['REMOTE_ADDR'];
        if ($ref_id)
        {
            if (User::exists_by_username($ref_id))
            {
                $error = "Referrer does not exist.<br />
				&gt; <a href='register.php'>Back</a>";
            }
            
            if (User::check_ref_ip($ref_id, $ip))
            {
                $error = "No creating referral multies.<br />
				&gt; <a href='register.php'>Back</a>";
            }
        }
        if(!$error) {
            $new_user_id = User::add($username, $username, $_POST['password'], $starting_money, $_POST['email'], $ip);
            if ($ref_id)
            {
                User::get($ref_id)->increase_crystals(2);
                Event::add(
                    $ref_id,
                    "For refering $username to the game, you have earnt 2 valuable crystals!"
                );
                $e_rip = mysql_escape($rem_IP);
                $e_oip = mysql_escape($ip);
                Referral::add($_POST['ref'], $i, $e_rip, $e_oip);
            }
        }
        

        
        
    }
    if($error == "") {
        print "You have signed up, enjoy the game.<br />&gt; <a href='login.php'>Login</a>";
    } else {
        echo $error;
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

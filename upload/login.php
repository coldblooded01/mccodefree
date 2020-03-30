<?php
/*
MCCodes FREE
login.php Rev 1.1.0
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
print
        <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="css/game.css" type="text/css" rel="stylesheet" />
<script src="js/login.js" type="text/javascript" language="JavaScript"></script>
<title>{GAME_NAME}</title>
</head>
<body onload="getme();" bgcolor="#C3C3C3">
<img src="logo.png" alt="Your Game Logo" />
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
$year = date('Y');
print
        <<<EOF
    <h3>
      &gt; {GAME_NAME} Log-In
    </h3>
    <table width="80%">
      <tr>
        <td width="50%">
          <fieldset>
            <legend>About {GAME_NAME}</legend>
            {GAME_DESCRIPTION}
          </fieldset>
        </td>
        <td>
          <fieldset>
            <legend>Login</legend>
            <form action="authenticate.php" method="post" name="login" onsubmit="return saveme();" id="login">
              Username: <input type="text" name="username" /><br />
              Password: <input type="password" name="password" /><br />
              Remember me?<br />
              <input type="radio" value="ON" name="save" />Yes <input type="radio" name=
              "save" value="OFF" checked="checked" />No
              <input type="submit" value="Submit" />
            </form>
          </fieldset>
        </td>
      </tr>
    </table><br />
    <h3>
      <a href='register.php'>REGISTER NOW!</a>
    </h3><br />
    <div style="font-style: italic; text-align: center">
      Powered by codes made by Dabomstew. Copyright &copy; {$year} {GAME_OWNER}.
      Ported to PHP 7.0 by Yeraycat.
    </div>
  </body>
</html>
EOF;

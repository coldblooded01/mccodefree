<?php
/*
MCCodes FREE
global_func.php Rev 1.1.0
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

if (strpos($_SERVER['PHP_SELF'], "global_func.php") !== false)
{
    exit;
}

function money_formatter($muny, $symb = '$')
{
    $moneys = "";
    $muny = (string) $muny;
    if (strlen($muny) <= 3)
    {
        return $symb . $muny;
    }
    $dun = 0;
    for ($i = strlen($muny); $i > 0; $i -= 1)
    {
        if ($dun % 3 == 0 && $dun > 0)
        {
            $moneys = "," . $moneys;
        }
        $dun += 1;
        $moneys = $muny[$i - 1] . $moneys;
    }
    return $symb . $moneys;
}

function itemtype_dropdown($connection, $ddname = "item_type", $selected = -1)
{
    $ret = "<select name='$ddname' type='dropdown'>";
    $q =
            mysqli_query($connection, "SELECT * FROM itemtypes ORDER BY itmtypename ASC");
    if ($selected == -1)
    {
        $first = 0;
    }
    else
    {
        $first = 1;
    }
    while ($r = mysqli_fetch_array($q))
    {
        $ret .= "\n<option value='{$r['itmtypeid']}'";
        if ($selected == $r['itmtypeid'] || $first == 0)
        {
            $ret .= " selected='selected'";
            $first = 1;
        }
        $ret .= ">{$r['itmtypename']}</option>";
    }
    $ret .= "\n</select>";
    return $ret;
}

function item_dropdown($connection, $ddname = "item", $selected = -1)
{
    $ret = "<select name='$ddname' type='dropdown'>";
    $q = mysqli_query(
        $connection,
        "SELECT * FROM items ORDER BY itmname ASC"
    );
    if ($selected == -1)
    {
        $first = 0;
    }
    else
    {
        $first = 1;
    }
    while ($r = mysqli_fetch_array($q))
    {
        $ret .= "\n<option value='{$r['itmid']}'";
        if ($selected == $r['itmid'] || $first == 0)
        {
            $ret .= " selected='selected'";
            $first = 1;
        }
        $ret .= ">{$r['itmname']}</option>";
    }
    $ret .= "\n</select>";
    return $ret;
}

function location_dropdown($connection, $ddname = "location", $selected = -1)
{
    $ret = "<select name='$ddname' type='dropdown'>";
    $q = mysqli_query(
        $connection,
        "SELECT * FROM cities ORDER BY cityname ASC"
    );
    if ($selected == -1)
    {
        $first = 0;
    }
    else
    {
        $first = 1;
    }
    while ($r = mysqli_fetch_array($q))
    {
        $ret .= "\n<option value='{$r['cityid']}'";
        if ($selected == $r['cityid'] || $first == 0)
        {
            $ret .= " selected='selected'";
            $first = 1;
        }
        $ret .= ">{$r['cityname']}</option>";
    }
    $ret .= "\n</select>";
    return $ret;
}

function shop_dropdown($connection, $ddname = "shop", $selected = -1)
{
    $ret = "<select name='$ddname' type='dropdown'>";
    $q = mysqli_query(
        $connection,
        "SELECT * FROM shops ORDER BY shopNAME ASC"
    );
    if ($selected == -1)
    {
        $first = 0;
    }
    else
    {
        $first = 1;
    }
    while ($r = mysqli_fetch_array($q))
    {
        $ret .= "\n<option value='{$r['shopID']}'";
        if ($selected == $r['shopID'] || $first == 0)
        {
            $ret .= " selected='selected'";
            $first = 1;
        }
        $ret .= ">{$r['shopNAME']}</option>";
    }
    $ret .= "\n</select>";
    return $ret;
}

function user_dropdown($connection, $ddname = "user", $selected = -1)
{
    $ret = "<select name='$ddname' type='dropdown'>";
    $q = mysqli_query(
        $connection,
        "SELECT * FROM users ORDER BY username ASC"
    );
    if ($selected == -1)
    {
        $first = 0;
    }
    else
    {
        $first = 1;
    }
    while ($r = mysqli_fetch_array($q))
    {
        $ret .= "\n<option value='{$r['userid']}'";
        if ($selected == $r['userid'] || $first == 0)
        {
            $ret .= " selected='selected'";
            $first = 1;
        }
        $ret .= ">{$r['username']}</option>";
    }
    $ret .= "\n</select>";
    return $ret;
}

function fed_user_dropdown($connection, $ddname = "user", $selected = -1)
{
    $ret = "<select name='$ddname' type='dropdown'>";
    $q = mysqli_query(
        $connection,
        "SELECT * FROM users WHERE fedjail=1 ORDER BY username ASC"
    );
    if ($selected == -1)
    {
        $first = 0;
    }
    else
    {
        $first = 1;
    }
    while ($r = mysqli_fetch_array($q))
    {
        $ret .= "\n<option value='{$r['userid']}'";
        if ($selected == $r['userid'] || $first == 0)
        {
            $ret .= " selected='selected'";
            $first = 1;
        }
        $ret .= ">{$r['username']}</option>";
    }
    $ret .= "\n</select>";
    return $ret;
}


function mysql_escape($str)
{
    global $c;
    return mysqli_real_escape_string($c, $str);
}

function get_rank($stat, $mykey)
{
    global $ir, $userid, $c;
    $query = "SELECT count(*) FROM userstats us LEFT JOIN users u ON us.userid=u.userid WHERE us.$mykey > $stat AND us.userid != $userid AND u.user_level != 0";
    $q = mysqli_query(
        $c,
        $query
    )or die(mysqli_error($c));
    return mysqli_data_seek($q, 0) + 1;
}

/**
 * Given a password input given by the user and their actual details,
 * determine whether the password entered was correct.
 *
 * Note that password-salt systems don't require the extra md5() on the $input.
 * This is only here to ensure backwards compatibility - that is,
 * a v2 game can be upgraded to use the password salt system without having
 * previously used it, without resetting every user's password.
 *
 * @param string $input The input password given by the user.
 * 						Should be without slashes.
 * @param string $salt 	The user's unique pass salt
 * @param string $pass	The user's encrypted password
 *
 * @return boolean	true for equal, false for not (login failed etc)
 *
 */
function verify_user_password($input, $salt, $pass)
{
    return ($pass === encode_password($input, $salt));
}

/**
 * Given a password and a salt, encode them to the form which is stored in
 * the game's database.
 *
 * @param string $password 		The password to be encoded
 * @param string $salt			The user's unique pass salt
 * @param boolean $already_md5	Whether the specified password is already
 * 								a md5 hash. This would be true for legacy
 * 								v2 passwords.
 *
 * @return string	The resulting encoded password.
 */
function encode_password($password, $salt, $already_md5 = false)
{
    if (!$already_md5)
    {
        $password = md5($password);
    }
    return md5($salt . $password);
}

/**
 * Generate a salt to use to secure a user's password
 * from rainbow table attacks.
 *
 * @return string	The generated salt, 8 alphanumeric characters
 */
function generate_pass_salt()
{
    return substr(md5(microtime_float()), 0, 8);
}

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float) $usec + (float) $sec);
}

/**
 *
 * @return string The URL of the game.
 */
function determine_game_urlbase()
{
    $domain = $_SERVER['HTTP_HOST'];
    $turi = $_SERVER['REQUEST_URI'];
    $turiq = '';
    for ($t = strlen($turi) - 1; $t >= 0; $t--)
    {
        if ($turi[$t] != '/')
        {
            $turiq = $turi[$t] . $turiq;
        }
        else
        {
            break;
        }
    }
    $turiq = '/' . $turiq;
    if ($turiq == '/')
    {
        $domain .= substr($turi, 0, -1);
    }
    else
    {
        $domain .= str_replace($turiq, '', $turi);
    }
    return $domain;
}

/**
 * Get the file size in bytes of a remote file, if we can.
 *
 * @param string $url	The url to the file
 *
 * @return int			The file's size in bytes, or 0 if we could
 * 						not determine its size.
 */

function get_filesize_remote($url)
{
    // Retrieve headers
    if (strlen($url) < 8)
    {
        return 0; // no file
    }
    $is_ssl = false;
    if (substr($url, 0, 7) == 'http://')
    {
        $port = 80;
    }
    else if (substr($url, 0, 8) == 'https://' && extension_loaded('openssl'))
    {
        $port = 443;
        $is_ssl = true;
    }
    else
    {
        return 0; // bad protocol
    }
    // Break up url
    $url_parts = explode('/', $url);
    $host = $url_parts[2];
    unset($url_parts[2]);
    unset($url_parts[1]);
    unset($url_parts[0]);
    $path = '/' . implode('/', $url_parts);
    if (strpos($host, ':') !== false)
    {
        $host_parts = explode(':', $host);
        if (count($host_parts) == 2 && ctype_digit($host_parts[1]))
        {
            $port = (int) $host_parts[1];
            $host = $host_parts[0];
        }
        else
        {
            return 0; // malformed host
        }
    }
    $request =
            "HEAD {$path} HTTP/1.1\r\n" . "Host: {$host}\r\n"
                    . "Connection: Close\r\n\r\n";
    $fh = fsockopen(($is_ssl ? 'ssl://' : '') . $host, $port);
    if ($fh === false)
    {
        return 0;
    }
    fwrite($fh, $request);
    $headers = array();
    $total_loaded = 0;
    while (!feof($fh) && $line = fgets($fh, 1024))
    {
        if ($line == "\r\n")
        {
            break;
        }
        if (strpos($line, ':') !== false)
        {
            list($key, $val) = explode(':', $line, 2);
            $headers[strtolower($key)] = trim($val);
        }
        else
        {
            $headers[] = strtolower($line);
        }
        $total_loaded += strlen($line);
        if ($total_loaded > 50000)
        {
            // Stop loading garbage!
            break;
        }
    }
    fclose($fh);
    if (!isset($headers['content-length']))
    {
        return 0;
    }
    return (int) $headers['content-length'];
}
// GPC fix: added in 1.1.1
if (version_compare(PHP_VERSION, '5.4.0-dev') < 0
        && function_exists('get_magic_quotes_gpc'))
{
    $_core_gpc_on = get_magic_quotes_gpc();
}
else
{
    $_core_gpc_on = false;
}
if (!$_core_gpc_on)
{
    foreach ($_POST as $k => $v)
    {
        $_POST[$k] = addslashes($v);
    }
    foreach ($_GET as $k => $v)
    {
        $_GET[$k] = addslashes($v);
    }
}
// Error reporting we want
@error_reporting(E_ALL & ~E_NOTICE);
// Tidy?
if (class_exists('tidy'))
{

    function tidy_test()
    {
        $html = ob_get_clean();

        // Specify configuration
        $config =
                array('indent' => true, 'output-xhtml' => true, 'wrap' => 200);

        // Tidy
        $tidy = new tidy;
        $tidy->parseString($html, $config, 'latin1');
        $tidy->cleanRepair();

        // Output
        echo $tidy;
    }
    ob_start();
    register_shutdown_function('tidy_test');
}

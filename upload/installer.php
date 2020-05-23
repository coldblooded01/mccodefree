<?php
/*
MCCodes FREE
installer.php Rev 1.1.0
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

if (file_exists('./installer.lock'))
{
    exit;
}
define('MONO_ON', 1);
session_name('MCCSID');
session_start();
if (!isset($_SESSION['started']))
{
    session_regenerate_id();
    $_SESSION['started'] = true;
}
require_once('installer_head.php');
require_once('global_func.php');
require_once('lib/installer_error_handler.php');

set_error_handler('error_php');
if (!isset($_GET['code']))
{
    $_GET['code'] = '';
}
switch ($_GET['code'])
{
case "install":
    install();
    break;
case "config":
    config();
    break;
default:
    diagnostics();
    break;
}

function menuprint($highlight)
{
    $items =
            array('diag' => '1. Diagnostics', 'input' => '2. Configuration',
                    'sql' => '3. Installation & Extras',);
    $c = 0;
    echo "<hr />";
    foreach ($items as $k => $v)
    {
        $c++;
        if ($c > 1)
        {
            echo ' >> ';
        }
        if ($k == $highlight)
        {
            echo '<span style="color: black;">' . $v . '</span>';
        }
        else
        {
            echo '<span style="color: gray;">' . $v . '</span>';
        }
    }
    echo '<hr />';
}

function diagnostics()
{
    menuprint("diag");
    if (version_compare(phpversion(), '7.0') < 0)
    {
        $pv = '<span style="color: red">Failed</span>';
        $pvf = 0;
    }
    else
    {
        $pv = '<span style="color: green">OK</span>';
        $pvf = 1;
    }
    if (is_writable('./'))
    {
        $wv = '<span style="color: green">OK</span>';
        $wvf = 1;
    }
    else
    {
        $wv = '<span style="color: red">Failed</span>';
        $wvf = 0;
    }
    if (function_exists('mysqli_connect'))
    {
        $dv = '<span style="color: green">OK</span>';
        $dvf = 1;
    }
    else
    {
        $dv = '<span style="color: red">Failed</span>';
        $dvf = 0;
    }
    echo "
    <h3>Basic Diagnostic Results:</h3>
    <table width='80%' border='1' cellspacing='1' cellpadding='1' align='center'>
    		<tr>
    			<td>PHP version >= 7.0</td>
    			<td>{$pv}</td>
    		</tr>
    		<tr>
    			<td>Game folder writable</td>
    			<td>{$wv}</td>
    		</tr>
    		<tr>
    			<td>MySQL support in PHP present</td>
    			<td>{$dv}</td>
    		</tr>
    		<tr>
    			<td>MCCodes up to date</td>
    			<td>
        			<iframe
        				src='http://www.mccodes.com/update_check.php?version=11000&amp;type=free'
        				width='250' height='30'></iframe>
        		</td>
    </table>
       ";
    if ($pvf + $wvf + $dvf < 3)
    {
        echo "
		<hr />
		<span style='color: red; font-weight: bold;'>
		One of the basic diagnostics failed, so Setup cannot continue.
		Please fix the ones that failed and try again.
		</span>
		<hr />
   		";
    }
    else
    {
        echo "
		<hr />
		&gt; <a href='installer.php?code=config'>Next Step</a>
		<hr />
   		";
    }
}

function config()
{
    menuprint("input");
    echo "
    <h3>Configuration:</h3>
    <form action='installer.php?code=install' method='post'>
    <table width='75%' class='table' cellspacing='1' cellpadding='1' align='center'>
    		<tr>
    			<th colspan='2'>Database Config</th>
    		</tr>
    		<tr>
    			<td align='center'>
    				Hostname<br />
    				<small>This is usually localhost</small>
    			</td>
    			<td><input type='text' name='hostname' value='localhost' /></td>
    		</tr>
    		<tr>
    			<td align='center'>
    				Username<br />
    				<small>The user must be able to use the database</small>
    			</td>
    			<td><input type='text' name='username' value='' /></td>
    		</tr>
    		<tr>
    			<td align='center'>Password</td>
    			<td><input type='text' name='password' value='' /></td>
    		</tr>
    		<tr>
    			<td align='center'>
    				Database Name<br />
    				<small>The database should not have any other software using it.</small>
    			</td>
    			<td><input type='text' name='database' value='' /></td>
    		</tr>
    		<tr>
    			<th colspan='2'>Game Config</th>
    		</tr>
    		<tr>
    			<td align='center'>Game Name</td>
    			<td><input type='text' name='game_name' /></td>
    		</tr>
    		<tr>
    			<td align='center'>
    				Game Owner<br />
    				<small>This can be your nick, real name, or a company</small>
    			</td>
    			<td><input type='text' name='game_owner' /></td>
    		</tr>
    		<tr>
    			<td align='center'>
    				Game Description<br />
    				<small>This is shown on the login page.</small>
    			</td>
    			<td><textarea rows='6' cols='40' name='game_description'></textarea></td>
    		</tr>
    		<tr>
    			<td align='center'>
    				PayPal Address<br />
    				<small>This is where the payments for game DPs go. Must be at least Premier.</small>
    			</td>
    			<td><input type='text' name='paypal' /></td>
    		</tr>
    		<tr>
    			<th colspan='2'>Admin User</th>
    		</tr>
    		<tr>
    			<td align='center'>Username</td>
    			<td><input type='text' name='a_username' /></td>
    		</tr>
    		<tr>
    			<td align='center'>Password</td>
    			<td><input type='password' name='a_password' /></td>
    		</tr>
    		<tr>
    			<td align='center'>Confirm Password</td>
    			<td><input type='password' name='a_cpassword' /></td>
    		</tr>
    		<tr>
    			<td align='center'>E-Mail</td>
    			<td><input type='text' name='a_email' /></td>
    		</tr>
    		<tr>
    			<td align='center'>Gender</td>
    			<td>
    				<select name='gender' type='dropdown'>
    					<option value='Male'>Male</option>
    					<option value='Female'>Female</option>
    				</select>
    			</td>
    		</tr>
    		<tr>
    			<td colspan='2' align='center'>
    				<input type='submit' value='Install' />
    			</td>
    		</tr>
    </table>
    </form>
       ";
}

function gpc_cleanup($text)
{
    return stripslashes($text);
}

function install()
{
    menuprint('sql');
    $paypal =
            (isset($_POST['paypal']) && valid_email($_POST['paypal']))
                    ? gpc_cleanup($_POST['paypal']) : '';
    $adm_email =
            (isset($_POST['a_email']) && valid_email($_POST['a_email']))
                    ? gpc_cleanup($_POST['a_email']) : '';
    $adm_username =
            (isset($_POST['a_username']) && strlen($_POST['a_username']) > 3)
                    ? gpc_cleanup($_POST['a_username']) : '';
    $adm_gender =
            (isset($_POST['gender'])
                    && in_array($_POST['gender'], array('Male', 'Female'),
                            true)) ? $_POST['gender'] : 'Male';
    $description =
            (isset($_POST['game_description']))
                    ? gpc_cleanup($_POST['game_description']) : '';
    $owner =
            (isset($_POST['game_owner']) && strlen($_POST['game_owner']) > 3)
                    ? gpc_cleanup($_POST['game_owner']) : '';
    $game_name =
            (isset($_POST['game_name'])) ? gpc_cleanup($_POST['game_name'])
                    : '';
    $adm_pswd =
            (isset($_POST['a_password']) && strlen($_POST['a_password']) > 3)
                    ? gpc_cleanup($_POST['a_password']) : '';
    $adm_cpswd =
            isset($_POST['a_cpassword']) ? gpc_cleanup($_POST['a_cpassword'])
                    : '';
    $db_hostname =
            isset($_POST['hostname']) ? gpc_cleanup($_POST['hostname']) : '';
    $db_username =
            isset($_POST['username']) ? gpc_cleanup($_POST['username']) : '';
    $db_password =
            isset($_POST['password']) ? gpc_cleanup($_POST['password']) : '';
    $db_database =
            isset($_POST['database']) ? gpc_cleanup($_POST['database']) : '';
    $errors = array();
    if (empty($db_hostname))
    {
        $errors[] = 'No Database hostname specified';
    }
    if (empty($db_username))
    {
        $errors[] = 'No Database username specified';
    }
    if (empty($db_database))
    {
        $errors[] = 'No Database database specified';
    }
    if (empty($adm_username)
            || !preg_match("/^[a-z0-9_]+([\\s]{1}[a-z0-9_]|[a-z0-9_])+$/i",
                    $adm_username))
    {
        $errors[] = 'Invalid admin username specified';
    }
    if (empty($adm_pswd))
    {
        $errors[] = 'Invalid admin password specified';
    }
    if ($adm_pswd !== $adm_cpswd)
    {
        $errors[] = 'The admin passwords did not match';
    }
    if (empty($adm_email))
    {
        $errors[] = 'Invalid admin email specified';
    }
    if (empty($owner)
            || !preg_match("/^[a-z0-9_]+([\\s]{1}[a-z0-9_]|[a-z0-9_])+$/i",
                    $owner))
    {
        $errors[] = 'Invalid game owner specified';
    }
    if (empty($game_name))
    {
        $errors[] = 'Invalid game name specified';
    }
    if (empty($description))
    {
        $errors[] = 'Invalid game description specified';
    }
    if (empty($paypal))
    {
        $errors[] = 'Invalid game PayPal specified';
    }
    if (count($errors) > 0)
    {
        echo "Installation failed.<br />
        There were one or more problems with your input.<br />
        <br />
        <b>Problem(s) encountered:</b>
        <ul>";
        foreach ($errors as $error)
        {
            echo "<li><span style='color: red;'>{$error}</span></li>";
        }
        echo "</ul>
        &gt; <a href='installer.php?code=config'>Go back to config</a>";
        require_once('installer_foot.php');
        exit;
    }
    // Try to establish DB connection first...
    echo 'Attempting DB connection...<br />';
    $c = mysqli_connect($db_hostname, $db_username, $db_password, $db_database);
    
    // Done, move on
    echo '... Successful.<br />';
    echo 'Writing game config file...<br />';
    echo 'Write DB Connector...<br />';
    $code = md5(rand(1, 100000000000));
    if (file_exists("mysql.php"))
    {
        unlink("mysql.php");
    }
    $e_db_hostname = addslashes($db_hostname);
    $e_db_username = addslashes($db_username);
    $e_db_password = addslashes($db_password);
    $e_db_database = addslashes($db_database);
    $config_file =
            <<<EOF
<?php
\$c = mysqli_connect('{$e_db_hostname}', '{$e_db_username}', '{$e_db_password}', '{$e_db_database}') or die(mysqli_error(\$c));

EOF;
    $f = fopen('mysql.php', 'w');
    fwrite($f, $config_file);
    fclose($f);
    echo '... file written.<br />';
    echo 'Writing base database schema...<br />';
    $fo = fopen("dbdata.sql", "r");
    $query = '';
    $lines = explode("\n", fread($fo, 1024768));
    fclose($fo);
    foreach ($lines as $line)
    {
        if (!(strpos($line, "--") === 0) && trim($line) != '')
        {
            $query .= $line;
            if (!(strpos($line, ";") === FALSE))
            {
                mysqli_query($c, $query);
                $query = '';
            }
        }
    }
    echo '... done.<br />';
    echo 'Writing game configuration...<br />';
    $ins_username = mysqli_real_escape_string(
        $c,
        htmlentities($adm_username, ENT_QUOTES, 'ISO-8859-1')
    );
    $salt = generate_pass_salt();
    $e_salt = mysqli_real_escape_string($c, $salt);
    $encpsw = encode_password($adm_pswd, $salt);
    $e_encpsw = mysqli_real_escape_string($c, $encpsw);
    $ins_email = mysqli_real_escape_string($c, $adm_email);
    $IP = mysqli_real_escape_string($c, $_SERVER['REMOTE_ADDR']);
    $ins_game_name = htmlentities($game_name, ENT_QUOTES, 'ISO-8859-1');
    $ins_game_desc =
            nl2br(htmlentities($description, ENT_QUOTES, 'ISO-8859-1'));
    $ins_game_owner = htmlentities($owner, ENT_QUOTES, 'ISO-8859-1');
    $ins_game_id1name =
            htmlentities($adm_username, ENT_QUOTES, 'ISO-8859-1');
    mysqli_query(
        $c,
            "INSERT INTO `users`
             (`username`, `login_name`, `userpass`, `level`, `money`,
             `crystals`, `donatordays`, `user_level`, `energy`, `maxenergy`,
             `will`, `maxwill`, `brave`, `maxbrave`, `hp`, `maxhp`, `location`,
             `gender`, `signedup`, `email`, `bankmoney`, `lastip`,
             `pass_salt`)
             VALUES ('{$ins_username}', '{$ins_username}', '{$e_encpsw}', 1,
             100, 0, 0, 2, 12, 12, 100, 100, 5, 5, 100, 100, 1,
             '{$adm_gender}', " . time()
                    . ", '{$ins_email}', -1, '$IP',
             '{$e_salt}')") or die(mysqli_error($c));
    $i = mysqli_insert_id($c);
    mysqli_query(
        $c,
        "INSERT INTO `userstats`
             VALUES($i, 10, 10, 10, 10, 10)"
        );
    
    require_once(dirname(__FILE__) . "/models/setting.php");
    $game_name = Setting::create('GAME_NAME', $ins_game_name);
    $game_description = Setting::create('GAME_DESCRIPTION', $ins_game_desc);
    $game_owner = Setting::create('GAME_OWNER', $ins_game_owner);
    $paypal = Setting::create('PAYPAL', $paypal);
    $id1_name = Setting::create('ID1_NAME', $ins_game_id1name);
    $cron_code = Setting::create('CRON_CODE', $code);
    

    echo '... Done.<br />';
    $path = dirname($_SERVER['SCRIPT_FILENAME']);
    echo "
    <h2>Installation Complete!</h2>
    <hr />
    <h3>Cron Info</h3>
    <br />
    This is the cron info you need for section <b>1.2 Cronjobs</b> of the installation instructions.<br />
    <pre>
    */5 * * * * php $path/crons/cron_fivemins.php $code
    * * * * * php $path/crons/cron_minute.php $code
    0 * * * * php $path/crons/cron_hour.php $code
    0 0 * * * php $path/crons/cron_day.php $code
    </pre>
       ";
    echo "<h3>Installer Security</h3>
    Attempting to remove installer... ";
    @unlink('./installer.php');
    $success = !file_exists('./installer.php');
    echo "<span style='color: "
            . ($success ? "green;'>Succeeded" : "red;'>Failed")
            . "</span><br />";
    if (!$success)
    {
        echo "Attempting to lock installer... ";
        @touch('./installer.lock');
        $success2 = file_exists('installer.lock');
        echo "<span style='color: "
                . ($success2 ? "green;'>Succeeded" : "red;'>Failed")
                . "</span><br />";
        if ($success2)
        {
            echo "<span style='font-weight: bold;'>"
                    . "You should now remove dbdata.sql, installer.php, installer_foot.php and installer_home.php from your server."
                    . "</span>";
        }
        else
        {
            echo "<span style='font-weight: bold; font-size: 20pt;'>"
                    . "YOU MUST REMOVE dbdata.sql, installer.php, "
                    . "installer_foot.php and installer_home.php from your server.<br />"
                    . "Failing to do so will allow other people "
                    . "to run the installer again and potentially "
                    . "mess up your game entirely." . "</span>";
        }
    }
    else
    {
        require_once('installer_foot.php');
        @unlink('./installer_head.php');
        @unlink('./installer_foot.php');
        @unlink('./dbdata.sql');
        exit;
    }
}

function file_update($file, $from, $to)
{
    $fct = file_get_contents($file);
    $fct = str_replace($from, $to, $fct);
    $fh = fopen($file, 'w');
    if ($fh !== false)
    {
        fwrite($fh, $fct);
        fclose($fh);
    }
}
//thx to http://www.phpit.net/code/valid-email/ for valid_email

function valid_email($email)
{
    // First, we check that there's one @ symbol, and that the lengths are right
    if (preg_match("/^[^@]{1,64}@[^@]{1,255}$/s", $email) == 0)
    {
        // Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
        return false;
    }
    // Split it into sections to make life easier
    $email_array = explode("@", $email);
    $local_array = explode(".", $email_array[0]);
    for ($i = 0; $i < sizeof($local_array); $i++)
    {
        if (preg_match(
                "/^(([A-Za-z0-9!#$%&'*+\\/=?^_`{|}~-][A-Za-z0-9!#$%&'*+\\/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$/s",
                $local_array[$i]) == 0)
        {
            return false;
        }
    }
    if (preg_match("/^\[?[0-9\.]+\]?$/s", $email_array[1]) == 0)
    { // Check if domain is IP. If not, it should be valid domain name
        $domain_array = explode(".", $email_array[1]);
        if (sizeof($domain_array) < 2)
        {
            return false; // Not enough parts to domain
        }
        for ($i = 0; $i < sizeof($domain_array); $i++)
        {
            if (preg_match(
                    "/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$/s",
                    $domain_array[$i]) == 0)
            {
                return false;
            }
        }
    }
    return true;
}
require_once('installer_foot.php');

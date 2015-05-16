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

session_start();
require "includes/global_func.php";
if ($_SESSION['loggedin'] == 0)
{
    header("Location: login.php");
    exit;
}
$userid = $_SESSION['userid'];
require "header.php";
$h = new headers;
$h->startheaders();
include "includes/mysql.php";
global $c;
$is =
        mysqli_query($c,
                "SELECT u.*,us.* FROM users u LEFT JOIN userstats us ON u.userid=us.userid WHERE u.userid=$userid") or die(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
$ir = mysqli_fetch_array($is);
check_level();
$fm = money_formatter($ir['money']);
$cm = money_formatter($ir['crystals'], '');
$lv = date('F j, Y, g:i a', $ir['laston']);
$h->userdata($ir, $lv, $fm, $cm);
$h->menuarea();
print "<h3>Cyber Bank</h3>";
if ($ir['cybermoney'] > -1)
{
    switch ($_GET['action'])
    {
    case "deposit":
        deposit();
        break;

    case "withdraw":
        withdraw();
        break;

    default:
        index();
        break;
    }

}
else
{
    if (isset($_GET['buy']))
    {
        if ($ir['money'] > 9999999)
        {
            print
                    "Congratulations, you bought a bank account for \$10,000,000!<br />
<a href='cyberbank.php'>Start using my account</a>";
            mysqli_query(
                    $c, 
                    "UPDATE users SET money=money-10000000,cybermoney=0 WHERE userid=$userid");
        }
        else
        {
            print
                    "You do not have enough money to open an account.
<a href='explore.php'>Back to town...</a>";
        }
    }
    else
    {
        print
                "Open a bank account today, just \$10,000,000!<br />
<a href='cyberbank.php?buy'>&gt; Yes, sign me up!</a>";
    }
}

function index()
{
    global $ir, $c, $userid, $h;
    print
            "\n<b>You currently have ". money_formatter ($ir['cybermoney']) . "in the bank.</b><br />
At the end of each day, your bank balance will go up by 7%.<br />
<table width='75%' border='2'> <tr> <td width='50%'><b>Deposit Money</b><br />
It will cost you 15% of the money you deposit, rounded up. The maximum fee is \$1,500,000.<form action='cyberbank.php?action=deposit' method='post'>
Amount: <input type='text' name='deposit' value='{$ir['money']}' /><br />
<input type='submit' value='Deposit' /></form></td> <td>
<b>Withdraw Money</b><br />
It will cost you 7.5% of the money you withdraw, rounded up. The maximum fee is \$750,000.<form action='cyberbank.php?action=withdraw' method='post'>
Amount: <input type='text' name='withdraw' value='{$ir['cybermoney']}' /><br />
<input type='submit' value='Withdraw' /></form></td> </tr> </table>";
}

function deposit()
{
    global $ir, $c, $userid, $h;
    $_POST['deposit'] = abs((int) $_POST['deposit']);
    if ($_POST['deposit'] > $ir['money'])
    {
        print "You do not have enough money to deposit this amount.";
    }
    else
    {
        $fee = ceil($_POST['deposit'] * 15 / 100);
        if ($fee > 1500000)
        {
            $fee = 1500000;
        }
        $gain = $_POST['deposit'] - $fee;
        $ir['cybermoney'] += $gain;
        mysqli_query($c,
                "UPDATE users SET cybermoney=cybermoney+$gain, money=money-{$_POST['deposit']} where userid=$userid");
        print
                "You hand over \${$_POST['deposit']} to be deposited, <br />
after the fee is taken (\$$fee), \$$gain is added to your account. <br />
<b>You now have \${$ir['cybermoney']} in the Cyber Bank.</b><br />
<a href='cyberbank.php'>&gt; Back</a>";
    }
}

function withdraw()
{
    global $ir, $c, $userid, $h;
    $_POST['withdraw'] = abs((int) $_POST['withdraw']);
    if ($_POST['withdraw'] > $ir['cybermoney'])
    {
        print "You do not have enough banked money to withdraw this amount.";
    }
    else
    {
        $fee = ceil($_POST['withdraw'] * 75 / 1000);
        if ($fee > 750000)
        {
            $fee = 750000;
        }
        $gain = $_POST['withdraw'] - $fee;
        $ir['cybermoney'] -= $gain;
        mysqli_query($c,
                "UPDATE users SET cybermoney=cybermoney-$gain, money=money+$gain where userid=$userid");
        print
                "You ask to withdraw $gain, <br />
the teller hands it over after she takes the bank fees. <br />
<b>You now have \${$ir['cybermoney']} in the Cyber Bank.</b><br />
<a href='cyberbank.php'>&gt; Back</a>";
    }
}
$h->endpage();

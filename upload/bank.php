<?php
/*
MCCodes FREE
bank.php Rev 1.1.0
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
$user = User::get($userid);
require "header.php";
$h = new Header();
$h->startheaders();

$user->check_level();
$h->userdata($user);
$h->menuarea();
print "<h3>Bank</h3>";
if ($user->bank_money > -1)
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
        if ($user->money > 49999)
        {
            print
                    "Congratulations, you bought a bank account for \$50,000!<br />
<a href='bank.php'>Start using my account</a>";
            $user->buy_bank_account();
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
                "Open a bank account today, just \$50,000!<br />
<a href='bank.php?buy'>&gt; Yes, sign me up!</a>";
    }
}

function index()
{
    global $user;
    print
            "\n<b>You currently have \${$user->bank_money} in the bank.</b><br />
At the end of each day, your bank balance will go up by 2%.<br />
<table width='75%' border='2'> <tr> <td width='50%'><b>Deposit Money</b><br />
It will cost you 15% of the money you deposit, rounded up. The maximum fee is \$3,000.<form action='bank.php?action=deposit' method='post'>
Amount: <input type='text' name='deposit' value='{$user->money}' /><br />
<input type='submit' value='Deposit' /></form></td> <td>
<b>Withdraw Money</b><br />
There is no fee on withdrawals.<form action='bank.php?action=withdraw' method='post'>
Amount: <input type='text' name='withdraw' value='{$user->bank_money}' /><br />
<input type='submit' value='Withdraw' /></form></td> </tr> </table>";
}

function deposit()
{
    global $user;
    $_POST['deposit'] = abs((int) $_POST['deposit']);
    $deposit = $_POST['deposit'];
    if ($deposit > $user->money)
    {
        print "You do not have enough money to deposit this amount.";
    }
    else
    {
        $fee = $user->bank_deposit($deposit);
        $final_amount = $deposit - $fee;
        print
                "You hand over \${$deposit} to be deposited, <br />
after the fee is taken (\$$fee), \$$final_amount is added to your account. <br />
<b>You now have \${$user->bank_money} in the bank.</b><br />
<a href='bank.php'>&gt; Back</a>";
    }
}

function withdraw()
{
    global $user;
    $_POST['withdraw'] = abs((int) $_POST['withdraw']);
    $withdraw = $_POST['withdraw'];
    if ($withdraw > $user->bank_money)
    {
        print "You do not have enough banked money to withdraw this amount.";
    }
    else
    {
        $user->bank_withdraw($withdraw);
        print
                "You ask to withdraw $withdraw, <br />
the banking lady grudgingly hands it over. <br />
<b>You now have \${$user->bank_money} in the bank.</b><br />
<a href='bank.php'>&gt; Back</a>";
    }
}
$h->endpage();

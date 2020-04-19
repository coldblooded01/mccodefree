<?php
if (!defined('IN_STAFF'))
{
    header('HTTP/1.1 400 Bad Request');
    exit;
}

require_once(dirname(__FILE__) . "/models/event.php");

// Admin/Secretary/Assistant

function fed_user_form()
{
    global $ir, $c, $h, $userid;
    print 
            "<h3>Jailing User</h3>
The user will be put in fed jail and will be unable to do anything in the game.<br />
<form action='new_staff.php?action=fedsub' method='post'>
User: " . user_dropdown($c, 'user', $_GET['XID'])
                    . "<br />
Days: <input type='text' name='days' /><br />
Reason: <input type='text' name='reason' /><br />
<input type='submit' value='Jail User' /></form>";
}

function fed_user_submit()
{
    global $ir, $c, $h, $userid;
    $ins_user = abs((int) $_POST['user']);
    $ins_days = abs((int) $_POST['days']);
    $ins_reason = mysqli_real_escape_string(
        $c,
        htmlentities(
            stripslashes($_POST['reason']),
            ENT_QUOTES,
            'ISO-8859-1'
        )
    );
    $q = mysqli_query($c, "SELECT * FROM users WHERE userid={$ins_user}");
    if (mysqli_num_rows($q) == 0)
    {
        return;
    }
    $r = mysqli_fetch_array($q);
    if (($ir['user_level'] != 2)
            && ($r['user_level'] == 2 || $r['user_level'] == 3))
    {
        print "You cannot jail other staff.";
    }
    else
    {
        $re = mysqli_query(
            $c,
            "UPDATE users SET fedjail=1 WHERE userid={$ins_user}"
        );
        if (mysql_affected_rows($c))
        {
            mysqli_query(
                $c,
                "INSERT INTO fedjail VALUES(NULL,{$ins_user},{$ins_days},$userid,'{$ins_reason}')"
            );
        }
        mysqli_query(
            $c,
            "INSERT INTO jaillogs VALUES(NULL,$userid, {$ins_user}, {$ins_days}, '{$ins_reason}',"
                . time() . ")"
        );
        print "User jailed.";
    }
}

function unfed_user_form()
{
    global $ir, $c, $h, $userid;
    print 
            "<h3>Unjailing User</h3>
The user will be taken out of fed jail.<br />
<form action='new_staff.php?action=unfedsub' method='post'>
User: " . fed_user_dropdown($c, 'user')
                    . "<br />
<input type='submit' value='Unjail User' /></form>";
}

function unfed_user_submit()
{
    global $ir, $c, $h, $userid;
    $ins_user = abs((int) $_POST['user']);
    mysqli_query($c, "UPDATE users SET fedjail=0 WHERE userid={$ins_user}");
    mysqli_query($c,"DELETE FROM fedjail WHERE fed_userid={$ins_user}");
    mysqli_query(
        $c,
        "INSERT INTO unjaillogs VALUES(NULL,$userid, {$ins_user}, "
            . time() . ")"
    );
    print "User unjailed.";
}

function view_attack_logs()
{
    global $ir, $c, $h, $userid;
    print 
            "<h3>Attack Logs</h3>
<table width=75%><tr style='background:gray'><th>Time</th><th>Detail</th></tr>";
    $q = mysqli_query($c, "SELECT * FROM attacklogs ORDER BY time DESC");
    while ($r = mysqli_fetch_array($q))
    {
        print 
                "<tr><td>" . date('F j, Y, g:i:s a', $r['time'])
                        . "</td><td>{$r['attacker']} attacked {$r['attacked']} and {$r['result']} and stole \${$r['stole']}</td></tr>";
    }
    print "</table>";
}

function ip_search_form()
{
    global $ir, $c, $h, $userid;
    print 
            "<h3>IP Search</h3>
<form action='new_staff.php?action=ipsub' method='post'>
IP: <input type='text' name='ip' value='...' /><br />
<input type='submit' value='Search' /></form>";
}

function ip_search_submit()
{
    global $ir, $c, $h, $userid;
    $disp_ip = htmlentities(stripslashes($_POST['ip']), ENT_QUOTES, 'ISO-8859-1');
    $mysql_ip = mysqli_real_escape_string($c, stripslashes($_POST['ip']));
    print 
            "Searching for users with the IP: <b>{$disp_ip}</b><br />
<table width=75%><tr style='background:gray'> <th>User</th> <th>Level</th> <th>Money</th> </tr>";
    $q = mysqli_query($c, "SELECT * FROM users WHERE lastip='{$mysql_ip}'");
    $ids = array();
    while ($r = mysqli_fetch_array($q))
    {
        $ids[] = $r['userid'];
        print 
                "\n<tr> <td> <a href='viewuser.php?u={$r['userid']}'>{$r['username']}</a></td> <td> {$r['level']}</td> <td>{$r['money']}</td> </tr>";
    }
    print 
            "</table><br />
<b>Mass Jail</b><br />
<form action='new_staff.php?action=massjailip' method='post'>
<input type='hidden' name='ids' value='" . implode(",", $ids)
                    . "' /> Days: <input type='text' name='days' value='300' /> <br />
Reason: <input type='text' name='reason' value='Same IP users, Mail fedjail@monocountry.com with your case.' /><br />
<input type='submit' value='Mass Jail' /></form>";
}

function mass_jail()
{
    global $ir, $c, $h, $userid;
    $ids = explode(",", $_POST['ids']);
    $ins_days = abs((int) $_POST['days']);
    $ins_reason = mysqli_real_escape_string(
        $c,
        htmlentities(
            stripslashes($_POST['reason']),
            ENT_QUOTES,
            'ISO-8859-1'
        )
    );
    foreach ($ids as $id)
    {
        if (ctype_digit($id))
        {
            $q = mysqli_query($c, "SELECT * FROM users WHERE userid=$id");
            if (mysqli_num_rows($q) == 0)
            {
                continue;
            }
            $r = mysqli_fetch_array($q);
            if (($ir['user_level'] != 2)
                    && ($r['user_level'] == 2 || $r['user_level'] == 3))
            {
                print "You cannot jail other staff.";
            }
            else
            {
                $re = mysqli_query(
                    $c,
                    "UPDATE users SET fedjail=1 WHERE userid={$id}"
                );
                if (mysql_affected_rows($c))
                {
                    mysqli_query(
                        $c,
                        "INSERT INTO fedjail VALUES(NULL,{$id},{$ins_days},$userid,'{$ins_reason}')"
                    );
                }
                mysqli_query(
                    $c,
                    "INSERT INTO jaillogs VALUES(NULL,$userid, {$id}, {$ins_days}, '{$ins_reason}',"
                        . time() . ")"
                );
                print "User jailed : $id.";

            }
        }
    }
}

function view_itm_logs()
{
    global $ir, $c, $h, $userid;
    print 
            "<h3>Item Xfer Logs</h3>
<table width=75%><tr style='background:gray'><th>Time</th><th>Detail</th></tr>";
    $q =
            mysqli_query(
                    "SELECT ix.*,u1.username as sender, u2.username as sent,i.itmname as item
                    FROM itemxferlogs ix
                    LEFT JOIN users u1 ON ix.ixFROM=u1.userid
                    LEFT JOIN users u2 ON ix.ixTO=u2.userid
                    LEFT JOIN items i ON i.itmid=ix.ixITEM
                    ORDER BY ix.ixTIME DESC", $c);
    while ($r = mysqli_fetch_array($q))
    {
        print 
                "<tr><td>" . date("F j, Y, g:i:s a", $r['ixTIME'])
                        . "</td><td>{$r['sender']} sent {$r['ixQTY']}  {$r['item']}(s) to {$r['sent']} </td></tr>";
    }
    print "</table>";
}

function view_cash_logs()
{
    global $ir, $c, $h, $userid;
    print 
            "<h3>Cash Xfer Logs</h3>
<table width=75% border=1> <tr style='background:gray'> <th>ID</th> <th>Time</th> <th>User From</th> <th>User To</th> <th>Multi?</th> <th>Amount</th> <th>&nbsp;</th> </tr>";
    $q =
            mysqli_query(
                    "SELECT cx.*,u1.username as sender, u2.username as sent FROM cashxferlogs cx LEFT JOIN users u1 ON cx.cxFROM=u1.userid LEFT JOIN users u2 ON cx.cxTO=u2.userid ORDER BY cx.cxTIME DESC",
                    $c)
            or die(
                    mysqli_error($c) . "<br />"
                            . "SELECT cx.*,u1.username as sender, u2.username as sent FROM cashxferlogs cx LEFT JOIN users u1 ON cx.cxFROM=u1.userid LEFT JOIN users u2 ON cx.cxTO=u2.userid ORDER BY cx.cxTIME DESC");
    while ($r = mysqli_fetch_array($q))
    {
        if ($r['cxFROMIP'] == $r['cxTOIP'])
        {
            $m = "<span style='color:red;font-weight:800'>MULTI</span>";
        }
        else
        {
            $m = "";
        }
        print 
                "<tr><td>{$r['cxID']}</td> <td>"
                        . date("F j, Y, g:i:s a", $r['cxTIME'])
                        . "</td><td><a href='viewuser.php?u={$r['cxFROM']}'>{$r['sender']}</a> [{$r['cxFROM']}] (IP: {$r['cxFROMIP']}) </td><td><a href='viewuser.php?u={$r['cxTO']}'>{$r['sent']}</a> [{$r['cxTO']}] (IP: {$r['cxTOIP']}) </td> <td>$m</td> <td> \${$r['cxAMOUNT']}</td> <td> [<a href='new_staff.php?action=fedform&XID={$r['cxFROM']}'>Jail Sender</a>] [<a href='new_staff.php?action=fedform&XID={$r['cxTO']}'>Jail Receiver</a>]</td> </tr>";
    }
    print "</table>";
}

// Admin or Secretary

function give_item_form()
{
    global $ir, $c;
    print 
            "<h3>Giving Item To User</h3>
<form action='new_staff.php?action=giveitemsub' method='post'>
User: " . user_dropdown($c, 'user') . "<br />
Item: " . item_dropdown($c, 'item')
                    . "<br />
Quantity: <input type='text' name='qty' value='1' /><br />
<input type='submit' value='Give Item' /></form>";
}

function give_item_submit()
{
    global $ir, $c;
    $_POST['item'] = abs(@intval($_POST['item']));
    $_POST['user'] = abs(@intval($_POST['user']));
    $_POST['qty'] = abs(@intval($_POST['qty']));
    $d = mysqli_query(
        $c,
        "SELECT COUNT(itmid) FROM items WHERE itmid={$_POST['item']}"
    );
    if (mysqli_data_seek($d, 0, 0) == 0)
    {
        print "There is no such item.";
        return;
    }
    mysqli_query(
        $c,
        "INSERT INTO inventory VALUES(NULL,{$_POST['item']},{$_POST['user']},{$_POST['qty']})"
    ) or die(mysqli_error($c));
    print 
            "You gave {$_POST['qty']} of item ID {$_POST['item']} to user ID {$_POST['user']}";
}

function mail_user_form()
{
    global $ir, $c, $h, $userid;
    print 
            "<h3>Mail Banning User</h3>
The user will be banned from the mail system.<br />
<form action='new_staff.php?action=mailsub' method='post'>
User: " . user_dropdown($c, 'user', $_GET['XID'])
                    . "<br />
Days: <input type='text' name='days' /><br />
Reason: <input type='text' name='reason' /><br />
<input type='submit' value='Mailban User' /></form>";
}

function mail_user_submit()
{
    global $ir, $c, $h, $userid;
    $ins_user = abs((int) $_POST['user']);
    $ins_days = abs((int) $_POST['days']);
    $ins_reason = mysqli_real_escape_string(
        $c,
        htmlentities(
            stripslashes($_POST['reason']),
            ENT_QUOTES,
            'ISO-8859-1'
        )
    );
    $log_reason = stripslashes($_POST['reason']);
    $re = mysqli_query(
        $c,
        "UPDATE users SET mailban={$ins_days},mb_reason='{$ins_reason}' WHERE userid={$ins_user}"
    );
    Event::add(
        $ins_user,
        "You were banned from mail for {$ins_days} day(s) for the following reason: {$log_reason}"
    );
    print "User mail banned.";
}

function inv_user_begin()
{
    global $ir, $c, $h, $userid;

    print 
            "<h3>Viewing User Inventory</h3>
You may browse this user's inventory.<br />
<form action='new_staff.php?action=invuser' method='post'>
User: " . user_dropdown($c, 'user')
                    . "<br />
<input type='submit' value='View Inventory' /></form>";
}

function inv_user_view()
{
    global $ir, $c, $h, $userid;
    $test_user = abs((int) $_POST['user']);
    $inv =
            mysqli_query(
                    "SELECT iv.*,i.*,it.* FROM inventory iv
                    LEFT JOIN items i ON iv.inv_itemid=i.itmid
                    LEFT JOIN itemtypes it ON i.itmtype=it.itmtypeid
                    WHERE iv.inv_userid={$test_user}", $c);
    if (mysqli_num_rows($inv) == 0)
    {
        print "<b>This person has no items!</b>";
    }
    else
    {
        print 
                "<b>Their items are listed below.</b><br />
<table width=100%><tr style='background-color:gray;'><th>Item</th><th>Sell Value</th><th>Total Sell Value</th><th>Links</th></tr>";
        while ($i = mysqli_fetch_array($inv))
        {
            print "<tr><td>{$i['itmname']}";
            if ($i['inv_qty'] > 1)
            {
                print "&nbsp;x{$i['inv_qty']}";
            }
            print "</td><td>\${$i['itmsellprice']}</td><td>";
            print "$" . ($i['itmsellprice'] * $i['inv_qty']);
            print 
                    "</td><td>[<a href='new_staff.php?action=deleinv&ID={$i['inv_id']}'>Delete</a>]";
            print "</td></tr>";
        }
        print "</table>";
    }
}

function inv_delete()
{
    global $ir, $c, $h, $userid;
    $del_id = abs((int) $_GET['ID']);
    mysqli_query("DELETE FROM inventory WHERE inv_id={$del_id}", $c);
    print "Item deleted from inventory.";
}

function credit_user_form()
{
    global $ir, $c, $h, $userid;
    print 
            "<h3>Crediting User</h3>
You can give a user money/crystals.<br />
<form action='new_staff.php?action=creditsub' method='post'>
User: " . user_dropdown($c, 'user')
                    . "<br />
Money: <input type='text' name='money' /> Crystals: <input type='text' name='crystals' /><br />
<input type='submit' value='Credit User' /></form>";
}

function credit_user_submit()
{
    global $ir, $c, $h, $userid;
    $_POST['money'] = (int) $_POST['money'];
    $_POST['crystals'] = (int) $_POST['crystals'];
    $cred_user = abs((int) $_POST['user']);
    mysqli_query(
            "UPDATE users u SET money=money+{$_POST['money']}, crystals=crystals+{$_POST['crystals']} WHERE u.userid={$cred_user}",
            $c);
    print "User credited.";
}

function view_mail_logs()
{
    global $ir, $c, $h, $userid;
    $_GET['st'] = abs((int) $_GET['st']);
    $rpp = 100;

    print 
            "<h3>Mail Logs</h3>
<table width=75% border=2> \n<tr style='background:gray'> <th>ID</th> <th>Time</th> <th>User From</th> <th>User To</th> <th width>Subj</th> <th width=30%>Msg</th> <th>&nbsp;</th> </tr>";
    $q =
            mysqli_query(
                    "SELECT m.*,u1.username as sender, u2.username as sent FROM mail m LEFT JOIN users u1 ON m.mail_from=u1.userid LEFT JOIN users u2 ON m.mail_to=u2.userid WHERE m.mail_from != 0 ORDER BY m.mail_time DESC LIMIT {$_GET['st']},$rpp",
                    $c)
            or die(
                    mysqli_error($c) . "<br />"
                            . "SELECT cx.*,u1.username as sender, u2.username as sent FROM cashxferlogs cx LEFT JOIN users u1 ON cx.cxFROM=u1.userid LEFT JOIN users u2 ON cx.cxTO=u2.userid ORDER BY cx.cxTIME DESC LIMIT {$_GET['st']},$rpp");
    while ($r = mysqli_fetch_array($q))
    {
        print 
                "\n<tr><td>{$r['mail_id']}</td> <td>"
                        . date("F j, Y, g:i:s a", $r['mail_time'])
                        . "</td><td>{$r['sender']} [{$r['mail_from']}] </td> <td>{$r['sent']} [{$r['mail_to']}] </td> \n<td> {$r['mail_subject']}</td> \n<td>{$r['mail_text']}</td> <td> [<a href='new_staff.php?action=mailform&XID={$r['mail_from']}'>MailBan Sender</a>] [<a href='new_staff.php?action=mailform&XID={$r['mail_to']}'>MailBan Receiver</a>]</td> </tr>";
    }
    print "</table><br />
";
    $q2 = mysqli_query("SELECT mail_id FROM mail WHERE mail_from != 0", $c);
    $rs = mysqli_num_rows($q2);
    $pages = ceil($rs / 20);
    print "Pages: ";
    for ($i = 1; $i <= $pages; $i++)
    {
        $st = ($i - 1) * 20;
        print "<a href='new_staff.php?action=maillogs&st=$st'>$i</a>&nbsp;";
        if ($i % 7 == 0)
        {
            print "<br />\n";
        }
    }
}

function reports_view()
{
    global $ir, $c, $h, $userid;
    print 
            "<h3>Player Reports</h3>
<table width=80%><tr style='background:gray'><th>Reporter</th> <th>Offender</th> <th>What they did</th> <th>&nbsp;</th> </tr>";
    $q =
            mysqli_query(
                    "SELECT pr.*,u1.username as reporter, u2.username as offender FROM preports pr LEFT JOIN users u1 ON u1.userid=pr.prREPORTER LEFT JOIN users u2 ON u2.userid=pr.prREPORTED ORDER BY pr.prID DESC",
                    $c) or die(mysqli_error($c));
    while ($r = mysqli_fetch_array($q))
    {
        $report =
                nl2br(htmlentities($r['prTEXT'], ENT_QUOTES, 'ISO-8859-1'));
        print 
                "\n<tr>
                		<td><a href='viewuser.php?u={$r['prREPORTER']}'>{$r['reporter']}</a> [{$r['prREPORTER']}]</td>
                		<td><a href='viewuser.php?u={$r['prREPORTED']}'>{$r['offender']}</a> [{$r['prREPORTED']}]</td>
                		<td>{$report}</td>
                		<td><a href='new_staff.php?action=repclear&ID={$r['prID']}'>Clear</a></td>
                </tr>";
    }
    print "</table>";
}

function report_clear()
{
    global $ir, $c, $h, $userid;
    $_GET['ID'] = abs((int) $_GET['ID']);
    mysqli_query("DELETE FROM preports WHERE prID={$_GET['ID']}", $c);
    print 
            "Report cleared and deleted!<br />
<a href='new_staff.php?action=reportsview'>&gt; Back</a>";
}

// Admins Only

function new_user_form()
{
    global $ir, $c;
    print 
            "Adding a new user.<br />
<form action='new_staff.php?action=newusersub' method='post'>
Username: <input type='text' name='username' /><br />
Login Name: <input type='text' name='login_name' /><br />
Email: <input type='text' name='email' /><br />
Password: <input type='text' name='userpass' /><br />
Type: <input type='radio' name='user_level' value='0' />NPC <input type='radio' name='user_level' value='1' checked='checked' />Regular Member<br />
Level: <input type='text' name='level' value='1' /><br />
Money: <input type='text' name='money' value='100' /><br />
Crystals: <input type='text' name='crystals' value='0' /><br />
Donator Days: <input type='text' name='donatordays' value='0' /><br />
Gender: <select name='gender' type='dropdown'><option>Male</option><option>Female</option></select><br />
<br />
<b>Stats</b><br />
Strength: <input type='text' name='strength' value='10' /><br />
Agility: <input type='text' name='agility' value='10' /><br />
Guard: <input type='text' name='guard' value='10' /><br />
Labour: <input type='text' name='labour' value='10' /><br />
IQ: <input type='text' name='labour' value='10' /><br />
<br />
<input type='submit' value='Create User' /></form>";
}

function new_user_submit()
{
    global $ir, $c, $userid;
    if (!isset($_POST['username']) || !isset($_POST['login_name'])
            || !isset($_POST['userpass']))
    {
        print 
                "You missed one or more of the required fields. Please go back and try again.<br />
<a href='new_staff.php?action=newuser'>&gt; Back</a>";
        $h->endpage();
        exit;
    }
    $level = abs((int) $_POST['level']);
    $money = abs((int) $_POST['money']);
    $crystals = abs((int) $_POST['crystals']);
    $donator = abs((int) $_POST['donatordays']);
    $ulevel = abs((int) $_POST['user_level']);
    $strength = abs((int) $_POST['strength']);
    $agility = abs((int) $_POST['agility']);
    $guard = abs((int) $_POST['guard']);
    $labour = abs((int) $_POST['labour']);
    $iq = abs((int) $_POST['iq']);
    $energy = 10 + $level * 2;
    $brave = 3 + $level * 2;
    $hp = 50 + $level * 50;
    $username = mysqli_real_escape_string(
        $c,
        strip_tags(stripslashes($_POST['username']))
    );
    $loginname = mysqli_real_escape_string(
        $c,
        strip_tags(stripslashes($_POST['login_name']))
    );
    $password = stripslashes($_POST['userpass']);
    $salt = generate_pass_salt();
    $enc_psw = encode_password($password, $salt, false);
    $i_salt = mysqli_real_escape_string($c, $salt);
    $i_encpsw = mysqli_real_escape_string($c, $enc_psw);
    $email = mysqli_real_escape_string(
        $c,
        strip_tags(stripslashes($_POST['email']))
    );
    $gender = (isset($_POST['gender'])
                && in_array($_POST['gender'], array('Male', 'Female')))
                    ? $_POST['gender'] : 'Male';
    mysqli_query(
        $c,
        "INSERT INTO users (username, login_name, userpass, level, money, crystals, donatordays,
             user_level, energy, maxenergy, will, maxwill, brave, maxbrave, hp, maxhp, location, gender,
              signedup, email, bankmoney, pass_salt)
        VALUES( '{$username}', '{$loginname}', '{$i_encpsw}', $level,
               $money, $crystals, $donator, $ulevel, $energy, $energy, 100, 100, $brave, $brave, $hp, $hp, 1,
                '{$gender}', " . time() . ", '{$email}', -1, '{$i_salt}')"
    );
    $i = mysqli_insert_id($c);
    mysqli_query(
        $c,
        "INSERT INTO userstats VALUES($i, $strength, $agility, $guard, $labour, $iq)"
    );
    print "User created!";
}

function new_item_form()
{
    global $ir, $c;
    print 
            "<h3>Adding an item to the game</h3><form action='new_staff.php?action=newitemsub' method='post'>
Item Name: <input type='text' name='itmname' value='' /><br />
Item Desc.: <input type='text' name='itmdesc' value='' /><br />
Item Type: " . itemtype_dropdown($c, 'itmtype')
                    . "<br />
Item Buyable: <input type='checkbox' name='itmbuyable' checked='checked' /><br />
Item Price: <input type='text' name='itmbuyprice' /><br />
Item Sell Value: <input type='text' name='itmsellprice' /><br /><br />
<b>Specialized</b><br />
Item Energy Regen (food only): <input type='text' name='energy' value='1' /><br />
Item Health Regen (medical only): <input type='text' name='health' value='10' /><br />
Power (weapons only): <input type='text' name='damage' value='10' /><br />
Damage Off (armor only): <input type='text' name='Defence' value='10' /><br />
<input type='submit' value='Add Item To Game' /></form>";
}

function new_item_submit()
{
    global $ir, $c, $h;
    if (!isset($_POST['itmname']) || !isset($_POST['itmdesc'])
            || !isset($_POST['itmtype']) || !isset($_POST['itmbuyprice'])
            || !isset($_POST['itmsellprice']))
    {
        print 
                "You missed one or more of the fields. Please go back and try again.<br />
<a href='new_staff.php?action=newitem'>&gt; Back</a>";
        $h->endpage();
        exit;
    }
    $itmname = mysqli_real_escape_string(
        $c,
        strip_tags(stripslashes($_POST['itmname']))
    );
    $itmdesc = mysqli_real_escape_string(
        $c,
        strip_tags(stripslashes($_POST['itmdesc']))
    );
    if ($_POST['itmbuyable'] == 'on')
    {
        $itmbuy = 1;
    }
    else
    {
        $itmbuy = 0;
    }
    // verify item type
    $itmtype = abs(@intval($_POST['itmtype']));
    $itq = mysqli_query(
        $c,
        "SELECT COUNT(`itmtypeid`) FROM itemtypes WHERE `itmtypeid` = {$itmtype}"
    );
    if (mysqli_data_seek($itq, 0, 0) == 0)
    {
        print 
                "That item type doesn't exist.<br />
<a href='new_staff.php?action=newitem'>&gt; Back</a>";
        $h->endpage();
        exit;
    }
    $itmbuyp = abs(@intval($_POST['itmbuyprice']));
    $itmsellp = abs(@intval($_POST['itmsellprice']));
    $m = mysqli_query(
        $c,
        "INSERT INTO items VALUES(NULL,{$itmtype},'$itmname','$itmdesc',
            {$itmbuyp},{$itmsellp},$itmbuy)"
    ) or die(mysqli_error($c));
    if ($_POST['itmtype'] == 1)
    {
        $stat = abs(@intval($_POST['energy']));
        $i = mysqli_insert_id();
        mysqli_query( $c, "INSERT INTO food VALUES($i,{$stat})")
            or die(mysqli_error($c));
    }
    if ($_POST['itmtype'] == 3 || $_POST['itmtype'] == 4)
    {
        $stat = abs(@intval($_POST['damage']));
        $i = mysqli_insert_id();
        mysqli_query($c, "INSERT INTO weapons VALUES($i,{$stat})")
            or die(mysqli_error($c));
    }
    if ($_POST['itmtype'] == 5)
    {
        $stat = abs(@intval($_POST['health']));
        $i = mysqli_insert_id();
        mysqli_query($c, "INSERT INTO medical VALUES($i,{$stat})")
            or die(mysqli_error($c));
    }
    if ($_POST['itmtype'] == 7)
    {
        $stat = abs(@intval($_POST['Defence']));
        $i = mysqli_insert_id();
        mysqli_query($c, "INSERT INTO armour VALUES($i,{$stat})")
            or die(mysqli_error($c));
    }
    print "The {$_POST['itmname']} Item was added to the game.";
}

function kill_item_form()
{
    global $ir, $c, $h, $userid;

    print 
            "<h3>Deleting Item</h3>
The item will be permanently removed from the game.<br />
<form action='new_staff.php?action=killitemsub' method='post'>
Item: " . item_dropdown($c, 'item')
                    . "<br />
<input type='submit' value='Kill Item' /></form>";
}

function kill_item_submit()
{
    global $ir, $c, $h, $userid;
    $_POST['item'] = abs(@intval($_POST['item']));
    $d = mysqli_query(
        $c,
        "SELECT * FROM items WHERE itmid={$_POST['item']}"
    );
    if (mysqli_num_rows($d) == 0)
    {
        print "There is no such item.";
        return;
    }
    $itemi = mysqli_fetch_array($d);
    mysqli_query($c, "DELETE FROM items WHERE itmid={$_POST['item']}");
    mysqli_query($c, "DELETE FROM shopitems WHERE sitemITEMID={$_POST['item']}");
    mysqli_query($c, "DELETE FROM inventory WHERE inv_itemid={$_POST['item']}");
    mysqli_query($c, "DELETE FROM food WHERE item_id={$_POST['item']}");
    mysqli_query($c, "DELETE FROM weapons WHERE item_id={$_POST['item']}");
    mysqli_query($c, "DELETE FROM medical WHERE item_id={$_POST['item']}");
    mysqli_query($c, "DELETE FROM armour WHERE item_ID={$_POST['item']}");
    mysqli_query($c, "DELETE FROM itemmarket WHERE imITEM={$_POST['item']}");
    print "The {$itemi['itmname']} Item was removed from the game.";
}

function edit_item_begin()
{
    global $ir, $c, $h, $userid;
    print 
            "<h3>Editing Item</h3>
You can edit any aspect of this item.<br />
<form action='new_staff.php?action=edititemform' method='post'>
Item: " . item_dropdown($c, 'item')
                    . "<br />
<input type='submit' value='Edit Item' /></form>";
}

function edit_item_form()
{
    global $ir, $c, $h;
    $_POST['item'] = abs(@intval($_POST['item']));
    $d = mysqli_query($c, "SELECT * FROM items WHERE itmid={$_POST['item']}");
    if (mysqli_num_rows($d) == 0)
    {
        print "There is no such item.";
        return;
    }
    $itemi = mysqli_fetch_array($d);
    $f = mysqli_query($c, "SELECT * FROM food WHERE item_id={$_POST['item']}");
    if (mysqli_num_rows($f) > 0)
    {
        $a = mysqli_fetch_array($f);
        $energy = $a['energy'];
    }
    else
    {
        $energy = 1;
    }
    $f = mysqli_query($c, "SELECT * FROM medical WHERE item_id={$_POST['item']}");
    if (mysqli_num_rows($f) > 0)
    {
        $a = mysqli_fetch_array($f);
        $health = $a['health'];
    }
    else
    {
        $health = 10;
    }
    $f = mysqli_query($c, "SELECT * FROM weapons WHERE item_id={$_POST['item']}");
    if (mysqli_num_rows($f) > 0)
    {
        $a = mysqli_fetch_array($f);
        $damage = $a['damage'];
    }
    else
    {
        $damage = 1;
    }
    $f = mysqli_query($c, "SELECT * FROM armour WHERE item_ID={$_POST['item']}");
    if (mysqli_num_rows($f) > 0)
    {
        $a = mysqli_fetch_array($f);
        $def = $a['Defence'];
    }
    else
    {
        $def = 10;
    }
    print 
            "<h3>Editing Item</h3>
<form action='new_staff.php?action=edititemsub' method='post'>
<input type='hidden' name='itmid' value='{$_POST['item']}' />
Item Name: <input type='text' name='itmname' value='{$itemi['itmname']}' /><br />
Item Desc.: <input type='text' name='itmdesc' value='{$itemi['itmdesc']}' /><br />
Item Type: " . itemtype_dropdown($c, 'itmtype', $itemi['itmtype'])
                    . "<br />
Item Buyable: <input type='checkbox' name='itmbuyable'";
    if ($itemi['itmbuyable'])
    {
        print " checked='checked'";
    }
    print 
            " /><br />
Item Price: <input type='text' name='itmbuyprice' value='{$itemi['itmbuyprice']}' /><br />
Item Sell Value: <input type='text' name='itmsellprice' value='{$itemi['itmsellprice']}'/><br /><br />
<b>Specialized</b><br />
Item Energy Regen (food only): <input type='text' name='energy' value='$energy' /><br />
Item Health Regen (medical only): <input type='text' name='health' value='$health' /><br />
Power (weapons only): <input type='text' name='damage' value='$damage' /><br />
Damage Off (armor only): <input type='text' name='Defence' value='$def' /><br />
<input type='submit' value='Edit Item' /></form>";
}

function edit_item_sub()
{
    global $ir, $c, $h, $userid;

    if (!isset($_POST['itmname']) || !isset($_POST['itmdesc'])
            || !isset($_POST['itmtype']) || !isset($_POST['itmbuyprice'])
            || !isset($_POST['itmsellprice']))
    {
        print 
                "You missed one or more of the fields. Please go back and try again.<br />
<a href='new_staff.php?action=edititem'>&gt; Back</a>";
        $h->endpage();
        exit;
    }
    $itmid = abs(@intval($_POST['itmid']));
    $iq = mysqli_query($c, "SELECT COUNT(`itmid`) FROM items WHERE `itmid` = {$itmid}");
    if (mysqli_data_seek($iq, 0) == 0)
    {
        print 
                "That item doesn't exist.<br />
<a href='new_staff.php?action=edititem'>&gt; Back</a>";
        $h->endpage();
        exit;
    }
    $itmname = mysqli_real_escape_string(
        $c,
        strip_tags(stripslashes($_POST['itmname']))
    );
    $itmdesc = mysqli_real_escape_string(
        $c,
        strip_tags(stripslashes($_POST['itmdesc']))
    );
    if ($_POST['itmbuyable'] == 'on')
    {
        $itmbuy = 1;
    }
    else
    {
        $itmbuy = 0;
    }
    // verify item type
    $itmtype = abs(@intval($_POST['itmtype']));
    $itq = mysqli_query(
        $c,
        "SELECT COUNT(`itmtypeid`) FROM itemtypes WHERE `itmtypeid` = {$itmtype}"
    );
    if (mysqli_data_seek($itq, 0) == 0)
    {
        print 
                "That item type doesn't exist.<br />
<a href='new_staff.php?action=edititem'>&gt; Back</a>";
        $h->endpage();
        exit;
    }
    $itmbuyp = abs(@intval($_POST['itmbuyprice']));
    $itmsellp = abs(@intval($_POST['itmsellprice']));
    mysqli_query($c, "DELETE FROM items WHERE itmid={$itmid}");
    mysqli_query($c, "DELETE FROM food WHERE item_id={$itmid}");
    mysqli_query($c, "DELETE FROM weapons WHERE item_id={$itmid}");
    mysqli_query($c, "DELETE FROM medical WHERE item_id={$itmid}");
    mysqli_query($c, "DELETE FROM armour WHERE item_ID={$itmid}");
    $m = mysqli_query(
        $c,
        "INSERT INTO items VALUES('{$itmid}',{$itmtype},'$itmname',
            '$itmdesc',{$itmbuyp},{$itmsellp},$itmbuy)"
    ) or die(mysqli_error($c));
    if ($_POST['itmtype'] == 1)
    {
        $stat = abs(@intval($_POST['energy']));
        mysqli_query(
            $c,
            "INSERT INTO food VALUES({$itmid},{$stat})"
        ) or die(mysqli_error($c));
    }
    if ($_POST['itmtype'] == 5)
    {
        $stat = abs(@intval($_POST['health']));
        mysqli_query(
            $c,
            "INSERT INTO medical VALUES({$itmid},{$stat})"
        ) or die(mysqli_error($c));
    }
    if ($_POST['itmtype'] == 3 || $_POST['itmtype'] == 4)
    {
        $stat = abs(@intval($_POST['damage']));
        mysqli_query(
            $c,
            "INSERT INTO weapons VALUES({$itmid},{$stat})"
        ) or die(mysqli_error($c));
    }
    if ($_POST['itmtype'] == 7)
    {
        $stat = abs(@intval($_POST['Defence']));
        $i = mysqli_insert_id();
        mysqli_query(
            $c,
            "INSERT INTO armour VALUES({$itmid},{$stat})"
        ) or die(mysqli_error($c));
    }
    print "The {$_POST['itmname']} Item was edited successfully.";
}

function new_shop_form()
{
    global $ir, $c, $h;
    print 
            "<h3>Adding a New Shop</h3>
<form action='new_staff.php?action=newshopsub' method='post'>
Shop Name: <input type='text' name='sn' value='' /><br />
Shop Desc: <input type='text' name='sd' value='' /><br />
Shop Location: " . location_dropdown($c, "sl")
                    . "<br />
<input type='submit' value='Create Shop' /></form>";
}

function new_shop_submit()
{
    global $ir, $c, $h;
    if (!isset($_POST['sn']) || !isset($_POST['sd']))
    {
        print 
                "You missed a field, go back and try again.<br />
<a href='new_staff.php?action=newitem'>&gt; Back</a>";
    }
    else
    {
        $sn = mysqli_real_escape_string(
            $c,
            strip_tags(stripslashes($_POST['sn']))
        );
        $sd = mysqli_real_escape_string(
            $c,
            strip_tags(stripslashes($_POST['sd']))
        );
        $location = abs(@intval($_POST['sl']));
        // Verify location
        $locq = mysqli_query(
            $c,
            "SELECT COUNT(`cityid`) FROM cities WHERE `cityid` = {$location}"
        );
        if (mysqli_data_seek($locq, 0) == 0)
        {
            print 
                    "That location doesn't exist.<br />
<a href='new_staff.php?action=newshop'>&gt; Back</a>";
            $h->endpage();
            exit;
        }
        mysqli_query($c, "INSERT INTO shops VALUES(NULL,{$location},'$sn','$sd')");
        print "The $sn Shop was successfully added to the game.";
    }
}

function new_stock_form()
{
    global $ir, $c, $h;
    print 
            "<h3>Adding an item to a shop</h3>
<form action='new_staff.php?action=newstocksub' method='post'>
Shop: " . shop_dropdown($c, "shop") . "<br />
Item: " . item_dropdown($c, "item")
                    . "<br />
<input type='submit' value='Add Item To Shop' /></form>";
}

function new_stock_submit()
{
    global $ir, $c, $h;
    $shop = abs(@intval($_POST['shop']));
    $item = abs(@intval($_POST['item']));
    // Verify details
    $shopq = mysqli_query(
        $c,
        "SELECT COUNT(`shopID`) FROM shops WHERE `shopID` = {$shop}"
    );
    if (mysqli_data_seek($shopq, 0) == 0)
    {
        print 
                "That shop doesn't exist.<br />
<a href='new_staff.php?action=newstock'>&gt; Back</a>";
        $h->endpage();
        exit;
    }
    $itemq = mysqli_query(
        $c,
        "SELECT COUNT(`itmid`) FROM items WHERE `itmid` = {$item}"
    );
    if (mysqli_data_seek($itemq, 0) == 0)
    {
        print 
                "That item doesn't exist.<br />
<a href='new_staff.php?action=newstock'>&gt; Back</a>";
        $h->endpage();
        exit;
    }
    mysqli_query($c, "INSERT INTO shopitems VALUES(NULL,{$shop},{$item})");
    print "Item ID {$item} was successfully added to shop ID {$shop}";
}

function edit_user_begin()
{
    global $ir, $c, $h, $userid;
    print 
            "<h3>Editing User</h3>
You can edit any aspect of this user. <br />
<form action='new_staff.php?action=edituserform' method='post'>
User: " . user_dropdown($c, 'user')
                    . "<br />
<input type='submit' value='Edit User' /></form>
OR enter a user ID to edit:
<form action='new_staff.php?action=edituserform' method='post'>
User: <input type='text' name='user' value='0' /><br />
<input type='submit' value='Edit User' /></form>";
}

function edit_user_form()
{
    global $ir, $c, $h, $userid;
    $user = abs(@intval($_POST['user']));
    $d = mysqli_query(
        $c,
        "SELECT u.*,us.* FROM users u LEFT JOIN userstats us on u.userid=us.userid WHERE u.userid={$user}"
    );
    if (mysqli_num_rows($d) == 0)
    {
        print 
                "That user doesn't exist.<br />
                &gt; <a href='new_staff.php?action=edituser'>Try again</a>";
        return;
    }
    $itemi = mysqli_fetch_array($d);
    $snbit = htmlentities($itemi['staffnotes'], ENT_QUOTES, 'ISO-8859-1');
    print 
            "<h3>Editing User</h3>
<form action='new_staff.php?action=editusersub' method='post'>
<input type='hidden' name='userid' value='{$_POST['user']}' />
Username: <input type='text' name='username' value='{$itemi['username']}' /><br />
Login Name: <input type='text' name='login_name' value='{$itemi['login_name']}' /><br />
Duties: <input type='text' name='duties' value='{$itemi['duties']}' /><br />
Staff Notes: <br />
<textarea rows='7' cols='60' name='staffnotes'>{$snbit}</textarea><br />
Level: <input type='text' name='level' value='{$itemi['level']}' /><br />
Money: \$<input type='text' name='money' value='{$itemi['money']}' /><br />
Bank: \$<input type='text' name='bankmoney' value='{$itemi['bankmoney']}' /><br />
Cyber Bank: \$<input type='text' name='cybermoney' value='{$itemi['cybermoney']}' /><br />
Crystals: <input type='text' name='crystals' value='{$itemi['crystals']}' /><br />
Mail Ban: <input type='text' name='mailban' value='{$itemi['mailban']}' /><br />
Mail Ban Reason: <input type='text' name='mb_reason' value='{$itemi['mb_reason']}' /><br />
Hospital time: <input type='text' name='hospital' value='{$itemi['hospital']}' /><br />
Hospital reason: <input type='text' name='hospreason' value='{$itemi['hospreason']}' /><br />
<h4>Stats</h4>
Strength: <input type='text' name='strength' value='{$itemi['strength']}' /><br />
Agility: <input type='text' name='agility' value='{$itemi['agility']}' /><br />
Guard: <input type='text' name='guard' value='{$itemi['guard']}' /><br />
Labour: <input type=user'text' name='labour' value='{$itemi['labour']}' /><br />
IQ: <input type='text' name='IQ' value='{$itemi['IQ']}' /><br />
<input type='submit' value='Edit User' /></form>";
}

function edit_user_sub()
{

    global $ir, $c, $h, $userid;
    $go = 0;
    $user = abs(@intval($_POST['userid']));
    if (!isset($_POST['level']))
    {
        $go = 1;
    }
    if (!isset($_POST['money']))
    {
        $go = 1;
    }
    if (!isset($_POST['bankmoney']))
    {
        $go = 1;
    }
    if (!isset($_POST['crystals']))
    {
        $go = 1;
    }
    if (!isset($_POST['strength']))
    {
        $go = 1;
    }
    if (!isset($_POST['agility']))
    {
        $go = 1;
    }
    if (!isset($_POST['guard']))
    {
        $go = 1;
    }
    if (!isset($_POST['labour']))
    {
        $go = 1;
    }
    if (!isset($_POST['IQ']))
    {
        $go = 1;
    }
    if (!isset($_POST['username']))
    {
        $go = 1;
    }
    if (!isset($_POST['login_name']))
    {
        $go = 1;
    }
    if ($go)
    {
        $_POST['user'] = $_POST['userid'];
        print "You did not fully fill out the form.";
        edit_user_form();
    }
    else
    {
        $_POST['level'] = (int) $_POST['level'];
        $_POST['strength'] = abs((int) $_POST['strength']);
        $_POST['agility'] = abs((int) $_POST['agility']);
        $_POST['guard'] = abs((int) $_POST['guard']);
        $_POST['labour'] = abs((int) $_POST['labour']);
        $_POST['IQ'] = abs((int) $_POST['IQ']);
        $_POST['money'] = (int) $_POST['money'];
        $_POST['bankmoney'] = (int) $_POST['bankmoney'];
        $_POST['cybermoney'] = (int) $_POST['cybermoney'];
        $_POST['crystals'] = (int) $_POST['crystals'];
        $_POST['mailban'] = (int) $_POST['mailban'];
        $_POST['hospital'] = abs((int) $_POST['hospital']);
        $username = mysqli_real_escape_string(
            $c,
            strip_tags(
                stripslashes($_POST['username'])
            )
        );
        $loginname = mysqli_real_escape_string(
            $c,
            strip_tags(
                stripslashes($_POST['login_name'])
            )
        );
        $duties = mysqli_real_escape_string(
            $c,
            strip_tags(
                stripslashes($_POST['duties'])
            )
        );
        $staffnotes = mysqli_real_escape_string(
            $c,
            strip_tags(
                stripslashes($_POST['staffnotes'])
            )
        );
        $mb_reason = mysqli_real_escape_string(
            $c,
            strip_tags(
                stripslashes($_POST['mb_reason'])
            )
        );
        $hospreason = mysqli_real_escape_string(
            $c,
            strip_tags(
                stripslashes($_POST['hospreason'])
            )
        );
        //check for username usage
        $u = mysqli_query(
            $c,
            "SELECT * FROM users WHERE username='{$username}' and userid != {$userid}"
        );
        if (mysqli_num_rows($u) != 0)
        {
            print "That username is in use, choose another.";
            print 
                    "<br /><a href='new_staff.php?action=edituser'>&gt; Back</a>";
            $h->endpage();
            exit;
        }
        $oq = mysqli_query($c, "SELECT * FROM users WHERE userid={$userid}");
        if (mysqli_num_rows($oq) == 0)
        {
            print 'That user doesn\'t exist.';
            print 
                    "<br /><a href='new_staff.php?action=edituser'>&gt; Back</a>";
            $h->endpage();
            exit;
        }
        $rm = mysqli_fetch_array($oq);
        $energy = 10 + $_POST['level'] * 2;
        $nerve = 3 + $_POST['level'] * 2;
        $hp = 50 + $_POST['level'] * 50;
        mysqli_query(
            $c,
            "UPDATE users 
                SET username='{$username}', level={$_POST['level']},
                    money={$_POST['money']}, crystals={$_POST['crystals']}, energy=$energy, brave=$nerve,
                    maxbrave=$nerve, maxenergy=$energy, hp=$hp, maxhp=$hp, hospital={$_POST['hospital']},
                    duties='{$duties}', staffnotes='{$staffnotes}', mailban={$_POST['mailban']},
                    mb_reason='{$mb_reason}', hospreason='{$hospreason}',
                    login_name='{$loginname}'
                WHERE userid={$userid}"
        );
        mysqli_query(
            $c,
            "UPDATE userstats
                SET strength={$_POST['strength']}, agility={$_POST['agility']},
                    guard={$_POST['guard']}, labour={$_POST['labour']}, IQ={$_POST['IQ']}
                WHERE userid={$userid}"
        );

        print "User edited....";

    }
}

function fed_edit_form()
{
    global $ir, $c, $h, $userid;
    print 
            "<h3>Editing Fedjail Reason</h3>
You are editing a player's sentence in fed jail.<br />
<form action='new_staff.php?action=fedesub' method='post'>
User: " . fed_user_dropdown($c, 'user')
                    . "<br />
Days: <input type='text' name='days' /><br />
Reason: <input type='text' name='reason' /><br />
<input type='submit' value='Jail User' /></form>";
}

function fed_edit_submit()
{
    global $ir, $c, $h, $userid;
    $ins_user = abs((int) $_POST['user']);
    $ins_days = abs((int) $_POST['days']);
    $ins_reason = mysqli_real_escape_string(
        $c,
        htmlentities(
            stripslashes($_POST['reason']),
            ENT_QUOTES,
            'ISO-8859-1'
        )
    );
    mysqli_query($c, "DELETE FROM fedjail WHERE fed_userid={$ins_user}");

    mysqli_query(
        $c,
        "INSERT INTO fedjail VALUES(NULL,{$ins_user},{$ins_days},$userid,'{$ins_reason}')"
    );
    mysqli_query(
        $c,
        "INSERT INTO jaillogs VALUES(NULL,$userid, {$ins_user}, {$ins_days}, '{$ins_reason}',"
            . time() . ")"
    );
    print "User's sentence edited.";
}

function newspaper_form()
{
    global $ir, $c, $h, $userid;
    $q = mysqli_query($c, "SELECT * FROM papercontent LIMIT 1");
    $news = htmlentities(mysqli_data_seek($q, 0), ENT_QUOTES, 'ISO-8859-1');
    print 
            "<h3>Editing Newspaper</h3><form action='new_staff.php?action=subnews' method='post'>
<textarea rows='7' cols='35' name='newspaper'>$news</textarea><br /><input type='submit' value='Change' /></form>";
}

function newspaper_submit()
{
    global $ir, $c, $h, $userid;
    $news = mysqli_real_escape_string($c, stripslashes($_POST['newspaper']));
    mysqli_query($c, "UPDATE papercontent SET content='$news'");
    print "Newspaper updated!";
}

function donators_list()
{
    global $ir, $c, $h, $userid;

    print 
            "<h3>Donations</h3>
This lists the donations that need to be checked with our records and processed.<br />
<table width=75%><tr style='background:gray'><th>ID</th><th>Donator</th><th>Time</th><th>&nbsp;</th></tr>";
    $q = mysqli_query(
        $c,
        "SELECT u.*,d.* FROM dps_process d LEFT JOIN users u ON u.userid=d.dp_userid"
    );
    while ($r = mysqli_fetch_array($q))
    {
        print 
                "<tr><td>{$r['dp_id']}</td><td><a href='viewuser.php?u={$r['userid']}'>{$r['username']}</td><td>"
                        . date('F j, Y, g:i:s a', $r['dp_time'])
                        . "</td><td><a href='new_staff.php?action=acceptdp&ID={$r['dp_id']}'>Accept</a> | <a href='new_staff.php?action=declinedp&ID={$r['dp_id']}'>Decline</a></td></tr>";
    }
}

function accept_dp()
{
    global $ir, $c, $h, $userid;
    $acc_id = abs((int) $_GET['ID']);
    $q = mysqli_query($c, "SELECT * FROM dps_process WHERE dp_id={$acc_id}");
    $r = mysqli_fetch_array($q);
    if ($r['dp_type'] == 'standard')
    {
        mysqli_query(
            $c,
            "UPDATE users u 
                LEFT JOIN userstats us ON u.userid=us.userid
                SET u.money=u.money+5000,u.crystals=u.crystals+50,
                    us.IQ=us.IQ+50,u.donatordays=u.donatordays+30
                WHERE u.userid={$r['dp_userid']}"
        );
    }
    else if ($r['dp_type'] == 'crystals')
    {
        mysqli_query(
            $c,
            "UPDATE users u LEFT JOIN userstats us ON u.userid=us.userid SET u.crystals=u.crystals+100,u.donatordays=u.donatordays+30 WHERE u.userid={$r['dp_userid']}"
        );
    }
    else if ($r['dp_type'] == 'iq')
    {
        mysqli_query(
            $c,
            "UPDATE users u LEFT JOIN userstats us ON u.userid=us.userid SET
us.IQ=us.IQ+120,u.donatordays=u.donatordays+30 WHERE u.userid={$r['dp_userid']}"
        );
    }
    else if ($r['dp_type'] == 'fivedollars')
    {
        mysqli_query(
            $c,
            "UPDATE users u LEFT JOIN userstats us ON u.userid=us.userid SET u.money=u.money+15000,u.crystals=u.crystals+75,
us.IQ=us.IQ+80,u.donatordays=u.donatordays+55 WHERE u.userid={$r['dp_userid']}"
        );
    }
    else if ($r['dp_type'] == 'tendollars')
    {
        mysqli_query(
            $c,
            "UPDATE users u LEFT JOIN userstats us ON u.userid=us.userid SET u.money=u.money+35000,u.crystals=u.crystals+160,
us.IQ=us.IQ+180,u.donatordays=u.donatordays+115 WHERE u.userid={$r['dp_userid']}"
        );
        mysqli_query(
            $c,
            "INSERT INTO inventory VALUES(NULL,12,{$r['dp_userid']},1)"
        );
    }
    mysqli_query($c, "DELETE FROM dps_process WHERE dp_id={$_GET['ID']}");
    Event::add(
        $r['dp_userid'],
        "Your Donation has been accepted and credited."
    );
    print "Donation accepted and credited to user.";
}

function decline_dp()
{
    global $ir, $c, $h, $userid;
    $del_id = abs((int) $_GET['ID']);
    $q = mysqli_query($c, "SELECT * FROM dps_process WHERE dp_id={$del_id}");
    $r = mysqli_fetch_array($q);
    mysqli_query($c, "DELETE FROM dps_process WHERE dp_id={$del_id}");
    Event::add($r['dp_userid'], "Your Donation has been rejected.");
    print "Donation rejected.";
}

function give_dp_form()
{
    global $ir, $c, $h, $userid;
    print 
            "<h3>Giving User DP</h3>
The user will receive the benefits of one 30-day donator pack.<br />
<form action='new_staff.php?action=givedpsub' method='post'>
User: " . user_dropdown($c, 'user')
                    . "<br />
<input type='radio' name='type' value='1' /> Pack 1 (Standard)<br />
<input type='radio' name='type' value='2' /> Pack 2 (Crystals)<br />
<input type='radio' name='type' value='3' /> Pack 3 (IQ)<br />
<input type='radio' name='type' value='4' /> Pack 4 (5.00)<br />
<input type='radio' name='type' value='5' /> Pack 5 (10.00)<br />
<input type='submit' value='Give User DP' /></form>";
}

function give_dp_submit()
{
    global $ir, $c, $h, $userid;
    $dp_user = abs((int) $_POST['user']);
    if ($_POST['type'] == 1)
    {
        mysqli_query(
            $c,
            "UPDATE users u LEFT JOIN userstats us ON u.userid=us.userid SET u.money=u.money+5000,u.crystals=u.crystals+50,
us.IQ=us.IQ+50,u.donatordays=u.donatordays+30 WHERE u.userid={$dp_user}"
        );
        $d = 30;
    }
    else if ($_POST['type'] == 2)
    {
        mysqli_query(
            $c,
            "UPDATE users u LEFT JOIN userstats us ON u.userid=us.userid SET u.crystals=u.crystals+100,
                u.donatordays=u.donatordays+30 WHERE u.userid={$dp_user}"
        );
        $d = 30;
    }
    else if ($_POST['type'] == 3)
    {
        mysqli_query(
            $c,
            "UPDATE users u LEFT JOIN userstats us ON u.userid=us.userid SET
us.IQ=us.IQ+120,u.donatordays=u.donatordays+30 WHERE u.userid={$dp_user}"
        );
        $d = 30;
    }
    else if ($_POST['type'] == 4)
    {
        mysqli_query(
            $c,
            "UPDATE users u LEFT JOIN userstats us ON u.userid=us.userid SET u.money=u.money+15000,u.crystals=u.crystals+75,
us.IQ=us.IQ+80,u.donatordays=u.donatordays+55 WHERE u.userid={$dp_user}"
        );
        $d = 55;
    }
    else if ($_POST['type'] == 5)
    {
        mysqli_query(
            $c,
            "UPDATE users u LEFT JOIN userstats us ON u.userid=us.userid SET u.money=u.money+35000,u.crystals=u.crystals+160,
us.IQ=us.IQ+180,u.donatordays=u.donatordays+115 WHERE u.userid={$dp_user}"
        );
        mysqli_query($c, "INSERT INTO inventory VALUES(NULL,12,{$dp_user},1)");
        $d = 115;
    }
    $esc_type =
            htmlentities(stripslashes($_POST['type']), ENT_QUOTES,
                    'ISO-8859-1');
    Event::add(
        $dp_user,
        "You were given one $d -day donator pack (Pack {$esc_type}) from the administration."
    );
    print "User given a DP.";
}

function staff_list()
{
    global $ir, $c, $h, $userid;

    print "<h3>Staff Management</h3>";
    print 
            "<b>Admins</b><br />
<table width=80%><tr style='background:gray'> <th>User</th> <th>Online?</th> <th>Links</th> </tr>";
    $q = mysqli_query(
        $c,
        "SELECT * FROM users WHERE user_level=2 ORDER BY userid ASC"
    );
    while ($r = mysqli_fetch_array($q))
    {
        if ($r['laston'] >= time() - 15 * 60)
        {
            $on = "<font color=green><b>Online</b></font>";
        }
        else
        {
            $on = "<font color=red><b>Offline</b></font>";
        }
        print 
                "\n<tr> <td><a href='viewuser.php?u={$r['userid']}'>{$r['username']}</a> [{$r['userid']}]</td> <td>$on</td> <td><a href='new_staff.php?action=userlevel&amp;level=3&amp;ID={$r['userid']}' >Secretary</a> &middot; <a href='new_staff.php?action=userlevel&amp;level=4&amp;ID={$r['userid']}' >IRC Op</a> &middot; <a href='new_staff.php?action=userlevel&amp;level=5&amp;ID={$r['userid']}' >Assistant</a> &middot; <a href='new_staff.php?action=userlevel&amp;level=1&amp;ID={$r['userid']}' >Member</a></td></tr>";
    }
    print "</table>";
    print 
            "<b>Secretaries</b><br />
<table width=80%><tr style='background:gray'> <th>User</th> <th>Online?</th> <th>Links</th> </tr>";
    $q = mysqli_query(
        $c,
        "SELECT * FROM users WHERE user_level=3 ORDER BY userid ASC"
    );
    while ($r = mysqli_fetch_array($q))
    {
        if ($r['laston'] >= time() - 15 * 60)
        {
            $on = "<font color=green><b>Online</b></font>";
        }
        else
        {
            $on = "<font color=red><b>Offline</b></font>";
        }
        print 
                "\n<tr> <td><a href='viewuser.php?u={$r['userid']}'>{$r['username']}</a> [{$r['userid']}]</td> <td>$on</td> <td><a href='new_staff.php?action=userlevel&amp;level=2&amp;ID={$r['userid']}' >Admin</a> &middot; <a href='new_staff.php?action=userlevel&amp;level=4&amp;ID={$r['userid']}' >IRC Op</a> &middot; <a href='new_staff.php?action=userlevel&amp;level=5&amp;ID={$r['userid']}' >Assistant</a> &middot; <a href='new_staff.php?action=userlevel&amp;level=1&amp;ID={$r['userid']}' >Member</a></td></tr>";
    }
    print "</table>";
    print 
            "<b>IRC Ops</b><br />
<table width=80%><tr style='background:gray'> <th>User</th> <th>Online?</th> <th>Links</th> </tr>";
    $q = mysqli_query(
        $c,
        "SELECT * FROM users WHERE user_level=4 ORDER BY userid ASC"
    );
    while ($r = mysqli_fetch_array($q))
    {
        if ($r['laston'] >= time() - 15 * 60)
        {
            $on = "<font color=green><b>Online</b></font>";
        }
        else
        {
            $on = "<font color=red><b>Offline</b></font>";
        }
        print 
                "\n<tr> <td><a href='viewuser.php?u={$r['userid']}'>{$r['username']}</a> [{$r['userid']}]</td> <td>$on</td> <td><a href='new_staff.php?action=userlevel&amp;level=2&amp;ID={$r['userid']}' >Admin</a> &middot; <a href='new_staff.php?action=userlevel&amp;level=3&amp;ID={$r['userid']}' >Secretary</a> &middot; <a href='new_staff.php?action=userlevel&amp;level=5&amp;ID={$r['userid']}' >Assistant</a> &middot; <a href='new_staff.php?action=userlevel&amp;level=1&amp;ID={$r['userid']}' >Member</a></td></tr>";
    }
    print "</table>";
    print 
            "<b>Assistants</b><br />
<table width=80%><tr style='background:gray'> <th>User</th> <th>Online?</th> <th>Links</th> </tr>";
    $q = mysqli_query(
        $c,
        "SELECT * FROM users WHERE user_level=5 ORDER BY userid ASC"
    );
    while ($r = mysqli_fetch_array($q))
    {
        if ($r['laston'] >= time() - 15 * 60)
        {
            $on = "<font color=green><b>Online</b></font>";
        }
        else
        {
            $on = "<font color=red><b>Offline</b></font>";
        }
        print 
                "\n<tr> <td><a href='viewuser.php?u={$r['userid']}'>{$r['username']}</a> [{$r['userid']}]</td> <td>$on</td> <td><a href='new_staff.php?action=userlevel&amp;level=2&amp;ID={$r['userid']}' >Admin</a> &middot; <a href='new_staff.php?action=userlevel&amp;level=3&amp;ID={$r['userid']}' >Secretary</a> &middot; <a href='new_staff.php?action=userlevel&amp;level=4&amp;ID={$r['userid']}' >IRC Op</a> &middot; <a href='new_staff.php?action=userlevel&amp;level=1&amp;ID={$r['userid']}' >Member</a></td></tr>";
    }
    print "</table>";
}

function userlevel()
{
    global $ir, $c, $h, $userid;

    $_GET['level'] = abs((int) $_GET['level']);
    $_GET['ID'] = abs((int) $_GET['ID']);
    mysqli_query(
        $c,
        "UPDATE users SET user_level={$_GET['level']} WHERE userid={$_GET['ID']}"
    );
    print "User's level adjusted.";
}

function userlevelform()
{
    global $ir, $c, $h, $userid;

    print 
            "<h3>User Level Adjust</h3>
<form action='new_staff.php' method='get'>
<input type='hidden' name='action' value='userlevel'>
User: " . user_dropdown($c, 'ID')
                    . "<br />
User Level:<br />
<input type='radio' name='level' value='1' /> Member<br />
<input type='radio' name='level' value='2' /> Admin<br />
<input type='radio' name='level' value='3' /> Secretary<br />
<input type='radio' name='level' value='4' /> IRC Op<br />
<input type='radio' name='level' value='5' /> Assistant<br />
<input type='submit' value='Adjust' /></form>";
}

function massmailer()
{
    global $ir, $c, $userid;
    if ($_POST['text'])
    {
        $_POST['text'] = mysqli_real_escape_string(
            nl2br(strip_tags(stripslashes($_POST['text'])))
        );
        $subj = "This is a mass mail from the administration";
        if ($_POST['cat'] == 1)
            $q = mysqli_query($c, "SELECT * FROM users ");
        else if ($_POST['cat'] == 2)
            $q = mysqli_query($c, "SELECT * FROM users WHERE user_level > 1");
        else if ($_POST['cat'] == 3)
            $q = mysqli_query($c, "SELECT * FROM users WHERE user_level=2");
        else
            $q = mysqli_query(
                $c,
                "SELECT * FROM users WHERE user_level={$_POST['level']}"
            );
        while ($r = mysqli_fetch_array($q))
        {
            mysqli_query(
                $c,
                "INSERT INTO mail VALUES(NULL, 0, 0, {$r['userid']}, "
                    . time() . ",'$subj','{$_POST['text']}')"
            );
            print "Mass mail sent to {$r['username']}.<br />";
        }
        print 
                "Mass mail sending complete!<br />
<a href='new_staff.php'>&gt; Back</a>";
    }
    else
    {
        print 
                "<b>Mass Mailer</b><br />
<form action='new_staff.php?action=massmailer' method='post'> Text: <br />
<textarea name='text' rows='7' cols='40'></textarea><br />
<input type='radio' name='cat' value='1' /> Send to all members <input type='radio' name='cat' value='2' /> Send to staff only <input type='radio' name='cat' value='3' /> Send to admins only<br />
OR Send to user level:<br />
<input type='radio' name='level' value='1' /> Member<br />
<input type='radio' name='level' value='2' /> Admin<br />
<input type='radio' name='level' value='3' /> Secretary<br />
<input type='radio' name='level' value='4' /> IRC Op<br />
<input type='radio' name='level' value='5' /> Assistant<br />
<input type='submit' value='Send' /></form>";
    }
}

function adnewspaper_form()
{
    global $ir, $c, $h, $userid;

    print 
            "<h3>Editing Admin News</h3><form action='new_staff.php?action=subadnews' method='post'>
<textarea rows='7' cols='35' name='newspaper'>";
    include "admin.news";
    print "</textarea><br /><input type='submit' value='Change' /></form>";
}

function adnewspaper_submit()
{
    global $ir, $c, $h, $userid;
    $l = fopen("admin.news", "w");
    fwrite($l, stripslashes($_POST['newspaper']));
    fclose($l);
    print "Admin News updated!";
}

// Experimental Stuff

function admin_user_record()
{
    global $ir, $userid, $admin, $c;
    $user = abs((int) $_GET['user']);
    if ($user)
    {
        $q = mysqli_query(
            $c,
            "SELECT u.*, us.*, h.*, c.*, f.*
                FROM users u
                LEFT JOIN userstats us ON u.userid=us.userid
                LEFT JOIN houses h ON u.maxwill=h.hWILL
                LEFT JOIN courses c ON u.course=c.crID
                LEFT JOIN fedjail f ON u.userid = f.fed_userid
                WHERE u.userid=$user"
        ) or die(mysqli_error($c));
        if (!mysqli_num_rows($q))
        {
            $_GET['user'] = 0;
            admin_user_record();
        }
        else
        {
            $r = mysqli_fetch_array($q);
            print 
                    "<table width='100%' border='2'><tr style='background: gray'>
<th>User</th> <th>Stats</th> <th>Restrictions</th> </tr>
<tr>
<td>
Username: {$r['username']}<br />
Login: {$r['login_name']}<br />
User ID: {$r['userid']}<br />
Level: {$r['level']}<br />
Exp: {$r['exp']}<br />
Money: {$r['money']}<br />
Crystals: {$r['crystals']}<br />
Last Active: {$r['laston']}<br />
Last IP: {$r['lastip']}<br />
Energy: {$r['energy']}<br />
Max Energy: {$r['maxenergy']}<br />
Health: {$r['hp']}<br />
Max Health: {$r['maxhp']}<br />
Will: {$r['will']}<br />
Max Will: {$r['maxwill']}<br />
Property: {$r['hNAME']}<br />
Brave: {$r['brave']}<br />
Max Brave: {$r['maxbrave']}<br />
Location: {$r['location']}<br />
Hospital: {$r['hospital']}<br />
Hosp Reason: {$r['hospreason']}<br />
User Level: {$r['user_level']}<br />
Duties: {$r['duties']}<br />
Gender: {$r['gender']}<br />
Course: {$r['cNAME']}<br />
Days Left: {$r['cdays']}<br />
Days Old: {$r['daysold']}<br />
Signed Up: {$r['signedup']}<br />
Donator: {$r['donatordays']}<br />
Email: {$r['email']}<br />
Pic: {$r['displaypic']}<br />
Bank: {$r['bankmoney']}<br />
Cyber Bank: {$r['cybermoney']}<br />
Notes: {$r['staffnotes']}
</td>
<td>
Strength: {$r['strength']}<br />
Agility: {$r['agility']}<br />
Guard: {$r['guard']}<br />
Labour: {$r['labour']}<br />
IQ: {$r['IQ']}
</td>
<td>
Fed Jail: {$r['fed_days']}<br />
Reason: {$r['fed_reason']}<br />
Who: {$r['fed_jailedby']}<br />
Mail Banned: {$r['mailban']}<br />
Mail Ban Reason: {$r['mb_reason']}
</td>
</tr>
</table>";
        }
    }
    else
    {
        print 
                <<<EOF
<form action='new_staff.php' method='get'>
<input type='hidden' name='action' value='record' />
<h4>User Record</h4>
Enter a user ID to view the record of: <input type='text' name='user' value='1' /><br />
<input type='submit' value='Go' />
</form>
EOF;
    }
}

function admin_user_changeid()
{
    global $ir, $userid, $admin, $c;
    $user = abs((int) $_POST['user']);
    $submit = abs((int) $_POST['submit']);
    $new_id = abs((int) $_POST['newid']);
    if ($submit && $user && $new_id)
    {
        mysqli_query($c, "UPDATE users SET userid=$new_id WHERE userid = $user");
        mysqli_query(
            $c,
            "UPDATE userstats SET userid=$new_id WHERE userid = $user"
        );
        mysqli_query(
            $c,
            "UPDATE adminlogs SET adUSER=$new_id WHERE adUSER = $user"
        );
        mysqli_query(
            $c,
            "UPDATE applications SET appUSER=$new_id WHERE appUSER = $user"
        );
        mysqli_query(
            $c,
            "UPDATE attacklogs SET attacker=$new_id WHERE attacker = $user"
        );
        mysqli_query(
            $c,
            "UPDATE attacklogs SET attacked=$new_id WHERE attacked = $user"
        );
        mysqli_query(
            $c,
            "UPDATE blacklist SET bl_ADDED=$new_id WHERE bl_ADDED = $user"
        );
        mysqli_query(
            $c,
            "UPDATE blacklist SET bl_ADDER=$new_id WHERE bl_ADDER = $user"
        );
        mysqli_query(
            $c,
            "UPDATE cashxferlogs SET cxFROM=$new_id WHERE cxFROM = $user"
        );
        mysqli_query(
            $c,
            "UPDATE cashxferlogs SET cxTO=$new_id WHERE cxTO = $user"
        );
        mysqli_query(
            $c,
            "UPDATE challengesbeaten SET userid=$new_id WHERE userid = $user"
        );
        mysqli_query(
            $c,
            "UPDATE challengesbeaten SET npcid=$new_id WHERE npcid = $user"
        );
        mysqli_query(
            $c,
            "UPDATE coursesdone SET userid=$new_id WHERE userid = $user"
        );
        mysqli_query(
            $c,
            "UPDATE crystalmarket SET cmADDER=$new_id WHERE cmADDER = $user"
        );
        mysqli_query(
            $c,
            "UPDATE dps_process SET dp_userid=$new_id WHERE dp_userid = $user"
        );
        mysqli_query(
            $c,
            "UPDATE events SET evUSER=$new_id WHERE evUSER = $user"
        );
        mysqli_query(
            $c,
            "UPDATE fedjail SET fed_userid=$new_id WHERE fed_userid = $user"
        );
        mysqli_query(
            $c,
            "UPDATE fedjail SET fed_jailedby=$new_id WHERE fed_jailedby = $user"
        );
        mysqli_query(
            $c,
            "UPDATE friendslist SET fl_ADDER=$new_id WHERE fl_ADDER = $user"
        );
        mysqli_query(
            $c,
            "UPDATE friendslist SET fl_ADDED=$new_id WHERE fl_ADDED = $user"
        );
        mysqli_query(
            $c,
            "UPDATE imarketaddlogs SET imaADDER=$new_id WHERE imaADDER = $user"
        );
        mysqli_query(
            $c,
            "UPDATE imbuylogs SET imbADDER=$new_id WHERE imbADDER = $user"
        );
        mysqli_query(
            $c,
            "UPDATE imbuylogs SET imbBUYER=$new_id WHERE imbBUYER = $user"
        );
        mysqli_query(
            $c,
            "UPDATE imremovelogs SET imrADDER=$new_id WHERE imrADDER = $user"
        );
        mysqli_query(
            $c,
            "UPDATE imremovelogs SET imrREMOVER=$new_id WHERE imrREMOVER = $user"
        );
        mysqli_query(
            $c,
            "UPDATE inventory SET inv_userid=$new_id WHERE inv_userid = $user"
        );
        mysqli_query(
            $c,
            "UPDATE itembuylogs SET ibUSER=$new_id WHERE ibUSER = $user"
        );
        mysqli_query(
            $c,
            "UPDATE itemmarket SET imADDER=$new_id WHERE imADDER = $user"
        );
        mysqli_query(
            $c,
            "UPDATE itemselllogs SET isUSER=$new_id WHERE isUSER = $user"
        );
        mysqli_query(
            $c,
            "UPDATE itemxferlogs SET ixFROM=$new_id WHERE ixFROM = $user"
        );
        mysqli_query(
            $c,
            "UPDATE itemxferlogs SET ixTO=$new_id WHERE ixTO = $user"
        );
        mysqli_query(
            $c,
            "UPDATE jaillogs SET jaJAILER=$new_id WHERE jaJAILER = $user"
        );
        mysqli_query(
            $c,
            "UPDATE jaillogs SET jaJAILED=$new_id WHERE jaJAILED = $user"
        );
        mysqli_query(
            $c,
            "UPDATE mail SET mail_from=$new_id WHERE mail_from = $user"
        );
        mysqli_query(
            $c,
            "UPDATE mail SET mail_to=$new_id WHERE mail_to = $user"
        );
        mysqli_query(
            $c,
            "UPDATE mail SET mail_from=$new_id WHERE mail_from = $user"
        );
        mysqli_query(
            $c,
            "UPDATE preports SET prREPORTED=$new_id WHERE prREPORTED = $user"
        );
        mysqli_query(
            $c,
            "UPDATE preports SET prREPORTER=$new_id WHERE prREPORTER = $user"
        );
        mysqli_query(
            $c,
            "UPDATE referals SET refREFER=$new_id WHERE refREFER = $user"
        );
        mysqli_query(
            $c,
            "UPDATE referals SET refREFED=$new_id WHERE refREFED = $user"
        );
        mysqli_query(
            $c,
            "UPDATE seclogs SET secUSER=$new_id WHERE secUSER = $user"
        );
        mysqli_query(
            $c,
            "UPDATE staffnotelogs SET snCHANGER=$new_id WHERE snCHANGER = $user"
        );
        mysqli_query(
            $c,
            "UPDATE staffnotelogs SET snCHANGED=$new_id WHERE snCHANGED = $user"
        );
        mysqli_query(
            $c,
            "UPDATE unjaillogs SET ujaJAILER=$new_id WHERE ujaJAILER = $user"
        );
        mysqli_query(
            $c,
            "UPDATE unjaillogs SET ujaJAILED=$new_id WHERE ujaJAILED = $user"
        );
        mysqli_query(
            $c,
            "UPDATE votes SET userid=$new_id WHERE userid = $user"
        );
        mysqli_query(
            $c,
            "UPDATE willplogs SET wp_userid=$new_id WHERE wp_userid = $user"
        );
        print "User's ID changed! They will have to re-login.";
    }
    else if ($user && $new_id)
    {
        $q = mysqli_query(
            $c,
            "SELECT username FROM users WHERE userid = $user"
        );
        $q2 = mysqli_query(
            $c,
            "SELECT userid FROM users WHERE userid = $new_id"
        );
        if (mysqli_num_rows($q2))
        {
            print 
                    "<font color='red'><b>That User ID is already in Use.</b></font><br />\n";
            $_POST['newid'] = 0;
            admin_user_changeid();
        }
        else
        {
            print 
                    "You are changing " . mysqli_data_seek($q, 0)
                            . "'s user ID to $new_id<br />
<form action='new_staff.php?action=change_id' method='post'>
<input type='hidden' name='user' value='$user' />
<input type='hidden' name='newid' value='$new_id' />
<input type='hidden' name='submit' value='1' />
<input type='submit' value='Change ID' />
</form>";
        }
    }
    else
    {
        print 
                "<h3>Change User ID</h3>
<form action='new_staff.php?action=change_id' method='post'>
<table border='1' width='50%'>
<tr>
<td align='right'>User's ID:</td> <td align='left'><input type='text' name='user' value='1' /></td>
</tr> <tr>
<td align='right'>New ID:</td> <td align='left'><input type='text' name='newid' value='1000' /></td>
</tr>  <tr>
<td align='center' colspan='2'> <input type='submit' value='Change ID' /> </td>
</tr> </table>";
    }
}

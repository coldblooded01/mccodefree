<?php

require_once(dirname(__FILE__) . "/../mysql.php");

require_once(dirname(__FILE__) . "/item.php");
require_once(dirname(__FILE__) . "/user.php");

class InventoryItem {

    public function __construct($id, $item, $user, $quantity) {
        $this->id = $id;
        $this->user = $user;
        $this->item = $item;
        $this->quantity = $quantity;
    }

    public static function create_from_mysqli_array($r) {
        return new InventoryItem(
            $r['inv_id'],
            Item::get($r['inv_itemid']),
            User::get($r['inv_userid']),
            $r['inv_qty']
        );
    }

    public static function get_inventory_from_user_id($user_id) {
        global $c;
        $query = "SELECT * 
            FROM inventory iv
            LEFT JOIN items i ON iv.inv_itemid = i.itmid
            LEFT JOIN itemtypes it ON i.itmtype = it.itmtypeid
            WHERE inv_userid={$user_id} 
            ORDER BY it.itmtypename ASC";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        $result = [];
        while ($r = mysqli_fetch_array($q))
        {
            array_push($result, self::create_from_mysqli_array($r));
        }
        mysqli_free_result($q);
        return $result;
    }

    public static function count_total_items() {
        global $c;
        $query = "SELECT SUM(`inv_qty`) as total FROM inventory";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        $r = mysqli_fetch_array($q);
        mysqli_free_result($q);
        return $r['total'];
    }

    public static function add($item_id, $user_id, $quantity) {
        global $c;
        $query = "INSERT INTO inventory VALUES (NULL, {$item_id}, {$user_id}, {$quantity})";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        mysqli_free_result($q);
    }

    public static function set_quantity($inv_id, $quantity) {
        global $c;
        $query = "UPDATE inventory SET inv_qty={$quantity} WHERE inv_id={$inv_id}";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
    }

    public static function user_has_item($user_id, $item_id) {
        global $c;
        $query = "SELECT inv_id FROM inventory WHERE inv_userid={$user_id} AND inv_itemid={$item_id}";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        $num_rows = mysqli_num_rows($q);
        mysqli_free_result($q);
        return $num_rows != 0;
    }

    public static function get_by_user_and_item($user_id, $item_id) {
        global $c;
        $query = "SELECT * FROM inventory WHERE inv_userid={$user_id} AND inv_itemid={$item_id}";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        $r = mysqli_fetch_arraY($q);
        mysqli_free_result($q);
        return self::create_from_mysqli_array($r);
    }

}
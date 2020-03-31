<?php

require_once(dirname(__FILE__) . "/../mysql.php");

class Setting {

    // public static $connection = $c;

    public function __construct($id, $value) {
        $this->id = $id;
        $this->value = $value;
    }


    public static function get_all() {
        global $c;
        $q = mysqli_query(
        $c,
        "SELECT * FROM settings;"
        );
        $i = 0;
        $settings = array();
        while ($r = mysqli_fetch_array($q)) {
        $settings[$r['settingID']] = $r['settingVALUE'];
        $i++;
        }

    }

    public static function create($id, $value) {
        global $c;
        $setting = new Setting($id, $value);
        $query = "INSERT INTO settings (settingID, settingVALUE) VALUES ('{$setting->id}', '{$setting->value}');";
        mysqli_query($c, $query);
        return $setting;
    }

    public static function get($id) {
        global $c;
        $query = "SELECT settingVALUE FROM settings WHERE settingID='{$id}';";
        $q = mysqli_query($c, $query);
        $r = mysqli_fetch_array($q);
        return new Setting($id, $r['settingVALUE']);
    }

    function save() {
        global $c;
        $query = "UPDATE settings SET settingVALUE='{$this->value}' WHERE settingID='{$this->id}'";
        mysqli_query($c, $query);
    }

    public static function migrate() {
        global $c;
        $query = "CREATE TABLE `settings` (
            `settingID` varchar(256) NOT NULL,
            `settingVALUE` longtext NOT NULL,
            PRIMARY KEY (`settingID`)
          ) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
        mysqli_query($c, $query);
    }
}
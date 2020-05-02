<?php

require_once(dirname(__FILE__) . "/../mysql.php");

class PaperContent {

    public function __construct($content) {
        $this->content = $content;
    }
    
    public static function get_paper_content() {
        global $c;
        $query = "SELECT content FROM papercontent LIMIT 1";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        $r = mysqli_fetch_array($q);
        mysqli_free_result($q);
        return new PaperContent($r['content']);
    }

    public static function update_paper_content($new_content) {
        global $c;
        $query = "UPDATE papercontent SET content='$new_content'";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        mysqli_free_result($q);
    }
}
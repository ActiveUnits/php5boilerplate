<?php
require_once("modules/view.php");

class Intro {
    public function run($req, $res) {
        $res->send(
            view("views/layout-default.html", array(
                "content" => view("views/intro.html"),
                "time" => $req->benchmark->elpasedTime(),
				"siteURL" => $req->base
            ))
        );
    }
}
?>
<?php
require_once("view.php");

class Intro {
    public function run($req, $res) {
        $res->send(
            view("views/layout-default.html", array(
                "javascript" => $res->javascript->get(),
                "stylesheet" => $res->stylesheet->get(),
                "content" => view("views/intro.html"),
                "time" => $req->benchmark->elpasedTime(),
				"host" => $req->host
            ))
        );
    }
}
?>
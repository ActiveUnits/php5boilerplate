<?php
class Routes {
    public function __construct($router){
        // some example php controllers
        $router->get("/something/index.html", "controllers/php/SampleController.php");
        $router->get("/@key1/@key2", "controllers/php/SampleController.php", 'run2');
        $router->get("/error", "controllers/php/SampleController.php", 'simulateError');
        $router->get("", "controllers/php/Intro.php");

        // example test post requests
        // curl -d  'user[name]=tj' 
        // curl -d '{"user":{"name":"tj"}}' -H "Content-Type: application/json"
        $router->post("*", function($req, $res){
            require_once("view.php");
            $res->send(view(dirname(__FILE__)."/views/layout-default.html", array(
                "javascript" => $res->javascript->get(),
                "stylesheet" => $res->stylesheet->get(),
                "content" => $req->body->user->name,
                "time" => $req->benchmark->elpasedTime()
            )));
        }); 

        // -------------------------------- setup custom 404 page ---------------------
        $router->all("*", function($req, $res) {
            require_once("view.php");
            $res->send(view("views/404.html", array("url" => $req->url)), 404);  
        });
    }
}
?>
<?php
    set_include_path(dirname(__FILE__)."/php_modules/");
    require_once("debugger.php");

    // -------------------------------- require expressphp --------------------------
	require_once("expressphp/Request.php");
	require_once("expressphp/Response.php");
	require_once("expressphp/Application.php");

    // Glue the Application with Request and Response instances
	$app = new Application(new Request(), new Response());

    // -------------------------------- setup middleware order --------------------
    $app->using(array(
        // $request->benchmark variable will contain utility functions for taking 
        // benchmark stats per current request:
        // time taken, start time, memory, cpu & etc...
        "expressphp/middleware/Benchmark.php",

        // load the current /config/config.json into $request->config variable 
        "config" => "expressphp/middleware/Config.php",

        "expressphp/middleware/BodyParser.php",

        // router will handle the incoming $request and execute any routes been set.
        "router" => "expressphp/middleware/Router.php"
    ));

    // -------------------------------- setup modes -------------------------------
    // in production do not show debug to agents, just log everything including stack traces.
    $app->mode('production', function() use ($debugger) {
        $debugger->DEBUG = FALSE;
    });
    $app->mode('staging', function() use ($app) {
    });
    $app->mode('local', function() use ($app) {
    });
    $app->mode('test', function() use ($app) {
    });

	// -------------------------------- setup router ------------------------------
    $app->router->get("/something/index.html", "controllers/SampleController.php");
    $app->router->get("/@key1/@key2", "controllers/SampleController.php", 'run2');
    $app->router->get("/error", "controllers/SampleController.php", 'simulateError');
	$app->router->get("", "controllers/Intro.php");

    // example test requests
    // curl -d  'user[name]=tj' 
    // curl -d '{"user":{"name":"tj"}}' -H "Content-Type: application/json"
    $app->router->post("*", function($req, $res){
        require_once("view.php");
        $res->send(view(dirname(__FILE__)."/views/layout-default.html", array(
            "content" => $req->body->user->name,
            "time" => $req->benchmark->elpasedTime()
        )));
    }); 

    // -------------------------------- setup custom 404 page ---------------------
    $app->router->all("*", function($req, $res) {
		require_once("view.php");
        $res->send(view("views/404.html", array("url" => $req->url)), 404);  
    });

    // -------------------------------- setup custom error page ---------------------
    $debugger->errorHandler = function(Exception $e) use ($app) {
        require_once("view.php");
        $app->response->send(view(dirname(__FILE__)."/views/error.html", array(
            "message" => $e->getMessage(),
            "location" => "File: ".$e->getFile()." Line: ".$e->getLine(),
            "stackTrace" => '<li>'.implode(explode("\n", $e->getTraceAsString()), '<li>')
        )), 500);
    };
	
    // finally run the application in the defined mode (/config/config.json)
	$app->run($app->config->get("mode"));
?>
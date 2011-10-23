<?php
	require_once("modules/expressphp/Request.php");
	require_once("modules/expressphp/Response.php");
	require_once("modules/expressphp/Application.php");

    // Glue the Application with Request and Response instances
	$app = new Application(new Request(), new Response());

    // -------------------------------- setup middleware order --------------------
    
    $app->using(array(
        // $request->benchmark variable will contain utility functions for taking 
        // benchmark stats per current request:
        // time taken, start time, memory, cpu & etc...
        "modules/expressphp/middleware/Benchmark.php",

        // catch all exceptions and errors, including parse errors too
        "logger" => "modules/expressphp/middleware/Logger.php",

        // load the current /config/config.json into $request->config variable 
        "config" => "modules/expressphp/middleware/Config.php",

        // router will handle the incoming $request and execute any routes been set.
        "router" => "modules/expressphp/middleware/Router.php"
    ));

    // -------------------------------- setup modes -------------------------------

    // in production do not show debug to agents, just log everything including stack traces.
    $app->mode('production', function() use ($app) {
        $app->logger->DEBUG = FALSE;
    });

    // just for fun in test mode register additional route and middleware :)
    $app->mode('test', function() use ($app) {
        $app->using("modules/expressphp/middleware/BodyParser.php");
        $app->router->post("*",function($req, $res){
			$res->send(view("views/post-sample.html"));
        });
    });

	// -------------------------------- setup router ------------------------------
    $app->router->get("/something/index.html", "controllers/SampleController.php");
    $app->router->get("/@key1/@key2", "controllers/SampleController.php", 'run2');
    $app->router->get("/error", "controllers/SampleController.php", 'simulateError');
	$app->router->get("", "controllers/Intro.php");

    // -------------------------------- setup custom error page ---------------------
    $app->logger->errorHandler = function(Exception $e) use ($app) {
		require_once("modules/view.php");
        $app->response->send(view(dirname(__FILE__)."/views/error.html", array(
            "message" => $e->getMessage(),
            "location" => "File: ".$e->getFile()." Line: ".$e->getLine(),
            "stackTrace" => '<li>'.implode(explode("\n", $e->getTraceAsString()), '<li>')
        )), 500);
    };

    // -------------------------------- setup custom 404 page ---------------------
    $app->router->all("*", function($req, $res) use ($app) {
		require_once("modules/view.php");
        $res->send(view("views/404.html", array("url" => $app->request->url)), 404);  
    });
	
    // finally run the application in the defined mode (/config/config.json)
	$app->run($app->config->get("mode"));
?>
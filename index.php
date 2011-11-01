<?php
    set_include_path(dirname(__FILE__)."/modules/php/");

    // always register errors handler first
    require_once("ErrorHandler.php");
    $errorHandler = new ErrorHandler();

    // setup custom error page using view module
    $errorHandler->onError = function(Exception $e) {
        require_once("view.php");
        header("Status: 500 Internal Server Error");
        exit(view(dirname(__FILE__)."/views/error.html", array(
            "message" => $e->getMessage(),
            "location" => "File: ".$e->getFile()." Line: ".$e->getLine(),
            "stackTrace" => '<li>'.implode(explode("\n", $e->getTraceAsString()), '<li>')
        )));
    };

    // -------------- require expressphp $app instance ----------------------------
    require_once("expressphp/Response.php");
    require_once("expressphp/Request.php");
    require_once("expressphp/Middleware.php");

    $app = new Middleware(array(
        "root" => dirname(__FILE__)
    ));

    // setup middleware
    $app->using(array(
        // $request->benchmark variable will contain utility functions for benchmarking
        "expressphp/middleware/Benchmark.php",
        // $request->config variable will contain current config
        "config" => "expressphp/middleware/JSONConfig.php",
        // $response->javascript variable will contain javascript assets support
        "javascript" => "expressphp/middleware/Javascript.php",
        // $response->css variable will contain css assets support
        "css" => "expressphp/middleware/CSS.php",
        // $request->body will be parsed to object if incoming request is POST or PUT
        "expressphp/middleware/BodyParser.php",
        // router will handle the incoming $request and execute any routes been set via the Routes controller
        "router" => "expressphp/middleware/Router.php"
    ));
    $app->router->using(array("controllers/php/Routes.php"));

    // set configuration source file 
    $app->config->source(array(
        "public"=>"/config/config.json"
    ));

    // set javascript source paths
    $app->javascript->source(array(
        "/modules/js/*.js",
        "/models/js/*.js",
        "/controllers/js/*.js"
    ));
    $app->javascript->destination("/assets/compiled/");

    // set css source paths
    $app->css->source(array(
        "/assets/css/*.css"
    ));
    $app->css->destination("/assets/compiled/");

    // only in production disable debugging.
    if($app->config->get("public.mode") == "production") {
        $errorHandler->DEBUG = FALSE;
        $app->javascript->DEBUG = FALSE;
        $app->stylesheet->DEBUG = FALSE;
    }
	
    // finally run the application in the defined mode (/config/config.json)
	$app->run(new Request(), new Response());
?>
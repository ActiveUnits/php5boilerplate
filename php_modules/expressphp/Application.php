<?php
class Application {
    public $request = NULL;
    public $response = NULL;
    
    private $modules = array();
    private $modes = array();

    public function __construct($request, $response){
        $this->request = $request;
        $this->response = $response;
    }

    public function mode($name, $handler) {
        $this->modes[$name] = $handler;
        return $this;
    }

    public function using($modules) {
	
        if(is_string($modules)) {
            $modules = array($modules);
		}

        foreach($modules as $name => $path) {
            require_once($path);
            $parts = explode("/", $path);
            $className = str_replace(".php", "", array_pop($parts));
            $instance = new $className();
            $this->modules []= array("instance" => $instance);
            if(is_string($name)) {
                $this->$name = $instance;
            }
        }

        return $this;
    }

    public function run($modeName) {
	
        if(isset($this->modes[$modeName])) {
            $modehandler = $this->modes[$modeName];
            $modehandler($this->request, $this->response);
        }

        // then, execute all middleware modules in FIFO sequence
        foreach($this->modules as $module) {
            $module["instance"]->run($this->request, $this->response);
        }
		
    }
}
?>
<?php
class Router {

    private $_rules;

    public function __construct($app){
        
    }

    public function addRule($method, $pattern, $handler, $action = "run") {
        $this->_rules[] = (object) array("pattern" => $pattern, "method" => $method, "handler" => $handler, "action" => $action);
    }

    public function all($pattern, $handler, $action = "run") {
        $this->get($pattern, $handler, $action);
        $this->post($pattern, $handler, $action);
        $this->delete($pattern, $handler, $action);
        $this->put($pattern, $handler, $action);
    }

    public function get($pattern, $handler, $action = "run") {
        $this->addRule("GET", $pattern, $handler, $action);
    }

    public function post($pattern, $handler, $action = "run") {
        $this->addRule("POST", $pattern, $handler, $action);
    }

    public function delete($pattern, $handler, $action = "run") {
        $this->addRule("DELETE", $pattern, $handler, $action);
    }

    public function put($pattern, $handler, $action = "run") {
        $this->addRule("PUT", $pattern, $handler, $action);
    }

    public function run($request, $response) {
        $numOfRules = count($this->_rules);
        for($i=0; $i<$numOfRules; $i++) {
            $rule = $this->_rules[$i];
            
            if($rule->method == $request->method && $this->match($rule->pattern, $request->url, $request->params)) {
                    $handler = $rule->handler;
                    if(is_callable($handler)) {
                        $handler($request, $response);
                    } else if(is_string($handler)) {

                        require_once($handler);

                        $parts = explode("/",$handler);
                        $last =  array_pop($parts);
                        $className = str_replace(".php", "", $last);
                        $instance = new $className($this);

                        $action = $rule->action;
                        $instance->$action($request, $response);

                    } else
                        throw new ErrorException("not usable ".$handler.' for route handler');
            }
        }
    }

    public function match($pattern, $url, array &$params = array()) {
        $ids = array();

        if($pattern == $url)
            return true;
       
        // Build the regex for matching
        $regex = '/^'.implode('\/', array_map(
            function($str) use (&$ids){
                if ($str == '*') {
                    $str = '(.*)';
                }
                else if ($str != "" && $str{0} == '@') {
                    if (preg_match('/@(\w+)(\:([^\/]*))?/', $str, $matches)) {
                        $ids[$matches[1]] = true;
                        return '(?P<'.$matches[1].'>'.(isset($matches[3]) ? $matches[3] : '[^(\/|\?)]+').')';
                    }
                }
                return $str; 
            },
            explode('/', $pattern)
        )).'\/?(?:\?.*)?$/i';

        // Attempt to match route and named parameters
        if (preg_match($regex, $url, $matches)) {
            if (!empty($ids)) {
                $params = array_intersect_key($matches, $ids);
            }
            return true;
        }

        return false;
    }
}
?>
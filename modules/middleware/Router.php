<?php
class Router {

    private $_rules;

    public function addRule($method, $pattern, $path, $action = "run") {
        $this->_rules[] = (object) array("pattern" => $pattern, "method" => $method, "path" => $path, "action" => $action);
    }

    public function all($pattern, $path, $action = "run") {
        $this->get($pattern, $path, $action);
        $this->post($pattern, $path, $action);
        $this->delete($pattern, $path, $action);
        $this->put($pattern, $path, $action);
    }

    public function get($pattern, $path, $action = "run") {
        $this->addRule("GET", $pattern, $path, $action);
    }

    public function post($pattern, $path, $action = "run") {
        $this->addRule("POST", $pattern, $path, $action);
    }

    public function delete($pattern, $path, $action = "run") {
        $this->addRule("DELETE", $pattern, $path, $action);
    }

    public function put($pattern, $path, $action = "run") {
        $this->addRule("PUT", $pattern, $path, $action);
    }

    public function run($request, $response) {
        $numOfRules = count($this->_rules);
        for($i=0; $i<$numOfRules; $i++) {
            $rule = $this->_rules[$i];
            
            if($rule->method == $request->method && $this->match($rule->pattern, $request->url, $request->params)) {
                
                    if(is_callable($rule->path)) {
                        $handler = $rule->path;
                        $handler($request, $response);
                    } else if(is_string($rule->path)) {

                        require_once($rule->path);

                        $parts = explode("/",$rule->path);
                        $last =  array_pop($parts);
                        $className = str_replace(".php", "", $last);
                        $instance = new $className($this);

                        $action = $rule->action;
                        $instance->$action($request, $response);

                    } else
                        throw new ErrorException("not usable ".$rule->path.' for route handler');
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
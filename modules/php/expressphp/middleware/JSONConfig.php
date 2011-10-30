<?php
class JSONConfig {
    private $data;
    private $root;
    
    public function __construct($app){
        $this->data = new stdClass();
        $this->root = $app->root;
    }

    public function source($source) {
        if(!is_array($source))
            $source = array($source);
        foreach($source as $key => $path) {
            $this->data->{$key} = json_decode(file_get_contents($this->root.$path));
        }
    }
    
    public function run($req, $res) {
        $req->config = $this;
    }

    public function get($key){

        $parts = explode(".", $key);
        $current = $this->data;
        foreach($parts as $part) {
            $current = $current->$part;
        }

        return $current;
    }
}
?>
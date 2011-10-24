<?php
class Config {
    private $data;
    
    public function __construct(){
        $this->data = json_decode(file_get_contents("config/config.json"));
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
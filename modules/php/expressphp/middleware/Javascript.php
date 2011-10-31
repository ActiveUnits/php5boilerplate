<?php
require_once("rglob.php");
require_once("FileJoin.php");

// TODO DRY <-> CSS.php
class Javascript {
    public $hashFilePath = "js.hash";
    public $compiledPath = "";
    public $DEBUG = TRUE;

    private $dirs = array();
    private $hash = "";
    private $root = "";

    public function __construct($options){
        $this->root = $options->root;
    }

    public function run($req, $res) {
        $res->javascript = $this;
        if(file_exists($this->root.$this->hashFilePath))
            $this->hash = file_get_contents($this->root.$this->hashFilePath);
    }

    public function source($dirs) {
        $this->dirs = $dirs;
    }

    public function destination($dir) {
        $this->hashFilePath = $dir."js.hash";
        $this->compiledPath = $dir;
    }

    public function compile() {
        if($this->hash != "") {
            unlink($this->root.$this->hashFilePath);
            unlink($this->root."{$this->compiledPath}{$this->hash}.js");
        }

        $joiner = new FileJoin($this->root);
        $content = $joiner->run($this->dirs);
        $this->hash = md5($content);
        file_put_contents($this->root.$this->hashFilePath, $this->hash);
        file_put_contents($this->root."{$this->compiledPath}{$this->hash}.js", $content);
    }

    public function get(){
        if($this->DEBUG == FALSE) {

            // check is there compiled version and use it directly, otherwise compile and use.
            if($this->hash == "")
                $this->compile();

            return "<script src='{$this->compiledPath}{$this->hash}.js'></script>";
        }
        
        $result = "";
        foreach($this->dirs as $dir) {
            $files = rglob($this->root.$dir);
            foreach($files as $file) {
                $file = str_replace($this->root, "", $file);
                $result .= "<script src='{$file}'></script>\n";
            }
        }

        // always re-compile assets if not in debug mode
        $this->compile();
  
        return $result;
    }
}
?>
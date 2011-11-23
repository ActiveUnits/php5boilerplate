<?php
/**
*	Class for rendering given template with its {placeholders} populated with 'values'
*
* 	usage:
*	$view = new View("/path/to/template", array("key" => "value"));
*	$renderedView = (string)$view;
*	echo $view;
*	
*	Note: this file also inserts global function 'view' if available
*
*	usage2:
*	echo view("/path/to/template",array("key" => "value"))
*/
class ViewDebug {
    private static $enable = false;
    public static function debug($mode = null) {
        if($mode != null) {
            self::$enable = $mode;
        } else {
            return self::$enable;
        }
    }
}
class ViewCache {
    private static $cache;
    public static function add($file, $content) {
        if(self::$cache == NULL) {
            self::$cache = (object) array();
        }
        self::$cache->$file = $content;
    }
    public static function get($file) {
        if(isset(self::$cache->$file)) {
            return self::$cache->$file;
        } else {
            return false;
        }
    }
}
class View {

	/**
	*	template file contents ({placeholders} are not replaced with values)
	*/
	public $tplFileContent = NULL;
	/**
	* 	assoc array containing key => value which will be used for populating the template
	*/
	public $vars = array();

	public function __construct($path, array $data){
        
        $cache = ViewCache::get($path);
        
        if(!$cache) {
            if(ViewDebug::debug()) {
                var_dump($path);
            }
            $fh = @fopen($path, "r");
            if(!$fh) {
                throw new ErrorException("Missing file '".$path."'.");
            }
            $this->tplFileContent = fread($fh, filesize($path));
            fclose($fh);
            ViewCache::add($path, $this->tplFileContent);
        } else {
            $this->tplFileContent = $cache;
        }

		$this->vars = $data;
	}

	public function __toString() {		
		// adding assigned variabls
		$output = $this->tplFileContent; // TODO is this by copy or reference?
		foreach($this->vars as $key => $value) {
			$output = str_replace("{".$key."}", $value, $output);
		}

		return $output;
	}
}

/**
*	helper global function
*	returns View
*/
function view($path, $data = array()) {
	return new View($path, $data);
}
?>
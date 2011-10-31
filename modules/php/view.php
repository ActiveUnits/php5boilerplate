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
		$fh = @fopen($path, "r");
		if(!$fh)
			throw new ErrorException("Missing file '".$path."'.");
		$this->tplFileContent = fread($fh, filesize($path));
		fclose($fh);

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
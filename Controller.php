<?php
namespace Wax\Controller;
use Wax\Template\Template;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @package PHP-Wax
 * Provides basic functionality which controllers inherit.
 */

class Controller {
  protected $class_name='';
  
  // A reference to the master application object
  public $response = false;
  
  public $controller;
  public $action;
  public $use_layout='application';
  public $use_view="_default";
  public $use_format="html";
  public $referrer;
	
	// Flag which can be set to false to render nothing
	public $render = true;
	
  public $view_paths = array();

	public function __construct($request_attributes = [], $application = false) {
	  $this->init($request_attributes);  
    $this->application = $application;  
  }
  
  public function init($request_attributes){
    $this->class_name=get_class($this);
    $this->controller = $request_attributes["controller"];
    $this->action = $request_attributes["action"];
    $this->view_paths();
  }
  
  public function view_paths() {
    //$this->view_paths[]=VIEW_DIR.$this->controller;
    //$this->view_paths[]=VIEW_DIR."shared";
  }
  
  

	/**
 	 *	Sends a header redirect, moving the app to a new url.
	 *	@access protected
	 *	@param string $route
 	 */   
  public function redirect_to($options, $protocol="http://", $status=302) {
    switch(true) {
      case is_array($options):
        $url = $protocol.$_SERVER['HTTP_HOST'].UrlHelper::url_for($options);
        $this->response->redirect($url, $status);
        break;
      case preg_match("/^\w+:\/\/.*/", $options):
        $this->response->redirect($options,$status);
        break;
      case $options=="back":
        if(!$_SERVER['HTTP_REFERER']) return false;
        $this->response->redirect($_SERVER['HTTP_REFERER']);
        break;
      case is_string($options):
        if(substr($options,0,1)!="/"){
          if(substr($_SERVER['REQUEST_URI'],-1) != "/") $options = "/" . $options;
          $options = $_SERVER['REQUEST_URI'] . $options;
        }
        $url = $protocol.$_SERVER['HTTP_HOST'].$options;
        $this->response->redirect($url,$status);
        break;
    }
    $this->response->execute();
    exit;
  }

	
  
  /**
   *  Returns a view as a string.
	 *	@return string
 	 */
  public function render_view() {
		if(!$this->use_view) return false;
		if($this->use_view == "none") return false;
		if($this->use_view=="_default") $this->use_view = $this->action;

    $view = new Template($this);
    foreach($this->view_paths as $path) {
      $view->add_path($path."/".$this->use_view);
    }
    if($this->use_format) $content = $view->parse($this->use_format, 'views');
		else $content = $view->parse('html', 'views');
		return $content;
  }
  
  /**
   *  Returns a layout as a string.
	 *	@return string
 	 */
  public function render_layout() {
		if(!$this->use_layout) return "";
    $layout = new Template($this);
    $layout->add_path(VIEW_DIR."layouts/".$this->use_layout);
    ob_end_clean();
	  return $layout->parse($this->use_format);      
  }
  
  
  /**
   *  Returns a constructed response
	 *	@return Response
 	 */
  public function render() {
    
  }
  

  
	/**
 	 *	In the abstract class this remains empty. It is overridden by the controller,
	 *	any commands will be run by all actions prior to running the action.
	 *	@access protected
 	 */
   public function controller_global() {}
   

}


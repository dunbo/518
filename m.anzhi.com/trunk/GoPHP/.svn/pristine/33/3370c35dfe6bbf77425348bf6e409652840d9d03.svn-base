<?php
abstract class GoController
{
	/**
	 * 
	 * api request处理输入参数
	 * @var GoRequest
	 */
	protected $request;
	/**
	 * 
	 * api response 处理返回内容
	 * @var GoResponse
	 */
	protected $response;
	
	protected $controllerName;
	protected $actionName;
	
	public function __construct()
	{
		
	}
	
	final function run($option)
	{
		$this->request->processInput();
		$this->dispatch($option);
		$this->response->processData();
	}
	
	/**
	 * 
	 * @return GoRequest
	 */
	final function getRequest()
	{
		return $this->request;
	}
	
	/**
	 * 
	 * @return GoResponse
	 */	
	final function getResponse()
	{
		return $this->response;
	}
	
	abstract function dispatch($option = array());
}

class GoWebController extends GoController
{
	public function __construct()
	{
		parent::__construct();
		load_core('GoRequest');
		load_core('GoResponse');
		$this->request = new GoWebRequest();
		$this->response = new GoWebResponse();
		$this->controller_root = GO_APP_ROOT. DS. 'controller';
	}
	public function dispatch($option = array())
	{
		if ($this->loadController()) {
			$this->executeActoin($this->controllerName, $this->actionName);
		}
	}
		
	protected function loadController()
	{
		load_core('GoAction');
		$controllerString = isset($_GET['c']) ? trim($_GET['c']) : 'default';
		$this->actionName = isset($_GET['a']) ? trim($_GET['a']) : 'index';
		if (stripos('_', $controllerString) !== false) {
			$arr = explode('_', strtolower($controllerString));
			$this->controllerName = array_slice($arr, -1);
			$arr = array_slice($arr, 0, -1);
			$controller_dir = implode(DS, $arr). DS;
		} else {
			$this->controllerName = strtolower($controllerString);
			$controller_dir = '';
		}

		$controller_file = $this->controller_root. DS. $controller_dir .$this->controllerName. '.class.php';
		if (file_exists($controller_file)) {
			go_require_once($controller_file);
			return true;
		} else {
			go_error("controller file {$this->controllerName}  does not exist!", 'HTTP/1.1 417 Expectation Failed');
			return false;
		}
	}
	
	protected function executeActoin($controller, $action)
	{
		$controllerClass = $controller. 'Controller';

		if (class_exists($controllerClass)) {
			$controllerObj = new $controllerClass;
			$actionFunc = $action. 'Action';
			
			if (method_exists($controllerObj, $actionFunc)) {
				$result = $controllerObj->$actionFunc();
			} else {
				go_error("no action called {$action} from controller {$controller}  can be found!", 'HTTP/1.1 417 Expectation Failed');
			}
			
		} else {
			go_error("no controller class called {$controller}  can be found!", 'HTTP/1.1 417 Expectation Failed');
		}
		return $result;
	}
	
	public function getControllerName()
	{
		return $this->controller;
	}
	
	public function getActionName()
	{
		return $this->action;
	}
}

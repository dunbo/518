<?php
class GoContext
{
	/**
	 * 
	 * Enter description here ...
	 * @var GoController
	 */
	protected $controller;
	
	/**
	 * 
	 * @var GoContext
	 */
	static $_instance;	
	
	
	public static function getInstance()
	{
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	public function getController()
	{
		return $this->controller;
	}
	
	/**
	 * 
	 * @return GoRequest
	 */
	public function getRequest()
	{
		return $this->controller->getRequest();
	}
	
	/**
	 * 
	 * @return GoResponse
	 */
	public function getResponse()
	{
		return $this->controller->getResponse();
	}

	/**
	 * 
	 * 
	 * @return GoContext
	 */
	public function setController($controllerAdapter)
	{
		$this->controller = new $controllerAdapter();
		return $this;
	}
	
	public function setContext(& $service)
	{
		$this->controller = $service;
	}
	
	public function getContext()
	{
		return $this->controller;
	}
	
	public function execute($option = array())
	{
		$this->controller->run($option);
	}
}
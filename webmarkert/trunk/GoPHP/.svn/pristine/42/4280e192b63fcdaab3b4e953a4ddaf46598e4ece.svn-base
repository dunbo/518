<?php
abstract class GoAction
{
	/**
	 * 
	 * Enter description here ...
	 * @var GoService
	 */
	protected $context;
	
	public $status = 200;
	
	public function GoAction()
	{
		load_core('GoContext');
		$this->context = GoContext::getInstance()->getContext();
		if (empty($this->context)) {
			//兼容部分旧代码
			//@TODO所有service需要调用GoContext的setContext方法将service本身注册成为一个context
			$this->context = GoService::getInstance();
		}
	}

	abstract public function execute();
	
	public function getParameter($key = null, $default = '')
	{
		return $this->context->getParameter($key, $default);
	}

	public function getParameters() {
		return $this->context->getParameters();
	}
}

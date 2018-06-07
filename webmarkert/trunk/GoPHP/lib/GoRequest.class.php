<?php
abstract class GoRequest
{
	protected $input;
	protected $key;
	protected $parameters;
	
	public function __construct($input)
	{
		$this->input = $input;
	}
	
	abstract public function processInput();
	
	public function getKey()
	{
		return $this->key;
	}	
	public function getParameters($key = '', $default = null)
	{
		if(empty($key)) {
			return $this->parameters;
		} else {
			return empty($this->parameters[$key]) ? $default : $this->parameters[$key];
		}
	}	
}
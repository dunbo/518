<?php
class GoBenchmark
{
	protected $benchmark;
	protected $marker_list = array();
	protected $pid;
	public $output = false;
	function start()
	{
		$this->start = microtime_float();
		$this->marker_list[] = array('begin',  microtime_float());
		$this->pid = getmypid();
		$this->benchmark = "\n-== start benchmark for pid {{$this->pid}} ==-\n";
	}
	
	function setMarker($marker)
	{
		$this->popMaker();
		$this->marker_list[] = array($marker,  microtime_float());
	}
	
	function stop()
	{
		$this->popMaker();
		$this->end = microtime_float();
		$s = $this->end - $this->start;
		$this->benchmark .= "-== stop benchmark for pid {$this->pid} {$s}==-\n";
		
		if ($this->output) {
			echo $this->benchmark;
		} else {
			file_put_contents('/tmp/benchmark.log', $this->benchmark, FILE_APPEND);
		}
	}
	
	function popMaker()
	{
		$now = microtime_float();
		$old_marker = array_pop($this->marker_list);
		$time = $now - $old_marker[1];
		$this->benchmark .= "{$old_marker[1]}: {$old_marker[0]} [{$time}]\n";		
	}
}
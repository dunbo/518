<?php
class GoGearmanWorker {
	
	private $worker;
	private $function_flag = false;
	
	public function __construct($config = '') {
		$this->worker = new GearmanWorker();
		$key = 'default';
		if (empty($config)) {
			($task_server = load_config('task_server')) ? True : $task_server = '127.0.0.1';
			($task_port = load_config('task_port')) ? True : $task_port = '4730';
		} else {
			$task_server = load_config("gearman/{$config}/task_server");
			$task_port = load_config("gearman/{$config}/task_port");
		}

		$this->worker->addServer($task_server, $task_port);
		
		$this->worker->addOptions(GEARMAN_WORKER_NON_BLOCKING);
		$this->worker->setTimeout(1000);
	}

	public function run() {
		if (!$this->function_flag) {
			die("no function register!\n");
		}
		//worker运行
		declare(ticks = 1);
		pcntl_signal(SIGINT,  array(GoGearmanWorker, 'sigStopHandler'));
		pcntl_signal(SIGTERM, array(GoGearmanWorker, 'sigStopHandler'));
		while (1) {
			$this->worker->work();

			if (GEARMAN_SUCCESS == $this->worker->returnCode()) continue;

			//printf("Waiting for next job... (code: %d)\n", $this->worker->returnCode());

			if (! $this->worker->wait()) {
				if (GEARMAN_NO_ACTIVE_FDS == $this->worker->returnCode()) {
					printf("Waiting 5 secs for UP...\n");
					sleep(5);
					continue;
				} elseif (GEARMAN_TIMEOUT !== $this->worker->returnCode()) {
					printf("Failed to wait while server was UP\n", $this->worker->returnCode());
					break;
				}
			}
		}
	}

	public function addFunction($jobname, $function) {
		$r = false;
		if (function_exists($function)) {
			$this->function_flag = true;
			$this->worker->addFunction($jobname, $function);
			$r = true;
		}
		return $r;
	}

	public function sigStopHandler ($signal) 	{
		die(sprintf("%s Signal num: %d at %s\n", date('Y-m-d H:i:s'), $signal, __METHOD__));
	}
}

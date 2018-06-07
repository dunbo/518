<?

function xmpp_printf($email, $format) {
	$args = func_get_args();
	if (count($args) < 2)
		return;
	array_shift($args);
	$message = call_user_func_array("sprintf", $args);
	$message = json_encode(array('to' => $email, 'body' => $message));
	# a local copy
	$log_path = '/tmp/'. basename(__FILE__). '.php';
	$date_str = date('Y-m-d H:i:s', time());
	file_put_contents($log_path, "${date_str} ${message}\n", FILE_APPEND);
	# TODO: config
	$host = '127.0.0.1';
	$port = 10032;
	# pass these to the daemon
	$socket = socket_create(AF_INET, SOCK_STREAM, 0);
	if (!$socket) {
		file_put_contents($log_path, "${date_str} socket_create failed\n", FILE_APPEND);
		return false;
	}
	if (!socket_connect($socket, $host, $port)) {
		file_put_contents($log_path, "${date_str} socket_connect failed\n", FILE_APPEND);
		return false;
	}
	$ret = socket_write($socket, $message);
	if (!$ret) {
		file_put_contents($log_path, "${date_str} socket_write failed\n", FILE_APPEND);
		return false;
	}
	socket_close($socket);
	return true;
}


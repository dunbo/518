<?php
require_once(dirname(__FILE__).'/../init.php');
$worker->addFunction("send_message", "send_message_func");
while ($worker->work());


function send_message_func($job)
{
    http_post_mobile(unserialize($job->workload()));
}


function http_post_mobile($vals) {
    if (preg_match('/^192\.168\.0/i', $_SERVER['SERVER_ADDR']) || $_SERVER['SERVER_ADDR'] == '10.0.3.15' || $_SERVER['SERVER_ADDR'] == '114.247.222.131') {
        //$url = 'http://192.168.0.99/service.php?do=sendSms';
        //$host = 'Host: 9.sms.anzhi.com';
        //$url = 'http://192.168.0.74:91/service.php?do=sendSms';
        //$host = 'Host: localhost';

        $url = 'http://118.26.224.18/service.php?do=sendSms';
        $host = 'Host: smsapi.goapk.com';
    } else {
        $url = 'http://192.168.1.18/service.php?do=sendSms';
        $host = 'Host: smsapi.goapk.com';
    }

    $url .= '&key=87f337977106a8b12ca1ccb11b3c2637&rand=' . microtime(true);

    $res = curl_init();
    curl_setopt($res, CURLOPT_URL, $url);
    curl_setopt($res, CURLOPT_TIMEOUT, 15);
    curl_setopt($res, CURLOPT_HTTPHEADER, array($host, 'Expect:'));
    curl_setopt($res, CURLOPT_POST, true);
    curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
    $result = curl_exec($res);
    $http_code = curl_getinfo($res, CURLINFO_HTTP_CODE);
    $errno = curl_errno($res);
    $error = curl_error($res);
    curl_close($res);

    $log_file = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ? 'e:/mobile.log' : LOG . date('Y-m-d') . '/mobile.log';
    if (!is_dir(dirname($log_file)))
        mkdir(dirname($log_file), 0777, true);
    file_put_contents($log_file, "post|{$url}|{$result}|{$host}|{$http_code}|{$errno}|{$error}|" . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

    return array(
        'ret' => $result,
        'http_code' => $http_code,
    );
}

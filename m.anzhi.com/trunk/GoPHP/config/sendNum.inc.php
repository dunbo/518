<?php
$config['sendNum']['redis'] = array(
    array(
        'host' => '192.168.1.153',
        'port' => '6379',
        'read' => true,
        'write' => true,
    ),
);

$config['game_receipt']['redis'] = array(
    array(
        'host' => '192.168.1.154',
        'port' => '6379',
        'read' => true,
        'write' => true,
    ),
);

return $config;
?>

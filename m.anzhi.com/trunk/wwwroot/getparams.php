<?php
include_once (dirname(realpath(__FILE__)).'/init.php');

if (count($_GET)) {
    $data = array(
        'data' => $_GET,
        'time' => time(),
    );
    permanentlog('getparams.json', json_encode($data));
}
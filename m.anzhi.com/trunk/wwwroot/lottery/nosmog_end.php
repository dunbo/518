<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');

$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> display("nosmog_end.html");
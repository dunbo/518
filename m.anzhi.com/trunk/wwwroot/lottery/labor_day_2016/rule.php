<?php
include_once ('./fun.php');
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> display("lottery/{$prefix}/rule.html");	


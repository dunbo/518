<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> display("friends_rule.html");
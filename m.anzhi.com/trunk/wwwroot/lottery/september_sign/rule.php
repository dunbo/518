<?php
include_once ('./fun.php');

$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['prefix'] = $prefix;
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['sid'] = $sid;

$tpl = "lottery/{$prefix}/rule.html";
$tplObj -> display($tpl);	



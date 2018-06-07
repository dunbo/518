<?php

include_once (dirname ( realpath ( __FILE__ ) ) . '/init.php');

if($_GET['from'] == 1){
	setCookie('anzhiapks',1,time()+86400);
	echo 1;
	return 1;
}
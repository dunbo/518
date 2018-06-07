<?php
require_once(dirname(realpath(__FILE__)) . '/init_page.php');
$count		=	(int)$_REQUEST['count'];
$info = get_words($count);
echo json_encode($info);
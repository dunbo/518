<?php
include_once ('../stdafx.php');
$ymd = date("Y-m-d",time());
$dir = P_LOG_DIR."/admin.goapk.com/".$ymd;
echo $dir."<br/>";
if(!is_dir($dir)) mkdir($dir,0755);
$test = "heelowww";
file_put_contents($dir."/test".".log",$test."\n",FILE_APPEND);
echo "end";
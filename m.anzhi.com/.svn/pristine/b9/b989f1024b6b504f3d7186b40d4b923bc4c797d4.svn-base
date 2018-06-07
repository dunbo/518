<?php

include_once(dirname(realpath(__FILE__)) . "/init_page.php");

// 判断当前时间活动是否已结束，结束则跳转到结束页面（结束页面不能include此文件！）
if ($now > $activity_end_time) {
	// 超出活动日期，超出后在这里跳转
	header("location:/lottery/guessappbattle/end.php?sid={$sid}");
	exit;
}
<?php 
//www ͬ���˳�
session_start();
unset($_SESSION['user_data']);
if($_COOKIE['autoLogin'] == true) setcookie('autoLogin','',-1);
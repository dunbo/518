<?php
  error_reporting(E_ALL ^ E_NOTICE);
  header('Content-type: text/html; charset=utf-8');
    $old = file_get_contents("save.json");
    $fp = fopen("save.json", "w");
    fwrite($fp, $old+1);
    fclose($fp);
?>

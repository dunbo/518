<?php
if ($_COOKIE['wap']=="concise")
setcookie("wap", "" );
else
setcookie("wap", "concise");
header("location: http://".$_SERVER["HTTP_HOST"]);
?>

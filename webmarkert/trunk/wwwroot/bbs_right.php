<?php
$time = time();
$furl = "./bbs_right.html";
if(file_exists($furl)){
    if($time-filemtime($furl)<= 3600)
    {
            $html = file_get_contents($furl);
            echo $html;
    }else{
            //$data = file_get_contents("http://www.goapk.com/bbs_static.php");
			ob_start();
            include "./bbs_static.php";
			$data = ob_get_clean();
            if(!empty($data)){
                 /*if(file_exists($furl))  unlink($furl);
                $fp=fopen($furl,'w+');
                fwrite($fp,$data) or die('写文件错误');
                fclose($fp);
                echo $data;*/
                      $lockfile = $furl.".lock";
                    if(!file_exists($lockfile)){
                      file_put_contents($lockfile,"Lock");
                      $fp=fopen($furl,'w+');
                      fwrite($fp, $data);
                      fclose($fp);
                      unlink($lockfile);
                    }
                    echo $data;
            }else{
                $html = file_get_contents($furl);
                echo $html;
            }
    }
}else{
	ob_start();
    $data = include "./bbs_static.php";
	$data = ob_get_clean();
    $fp=fopen($furl,'w+');
    fwrite($fp,$data) or die('写文件错误');
    fclose($fp);
    echo $data;
}
?>
<?php

class ImagesAction extends CommonAction {
	function imagetrans (){
		$src = $_GET['src'];
		$src_base64_decode = base64_decode($src);
		//var_dump($src,$src_base64_decode);
		$this -> assign('src',$src_base64_decode);
		$this->display();
	}
}
?>
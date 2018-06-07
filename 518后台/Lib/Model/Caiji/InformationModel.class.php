<?php
class InformationModel extends AdvModel {
    protected $connect_id = 24;
    public function __construct(){
		parent::__construct();
		$cj_Connect = C('DB_CAIJI');
		$this -> addConnect($cj_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
    }	
	function save_status($passed,$id){
		$map = array(
			'passed' => $passed,
			'update_tm' => time(),
		);
		$where = array('id' => array("in",$id));
		$ret = $this->table('seo_caiji_news')->where($where)->save($map);	
		return 	$ret;
	} 
	function information_subclass_config(){
		$config = array(
			1 	=> 	"游戏新闻",
			2 	=>	"游戏攻略",
			3	=>  "it资讯",
			4	=>	"人工智能",
			5	=>	"互联网",
			6	=>	"社会新闻",
		);
		return $config ;
	}
	function update_data(){
		/*
		//文章中图片处理,开始
		preg_match_all("/<img.+?src=\"(\/Public\/js\/kindeditor.*?)\".+?\/>/u",$_POST['module_content'],$matches);
		if($matches[1]) {	//有需要上传的新图片
			$pre_path = $_SERVER['DOCUMENT_ROOT'];		//web根目录
			//图片宽度不超过600px检查
			foreach($matches[1] as $key => $val) {
				unset($width,$height,$type,$attr);
				list($width,$height,$type,$attr) = getimagesize($pre_path.$val);
				if($width>600) {
					$return = array(
						'code' => 0,
						'msg' => '有图片宽度超过限定的600px，请检查！',
					);
					return $return;
				}
			}
			//上传图片
			$files = array();
			$files_name = array();
			foreach($matches[1] as $key => $val) {
				$upload_model = D("Dev.Uploadfile");
				$files_name[$key] = str_replace('.','',microtime(true)).'_'.$upload_model -> rand_code(8);
			}
			foreach($matches[1] as $key => $val) {
				$files[$files_name[$key]] = '@'.$pre_path.$val;
			}
			$vals = array(
				'do' => 'save',
				'static_data' => CAIJI_UPLOAD_PATH,
			);
			$upload_model = D("Dev.Uploadfile");
			$arr = $upload_model -> _http_post(array_merge($vals,$files));		
			var_dump($arr );exit;
			//$arr = AnzhiremindAction::dev_upload($files);
			if($arr['info']['http_code']!=200) {
				$return = array(
					'code' => 0,
					'msg' => "和图片服务器通讯失败，请重试！({$arr['errno']}:{$arr['error']})",
				);
				return $return;				
			}
			//删除public下图片
			foreach($matches[1] as $key => $val) {
				unlink($pre_path.$val);
			}
			$new_arr = array();
			if($arr['ret']) {
				foreach($arr['ret'] as $key=>$val) {
					unset($k,$new_k);
					$k = array_search($key,$files_name);
					$new_k = $matches[1][$k];
					$new_arr[$new_k] = CAIJI_ATTACHMENT_HOST . $val;
				}
				//文章内容中图片路径替换
				$_POST['module_content'] = strtr($_POST['module_content'],$new_arr);
			}
		}
		*/
		unset($_POST['__hash__']);
		$map = array();	
		if($_FILES['news_pic']['tmp_name']){
			$map['news_pic'] = $this -> upload_news_pic();	
		}
		foreach($_POST as $key => $val){
			$map[$key] = $val;
		}
		$id = $_POST['id'];
		$where = array('id' => $id);
		$ret = $this->table('seo_caiji_news')->where($where)->save($map);	
		$return = array(
			'code' => $ret,
		);		
		return $return;
		//echo $this->getlastsql();exit;
	}
	function upload_news_pic(){
		$src = $_FILES['news_pic']['tmp_name'];
		$ytypes = $_FILES['news_pic']['name'];
		$info = pathinfo($ytypes);
		$type =  $info['extension'];//获取文件件扩展名
		list($msec,$sec) = explode(' ',microtime());
		$msec = substr($msec,2);
		$dir_img = CAIJI_UPLOAD_PATH. '/image/'. date("Ym/d/");
		if(!is_dir($dir_img)) {
			if(!mkdir($dir_img,0777,true)) {
				$return = array(
					'code' => 0,
					'msg' => "创建目录失败{$dir_img}",
				);
				exit(json_encode($return));		 
			}
		}
		$dst = $dir_img.$msec.'.'.$type;	
		if(move_uploaded_file($src,$dst)) {
			$path = str_replace(CAIJI_UPLOAD_PATH,'',$dst);
		}else{
			$path = '';
		}
		return 	$path;
	}
}
?>

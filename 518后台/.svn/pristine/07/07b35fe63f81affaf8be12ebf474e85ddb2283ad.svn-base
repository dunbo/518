<?php
class specialManageAction extends CommonAction {
     public $upload_pic_url = UPLOAD_PATH;//UPLOAD_PATH; //上传图片路径
    // 专题列表
     function showSpecialList() {
     $special_model = M("feature");
     $web_special_model = M("webmarket_feature_picture");
     $web_feature = $web_special_model ->field("feature_id")->select();
     $where = "";
     if($web_feature){
         foreach($web_feature as $info){
           $webfeature[] = $info['feature_id'];
         }
     $feature_id_str = implode(",",$webfeature);
     $where = "and feature_id not in (".$feature_id_str.")";
     }
     //$special_list = $special_model -> where("status = 1 and pid = 1 ".$where) -> getField("feature_id,name");
     //$specialList = $special_model -> where("status = 1 and pid = 1 ") -> getField("feature_id,name");
     $special_list = $special_model -> where("status = 1 and pid like '%,1,%' ".$where) -> getField("feature_id,name");
     $specialList = $special_model -> where("status = 1 and pid like '%,1,%' ") -> getField("feature_id,name");
     $web_special_list = $web_special_model -> select();
     $this -> assign("special_list",$special_list);
     $this -> assign("speciallist",$specialList);
     $this -> assign("web_special_list",$web_special_list);
     $this -> display("showSpecialList");
     }
     function addFeature() {
     $special_model = M("feature");
     $web_special_model = M("webmarket_feature_picture");
     $featureids = $_GET['feature_ids'];
     $featureid_arr = explode(",",$featureids);
     $count = $web_special_model -> count();
     if($count >0){
         $web_special_model ->query("delete from  __TABLE__");
     }
     $featureid_str = implode("),(",$featureid_arr);
     $featureid_str = "(".$featureid_str.");";
     $affected = $web_special_model ->query("insert into __TABLE__ (`feature_id`) values ".$featureid_str);
     $this->writelog("专题轮播图管理:添加feature_id为".$featureid_str,'webmarket_feature_picture',"{$featureids}",__ACTION__ ,"","add");
     redirect('/index.php/'.GROUP_NAME.'/specialManage/showSpecialList');
     }
     function removeFeature() {
     $special_model = M("feature");
     $web_special_model = M("webmarket_feature_picture");
     $featureids = $_GET['feature_ids'];
     $featureid_str = "(".$featureids.");";
     $affected = $web_special_model ->query("delete from __TABLE__ where feature_id in ".$featureid_str);
     redirect('/index.php/'.GROUP_NAME.'/specialManage/showSpecialList');
     }
     function specialPicList() {
     $special_model = M("feature");
     $special_list = $special_model -> where("status = 1") -> select();
     $this -> assign('special_List' , $special_list);
     $this -> display("specialPicList");
     }
     function addPicture() {
     $special_model = M("feature");
     $feature_id = $_GET['id'];
     $arr = explode("&",$feature_id);
     $fid = $arr[0];
     $special_info = $special_model -> query("select * from __TABLE__ where feature_id = ".$fid);
     $this -> assign ('method',"Add");
     $this -> assign('name',$special_info[0]['name']);
     $this -> assign('id',$special_info[0]['feature_id']);
     $this -> assign('webicon',$special_info[0]['webicon']);
     $this -> display('addPicture');
     }
     function doAddPicture() {
     $special_model = M("feature");
     $feature_id = $_POST['feature_id'];
     $upload_time = time();
     $time_arr = explode("-",date("Y-m-d",$upload_time));
     //文件名组合
/*      $pic_dir =$time_arr[0].$time_arr[1].'/'.$time_arr[2];
     $tmp_path = $_FILES['webicon']['tmp_name'];
     $file_name = $_FILES['webicon']['name'];

     $file_ext = substr($file_name,strrpos($file_name,"."));

     $file_name = $upload_time.$file_ext;
     $data = array();
     $file_path = '/img/'.$pic_dir.'/';
     $data['webicon'] = $file_path.$file_name;
     $derec_path = $this ->upload_pic_url . $file_path . $file_name;
     $affected = $this -> _mkdir($this->upload_pic_url.$file_path);
     if(!$affected){
       $this -> error("文件夹生成失败！！");
     } */
		$path = date('Ym/d/', time());
		$file_path = '/img/'.$path.'/';
		$config = array(
			'multi_config' => array(
				'webicon' => array(
					'savepath' => $this ->upload_pic_url . $file_path,
					'saveRule' => 'time'
				),
			),
			'img_p_size' =>  1024*15,
			'img_p_width' =>  161,
			'img_p_height' => 106,
		);
		$upload=$this->_uploadapk(0, $config);
		/* move_uploaded_file($tmp_path,$derec_path) */
     if($upload){
		$data['webicon'] = $upload['image'][0]['url'];
           $affected = $special_model -> where('feature_id = '.$feature_id) -> save($data);
           if($affected){
           		$this->writelog("图片添加feature_id为".$affected."内容为：".print_r($data,true),'sj_feature',"{$affected}",__ACTION__ ,"","edit");
                $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/specialManage/specialPicList');
                $this->success("图片添加成功！");
           }else{
             $this -> error("图片添加失败！");
           }
       }else{
             $this -> error("文件添加失败！");
       }
     }
     function editPicture() {
     $special_model = M("feature");
     $feature_id = $_GET['id'];
     $arr = explode("&",$feature_id);
     $fid = $arr[0];
     $special_info = $special_model -> where("feature_id = ".$fid) ->select();
     $this -> assign ('method',"Edit");
     $this -> assign('name',$special_info[0]['name']);
     $this -> assign('id',$special_info[0]['feature_id']);
     $this -> display('addPicture');
     }
     function doEditPicture() {
     $special_model = M("feature");
     $feature_id = $_POST['feature_id'];
     $upload_time = time();
/*      $time_arr = explode("-",date("Y-m-d",$upload_time));
        //文件名组合
     $pic_dir =$time_arr[0].$time_arr[1].'/'.$time_arr[2];
     $tmp_path = $_FILES['webicon']['tmp_name'];
     $file_name = $_FILES['webicon']['name'];

     $file_ext = substr($file_name,strrpos($file_name,"."));

     $file_name = $upload_time.$file_ext;
     $data = array();
     $file_path = '/img/'.$pic_dir.'/';
     $data['webicon'] = $file_path.$file_name;
     $derec_path = $this ->upload_pic_url . $file_path . $file_name;
     $affected = $this -> _mkdir($this->upload_pic_url.$file_path);
     if(!$affected){
       $this -> error("文件夹生成失败！！");
     } */

		$path = date('Ym/d', time());
		$file_path = '/img/'.$path.'/';
		$config = array(
			'multi_config' => array(
				'webicon' => array(
					'savepath' => $this ->upload_pic_url . $file_path,
					'saveRule' => 'time'
				),
			),
			'img_p_size' =>  1024*15,
			'img_p_width' =>  161,
			'img_p_height' => 106,
		);
		$upload=$this->_uploadapk(0, $config);	
     if($upload){
			$data['webicon'] = $upload['image'][0]['url'];
			$affected = $special_model -> where('feature_id = '.$feature_id) -> save($data);
			   if($affected){
					$this->writelog("图片添加feature_id为".$feature_id."内容为：".print_r($data,true),'sj_feature',"{$affected}",__ACTION__ ,"","edit");
					$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/specialManage/specialPicList');
					$this->success("图片添加成功！");
			   }else{
				 $this -> error("图片添加失败！");
			   }
       }else{
             $this -> error("文件添加失败！");
       }
     }
    //递归创建文件夹
    function _mkdir($path, $mod = '0755', $recursive = false){
    static $loop = 0;
    $loop ++;
    if (file_exists($path) || @mkdir($path, $mod, $recursive) ) {
        return true;
    }
    if($loop >20) return false;
    return $this -> _mkdir(dirname($path)) && $this -> _mkdir($path, $mod, $recursive);
    }
    function editFeatureTxt() {
        $feature_id = $_GET['id'];
        $special_model = M("feature");
        $special_txt_model = M("webmarket_feature_text");
        $special_info = $special_model -> where("feature_id = ".$feature_id) -> select();
        $special_txt_info = $special_txt_model -> where("feature_id =".$feature_id." and status = 1") -> select();
        if($special_txt_info){
            $action_title = "编辑";
            $action_method = "doEditTxtAction";
            $this -> assign("special_txt_info",$special_txt_info[0]);
        }else{
            $action_title = "添加";
            $action_method = "doAddTxtAction";
        }
        $this -> assign("feature_id",$feature_id);
        $this -> assign("special_info",$special_info[0]);
        $this -> assign("action_title",$action_title);
        $this -> assign("action_method",$action_method);
        $this -> display("editFeatureTxt");
    }
    function doEditTxtAction() {
         $id = $_POST['id'];
         $feature_id = $_POST['feature_id'];
         $data['title'] = $_POST['title'];
         $data['text'] = $_POST['text'];
         $special_txt_model = M("webmarket_feature_text");
		 $log = $this -> logcheck(array("id = " .$id. " and feature_id = ".$feature_id),'sj_webmarket_feature_text',$data,$special_txt_model);
         $affect = $special_txt_model -> where("id = " .$id. " and feature_id = ".$feature_id) -> save($data);
         if($affect){
         		$this->writelog("首页图片管理-专题文本编辑id为:".$id.",内容为:".$log,'sj_webmarket_feature_text',"{$id}",__ACTION__ ,"","edit");
                $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/specialManage/specialPicList');
                $this->success("专题文本编辑成功！");
         }else{
                $this -> error("专题文本编辑失败！");
         }
    }
    function doAddTxtAction() {
         $data['feature_id'] = $_POST['feature_id'];
         $data['title'] = $_POST['title'];
         $data['text'] = $_POST['text'];
         $data['status'] = 1;
         $special_txt_model = M("webmarket_feature_text");
         $affect = $special_txt_model -> add($data);
         if($affect){
         		$this->writelog("首页图片管理-专题文本添加,内容为".print_r($data,true),'sj_webmarket_feature_text',"{$affect}",__ACTION__ ,"","add");
                $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/specialManage/specialPicList');
                $this->success("专题文本添加成功！");
         }else{
                $this -> error("专题文本添加失败！");
         }
    }
}

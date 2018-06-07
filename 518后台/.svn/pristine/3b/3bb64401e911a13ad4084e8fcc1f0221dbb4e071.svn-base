<?php
class SchoolAction extends CommonAction {
     public function 	School_List() {
     	   $model = M('school_name');
     	   $new = $model->SELECT();
     	   //print_r($new);
     	   $this -> assign('new',$new);
         $this -> display();
     }
     public function doAdd_School() {
     	//print_r($_POST);
     	$model = M('school_name');
     	if(!empty($_POST))
     	{
     		if(!empty($_POST['key']))
     		{
     			$new = $model->where(array('schoolkey'=>$_POST['key']))->SELECT();//->order("cTime desc")->top10();
     			//print_r($new);
     		}else{
     			$this->error('MISS KEY');
     		}
     	}
     	IF(empty($new))
     	{
     		$data=array('schoolname'=>$_POST['name'],'schoolkey'=>$_POST['key'],'time'=>time());
     		$list = $model->add($data);
     	}
     	if (!empty($_FILES)) {
          //如果有文件上传 上传附件
      	 	$this->_upload($list);
      } 
         $this -> display();
     }
     public function Edit_School() {
				 $this -> display();
     }
     public function doAdd_User() {
        
     }
     public function User_List() {
     		 $model = M('school_user');
     		 $new = $model->SELECT();
     		 $this -> assign('new',$new);
         $this -> display();
     }
     public function doEdit_School(){
     	$this -> display();
    }
    protected function _upload($id) {
        import("@.ORG.UploadFile");
        $upload = new UploadFile();
        //设置上传文件大小
        $upload->maxSize = 3292200;
        //设置上传文件类型
        $upload->allowExts = explode(',', 'jpg,gif,png,jpeg');
        //设置附件上传目录
        $upload->savePath = 'D:/APMServ5.2.6/APMServ5.2.6/www/htdocs/wwwroot/newadmin.goapk.com/Tpl/default/Uploads/';
        //设置需要生成缩略图，仅对图像文件有效
        $upload->thumb = true;
        // 设置引用图片类库包路径
        $upload->imageClassPath = '@.ORG.Image';
        //设置需要生成缩略图的文件后缀
        $upload->thumbPrefix = 'm_,s_';  //生产2张缩略图
        //设置缩略图最大宽度
        $upload->thumbMaxWidth = '400,100';
        //设置缩略图最大高度
        $upload->thumbMaxHeight = '400,100';
        //设置上传文件规则
        $upload->saveRule = uniqid;
        //删除原图
        $upload->thumbRemoveOrigin = true;
        if (!$upload->upload()) {
            //捕获上传异常
            $this->error($upload->getErrorMsg());
        } else {
            //取得成功上传的文件信息
            $uploadList = $upload->getUploadFileInfo();
            //print_r($uploadList);
            //exit();
            foreach($uploadList as $key => $value)
            {
            	$data[]['image']=$uploadList[$key]['savename'];
            	$data[]['create_time'] = time();
            }
            //$_POST['image'] = $uploadList[0]['savename'];
        }
        //print_r($data);
        $model = M('school_image');
        //保存当前数据对象
        //$data['image'] = $_POST['image'];
        //$data['create_time'] = time();
        foreach($data as $key =>$value)
        {
        	if(!empty($data[$key]['image']))
        	{
        		$list = $model->add(array('uid'=>$id,'url'=>'/image/'.date('Ym/d',time()).'/m_'.$data[$key]['image'],'s_url'=>'/image/'.date('Ym/d',time()).'/s_'.$data[$key]['image'],'type'=>'1'));
        	}
        }
        //$list = $model->add($data);
        if ($list !== false) {
            $this->success('上传图片成功！');
           //print_r($uploadList);
        } else {
            $this->error('上传图片失败!');
        }
    } 
}
?>
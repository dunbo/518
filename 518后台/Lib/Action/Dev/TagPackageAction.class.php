<?php

/**
 * Desc:   标签软件管理
 */
class TagPackageAction extends CommonAction {
    public function list_category($cname){
        $category = array();
        foreach($cname as $k=>$v){
            foreach($v['sub'] as $sk=>$sv){
                $category[",{$sv['category_id']},"] = "{$sv['name']}";
            }
        }
        return $category;
    }

    public function notags_softlist(){
        //软件类型
        $soft_tmp = D("Dev.Softaudit");
        $cname = $soft_tmp ->return_category();
        $category = $this->list_category($cname);
        $this -> assign('category',$category);
        $this -> assign('cname',$cname);
        $this-> assign('from','notags_softlist');
        //软件类别条件
        if(!empty($_GET['cateid'])){
            $cateids = explode(',',$_GET['cateid']);
            $cateid = array_flip($cateids);
            $this -> assign('cateid',$cateid);
            $this -> assign("init_cateid",$_GET['cateid']);
        }

        $model = D('Dev.Tag');
        $this->assign('order',$_GET['order']);
        if($_GET){
            $res = $model->getnotagsoftlist($_GET);
        }

        $this->assign('order',$_GET['order']);
        $this->assign('list',$res['list']);
        $this->assign('page',$res['page']);
        $this->assign('count',$res['count']);
        $this->display();
    }
    public function notags_soft_de_list(){
        //软件类型
        
        $soft_tmp = D("Dev.Softaudit");
        $cname = $soft_tmp ->return_category();
        $category = $this->list_category($cname);
        $this -> assign('category',$category);
        
        $this -> assign('cname',$cname);

        $this-> assign('from','notags_soft_de_list');
        //获取游戏类别
        
        $game_category_id=array();
        $game_c=array();
        foreach($cname as $k=>$v){
            if($v['name']=='游戏'){
                $game_c=$v['sub'];
            }
        }
        foreach($game_c as $k=>$v){
            $game_category_id[]=','.$v['category_id'].',';
        }
        $_GET['notagsoftDefault']=1;
        
        $model = D('Dev.Tag');
        $this->assign('order',$_GET['order']);
        $res = $model->getnotagsoftlist($_GET,$game_category_id,1);


        $this->assign('order',$_GET['order']);
        $this->assign('list',$res['list']);
        $this->assign('page',$res['page']);
        $this->assign('count',$res['count']);
        $this->display();
    }

    //有标签软件
    public function tags_softlist()
    {
        if($this->isAjax())
        {
            $model = D('Dev.Tag');
            $softids = $_POST['softids'];
            $softidarr = explode(',',$softids);

            $rearr = array();
            foreach($softidarr as $k=>$v)
            {
                $key = 'tag_'.$v;
                $rs = $model->getsoftinfo($v);
                $rearr[$k]['key'] = $key;
                $rearr[$k]['value'] = $rs['tags'];
            }
            $this->ajaxReturn($rearr,'获取成功',1);
        }


        //软件类型
        $soft_tmp = D("Dev.Softaudit");
        $cname = $soft_tmp ->return_category();
        $category = $this->list_category($cname);
        $this -> assign('category',$category);
        $this -> assign('cname',$cname);
        $this-> assign('from','tags_softlist');
        //软件类别条件
        if(!empty($_GET['cateid'])){
            $cateids = explode(',',$_GET['cateid']);
            $cateid = array_flip($cateids);
            $this -> assign('cateid',$cateid);
            $this -> assign("init_cateid",$_GET['cateid']);
        }

        $model = D('Dev.Tag');
        $this->assign('order',$_GET['order']);
        if($_GET){
            $res = $model->getagsoftlist($_GET);
        }

        $this->assign('list',$res['list']);

        $softids = '';
        foreach($res['list'] as $v)
        {
            $softids = $softids.$v['softid'].',';
        }
        $softids = substr($softids,0,-1);
        $this->assign('softids',$softids);


        $this->assign('page',$res['page']);
        $this->assign('count',$res['count']);
        $this->display();
    }

    public function edit_tags(){
        $model = D('Dev.Tag');
        $from = $_GET['from'];
        $this-> assign('from',$from);
        $this-> assign('softid',$_GET['id']);
//        var_dump($_POST);
        $update_history = 0;
        if(!empty($_GET['id'])){
            $package = explode(',',$_GET['id']);
            if(count($package)==1){
                $tags = $model->get_package_tag($_GET['id']);
//                $update_history = 1;
                $this-> assign('tags',$tags);
            }
        }
        $all_tag=$model->get_tags(array());
        $all_tags='';
        foreach($all_tag as $v){
            $all_tags.= '"'.$v.'",';
        }
        $all_tags = substr($all_tags,0,-1);
        $this-> assign('all_tags',$all_tags);

        if(!empty($_POST['softid'])){
            $softid = explode(',',$_POST['softid']);
            if(count($softid)==1)  $update_history = 1;
        }
        if(!empty($_POST['s_tag_name'])){
			$where = array('tag_name'=> array('exp'," like '%{$_POST['s_tag_name']}%'"));
            exit(json_encode($model->get_tags($where)));
        }

        if($_POST['save_tag']==1){
            $res = $model->save_tags($_POST,$update_history);
            if($res){
                $this -> writelog("修改了包名为{$_POST['softid']}的标签",'sj_new_tag',$_POST['softid'],__ACTION__ ,'','edit');
                $this->success('保存成功');
            }else{
                $this->error('保存失败');
            }
        }

        $this->display();
    }

    public function tag_category(){
        $model = D('Dev.Tag');
        $list = $model->get_tag_category();
        $this->assign('list',$list);
        $this->display();
    }

    public function save_category(){
        $model = D('Dev.Tag');
        $type = $_GET['type'];
        $this->assign('type',$type);

        if(!empty($_GET['id'])){
            $this_cate = $model->get_category(array('id'=>$_GET['id']),$type);
            $this->assign('this_cate',$this_cate);
        }
        if($type==2){
            $category1 = $model->get_category_by_type(1);

            $this->assign('category1',$category1);
        }
        if($_POST){
            $text_length = 10;
            if (mb_strlen($_POST['name'], 'utf-8') > $text_length) {
                $this->error("标签分类{$text_length}个汉字以内");
            }
            $parten = "/[^\x{4e00}-\x{9fa5}a-z\d-_]/iu";
            if(preg_match($parten,$_POST['name'])){
                $this->error('标签分类格式不正确');
            }
            if($model->check_name($_POST)){
                if(!empty($_POST['id'])){
                    $old_info = $model->table('sj_new_tag_category')->where(array('id'=>$_POST['id']))->find();
                    $alter = true;
                    if($_POST['type'] == 2){
                        $cat_type = $model->table('sj_new_tag_cat_type')->where(array('c_id'=>$_POST['id']))->find();
                        if($cat_type['p_id']==$_POST['p_id']&&$old_info['name']==$_POST['name']&&$old_info['weight']==$_POST['weight']){
                            $alter = false;
                        }
                    }else{
                        if($old_info['name']==$_POST['name']&&$old_info['weight']==$_POST['weight']) $alter = false;
                    }
                    if(!$alter)
                        $this->error('没有任何改动');
                }
                $res = $model->save_category($_POST);
            }else{
                $this->error("标签分类不可重复");
            }

            if($res){
                if(!empty($_POST['id'])){
                    $this -> writelog("编辑了ID为{$_POST['id']}的标签分类",'sj_new_tag_category',$_POST['id'],__ACTION__ ,'','edit');
                }else{
                    $this -> writelog("添加了ID为{$res}的标签分类",'sj_new_tag_category',$res,__ACTION__ ,'','add');
                }
                $this->success('保存成功');
            }else{
                $this->error('保存失败');
            }
        }
        $this->display();
    }

    public function del_tags(){

        $model = D('Dev.Tag');
        $res = $model->del_tags($_GET);
        if($res){
            $this -> writelog("删除了ID为{$_GET['id']}的标签",'sj_new_tag',$_GET['id'],__ACTION__ ,'','del');
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
    
	
	public function import($type){
		if ($_POST) {
            if($_POST['type']==1){
                //导出失败明细
                $this->out_fail($type);
            }
            $rs = $this->read_csv($type);
            if($rs['code']==0){
                echo json_encode($rs);
                exit(0);
            }

        }
	}
	
	public function import_tags_relation(){
		$this->import(2);
        $this->assign('from','tag_category');
        $this->display();
	}
	
    public function import_tags(){
        $this->import(1);
        $this->assign('from',$_GET['from']);
        $this->display();
    }

    /***********************************
     * 读取excel数据
     * @param $file
     * @return bool
     */
    function read_csv($type) {
        $file = $_FILES['upload']['tmp_name'];
        $tmp_houzhui = $_FILES['upload']['name'];
        $tmp_arr = explode('.', $tmp_houzhui);
        $houzhui = array_pop($tmp_arr);

        if (strtoupper($houzhui) != 'CSV') {
            $this->ajaxReturn(array('code'=>0,'msg'=>'请上传格式为csv的文件'), '', 1);
        }

        $arr = array();
        $handel = fopen($file, "r");
        $i = 0;
        $package = $category = array();
        while (($num_arr = $this->mygetcsv($handel, 1000, ",")) !== FALSE) {

            //标题行不写入
            if ($i != 0) {
				if($type == 1){
					$package[] = $num_arr[0];
				}else{
					$category[] = $num_arr[0];
				}
                
                foreach($num_arr as $k=>$v){
                    $v = $this->convert_encoding($v);
                    $num_arr[$k] = $v;

                }
                $arr[$i] =  $num_arr;
            }
            $i++;
        }
        $model = D('Dev.Tag');
		if($type == 1){
			//标签导入
			list($fail_num,$correctnum,$r_data) = $model->save_import_tags($package,$arr);
		}else{
			//分类标签导入
			list($fail_num,$correctnum,$r_data) = $model->save_import_tags_re($category,$arr);
		}
        
        fclose($handel);
        //var_dump($r_data);
        if($fail_num>0){
            $this->ajaxReturn(array($fail_num,$correctnum,json_encode($r_data)), '导入失败！', 1);
        }else{
            $this->ajaxReturn(array('code'=>2,'msg'=>'添加成功'), '', 1);
        }
    }


    function out_fail($type){
        $allist = json_decode($_POST['fail_soft'],true);
        $this->down_csv($allist,$type);
    }

    function down_csv($data,$type){
        $date = date("Y-m-d H:i:s");
        if($type==2){
            $file_name = "标签分类&标签批量导入_{$date}.csv";
        }else{
            $file_name = "标签属性&标签批量导入_{$date}.csv";
        }

        header("Content-type:application/vnd.ms-excel");
        header("content-Disposition:filename={$file_name}");
        $handel = fopen("{$file_name}", "r");
        fwrite($handel,chr(0xEF).chr(0xBB).chr(0xBF));
        if($type==2){
            $desc = "标签分类ID,标签名称,失败原因\r\n";
        }else{
            $desc = "包名,软件名称,标签,失败原因\r\n";
        }


        foreach ($data as $v) {
            if(!isset($v['error'])){
                $error_str = '正确';
            }else{
                $error_str = $v['error'];
            }
            if($type==2) {
                $desc = "{$desc}{$v[0]},{$v[1]},{$error_str}\r";
            }else{
                $desc = "{$desc}{$v[0]},{$v[1]},{$v[2]},{$error_str}\r";
            }

        }
        $desc = iconv('utf-8', 'gbk', $desc);
        echo $desc;
        exit(0);
    }
}
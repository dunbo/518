<?php

if (!defined('IMG_HOST'))
    define('IMG_HOST', '/data/att/m.goapk.com');
if (!defined('IMG_URL'))
    define('IMG_URL', IMGATT_HOST);

class MessmanagerAction extends CommonAction {
    const HOST_TAG = "<!--{ANZHI_IMAGE_HOST}-->";
    //资讯列表
    function mess_list() {
      // header('content-type:text/html;charset=utf-8');
        $model = D('Sj.Coopwebset');
        $where = array();
        if(isset($_GET['passed'])){ 
          $passed=$_GET['passed'];
          $where['passed'] = $passed; 
          $this->assign('passed', $_GET['passed']);
        }else{
          $passed=1;
          $where['passed'] = $passed; 
          $this->assign('passed', 1);
        }
		$where['status'] = 1; 
        // $mess = $model->table('caiji_game_mess')->where($where)->select();
        // echo "<pre>";var_dump($mess);die;
        $count = $model->table('caiji_game_mess')->where($where)->count();
        import("@.ORG.Page2");
        $pg=$_GET['p']?$_GET['p']:1;
        $this->assign('pg', $pg);
        $param = http_build_query($_GET);
        $Page = new Page($count, 10, $param);
        $this->assign('total', $count);
		$where = array();
		$where['A.passed'] = $passed;
		$where['A.status'] = 1;
        if($passed==2){
          $mess_list = $model->table('caiji_game_mess as A')->field('A.*,B.website_name')->join('caiji_video_config as B on A.website_id=B.id')->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order('A.rank')->findALL();
           //echo $model->getlastsql();die;
        }else{
          $mess_list = $model->table('caiji_game_mess as A')->field('A.*,B.website_name')->join('caiji_video_config as B on A.website_id=B.id')->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order('A.id desc')->findALL();
		  //echo $model->getlastsql();die;
        }
        //echo $model->getlastsql();
        $list = array();
        foreach ($mess_list as $k => $v) {
            $list[$k]['id'] = $v['id'];
            $list[$k]['passed'] = $v['passed'];
            $list[$k]['news_name'] = $v['news_name']?$v['news_name']:'';
            $list[$k]['news_pic'] = $v['news_pic']?$v['news_pic']:'';
            $list[$k]['sensitive_status'] = $v['sensitive_status']?$v['sensitive_status']:'';
            $list[$k]['website_id'] = $v['website_id']?$v['website_id']:'';
            $list[$k]['website_name'] = $v['website_name']?$v['website_name']:'';
            $list[$k]['create_tm'] = $v['create_tm'] ? date("Y-m-d H:i:s", $v['create_tm']) : '';
        }
        // echo "<pre>";var_dump($list);die;
        $this->assign('list', $list);
        $this->assign('attachment_host', GAMEINFO_ATTACHMENT_HOST);
        // unset($server_list);
        // $Page->rollPage = 10;
        // $Page->setConfig('header', '篇记录');
        // $Page->setConfig('first', '首页');
        // $Page->setConfig('last', '尾页');
        // $this->assign('cmap', $cmap);
        // $this->assign('dmap', $dmap);
        $this->assign('page', $Page->show());
        // $this->assign('status', $status);
        $this->display();
    }
    //审核状态
    function change_status() {
          $passed = $_GET['passed'];
          $model = D('Sj.Coopwebset');
          if($_GET['biaoshi']==1){
            $ids = $_GET['ids'];
            $where = array(
              'id' => array('in',$ids)
            );
          }else{
            $id = $_GET['id'];
            $where = array(
              'id' => $id
            );
          }
          $data = array(
              "passed" => $passed,
              'update_tm' => time()
          );
          $old_info = $model->table('caiji_game_mess')->where(array("id"=>$id))->find();
          $res = $model->table('caiji_game_mess')->where($where)->save($data); 
          if(!empty($res)){
            if($passed==1){
              if($old_info['passed']==2){
                $this->writelog("搜索合作-资讯审核：id为{$id}的资讯由通过变为审核中",'caiji_game_mess',$id,__ACTION__ ,'','edit');
              }else{
                $this->writelog("搜索合作-资讯审核：id为{$id}的资讯由未通过变为审核中",'caiji_game_mess',$id,__ACTION__ ,'','edit');

              }
            }else if($passed==2){
              $this->writelog("搜索合作-资讯审核：通过了id为{$id}的资讯",'caiji_game_mess',$id,__ACTION__ ,'','edit');
            }else{
              $this->writelog("搜索合作-资讯审核：id为{$id}的资讯未通过",'caiji_game_mess',$id,__ACTION__ ,'','edit');
            }
            echo 1;
          }else{
            echo 2;
          }
    }
    //编辑资讯显示
    function edit_mess() {
        $model = D('Sj.Coopwebset');
        $id = $_GET['id'];
        $pg = $_GET['pg'];
        $this->assign("pg", $pg);
        $result = $model->table('caiji_game_mess')->where(array('id' => $id))->select();
        header('content-type:text/html;charset=utf-8');
        // echo "<pre>";var_dump($result);die;
        $module_content = $result[0]['module_content_web'];
        // 展示需要将图片host换上去
        $module_content = htmlspecialchars_decode($module_content);
        $module_content = str_replace(self::HOST_TAG, GAMEINFO_ATTACHMENT_HOST, $module_content);
        $result[0]['module_content_web'] = $module_content;
        // $result[0]['news_pic'] = $module_content;

        $this->assign("result", $result);
        // echo "<pre>";var_dump($result);die;
        // echo "<pre>";var_dump(GAMEINFO_ATTACHMENT_HOST);die;
        // echo "<pre>";var_dump(UPLOAD_PATH);die;
        $this->assign('attachment_host', GAMEINFO_ATTACHMENT_HOST);
        // $this->assign('function_name', $_GET['from']);
        // 记录页数参数，方便跳回第几页
        // $url_param = "";
        // foreach ($_GET as $key => $value) {
        //     if ($key == 'id' || $key == 'from')
        //         continue;
        //     if ($url_param != '')
        //         $url_param .= "&";
        //     if ($value != '')
        //         $url_param .= "{$key}={$value}";
        // }
        // $this->assign('url_param', $url_param);
        $this->display();
    }
    //审核状态
    function news_release() {
      $passed = $_GET['passed'];
      $model = D('Sj.Coopwebset');
      if($_GET['biaoshi']==1){
        // $ids = $_GET['ids'];
        // $id_arr=$ids;
        $ids = substr($_GET['ids'],1);
        $id_arr = explode(',',$ids);
        $a=1;
      }else if($_GET['biaoshi']==2){
        $ids = substr($_GET['ids'],1);
        $id_arr = explode(',',$ids);
      }else{
        $id = $_GET['id'];
        $id_arr=array($id);
      }
      foreach($id_arr as $id)
      {
        $flag=false;
        $been_result = $model->table('caiji_game_mess')->where(array('id' => $id))->select();
        if ($passed==1) {  //撤销
          $data['passed'] = 1;
          $rank_result = $model->table('caiji_game_mess')->where("rank > {$been_result[0]['rank']} and passed = 2")->select();
          foreach ($rank_result as $key => $val) {
            $data_rank['rank'] = $val['rank'] - 1;
            $change_result = $model->table('caiji_game_mess')->where(array('id' => $val['id']))->save($data_rank);
          }

          $result = $model->table('caiji_game_mess')->where(array('id' => $id))->save($data);
          if ($result) {
            $this->writelog("资讯审核_已取消id为{$id}的新闻资讯的发布",'caiji_game_mess',$id,__ACTION__ ,'','del');
          }
        } 
        else 
        {
          // 检查数据不能为空
          $non_empty_column = array('news_name', 'news_pic', 'news_content');
          foreach ($non_empty_column as $column) 
          {
            if (empty($been_result[0][$column])) 
            {
                if($a==1)//判断批量发布有问题
                {
                  $error_id[]=$id;
                  $flag=true;
                  break;
                }
                else//单条发布内容为空
                {
                  // $this->error("存在空的内容，不能发布！");
                  echo 3;return;
                }
            }
          }
          if($flag==true)
          {
            continue;
          }
          if ($been_result[0]['passed'] == 1) {  //发布
            $data['passed'] = 2;
            $data['update_tm'] = time();
            $been_result = $model->table('caiji_game_mess')->where(array('id' => $id, 'passed' => 1))->select();
            $rank_result = $model->table('caiji_game_mess')->where(array('passed' => 2))->select();
            $data['rank'] = 1;
            foreach ($rank_result as $key => $val) {
              $data_rank['rank'] = $val['rank'] + 1;
              $change_result = $model->table('caiji_game_mess')->where(array('id' => $val['id'], 'passed' => 2))->save($data_rank);
            }
            $result = $model->table('caiji_game_mess')->where(array('id' => $id))->save($data);

            if ($result) {
              $this->writelog("资讯审核：已发布id为{$id}的新闻资讯",'caiji_game_mess',$id,__ACTION__ ,'','edit');
            } 
          } elseif ($been_result[0]['passed'] == 2) { // 重新发布
            $been_result = $model->table('caiji_game_mess')->where(array('id' => $id))->select();
            $data['update_tm'] = time();
            $result = $model->table('caiji_game_mess')->where(array('id' => $id))->save($data);
            if ($result) {
              $this->writelog("已重新发布id为{$id}的新闻资讯",'caiji_game_mess',$id,__ACTION__ ,'','edit');
            }  
          } else {
            // $this->error("操作错误");
            echo 4;
          }
        }
      }
      // var_dump($result);die;
      if($result)
      {
        if($error_id)
        {
          $error_id=implode(',',$error_id);
          // $this->success("除了id为【{$error_id}存在空的内容不能发布】其他都操作成功");  
          // echo "除了id为【{$error_id}存在空的内容不能发布】其他都操作成功";
          echo $error_id;
        }
        else
        {
          // $this->success("操作成功");
          echo 1;
        }
      }
      else
      { 
        echo 2;
        // echo "全部都存在空的内容不能发布";
        // $this->error("操作失败");
      }
    }
    //编辑资讯提交
    function news_edit_submit() {
        $model = D('Sj.Coopwebset');
        $news_name = $_POST['news_name'];
        if (!$news_name) {
            $this->error("资讯标题不能为空");
        }
        $show_news_name = $_POST['show_news_name'];
        if (!$show_news_name) {
            $this->error("资讯展示标题不能为空");
        }
        $id = $_POST['id'];
        $news_pic = $_FILES['news_pic'];
        if ($news_pic['size']) {
            $high_wd = getimagesize($news_pic['tmp_name']);
            $widhig_hg = $high_wd[3];
            $wh_hg = explode(' ', $widhig_hg);
            $wh1_hg = $wh_hg[0];
            $widths_hg = explode('=', $wh1_hg);
            $width1_hg = substr($widths_hg[1], 0, -1);
            $width_go_hg = substr($width1_hg, 1);
            $hi1_hg = $wh_hg[1];
            $heights_hg = explode('=', $hi1_hg);
            $height1_hg = substr($heights_hg[1], 0, -1);
            $height_go_hg = substr($height1_hg, 1);
            if ($width_go_hg != 130 || $height_go_hg != 80) {
                //$this -> error("默认图片大小不符合条件");
            }
            preg_match("/\.(?:png|jpg|jpeg)$/i", $news_pic['name'], $matches);
            if (!$matches) {
                $this->error("上传图片类型错误！");
            }
            if ($news_pic['size'] > 35 * 1024) {
                $this->error("默认图片尺寸小于35K");
            }
            if (!$news_pic['name']) {
                $this->error("图片不能为空");
            }
            $file_img_path= UPLOAD_PATH.'/img/' . date("Ym/d/",time());
            if(!is_dir($file_img_path)){
                if(!mkdir(iconv("UTF-8", "GBK", $file_img_path),0777,true)){
                    jsmsg("创建目录失败", -1);
                }
            }
            $config = array(
                'multi_config' => array(
                    'news_pic' => array(
                        'savepath' => $file_img_path,
                        'saveRule' => 'getmsec'
                    ),
                ),
            );
            $list = $this->_uploadapk(0, $config);
            $news_url = $list['image'][0]['url'];
            $data['news_pic'] = $news_url;
        }
        $news_content = $_POST['news_content'];
        if (!$news_content) {
            $this->error("简介内容不能为空");
        }
        if (mb_strlen($news_content, 'utf-8') > 40) {
            $this->error("简介内容不能大于40个字");
        }
        $module_content = $_POST['editor_content'];
        if (empty($module_content) || $module_content == "<p>&nbsp;</p>") {
            $this->error("编辑内容不能为空");
        }
        $_POST['editor_content'] = stripcslashes($_POST['editor_content']);
        // 1，将与自己域名相关的图片域名换回约定的标签字符串
        $_POST['editor_content'] = str_replace(GAMEINFO_ATTACHMENT_HOST, self::HOST_TAG, $_POST['editor_content']);
        // 2，将富文本里的图片发送到服务器并路径内容写成约定标签
        preg_match_all("/<img.+?src=\"(\/Public\/js\/kindeditor.*?)\".+?\/>/u", $_POST['editor_content'], $matches);

        $pre_path = $_SERVER['DOCUMENT_ROOT'];

        foreach ($matches[1] as $key => $val) {
            $files_name[$key] = str_replace('.', '', microtime(true)) . '_' . MessmanagerAction::rand_code(8);
        }
        foreach ($matches[1] as $key => $val) {
            $files[$files_name[$key]] = '@' . $pre_path . $val;
        }
        $arr = MessmanagerAction::dev_upload($files);
        if ($arr['ret']) {
            foreach ($arr['ret'] as $key => $val) {
                unset($k, $new_k);
                $k = array_search($key, $files_name);
                $new_k = $matches[1][$k];
                $new_arr[$new_k] = self::HOST_TAG . $val;
            }
            //文章内容中图片路径替换
            $_POST['editor_content'] = strtr($_POST['editor_content'], $new_arr);
        }

        $been_result = $model->table('caiji_game_mess')->where(array('id' => $id))->select();
        $from = $been_result[0]['status'];
        $data['news_name'] = $news_name;
        $data['show_news_name'] = $show_news_name;
        $data['module_content_web'] = htmlspecialchars($_POST['editor_content']);
        $data['news_content'] = htmlspecialchars($news_content);
        $data['update_tm'] = time();
        /////////// new added data
        ///////////
        $data_tmp = array();
        foreach ($data as $key => $value) {
            $data_tmp[$key] = $value;
        }
        unset($data_tmp['module_content_web']);
        $where = array(
            'id' => $id
        );
        $caiji = D("Caiji");
        $log_result = $this->logcheck($where, 'caiji_game_mess', $data, $caiji);
        $find = $model->table('caiji_game_mess')->where($where)->find();
        if ($find['module_content_web'] != $data['module_content_web'])
            $log_result .= "，module_content字段也被编辑";
            $result = $model->table('caiji_game_mess')->where($where)->save($data);

        if ($result) {
            $this->writelog("资讯审核_已编辑id为{$id}的新闻资讯：" . $log_result,'caiji_game_mess',$id,__ACTION__ ,'','edit');
            // $pg = $_POST['pg'];
            $this->assign('jumpUrl', "/index.php/Sj/Messmanager/mess_list?passed={$find['passed']}&p={$_POST['pg']}");
            $this->success("编辑成功");
        } else {
            $this->error("编辑失败");
        }
    }
     //图片处理,代码来源:/dev.goapk.com/common.php
    //上传图片
    public static function dev_upload($files) {
        $vals = array(
            'do' => 'save',
            'static_data' => '/data/att/m.goapk.com',
        );
        return MessmanagerAction::_http_post(array_merge($vals, $files));
    }
    //摘自tools/ClsFactory.php中http_post函数
    public static function _http_post($vals) {
        $pro_env = C('PRO_ENV');
        if($pro_env == 1){
            //线上
            $host = '192.168.1.18';
            $host_dam = 'Host: dummy.goapk.com';
        }else if($pro_env == 2){
            $host = 'dummy.goapk.com';
            $host_dam = 'Host: dummy.goapk.com';
        }else if($pro_env == 3||$pro_env == 4){
            $host = '192.168.0.99';
            $host_dam = 'Host: 9.dummy.goapk.com';
        }

        $res = curl_init();
        curl_setopt($res, CURLOPT_URL, "http://${host}/service_dev.php");
        curl_setopt($res, CURLOPT_HTTPHEADER, array($host_dam));
        curl_setopt($res, CURLOPT_POST, true);
        curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
        $result = curl_exec($res);

        $info = curl_getinfo($res);
        $errno = curl_errno($res);
        $error = curl_error($res);
        curl_close($res);

        return array('ret' => json_decode($result, true), 'info' => $info, 'errno' => $errno, 'error' => $error);
    }
     public static function rand_code($num) {
        $str = '';
        for ($i = 0; $i < $num; $i++) {
            $str .= mt_rand(0, 9);
        }
        return $str;
    }
}
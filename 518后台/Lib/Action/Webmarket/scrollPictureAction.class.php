<?php
class scrollPictureAction extends CommonAction {
	public $picurl;  //图片URL
    public $upload_pic_url = UPLOAD_PATH;//"/data/att/m.goapk.com"; //上传图片路径
    public $url_array = array("1" =>"内链","2" => "外链");
    public $scollPicNum= 5;    //轮播图 数量
    public $type_array = array("0"=>"删除","1"=>"上线","2"=>"新上传");
		function uploadForm() {
			$scroll_picture=M("webmarket_channel");
			$pic_model = M("webmarket_show_picture");
			$pic_model_edit = M("webmarket_picture");
			$zh_time=time();
			$channel=$scroll_picture->where(array("status"=>1))->select();
/*			$id = $_GET['id'];
			$picid = $pic_model->where("id = $id")->find();
			$list = $pic_model_edit->where("id = '".$picid['picid']."'")->find();
			$this->assign("title",$list['title']);*/
			foreach ($channel as $key => $value) {
				$channel_select = $value['id'];
			
				$count_co = $pic_model -> where('chl_id =' . $channel_select.' and status = 1 and end_tm >'.$zh_time) ->order("rank desc") ->limit(1)->find();
				$count = $count_co['rank'];
				$channel[$key]['count'] = $count+1;
			}

			$this->assign("channel",$channel);
			$this -> display("uploadForm");
		}

		function uploadForm_edit(){
			$scroll_picture=M("webmarket_channel");
			$pic_model = M("webmarket_show_picture");
			$pic_model_edit = M("webmarket_picture");
			$zh_time=time();
			$chl_id = $_GET['chl_id'];
			$id = $_GET['id'];
			$text = $_GET['text'];		
			$picid = $pic_model->where("id = $id")->find();
			$list = $pic_model_edit->where("id = '".$picid['picid']."'")->find();
			$channel=$scroll_picture->where("status = 1 and id = '".$picid['chl_id']."'")->select();
			$this->assign("start_time",$picid['start_tm']);
			$this->assign("end_time",$picid['end_tm']);
			$this->assign("title",$list['title']);

			$this->assign("link",$list['link']);
			foreach ($channel as $key => $value) {
				$channel_select = $value['id'];
				$count =  $pic_model -> where('chl_id =' . $channel_select.' and status = 1 and end_tm >'.$zh_time)->count();
				//$count = $pic_model -> where('chl_id =' . $channel_select.' and status = 1 and end_tm >'.$zh_time) ->order("rank asc") ->count();
				$channel[0]['num'] = $count;
			}

			$this->assign("check",$list['link_type']);
			$this->assign("id",$id);
			$this->assign("text",$text);
			$this->assign("rank",$picid['rank']);
			$this->assign("channel",$channel);
			$this->assign("chlid",$chl_id);
			$this -> display("uploadForm_edit");
		}

		function editPicture(){

			$pic_model = M("webmarket_picture");
			$pic_show_model = M("webmarket_show_picture");
			$webmarket_model = M("webmarket_channel");

			$up_id = $_POST['id'];
			if($_POST['title']==''){
				$this->error("轮播图标题不能为空");
			}
			//检测渠道图片设置是否冲突
			$channel_arr=$_POST['channel_pic'];
			if(empty($channel_arr)){
				$this->error("请至少选择一个渠道！！！");
			}
			$count=count($channel_arr);
			$chl_arr = array();
			$rank_arr = array();
			foreach($channel_arr as $id){
			$fromdate  = strtotime($_POST["fromdate_{$id}"]);
			$todate = strtotime($_POST["todate_{$id}"]);

			$rank = $_POST["rank_{$id}"];
			$cid=$webmarket_model->where(array("id"=>$id))->getField('id');
			if(!$cid) $this ->error('不存在该渠道');
			//echo $webmarket_model->getlastsql();
			//echo aaaaaaa.$cid.bbbbbb;exit;
			$where['_string'] = 'chl_id ='.$cid.' and rank ='.$rank. ' and status=1 and start_tm<='.$todate.' and end_tm >='.$fromdate;
			
			$count = 0;
			$count = $pic_show_model -> where($where) -> count();
				if($count>0){
					$chl_arr[] = $id;
					$rank_arr[$id] = $rank;
				}
			}
			
			
			$data['title'] = $_POST['title'];
			$data['link'] =  $_POST['linkurl'];
			if(empty($data['link'])){
				$this->error("请填写图片链接后在提交！！！");
			}
			$data['link_type'] = $_POST['type'];
		
			$str=substr($data['link'],0,7);
			if($str!="http://"){
				$this->error("请正确填写连接地址(一定要加http://头)！！！");
			}
			$data['status'] = 1;
			if($_POST['chl_key'])$data['chl_key'] = $_POST['chl_key'];
			$data['upload_tm'] = time();
			$upload_date=date("Ymd",$data['upload_tm']);
			$tmp_filename = $_FILES["picurl"]["name"];
	
		if(!empty($tmp_filename)){

			$path = date('Ym/d/', time());
			if($_POST['ya']==2){
				$config = array(
					'multi_config' => array(
						'picurl' => array(
							'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'time'
						),
					),
					'img_p_size' =>  1024*50,
				);
				
			}else{
				$config = array(
					'multi_config' => array(
						'picurl' => array(
							'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'time'
						),
					),
					'img_p_size' =>  1024*50,
					'img_p_width' =>  695,
					'img_p_height' => 210,
				);
			}
			//echo "aaaaaaaa";exit;
			$upload=$this->_uploadapk(0, $config);	
			$data['picurl'] = $upload['image'][0]['url'];
			if($data['picurl']){
				//echo aaaaaa;exit;
				$result = $pic_model -> where("title = '".$data['title']."' and picurl = '".$data['picurl']."' and link ='".$data['link']."'")->count();
					if($result > 0){
						$this -> error("你的文件已上传！");
					}
					$pidd = $pic_show_model->where("id = $up_id")->find();
					$upd_id = $pidd['picid'];
					$affect = $pic_model -> where("id =$upd_id")->save($data);

					$starttime = strtotime($_POST['fromdate']." 00:00:00");
					$endtime = strtotime($_POST['todate']." 23:59:59");

					$pic_show_data['last_refresh'] = time();
					$pic_show_data['log'] = $_POST['rank'].",".time();
					$pic_show_id = $_POST['id'];
					$up_pic = $pic_show_model->where("id = $pic_show_id")->setField(array('start_tm','end_tm','last_refresh','log'),array($fromdate,$todate,$pic_show_data['last_refresh'],$pic_show_data['log']));

/*					if($affect){
						$channel_arr=$_POST['channel_pic'];
						$count=count($channel_arr);
						$channel_pic = array();
						$num=0;
						foreach($channel_arr as $k=>$val){
							$result=$webmarket_model->field("cid,chname")->where("id=".$val)->select();
							//print_r($result);exit;
							$channel_pic[$k]['chl_key']=$result[0]['cid'];
							//$channel_pic[$k]['chname']=$result[0]['chname'];
							$channel_pic[$k]['chl_id']=$val;
							$channel_pic[$k]['picid']=$affect;
							if(empty($_POST['rank_'.$val])){
								$this->error("渠道".$channel_pic[$k]['chname']."的位置为空！！！");
							}
							$channel_pic[$k]['rank']=$_POST['rank_'.$val];
							$channel_pic[$k]['status']=1;
							$channel_pic[$k]['start_tm']=strtotime($_POST['fromdate_'.$val]." 00:00:00");
							$channel_pic[$k]['end_tm']=strtotime($_POST['todate_'.$val]." 23:59:59");
							$channel_pic[$k]['last_refresh']=time();
							$channel_pic[$k]['log']=$channel_pic[$k]['rank'].",".$channel_pic[$k]['last_refresh'];
							$num+=$this->scroll_pic_judge($channel_pic[$k]['start_tm'],$channel_pic[$k]['end_tm'],$channel_pic[$k]['rank'],$channel_pic[$k]['chname'],$val);
						}
						if($num!=$count){
							$this->error("渠道图片上传失败");
							$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/scrollPicture/uploadForm');
						}
					}*/
					
						//$this->scroll_insert($channel_pic,"webmarket_show_picture");
						$zh_msg="上传轮播图id为".$affect."内容为".print_r($data,TRUE);
						$this->writelog($zh_msg,"sj_webmarket_picture","{$upd_id}",__ACTION__,'','edit');
						$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/scrollPicture/pictureList');
						$this->success("编辑成功！");
					}
			}else{
					$p_id = $pic_show_model->where("id = $up_id")->find();
					$id = $_POST['id'];
					$update_id = $p_id['picid'];
					$data_up['title'] = $_POST['title'];
					$data_up['link'] = $_POST['linkurl'];
					$data_up['link_type'] = $_POST['type'];
					$channel_arr=$_POST['channel_pic'];
					$val = $channel_arr[0];
					$start_tm=strtotime($_POST['fromdate_'.$val]." 00:00:00");
					$end_tm=strtotime($_POST['todate_'.$val]." 23:59:59");
					$rank = $_POST['rank_'.$val];
					$log = '';
					$pic_data = array('start_tm'=>$start_tm,'end_tm'=>$end_tm);
					$a_pic_data = array('title'=>$data_up['title'],'link'=>$data_up['link'],'link_type'=>$data_up['link_type']);
					$log .= $this -> logcheck(array('id' =>$id),'sj_webmarket_show_picture',$pic_data,$pic_show_model);

					$log .= $this -> logcheck(array('id' =>$update_id),'sj_webmarket_picture',$a_pic_data,$pic_model);
					$pic_show_model->where("id = $id")->setField(array('start_tm','end_tm'),array($start_tm,$end_tm));
					$affect = $pic_model-> where("id = $update_id")->setField(array('title','link','link_type'),array($data_up['title'],$data_up['link'],$data_up['link_type']));

					$this->writelog("运营位管理:市场-web:WEB渠道轮播图片管理:编辑了ID为{$id}的上线轮播图片,编辑了{$log}","sj_webmarket_picture","{$id}",__ACTION__,'','edit');
					$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/scrollPicture/scrollPicList/chl_id/'.$val.'');
							$this -> success("修改成功！");
	
				}
		}




		function addPicture(){
			$pic_model = M("webmarket_picture");
			$pic_show_model = M("webmarket_show_picture");
			$webmarket_model = M("webmarket_channel");
			if($_POST['title']==''){
				$this->error("轮播图标题不能为空");
			}
			//检测渠道图片设置是否冲突
			$channel_arr=$_POST['channel_pic'];
			if(empty($channel_arr)){
				$this->error("请至少选择一个渠道！！！");
			}
			$count=count($channel_arr);
			$chl_arr = array();
			$rank_arr = array();
			foreach($channel_arr as $id){
			$fromdate  = strtotime($_POST["fromdate_{$id}"]);
			$todate = strtotime($_POST["todate_{$id}"]);
			$rank = $_POST["rank_{$id}"];
			$cid=$webmarket_model->where(array("id"=>$id))->getField('id');
			if(!$cid) $this ->error('不存在改渠道');
			//echo $webmarket_model->getlastsql();
			//echo aaaaaaa.$cid.bbbbbb;exit;
			$where['_string'] = 'chl_id ='.$cid.' and rank ='.$rank. ' and status=1 and start_tm<='.$todate.' and end_tm >='.$fromdate;
			
			$count = 0;
			$count = $pic_show_model -> where($where) -> count();
				if($count>0){
					$chl_arr[] = $id;
					$rank_arr[$id] = $rank;
				}
			}

			if($chl_arr && $rank_arr){
				$w['_string'] =  'id in ('. implode(',',$chl_arr).')';
				$chl_list = $webmarket_model -> where($w) -> select();
				foreach($chl_list as $info){
				  $msg .= '图片在 渠道 : '.$info['nickname'].'|'.$info['chname'].'位置设置为'.$rank_arr[$info['id']]."在此时间段内有有冲突！";
				}
				$this -> error($msg);
			}
			//检测渠道图片设置是否冲突
			
			
			$data['title'] = $_POST['title'];
			$data['link'] =  $_POST['linkurl'];
			if(empty($data['link'])){
				$this->error("请填写图片链接后在提交！！！");
			}
			$data['link_type'] = $_POST['type'];
			/* if($data['link_type']==2){
				$zh_data_array=explode("/",$data['link']);
				if($zh_data_array[0]=="http:"){
					$data['link']=$data['link'];
				}else{
					$data['link']="http://".$data['link'];
				}
			}else{
				if (!ereg("^http://www.anzhi.com[\/]{1}[a-zA-Z]*[_]{1}[0-9]*[.]{1}html$",$data['link'])){
					$this->error("请正确填写内链地址！！！");
				}  
			} */
			$str=substr($data['link'],0,7);
			if($str!="http://"){
				$this->error("请正确填写连接地址(一定要加http://头)！！！");
			}
			$data['status'] = 1;
			if($_POST['chl_key'])$data['chl_key'] = $_POST['chl_key'];
			$data['upload_tm'] = time();
			$upload_date=date("Ymd",$data['upload_tm']);
			$tmp_filename = $_FILES["picurl"]["name"];
			/*$size = $_FILES["picurl"]["size"];
			if($size>61440){
				$this->error("图片过大，请缩小后在传！！！");
			}*/
			if(empty($tmp_filename)){
				$this->error("请选择图片！！！");
			}
			

			$path = date('Ym/d/', time());
			if($_POST['ya']==2){
				$config = array(
					'multi_config' => array(
						'picurl' => array(
							'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'time'
						),
					),
					'img_p_size' =>  1024*50,
				);
				
			}else{
				$config = array(
					'multi_config' => array(
						'picurl' => array(
							'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'time'
						),
					),
					'img_p_size' =>  1024*50,
					'img_p_width' =>  695,
					'img_p_height' => 210,
				);
			}
			//echo "aaaaaaaa";exit;
			$upload=$this->_uploadapk(0, $config);	
			$data['picurl'] = $upload['image'][0]['url'];
			if($data['picurl']){
				//echo aaaaaa;exit;
				$result = $pic_model -> where("title = '".$data['title']."' and picurl = '".$data['picurl']."' and link ='".$data['link']."'")->count();
					if($result > 0){
						$this -> error("你的文件已上传！");
					}
					$affect = $pic_model -> add($data);
					if($affect){
						$channel_arr=$_POST['channel_pic'];
						$count=count($channel_arr);
						$channel_pic = array();
						$num=0;
						foreach($channel_arr as $k=>$val){
							$result=$webmarket_model->field("cid,chname")->where("id=".$val)->select();
							//print_r($result);exit;
							$channel_pic[$k]['chl_key']=$result[0]['cid'];
							//$channel_pic[$k]['chname']=$result[0]['chname'];
							$channel_pic[$k]['chl_id']=$val;
							$channel_pic[$k]['picid']=$affect;
							if(empty($_POST['rank_'.$val])){
								$this->error("渠道".$channel_pic[$k]['chname']."的位置为空！！！");
							}
							$channel_pic[$k]['rank']=$_POST['rank_'.$val];
							$channel_pic[$k]['status']=1;
							$channel_pic[$k]['start_tm']=strtotime($_POST['fromdate_'.$val]." 00:00:00");
							$channel_pic[$k]['end_tm']=strtotime($_POST['todate_'.$val]." 23:59:59");
							$channel_pic[$k]['last_refresh']=time();
							$channel_pic[$k]['log']=$channel_pic[$k]['rank'].",".$channel_pic[$k]['last_refresh'];
							$num+=$this->scroll_pic_judge($channel_pic[$k]['start_tm'],$channel_pic[$k]['end_tm'],$channel_pic[$k]['rank'],$channel_pic[$k]['chname'],$val);
						}
						if($num!=$count){
							$this->error("渠道图片上传失败");
							$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/scrollPicture/uploadForm');
						}
						$this->scroll_insert($channel_pic,"webmarket_show_picture");
						$zh_msg="上传轮播图id为".$affect."内容为".print_r($data,TRUE);
						$this->writelog($zh_msg,"sj_webmarket_picture","{$affect}",__ACTION__ , "",'add');
						$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/scrollPicture/pictureList');
						$this->success("添加成功！");
					}
			}else{
				$this -> error("上传失败！");
				}
		}


		//渠道列表
		function pictureList(){
		    $channel_mod = new Model();
			$channel_list = $channel_mod ->table('sj_webmarket_channel')->where('status =1') -> order("cid desc")-> select();
		/*	$channel_pai = $channel_mod ->table('sj_webmarket_show_picture')->*/
			$this -> assign('channel_list',$channel_list);
			$this -> display('text_picList_channel');
		
		}
		//有效轮播图列表
		function scrollPicList() {
			$pic_model = M("webmarket_show_picture");
			$channel_model = M("webmarket_channel");
			$pic_list_model = M("webmarket_picture");
			$channel = M("channel");
			$channel_select = $_GET['chl_id'];
			$zh_time=time();
			$pic_list = $pic_model -> where('chl_id =' . $channel_select.' and status = 1 and start_tm <'.$zh_time.' and end_tm >'.$zh_time) ->order("rank asc") ->select();
			$count =  $pic_model -> where('chl_id =' . $channel_select.' and status = 1 and end_tm >'.$zh_time)->count();

			foreach($pic_list as $idx => $info){
				$pic_att = $pic_list_model -> where('id =' . $info['picid'].' and  status=1') -> select();
				$chan_name=$channel_model->field("chname,nickname")->where(array("id"=>$info['chl_id']))->select();
				$pic_list[$idx]['chlname']=$chan_name[0]['chname'];
				$pic_list[$idx]['nickname']=$chan_name[0]['nickname'];
				$pic_list[$idx]['title'] = $pic_att[0]['title'];
				$pic_list[$idx]['picurl'] = $pic_att[0]['picurl'];
				$pic_list[$idx]['link']  = $pic_att[0]['link'];
				$pic_list[$idx]['link_type'] = $pic_att[0]['link_type'];
				//对url进行过滤
				/* if($pic_list[$idx]['link_type']==1){
					$yu=explode("://",SCROLL_PIC_HOST);
					$zh_pic_data=explode("://",$pic_list[$idx]['link']);
					if($zh_pic_data[0]==$yu[0]){
						$zh_pic_data_one=explode("/",$zh_pic_data[1]);
						if($zh_pic_data_one[0]==$yu[1]){
							$pic_list[$idx]['link']=$pic_list[$idx]['link'];
						}else{
							$pic_list[$idx]['link']=$yu[0]."://".$yu[1]."/".$zh_pic_data[1];
						}
					}else{
						$zh_pic_data=explode("/",$pic_list[$idx]['link']);
						if($zh_pic_data[0]==$yu[1]){
							$pic_list[$idx]['link']=$yu[0]."://".$pic_list[$idx]['link'];	
						}else{
							$pic_list[$idx]['link']=$yu[0]."://".$yu[1]."/".$pic_list[$idx]['link'];
						}
					}
				} */
				
				$pic_list[$idx]['upload_tm'] = $pic_att[0]['upload_tm'];
			}
			$this -> assign("count",$count);
			$this->assign("zh_cid",$channel_select);
			$this -> assign("pic_list",$pic_list);
			$this -> display("text_picList_scroll");
		}
		function scrollPicList_out(){
			$pic_model = M("webmarket_show_picture");
			$channel_model = M("webmarket_channel");
			$pic_list_model = M("webmarket_picture");
			$channel = M("channel");
			$channel_select = $_GET['chl_id'];
			$zh_time=time();
			$out_list = $pic_model -> where('chl_id =' . $channel_select.' and status = 1 and start_tm >'.$zh_time) ->order("rank desc") -> select();
			$count =  $pic_model -> where('chl_id =' . $channel_select.' and status = 1 and end_tm >'.$zh_time)->count();
			foreach($out_list as $idx => $info){
				$pic_att = $pic_list_model -> where('id =' . $info['picid'].' and  status=1') -> select();
				$chan_name=$channel_model->field("chname,nickname")->where(array("id"=>$info['chl_id']))->select();
				$out_list[$idx]['chlname']=$chan_name[0]['chname'];
				$out_list[$idx]['nickname']=$chan_name[0]['nickname'];
				$out_list[$idx]['title'] = $pic_att[0]['title'];
				$out_list[$idx]['picurl'] = $pic_att[0]['picurl'];
				$out_list[$idx]['link']  = $pic_att[0]['link'];
				$out_list[$idx]['link_type'] = $pic_att[0]['link_type'];
				/* if($out_list[$idx]['link_type']==1){
					$yu=explode("://",SCROLL_PIC_HOST);
					$zh_pic_data=explode("://",$out_list[$idx]['link']);
					if($zh_pic_data[0]==$yu[0]){
						$zh_pic_data_one=explode("/",$zh_pic_data[1]);
						if($zh_pic_data_one[0]==$yu[1]){
							$out_list[$idx]['link']=$out_list[$idx]['link'];
						}else{
							$out_list[$idx]['link']=$yu[0]."://".$yu[1]."/".$zh_pic_data[1];
						}
					}else{
						$zh_pic_data=explode("/",$out_list[$idx]['link']);
						if($zh_pic_data[0]==$yu[1]){
							$out_list[$idx]['link']=$yu[0]."://".$out_list[$idx]['link'];	
						}else{
							$out_list[$idx]['link']=$yu[0]."://".$yu[1]."/".$out_list[$idx]['link'];
						}
					}
				} */
				$out_list[$idx]['upload_tm'] = $pic_att[0]['upload_tm'];
			}
			$this -> assign("count",$count);
			$this->assign("zh_cid",$channel_select);
			$this -> assign("out_list",$out_list);
			$this -> display("scrollPicList_out");
			
			
		}
	//过期轮播图
		function passPicList(){
			$pic_model = M("webmarket_show_picture");
			$channel_model = M("webmarket_channel");
			$pic_list_model = M("webmarket_picture");
			//$channel_select = $_GET['id'];
			$channel_key = $_GET['chl_id'];
			$date_time=time();
			$overdue_list = $pic_model -> where('chl_id ='.$channel_key.' and status = 1 and end_tm<'.$date_time ) -> order("rank desc")->select();
			foreach($overdue_list as $idx => $info){
			    $overdue_att = $pic_list_model -> where('id =' . $info['picid'].' and  status=1') -> select();
				$chan_name=$channel_model->field("chname,nickname")->where(array("id"=>$info['chl_id']))->select();
				$overdue_list[$idx]['chlname']=$chan_name[0]['chname'];
				$overdue_list[$idx]['nickname']=$chan_name[0]['nickname'];
				$overdue_list[$idx]['title'] = $overdue_att[0]['title'];
				$overdue_list[$idx]['picurl'] = $overdue_att[0]['picurl'];
				$overdue_list[$idx]['link']  = $overdue_att[0]['link'];
				$overdue_list[$idx]['link_type'] = $overdue_att[0]['link_type'];
				//对url进行过滤
				/* if($overdue_list[$idx]['link_type']==1){
					$yu=explode("://",SCROLL_PIC_HOST);
					$zh_pic_data=explode("://",$overdue_list[$idx]['link']);
					if($zh_pic_data[0]==$yu[0]){
						$zh_pic_data_one=explode("/",$zh_pic_data[1]);
						if($zh_pic_data_one[0]==$yu[1]){
							$overdue_list[$idx]['link']=$overdue_list[$idx]['link'];
						}else{
							$overdue_list[$idx]['link']=$yu[0]."://".$yu[1]."/".$zh_pic_data[1];
						}
					}else{
						$zh_pic_data=explode("/",$overdue_list[$idx]['link']);
						if($zh_pic_data[0]==$yu[1]){
							$overdue_list[$idx]['link']=$yu[0]."://".$overdue_list[$idx]['link'];	
						}else{
							$overdue_list[$idx]['link']=$yu[0]."://".$yu[1]."/".$overdue_list[$idx]['link'];
						}
					}
				} */
				$overdue_list[$idx]['upload_tm'] = $overdue_att[0]['upload_tm'];
			}
			$this->assign('zh_cid',$channel_key);
			$this -> assign('overdue_list',$overdue_list);
			$this -> display('text_picList_pass');

		}
		
	   
		//插入轮播图片渠道
		function scroll_channel_add(){
			$webmarket_picture=M("webmarket_channel");
			$webmarket_picture_list=$webmarket_picture->where("status=1")->order("id")->select();
			$this->assign("chname",$chname);
			$this->assign("webmarket_picture_list",$webmarket_picture_list);
			$this->display("add_channel");
		}
		function scroll_channel_addto(){
			$channel=M("channel");
			$scroll_picture=M("webmarket_channel");
			$channel_arr=$_POST['cid'];
			if(empty($channel_arr)){
				$this->error("未选择渠道，请选择渠道后在提交");
			}
			$channel_list=array();
			$arr = $scroll_picture->field("chname")->where("status=1")->select();
			$newname = array();
			
			foreach($arr as $v){
				$newchname[]=$v['chname'];
			}
			foreach($channel_arr as $k=>$val){
				$channel_list[$k]['chname']=$channel->where('cid='.$val)->getField('chname');
				$channel_list[$k]['cid']=$val;
				$channel_list[$k]['nickname']=$channel_list[$k]['chname'];
				$channel_list[$k]['status']=1;
				$channel_list[$k]['upload_tm']=time();
				if(in_array($channel_list[$k]['chname'],$newchname)){
					$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/scrollPicture/scroll_channel_add');
					$this->error('数据插入失败,渠道名'.$channel_list[$k]['chname'].'存在！');
				}
			}
			$this->scroll_insert($channel_list,"webmarket_channel");
			$this->success("添加成功！");
			
		}
		
		//添加渠道
		function add_channel(){
			$data['nickname']=$_POST['nickname'];
			$data['cid']=$_POST['cid'];
			$data['chname']=$_POST['chname'];
			$data['status']=1;
			$data['upload_tm']=time();
			$scroll_picture=M("webmarket_channel");
			$arr = $scroll_picture->field("nickname")->where("status=1")->select();
			$newname = array();
			foreach($arr as $v){
				$newchname[]=$v['nickname'];
			}
			//echo aaaaaaaa.$data['nickname'];exit;
			if(in_array($data['nickname'],$newchname)){
					$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/scrollPicture/scroll_channel_add');
					$this->error('数据插入失败,渠道昵称'.$data['nickname'].'存在！');
				}
			$scroll_picture->create(); 
			$res = $scroll_picture->add($data);
			if($res){
				$this->writelog("WEB首页渠道列表:添加id为{$res}的渠道","sj_webmarket_channe",$res, __ACTION__ ,'','add');
				$this->success("添加成功！");
			}else{
				$this->error("添加失败！");
			}

		}
		//删除渠道
		function delete_channel(){
			$webmarket_picture=M("webmarket_channel");
			$id=$_GET['id'];
			$affect = $webmarket_picture -> query("update __TABLE__ set status = 0 where id = " .$id);
			$this->writelog(" WEB首页渠道列表:删除了id为{$id}的渠道信息","sj_webmarket_channel","{$id}", __ACTION__ ,'','del');
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/scrollPicture/scroll_channel_add');
			$this->success('删除成功');
			
		}
		//对插入轮播图片判定
		function  scroll_pic_judge($start_tm,$end_tm,$rank,$chname,$chl_id){
			$date_time=strtotime(date("Y-m-d",time()));
			if($start_tm < $date_time||$end_tm < $date_time){
				$this->error("选择时间区间有错误！");
			}
			$pic_list = M("webmarket_show_picture");
			$map['rank'] = $rank;
			$map['chl_id'] = $chl_id;
			$map['_string'] = "status=1 AND start_tm <=".$end_tm." and end_tm >=".$start_tm;
			$count=$pic_list->where($map)->select(); 
			if($count>0){
				$this->error('渠道'.$chname.'数据插入失败');
				return 0;
			}else{
				return 1;
			}
		}
		//循环插入方法
		function   scroll_insert($channel_pic,$biao){
			$pic_list = M($biao);
			foreach($channel_pic as $k=>$val){
				$pic_list->create(); 
				$id=$pic_list->add($val); 
				//$msg_m="上传id为".$val['id']."内容为".print_r($val,TRUE)."的渠道信息";
				$this->writelog("上传id为".$id."内容为".print_r($val,TRUE)."的渠道信息","sj_".$biao,"{$id}", __ACTION__ ,'','add');
			}
		}
		//排序变更
		function scroll_picList_edit() {
         /*
         （类别从位置n变动到位置m，若n>m，则m前、n后均不变，n变为m，[>=m，<n]区间内位置依次+1；
         若n<m，则n前、m后均不变，n变为m，[>n，<=m]区间内位置依次-1）
         */
         $id = escape_string($_POST['id']);
		 $chl_id = escape_string($_POST['chl_id']);
		  $chl_key = escape_string($_POST['chl_key']);
		   $sta = escape_string($_POST['sta']);
         $m = escape_string($_POST['rank']);           //要替换的rank
         $n = escape_string($_POST['currank']);       //当前rank
		 $old_log=$_POST['log'];
		 $rank_model = M("webmarket_show_picture");
         $where = "";
		 $pic_list_update=array();
		  if(time()< $_POST['start_tm']){
				$where="start_tm > ".time();
		  }else{
				$where="start_tm < ".time()." and end_tm >".time();
		  }
         if($n > $m){   // 由大到小
              $where.= " and chl_id=".$chl_id." and status = 1 and rank >=".$m." and rank <".$n;
			  $pic_list=$rank_model->where($where)->select();
			  foreach($pic_list as $k=>$info){
				$info['rank']=$info['rank']+1;
				$info['log']=$info['log']."|".$info['rank'].",".time();
				$info['last_refresh']=time();
				$rank_model -> query("update __TABLE__ set rank = ".$info['rank'].",last_refresh=".$info['last_refresh'].",log='".$info['log']."' where id = " .$info['id']);
				$msg .="更新了id为".$info['id']."位置从".($info['rank']-1)."变成{$info['rank']} \n";
			  }
         }else if($n<$m){ // 由小到大
             $where.= " and chl_id=".$chl_id." and status = 1 and rank >".$n." and rank <=".$m;
			 $pic_list=$rank_model->where($where)->select();
			 foreach($pic_list as $k=>$info){
				$info['rank']=$info['rank']-1;
				$info['log']=$info['log']."|".$info['rank'].",".time();
				$info['last_refresh']=time();
				$rank_model -> query("update __TABLE__ set rank = ".$info['rank'].",last_refresh=".$info['last_refresh'].",log='".$info['log']."' where id = " .$info['id']);
				$msg .="更新了id为".$info['id']."位置从".($info['rank']-1)."变成{$info['rank']} \n";
			  }
         }
			   $log=$old_log."|".$m.",".time();
			   $last_refresh=time();
               $rank_model -> query("update __TABLE__ set rank = ".$m.",last_refresh=".$last_refresh.",log='".$log."' where id = " .$id);
			   $msg .="更新了id为".$id."的渠道图片信息";
			   	$this->writelog($msg,"sj_webmarket_show_picture","{$id}",__ACTION__ ,'','edit');
			   if($sta==1){
					$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/scrollPicture/scrollPicList/chl_id/'.$chl_id);
				}else{
					$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/scrollPicture/scrollPicList_out/chl_id/'.$chl_id);
				}
			   $this->success('编辑成功');
     }
	 //删除轮播图片操作
	 function picDelete(){
			$pic_id = (int) $_GET['id'];
			$rank = (int) $_GET['rank'];
			$chl_id = (int) $_GET['chl_id'];
	        if ($pic_id <= 0 || $rank <= 0 || $chl_id <= 0) {
			    $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/scrollPicture/scrollPicList/chl_id/'.$_GET['chl_id']);
			    $this->error("ID为".$id."图片删除失败！");
			}
			
			
			$chl_key = $_GET['chl_key'];
			$pic_model = M("webmarket_show_picture");
			$update['status'] = 0;
			$update['last_refresh'] = time();
			$mip['id'] = $pic_id;
			$pic_status = $pic_model -> where($mip) ->getField("title");
			$affect = $pic_model -> where($mip) -> save($update);
			if(time()< $_GET['start_tm']){
					$where="start_tm > ".time();
			}else{
					$where="start_tm < ".time();
			}
			if($affect){
				$where.=' and rank >'.$rank.' and status = 1 and chl_id='.$chl_id;
				$rank_big = $pic_model -> where($where) -> select();
				//print_r($rank_big);exit;
				foreach($rank_big as $key => $item){
					$item['rank'] = $item['rank'] -1;
					$item['log'] = $item['log']."|".$item['rank'].",".time();
					$item['last_refresh']=time();
					$pic_model -> query("update __TABLE__ set rank=".$item['rank'].",last_refresh=".$item['last_refresh'].",log='".$item['log']."' where id=".$item['id']);
				}
				$this->writelog("从轮播图列表中删除了ID为".$pic_id .",的轮播图","sj_webmarket_show_picture","{$pic_id}",__ACTION__ ,'','del');
				//$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/scrollPicture/scrollPicList/chl_id/'.$chl_id);

				$this->success("ID为".$pic_id ."轮播图删除成功！");
				}else{
				//$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/scrollPicture/scrollPicList/chl_id/'.$chl_id);
				$this->error("删除失败！");
			}
		
		}
	//轮播图片历史记录
	function picRecord(){
			$chl_id=$_GET['chl_id'];
			$start_time=strtotime($_GET['fromdate']);
			$end_time=strtotime($_GET['todate']);
			if(empty($start_time)||empty($end_time)){
				$start_time=time()-86400*10;
				$end_time=time()+86400;
			}
			$where="start_tm <=".$end_time." and end_tm >=".$start_time." and chl_id=".$chl_id;
			$channel_model = M("webmarket_show_picture");
			$webmarket_pic=M("webmarket_picture");
			//$channel=M("channel");
			$channel= M("webmarket_channel");
			$pic_list=$channel_model->where($where)->order("rank desc")->select();
			$pic_list_array=array();
			$log_list_array=array();
			foreach($pic_list  as $idex=>$val){
				$pic_list_array[$idex]['chl_key']=$val['chl_key'];
				$pic_list_array[$idex]['chl_id']=$val['chl_id'];
				$channel_name=$channel->field("chname,nickname")->where("id=".$val['chl_id'])->select();
				$title=$webmarket_pic->where("id=".$val['picid'])->getField("title");
				$pic_list_array[$idex]['id']=$val['id'];
				$pic_list_array[$idex]['rank']=$val['rank'];
				$pic_list_array[$idex]['status']=$val['status'];
				$pic_list_array[$idex]['chl_name']=$channel_name[0]['chname'];
				$pic_list_array[$idex]['nickname']=$channel_name[0]['nickname'];
				$pic_list_array[$idex]['title']=$title;
				$pic_list_array[$idex]['start_tm']=date("Y-m-d H:i:s",$val['start_tm']);
				$pic_list_array[$idex]['end_tm']=date("Y-m-d H:i:s",$val['end_tm']);
				$pic_list_array[$idex]['last_refresh']=date("Y-m-d H:i:s",$val['last_refresh']);
				$pic_list_array[$idex]['last_refresh_time']=$val['last_refresh'];
				$pic_list_array[$idex]['log']=$val['log'];
				$log_array=explode("|",$val['log']);
			
				array_pop($log_array);
			
				foreach($log_array as $k=>$info){
					$d_log_array=explode(",",$info);
					$log_list_array[$pic_list_array[$idex]['id']][$k]['rank']=$d_log_array[0];
					$log_list_array[$pic_list_array[$idex]['id']][$k]['last_refresh']=date("Y-m-d H:i:s",$d_log_array[1]);
					$log_list_array[$pic_list_array[$idex]['id']][$k]['last_refresh_time']=$d_log_array[1];
				}
			}
			$fromdate=date("Y-m-d",$start_time);
			$todate=date("Y-m-d",$end_time);
			$this -> assign("from_value",$fromdate);
			$this -> assign("to_value",$todate);
			$this -> assign("log_list_array",$log_list_array);
			$this -> assign("pic_list_array",$pic_list_array);
			$this -> display("search_pic_list");
		}
	function scroll_pic_lists(){
		$webmarket_picture=M("webmarket_picture");
		$where['status']=1;
		import("@.ORG.Page");
        $count= $webmarket_picture->where($where)->count();
		$param = http_build_query($_GET);
        $Page = new Page($count, 10, $param);
		$list=$webmarket_picture->where($where)->order("id desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($list as $k=>$val){
			if($val['link_type']==1){
				$list[$k]['link_type_name']="不弹窗";
			}elseif($val['link_type']==2){
				$list[$k]['link_type_name']="弹窗";
			}else{
				$list[$k]['link_type_name']="录入出错";
			}
		}
		//print_r($list);exit;
		if ($_GET['p'])
            $this->assign('p', $_GET['p']);
		else
        $this->assign('p', '1');
		$show = $Page->show();
        $this->assign("page", $show);
		$this->assign("list",$list);
		$this->display();
	}
	function scroll_pic_edit(){
		$webmarket_picture=M("webmarket_picture");
		$id=$_GET['id'];
		$where['status']=1;
		$where['id']=$id;
		$result=$webmarket_picture->where($where)->find();
		$this->assign("list",$result);
		$this->display();
	}
	function scroll_pic_editto(){
		$webmarket_picture=M("webmarket_picture");
		$id=$_POST['pic_id'];
		$where['id']=$id;
		$where['status']=1;
		$data['link']=$_POST['link'];
		$data['link_type']=$_POST['link_type'];
		$log = $this -> logcheck(array('id' =>$_POST['pic_id']),'sj_webmarket_picture',$data,$webmarket_picture);
		if($affect = $webmarket_picture -> where($where) -> save($data)){
			$this->writelog("首页轮播图管理_图片列表_从图列表中修改了ID为".$id .",的数据".$log,'sj_webmarket_picture',"{$id}",__ACTION__ ,'','edit');
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/scrollPicture/scroll_pic_lists');
			$this->success("ID为".$id."图片信息修改成功！");
		}else{
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/scrollPicture/scroll_pic_edit/id/{$id}');
			$this->error("ID为".$id."图片信息修改失败！");
		}
		
		
	}

		//更新某个排序
	function edit_rank_to(){
	    if(isset($_GET)){
			$table       = 'sj_webmarket_show_picture';
			$field       = 'rank';
			$target_id   = (int)$_GET['id'];
			$target_rank = (int)$_GET['rank'];
			
			if ($target_id <= 0 || $target_rank <= 0) {
			    $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/scrollPicture/scrollPicList/chl_id/'.$_GET['chl_id']);
			    $this->error("ID为".$id."图片位置修改失败！");
			}
			
			$where = array(
				'status' => 1,
				'chl_id' =>$_GET['chl_id'],
			);
			//更新排序
		    $param = $this->_updateRankInfo($table,$field,$target_id,$where,$target_rank);
			//$this -> writelog('更新了extent_id为'.$extent_id.'的区间', 'sj_extent', $extent_id);
		    
		    exit(json_encode($param));
		}
	}

}
?>
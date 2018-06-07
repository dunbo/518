<?php

class ChannelScheduleAction extends CommonAction {
    // const HOST_TAG = "<!--{ANZHI_IMAGE_HOST}-->";
	public $home_config = array(1=>7,2=>15,3=>23,4=>31,5=>39,6=>47,7=>55,8=>63,9=>71,10=>79,11=>87,12=>95,13=>103,14=>111,15=>119,16=>127,17=>135,18=>143,19=>151,20=>159,21=>167,22=>175,23=>183,24=>191,25=>199,26=>207,27=>215,28=>223,29=>231,30=>239,31=>247,32=>255,33=>263,34=>271,35=>279,36=>287,37=>295,38=>303,39=>311,40=>319,41=>327,42=>335,43=>343,44=>351,45=>359,46=>367,47=>375,48=>383,49=>391,50=>399,51=>407,52=>415,53=>423,54=>431,55=>439,56=>447,57=>455,58=>463,59=>471,60=>479);
    
    public function channel_schedule_list() {
        $model = D('Sj.ChannelSchedule');
        $where = array();
        if($_GET['schedule_tm'] && $schedule_tm = strtotime(trim($_GET['schedule_tm']))){
            $where["schedule_tm"] = array('eq', $schedule_tm);
            $this->assign("schedule_tm", $_GET['schedule_tm']);
        }else{
            $where["schedule_tm"] = array('egt', strtotime(date('Y-m-d')));
        }

        $where['status'] = 1;
        $type=$_GET['type']?$_GET['type']:1;

        $where['type'] = $type; 
        
        $this->assign('type', $type);

        $count = $model->where($where)->count();
        import("@.ORG.Page2");
        $pg=$_GET['p']?$_GET['p']:1;
        $this->assign('pg', $pg);
        $param = http_build_query($_GET);
        $Page = new Page($count, 30, $param);
        $this->assign('total', $count);

        $list = $model->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order('schedule_tm desc,rank desc')->select();
        // $home_config=array_flip($this->home_config);
        $schedule_tm_arr=array();
        foreach($list as $k=>$v){
              $list[$k]['schedule_tm']=date('Y-m-d',$v['schedule_tm']);
              if(date('Y-m-d')==$list[$k]['schedule_tm']){
                    $list[$k]['is_now_put']=1;
              }else if(date('Y-m-d')<$list[$k]['schedule_tm']){
                    $list[$k]['is_now_put']=2;
              }
              $schedule_tm_arr[$list[$k]['schedule_tm']][]=$v['id'];
              // if($v['type']==1){
              //       $list[$k]['rank']=$home_config[$v['rank']];
              // }
        }
        $schedule_tm_arr_t_n=array();

        foreach($schedule_tm_arr as $k=>$v){
            $schedule_tm_arr_t_n[$k]=count($v);
        }
        foreach($list as $k=>$v){
            if($schedule_tm_arr_t_n[$v['schedule_tm']]){
                $list[$k]['show_td']=$schedule_tm_arr_t_n[$v['schedule_tm']];
                unset($schedule_tm_arr_t_n[$v['schedule_tm']]);
            }
        }
        $this->assign('list', $list);

        // $this->assign('attachment_host', GAMEINFO_ATTACHMENT_HOST);

        $this->assign('page', $Page->show());
        $this->display();
    }

   
    public function channel_schedule_del() {
        $model = D('Sj.ChannelSchedule');
        
        if(trim($_GET['schedule_tm'])){
            $schedule_tm=strtotime(trim($_GET['schedule_tm']));
            $data = array(
                'status' => 0,
            );
            $where = array(
                'schedule_tm' => $schedule_tm,
                'type' => $_GET['type'],
            );
            $res = $model->where($where)->save($data);
        }else if($_GET['id']){
            $data = array(
                'status' => 0,
                'id' => $_GET['id'],
            );
            $res = $model->save($data);
        }
        if ($res) {
            if($_GET['id']){
                $this->writelog("删除了此条场景化卡片，其中id为{$_GET['id']}。", 'sj_channel_schedule', $_GET['id'], __ACTION__,"","del");
            }else{
            	$arr=array('1'=>'首页场景化卡片排期','2'=>'应用频道场景化排期','3'=>'游戏频道场景化排期');
                $this->writelog("删除了此条场景化卡片，其中{$arr[$_GET['type']]}的日期为{$_GET['schedule_tm']}。", 'sj_channel_schedule', $_GET['schedule_tm'], __ACTION__,"","del");
            }
            
            $this->success("操作成功");
        }else{
            $this->success("操作失败");
        }
    }
	
	public function import_schedule(){
		$type = $_GET['type'];
		if ($_GET['down_moban']) {
            $this->down_moban();
        } else if ($_FILES) {
            $err = $_FILES["upload_file"]["error"];
            if ($err) {
                $this->ajaxReturn($err,"上传文件错误，错误码为{$err}！", -1);
            }
            $file_name = $_FILES['upload_file']['name'];
            $tmp_arr = explode(".", $file_name);
            $name_suffix = array_pop($tmp_arr);
            if (strtoupper($name_suffix) != "CSV") {
                $this->ajaxReturn("",'请上传CSV格式文件！', -2);
            }
            $tmp_name = $_FILES['upload_file']['tmp_name'];
			
            $content_arr = $this->deal_schedult_file($tmp_name,$type);
			
            if ($content_arr == -1) {
                $this->ajaxReturn("",'文件打开错误，请检查文件是否损坏！', -3);
            } else if (empty($content_arr)) {
                $this->ajaxReturn("",'文件数据内容不能为空！', -4);
            }
		}
		
		$this->assign('type', $type);
		$this->display();
	}
	function deal_schedult_file($file,$type){
		$fp = fopen($file, "r");
        if ($fp === false) {
            return -1;
        }

		if($type == 1){			
			$table_name = 'sj_flexible_extent'; //首页
		}else{
			$table_name = 'sj_category_extent'; //应用,游戏
		}
		$time = time();
		$day_tm = $time + 86400*$d;
		$insert_tm = strtotime(date('Y-m-d',$day_tm));
		$day = date('n月j日',$day_tm);	 

		$key = 1;
		$mark = false;
		$data = array();
		$extent_ids = array();
		$error_msg = array();
		while(!feof($fp)){
			$line = array();
			$line = explode(',', fgets($fp));
			$rank = $line[0];
			if( $rank > 0 ){
				$insert_tm = strtotime($line[3]);
				// if($table_name == 'sj_flexible_extent'){
				// 	$rank = $this->home_config[$line[0]];
				// }
				//$key = $line[3].'_'.$rank;
				$data[$key]['rank'] = $rank;
				$data[$key]['extent_id'] = $line[1];
				$data[$key]['extent_name'] = $this->convert_encoding($line[2]);
				$data[$key]['table_name'] = $table_name;
				$data[$key]['schedule_tm'] = $insert_tm;
				$data[$key]['create_tm'] = $time;
				$data[$key]['type'] = $type;
				$extent_ids[] = $line[1];
			}
			//$error_msg[$key] = array('flag' => 0,'msg' => '');
			$key++;			
		}
		
		$error = 0;
		if($data){            
			$res_check = $this->pub_schedule_check_data($data,$table_name,$extent_ids);
			//var_dump($res_check);exit;
			if($res_check['code'] != 200){
				$this->ajaxReturn($res_check['data'],'您上传的CSV有如下问题，请修改后重新上传！', -5);
			}else{			
				$lock_name = 'sj_channel_schedule_upload_file';
				$import_lock = S($lock_name);
				if ($import_lock) {
					$this->ajaxReturn("",'后台有人正在导入，请稍后再尝试！', 1);
				}
				
				S($lock_name, 1, 60, 'File');
				$model = M('channel_schedule');
				$result_arr = array();
				$error = false;
				foreach($data as $key => $val){
					if(!($model->add($val))){
						//echo $model->getLastSql()."<br>";
						$result_arr[$key]=array('flag'=>1,'msg'=>"{$key}行插入失败");
						$error = true;
					}
				}			
				S($lock_name, NULL);
				if (!$error){
					$this->ajaxReturn("",'导入成功！', 0);
				} else {
					$this->ajaxReturn($result_arr,'存在部分导入失败记录！', -5);
				}
			}
		}
	}
	function pub_schedule_check_data($data,$table_name,$extent_ids){
			$error = false ;
			$code = 200;
			$model = new Model();
			$error_msg = array();
			$where = array(
				//'status' => 1,
				'extent_id' => array('IN', $extent_ids),
			);
            $extent_name_arr = $model->table($table_name)->where($where)->getField('extent_id,extent_name');
			foreach($data as $key => $val){ 
				$extent_db = array();
				if($extent_name_arr[$val['extent_id']] == $val['extent_name']){
					$where = array();
					//$where['rank'] = $val['rank'];
					$where['extent_id'] = $val['extent_id'];
					$where['table_name'] = $val['table_name'];
					$where['schedule_tm'] = $val['schedule_tm'];
					$where['type'] = $val['type'];
					$where['status'] = 1;
					$extent_db = $model->table('sj_channel_schedule')->where($where)->getField('id,extent_id');
					if($extent_db){
						$error_msg[$key]['flag'] = 1;
						$error_msg[$key]['msg'] .= "第{$key}行此区间已有排期;";
						$error = true;
					}
				}else{	
					$error_msg[$key]['flag'] = 1;
					$error_msg[$key]['msg'] .= "第{$key}行区间id为{$val['extent_id']}区间名 {$val['extent_name']} 填写有误;";
					$error = true;
				}
			}
			
			if($error){
				$code = 205;
			}
			return array('code'=>$code,'data'=>$error_msg);
	}
	
	function down_moban() {
        $file_dir = C("ADLIST_PATH") . "schedule_import_moban.csv";
        if (file_exists($file_dir)) {
            $file = fopen($file_dir,"r");
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: ".filesize($file_dir));
            Header("Content-Disposition: attachment; filename=" . urlencode('schedule_import_moban') . ".csv");
            echo fread($file, filesize($file_dir));
            fclose($file);
            exit(0);
        } else {
            header("Content-type: text/html; charset=utf-8");
            echo "File not found!";
            exit;
        }
    }
}
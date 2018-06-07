<?php
/**
 * 签到奖品配置
 * 2017/02/13
 */
class SignAwardAction extends CommonAction {	
	//通用奖品库
	public function award_list()
	{
		$model	=	D('Sj.SignAward');
		$where	=	array('is_pub' => 1, 'status' => 1);//奖品库
		$count	=	$model->getcount($where);
		import("@.ORG.Page");
		$param	=	http_build_query($_GET);
		$Page	=	new Page($count, 15, $param);
		$show	=	$Page->show();
		$order	=	' id desc';
		$list	=	$model->table('qd_draw_prize') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) ->order($order) -> select();
		$this->assign('page', $show);
		$this->assign('list', $list);
		$this->display();
	}
	//添加通用奖品
	public function award_add(){
		if($_POST){
			$model	=	D('Sj.SignAward');
			$table	=	"qd_draw_prize";
			$ret	=	$model -> pub_award_add_do($table);
			if($ret['code'] == 1) {
				$config	=	$ret['config'];
				$data	=	$ret['data'];
				if (!empty($config['multi_config'])) {
					$list = $this->_uploadapk(0, $config);
					foreach($list['image'] as $val) {
						$data[$val['post_name']] = $val['url'];
					}
				}
				$res = $model -> table($table) -> add($data);
				if($res){
					$this -> writelog("已添加id为{$res}的通用活动奖品",$table,$res,__ACTION__ ,'','add');
					$this -> success("操作成功");
				}else{
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}
		}else{
			$this -> display();
		}
	}
	
	//编辑通用奖品
	public function award_edit()
	{
		$id		=	$_GET['id'] ? (Int)$_GET['id'] : 0;
		$model	=	D('Sj.SignAward');
		if($_POST) {
			$model = D('Sj.SignAward');
			$table = "qd_draw_prize";
			$ret = $model -> pub_award_add_do($table);
			if($ret['code'] == 1){
				$config	=	$ret['config'];
				$data	=	$ret['data'];
				if (!empty($config['multi_config'])) {
					$list = $this->_uploadapk(0, $config);
					foreach($list['image'] as $val) {
						$data[$val['post_name']] = $val['url'];
					}
				}
				$where	=	array('id'=>$_POST['id']);
				$res	=	$model -> table($table)->where($where) -> save($data);
				if($res){
					$this -> writelog("已编辑id为{$_POST['id']}的通用活动奖品",$table,$_POST['id'],__ACTION__ ,'','edit');
					$this -> success("操作成功");
				}else{
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}
		}else {
			$where	=	array('id'=>$id);
			$list	=	$model->table('qd_draw_prize')->where($where)->find();
			$this -> assign('list',$list);
			$this -> display('award_add');
		}
	}
	
	//删除通用奖品
	function award_del(){
		$model = D('Sj.SignAward');
		$map = array(
				'update_tm' => time(),
				'status' => 0,
		);
		$where = array('id' => $_GET['id']);
		$result = $model -> table('qd_draw_prize')->where($where)->save($map);
		$this -> assign('jumpUrl',$_SERVER['HTTP_REFERER']);
		if($result){
			$this -> writelog("已删除id为{$_GET['id']}的通用活动奖品",'qd_draw_prize',$_GET['id'],__ACTION__ ,'','edit');
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}	

	//设置中奖概率
	public function sign_award_probability($table = '' ){
		$model	=	D('Sj.SignAward');
		$table	=	"qd_draw_prize";
		if($_POST) {
			$num2	=	explode("/",$_POST['probability']);
			$pos	=	strpos($_POST['probability'],"/");
			$id		=	$_POST['id'];
			$aid	=	$_POST['aid'];
			$atp	=	$_POST['atp']; //0或者空表示某天签到抽奖 1表示连续签到抽奖
			if(!empty($_POST['probability']) && $pos == false) {
				$this -> error("概率格式错误");
			}else if($_POST['probability'] != 0 && (!is_numeric($num2[0]) || !is_numeric($num2[1]))){
				$this -> error("中奖概率格式出错");
			}else if($_POST['probability'] == '') {
				$_POST['probability'] = 0;
			}
			if( $atp == 1 ) {
				$where = array('id'=>array('neq',$id),'cid'=>$aid,'status'=>1);
			}else {
				$where = array('id'=>array('neq',$id),'did'=>$aid,'status'=>1);
			}
			$list = $model -> table($table) -> where($where) ->field('probability')-> select();
			$probability_num = 0;
			foreach($list as $v) {
				if(empty($v['probability'])){
					continue;
				}
				$num = explode("/",$v['probability']);
				$calculate = ($num[0]/$num[1]);
				$probability_num = $probability_num + $calculate;
			}
			$calculate2 = ($num2[0]/$num2[1]);
			$probability_num = $probability_num + $calculate2;
			if($probability_num  > 1){
				$this -> error("概率不能大于1");
			}
			$where = array(
					'id'	=>	$_POST['id'],
			);
			$data = array(
					'probability'	=>	$_POST['probability'],
					'update_tm'		=>	time(),
			);
			$log = $this->logcheck($where, $table, $data, $model);
			$res = $model -> table($table) -> where($where) -> save($data);
			$this -> assign('jumpUrl',$_SERVER['HTTP_REFERER']);
			if($res){
				$this -> writelog("活动编辑概率：id为[{$_POST['pid']}]<br/>".$log,$table,$_POST['pid'],__ACTION__ ,'','edit');
				$this -> success("操作成功");
			}else{
				$this -> error("操作失败");
			}
		}else{
			$where = array('id'=>$_GET['id']);
			$list = $model -> table($table) -> where($where) -> field('id,cid,did,probability,name')->find();
			$this -> assign('atp',$_GET['atp']);
			$this -> assign('list',$list);
			$this -> display();
		}
	}
	
	//添加签到抽奖奖品配置
	function sign_award_add()
	{
		$atp = $_REQUEST['atp'];
		if($_POST) {
			$aid	=	$_POST['aid'];
			$level	=	$_POST['level'];
			if( !$aid || !$level ) {
				$this -> error("参数有误");
			}
			$mode	=	$_POST['mode'];
			$model	=	D('Sj.SignAward');
			$ret	=	$model -> sign_award_add_do('');
			if($ret['code'] == 1) {
				$config	=	$ret['config'];
				$data	=	$ret['data'];
				$data['level']	=	$level;
				$condition = array(
					'level'	=>	$level,
				);
				if( $atp == 1 ) {
					$condition['cid']	=	$aid;
					$data['cid']		=	$aid;
					//根据连续签到id获取月份id
					$info = $model->field('mid')->table('qd_sign_continuity')->where(array('id'=>$aid))->find();
					$data['mid'] =	$info['mid'];
				}else {
					$data['did']		=	$aid;
					$condition['did']	=	$aid;
					//根据每日签到id获取月份id
					$info = $model->field('mid')->table('qd_sign_days')->where(array('id'=>$aid))->find();
					$data['mid'] =	$info['mid'];
				}
				//检查level奖品位置不能重复添加
				$val_data	=	$model->table('qd_draw_prize')->where($condition)->find();
				if( !empty($val_data) ) {
					$this -> error('奖品位置不能重复添加');
				}
				if( $mode == 1 ) {
					//通用奖品库
					//获取这条通用奖品
					$rows = $model->table('qd_draw_prize')->where(array('id'=>$data['award_id'],'is_pub'=>1))->find();
					$data['level']		=	$level;
					$data['name']		=	$rows['name'];
					$data['pic_path']	=	$rows['pic_path'];
					$data['status']		=	1;
					$data['type']		=	$rows['type'];
					$data['is_pub']		=	0;
					$data['desc']		=	$rows['desc'];
					unset($data['award_id']);
					$result = $model->table('qd_draw_prize')->add($data);
					if( $data['type'] == 4 ) {
						//生成礼包码
						$gift_tm = json_decode($data['gift_file'], true);
						$this->gift_code_auto($result, $data['mid'], $ret['gift_code'],$gift_tm['start_tm'],$gift_tm['end_tm']);
					}
				}else {
					if (!empty($config['multi_config'])) {
						$list = $this->_uploadapk(0, $config);
						foreach($list['image'] as $val) {
							$data[$val['post_name']] = $val['url'];
						}
					}
					$result = $model -> table('qd_draw_prize')->add($data);
					if( $data['type'] == 4 ) {
						//生成礼包码
						$gift_tm = json_decode($data['gift_file'], true);
						$this->gift_code_auto($result, $data['mid'], $ret['gift_code'],$gift_tm['start_tm'],$gift_tm['end_tm']);
					}
				}
				if($result) {
					$this -> writelog("已添加id为{$result}的活动奖品",'qd_draw_prize',$result,__ACTION__ ,'','add');
					$this -> success("操作成功");
				}else {
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}
		}else {
			$aid	=	(Int)$_GET['aid'];//某天的id
			$level	=	(Int)$_GET['level'];//添加的位置为level
			if(!$aid || !$level) {
				$this -> error("参数有误");
			}
			//获取通用奖品库
			$where	=	array('is_pub' => 1, 'status' => 1);//奖品库
			$pub_awarad_list = D('Sj.SignAward')->table('qd_draw_prize')->where($where)->order('id desc')->select();
			$this -> assign('aid', $aid);
			$this -> assign('atp', $atp);
			$this -> assign('level', $level);
			$this -> assign('pub_awarad_list', $pub_awarad_list);
			$this -> display();
		}
	}
	
	function gift_code_auto($pid, $mid, $data, $start_tm, $end_tm)
	{
		if( !$pid || !$mid || !$data ) {
			return false;
		}
		$time	=	time();
		$model	=	D('Sj.SignAward');
		$model->table('qd_sign_gift')->where(array('pid'=>$pid))->delete();
		$cli	=	"/usr/local/bin/redis-cli -h";
		$ip		=	C('LOTTERY_REDIS_HOST');
		$port	=	C('LOTTERY_REDIS_PORT');
		shell_exec("{$cli} {$ip} -p {$port}  keys 'web_sign_gift:{$mid}:{$pid}:gift_num:*' | xargs {$cli} {$ip}  -p {$port} DEL");
		$val_str = '';
		foreach ( $data as $k => $v) {
			$val_str .= "('{$v[2]}',0,'',{$time},{$time},'{$v[1]}','{$v[0]}',{$mid},{$pid},{$start_tm},{$end_tm}),";
		}
		$val_str = trim($val_str, ',');
		$sql = "insert into qd_sign_gift(`gift_number`,`status`,`uid`,`create_tm`,`update_tm`,`package`,`softname`,`mid`,`pid`,`start_tm`,`end_tm`) values".$val_str;
		$res = $model->execute($sql);
		//刷缓存
		$redis	=	new redis();
		$redis -> connect(C('LOTTERY_REDIS_HOST'),C('LOTTERY_REDIS_PORT'));
		$pkg_key	=	"web_sign_gift:{$mid}:{$pid}:pkg";
		$limit		=	1000;
		$start		=	0;
		$cache_time	=	60*86400;
		for($start=0;;$start++) {
			$where = array(
					'mid'		=>	$mid,
					'pid'		=>	$pid,
					'status'	=>	0,
			);
			$offset	=	$start*$limit;
			$list	=	$model->table('qd_sign_gift')->where($where)->limit($offset.','.$limit)->select();
			if( !$list ) {
				break;
			}
			$prize_gift	=	array();
			foreach($list as $k => $v) {
				$prize_gift_pkg[]				=	$v['package'];
				$prize_gift[$v['package']][]	=	$v;
			}
			$redis->set($pkg_key, json_encode(array_unique($prize_gift_pkg)));
			$redis->setTimeout($pkg_key, $cache_time);
			foreach($prize_gift as $k => $v) {
				$gift_key	=	"web_sign_gift:{$mid}:{$pid}:gift_num:".$k;
				foreach ($v as $kk => $vv) {
					$redis->rpush($gift_key, json_encode($vv));
				}
				$redis->setTimeout($gift_key, $cache_time);
			}
		}
		return $res;
	}
	
	//编辑签到抽奖奖品配置
	function sign_award_edit()
	{
		$id		=	$_GET['id'];//奖品id
		$mid	=	$_GET['mid'];//月份id
		$model	=	D('Sj.SignAward');
		if( $_POST ) {
			$id		=	$_POST['id'];//奖品id
			$mid	=	$_POST['mid'];//月份id
			$mode	=	$_POST['mode'];
			$ret	=	$model -> sign_award_add_do('');
			if($ret['code'] == 1) {
				$config	=	$ret['config'];
				$data	=	$ret['data'];
				$prize_info = $model->table('qd_draw_prize')->where(array('id'=>$id,'is_pub'=>0))->find();
				if( $mode == 1 ) {
					//通用奖品库
					//获取这条通用奖品
					$rows = $model->table('qd_draw_prize')->where(array('id'=>$data['award_id'],'is_pub'=>1))->find();
					$map = array(
							'name'		=>	$rows['name'],
							'pic_path'	=>	$rows['pic_path'],
							'pkg_path'	=>	$data['pkg_path'],
							'num'		=>	$data['num'],
							'prize_num'	=>	$data['prize_num'],
							'status'	=>	1,
							'type'		=>	$rows['type'],
							'gift_file'	=>	$data['gift_file'],
							'desc'		=>	$rows['desc'],
							'update_tm'	=>	$data['update_tm'],
							'is_pub'	=>	0,
					);
					//检查是否修改了类型
					if( $prize_info['type'] != $map['type'] ) {
						$map['probability'] = 0;//每次编辑奖品类型改变概率清零
						$notice = "请重新配置中奖概率！";
					}
					$where = array('id' => $id);
					$result = $model->table('qd_draw_prize')->where($where)->save($map);
					if( $result && $data['type'] == 4 ) {
						//生成礼包码
						$gift_tm = json_decode($data['gift_file'], true);
						$this->gift_code_auto($id, $mid, $ret['gift_code'],$gift_tm['start_tm'],$gift_tm['end_tm']);
					}
				}else {
					if (!empty($config['multi_config'])) {
						$list = $this->_uploadapk(0, $config);
						foreach($list['image'] as $val) {
							$data[$val['post_name']] = $val['url'];
						}
					}
					//检查是否修改了类型
					if( $prize_info['type'] != $data['type'] ) {
						$data['probability'] = 0;//每次编辑奖品类型改变概率清零
						$notice = "请重新配置中奖概率！";
					}
					$where = array('id' => $id);
					$result = $model -> table('qd_draw_prize')->where($where)->save($data);
					if( $result && $data['type'] == 4 ) {
						//生成礼包码
						$gift_tm = json_decode($data['gift_file'], true);
						$this->gift_code_auto($id, $mid, $ret['gift_code'],$gift_tm['start_tm'],$gift_tm['end_tm']);
					}
				}
				if($result) {
					$this -> writelog("已编辑id为{$id}的活动奖品",'qd_draw_prize',$id,__ACTION__ ,'','add');
					$this -> success("操作成功！{$notice}");
				}else {
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}
		}else {
			$rows	=	$model->table('qd_draw_prize')->where(array('id'=>$id))->find();
			//获取通用奖品库
			$where	=	array('is_pub' => 1, 'status' => 1);//奖品库
			$pub_awarad_list = D('Sj.SignAward')->table('qd_draw_prize')->where($where)->order('id desc')->select();
			$gift_arr = json_decode($rows['gift_file'], true);
			$gift_count = D('Sj.SignAward')->table('qd_sign_gift')->where(array('pid'=>$id))->count();
			$this -> assign('id', $id);
			$this -> assign('mid', $mid);
			$this -> assign('rows', $rows);
			$this -> assign('gift_arr', $gift_arr);
			$this -> assign('gift_count', $gift_count);
			$this -> assign('pub_awarad_list', $pub_awarad_list);
			$this -> display('sign_award_add');
		}
	}
	
	//查看上传礼包文件和上传礼包直接发放
	public function pub_gift_view()
	{
		if( $_FILES['gift_file']['tmp_name'] ) {
			$model		=	D('Sj.SignAward');
			$gift_type	=	$_GET['gift_type'];
			$array		=	array('csv');
			$ytypes		=	$_FILES['gift_file']['name'];
			$info		=	pathinfo($ytypes);
			$type		=	$info['extension'];//获取文件件扩展名
			if( !in_array($type, $array) ) {
				$return = array(
					'code'	=>	0,
					'msg'	=>	"上传格式错误",
				);
				exit(json_encode($return));
			}
			$data_file	=	file_get_contents($_FILES['gift_file']['tmp_name']);
			//判断是否是utf-8编辑
			if(mb_check_encoding($data_file,"utf-8") != true) {
				$data_file	=	iconv("gbk","utf-8", $data_file);
			}
			$data_file	=	str_replace("\r\n","\n",$data_file);	
			$data_arr	=	explode("\n", $data_file);
			$data_arr	=	array_unique($data_arr);
			if( $gift_type == 4 ) {
				//礼包文件
				$newarr = array();
				$d_str = '';
				$str = '';
				$count = 0;
				foreach($data_arr as $k=>$v) {
					if($k == 0) {
						continue;
					}
					if( !$v ){
						continue;
					}
					list($softname, $pkg, $num) = explode(',',$v);
					if( $newarr[$num] ) {
						$str .= "重复数据：".$num."\n";
					}else {
						$newarr[$num]	=	1;
						$d_str .= $softname.':'.$pkg.':'.$num.';';
						$count ++;
					}
				}
				$d_str = trim($d_str,';');
				$i_str = "<input type='hidden' value='{$d_str}' name='code' />";
				if( $str != '' ) {
					$return = array(
							'code'	=>	0,
							'msg'	=>	$str,
					);
					exit(json_encode($return));
				}else {
					$return = array(
							'code'	=>	1,
							'msg'	=>	htmlentities("<span style='color:red'>验证通过，礼包码数量：{$count}个</span>".$i_str, ENT_COMPAT | ENT_HTML401, 'utf-8'),
					);
					exit(json_encode($return));
				}
			}elseif( $gift_type == 5 ) {
				//礼包直接发放
				$package	=	array();
				foreach ($data_arr as $k => $v) {
					if( $k == 0 ) {
						continue;
					}
					if( !empty($v) ) {
						$package[] = $v;
					}
				}
				$gift_data	=	array();
				$count		=	count($package);
				$page		=	1;
				$pageSize	=	20;
				$total		=	$count/$pageSize;
				$gift_count	=	0;
				for($i = 0; $i<$total; $i++) {
					$startNum	=	($page-1)*$pageSize;
					$val = 	array_slice($package, $startNum, $pageSize);
					$result = $model->httpGet($val);
					if( $result['code'] == 200 && !empty($result['result']) ) {
						foreach ($val as $key => $val) {
							foreach ($result['result'] as $k => $v) {
								if( $val == $v['giftSoftPname'] ){
									$gift_data[$val][] = array(
											'id'			=>	$v['id'],
											'giftSoftName'	=>	$v['giftSoftName'],
											'giftName'		=>	$v['giftName'],
											'giftTotal'		=>	$v['giftTotal'],
									);
									$gift_count += $v['giftTotal'];
								}
							}
						}
					}
					$page++;
				}
				
				if( !empty($gift_data) ) {
					//上传包名
					$date	 = date("Ym/d/");
					$dir_img = C('ACTIVITY_CSV') . '/package/'.$date;
					if(!is_dir($dir_img)) {
						if(!mkdir($dir_img,0777,true)) {
							//创建pagckage目录{$dir_img}失败
							$return = array(
									'code' => 0,
									'msg' => "创建pagckage目录{$dir_img}失败",
							);
							return $return;
						}
					}
					list($msec,$sec) = explode(' ',microtime());
					$types = 'csv';
					$msec = substr($msec,2);
					$dst = $dir_img.'package'.$_POST['aid'].'_'.$_POST['pid'].'_'.$msec.'.'.$types;
					if(move_uploaded_file($_FILES['package']['tmp_name'],$dst)) {
						$path = str_replace(C('ACTIVITY_CSV'),'',$dst);
						$str .= "<input type='hidden' id='pkg_path' name='pkg_path' value='{$path}'>";
					}
				
					$str .= "<ul data='{$gift_count}' style='list-style-type: none;text-align: left;'>";
					foreach ( $gift_data as $k => $v ) {
								$str .= "<li>{$k}</li>";
								$str .= "<li>";
						foreach ( $v as $kk => $vv) {
								$value = $k.":".$vv['id'];
								$str .= "<input type='checkbox' checked onclick='gift_change()' name='code[]' data='{$vv['giftTotal']}' value='{$value}'/>{$vv['giftName']}({$vv['giftTotal']})";
						}
						$str .= '</li>';
					}
					$str .= "</ul>";
					$return = array(
							'code'	=>	1,
							'msg'	=>	htmlentities($str, ENT_COMPAT | ENT_HTML401, 'utf-8'),
					);
					exit(json_encode($return));
				}else {
					$return = array(
							'code' => 0,
							'msg' => '未找到包名对应的礼包',
					);
					exit(json_encode($return));
				}
			}else {
				$return = array(
						'code' => 0,
						'msg' => '选择的类型有误',
				);
				exit(json_encode($return));
			}
		}		
	}
	
	
	//签到奖品配置列表
	function sign_award_list()
	{
		$aid = $_GET['aid'];
		$atp = $_GET['atp']?$_GET['atp']:0; //0或者空表示某天签到抽奖 1表示连续签到抽奖
		if( !$aid ) {
			$this->error('参数有误');
		}
		$model_award = D('Sj.SignAward');
		$where_go = array(
				'is_pub'	=>	0,
				'status'	=>	1,
		);
		if( $atp == 1 ) {
			$where_go['cid'] = $aid;
		}else {
			$where_go['did'] = $aid;
		}
		$list = array();
		$award_list	=	$model_award->table('qd_draw_prize')->where($where_go)->limit('0,8')->order('level asc')->select();
		foreach ($award_list as $key =>$val) {
			$list[$val['level']] = $val;
		}
		$this->assign('aid', $aid);
		$this->assign('atp', $atp);
		$this->assign('list', $list);
		$this -> display();
	}
	
	//签到奖品配置列表 -- 纯列表形式
	function sign_award_pure_list()
	{
		$aid = $_GET['aid'];
		$atp = $_GET['atp']?$_GET['atp']:0; //0或者空表示某天签到抽奖 1表示连续签到抽奖
		if( !$aid ) {
			$this->error('参数有误');
		}
		$model_award = D('Sj.SignAward');
		$where_go = array(
				'is_pub'	=>	0,
				'status'	=>	1,
		);
		if( $atp == 1 ) {
			$where_go['cid'] = $aid;//连续签到id
		}else {
			$where_go['did'] = $aid;//每日签到id
		}
		$list = array();
		$award_list	=	$model_award->table('qd_draw_prize')->where($where_go)->limit('0,8')->order('level asc')->select();
		foreach ($award_list as $key =>$val) {
			$list[$val['level']] = $val;
		}
		$this->assign('aid', $aid);
		$this->assign('atp', $atp);
		$this->assign('list', $list);
		$this -> display();
	}
	
	function sign_award_detail()
	{
		$aid = $_GET['aid'];
		$atp = $_GET['atp']; //0或者空表示某天签到抽奖 1表示连续签到抽奖
		if( !$aid ) {
			$this->error('参数有误');
		}
		$model_award = D('Sj.SignAward');
		$where_go = array(
				'is_pub'	=>	0,
				'status'	=>	1,
		);
		if( $atp == 1 ) {
			$where_go['cid'] = $aid;
		}else {
			$where_go['did'] = $aid;
		}
		$list = array();
		$award_list	=	$model_award->table('qd_draw_prize')->where($where_go)->limit('0,8')->order('level asc')->select();
		foreach ($award_list as $key =>$val) {
			$list[$val['level']] = $val;
		}
		$this->assign('aid', $aid);
		$this->assign('atp', $atp);
		$this->assign('list', $list);
		$this -> display();
	}
	
	function pub_get_coupon()
	{
		$id = $_REQUEST['coupon_id']?(Int)$_REQUEST['coupon_id']:0;
		$result = D('Sj.SignAward')->httpGetCoupon($id);
		if( $result['success'] ) {
			exit(json_encode( array('code'=>1, 'data'=>$result['data']['couponCount'])));
		}else {
			exit(json_encode(array('code'=>0, 'msg'=>$result['msg'])));
		}
		
	}
}
?>

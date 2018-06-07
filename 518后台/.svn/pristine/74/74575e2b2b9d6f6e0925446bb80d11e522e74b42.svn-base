<?php
/*****
******庄超滨 2015-03-20  
******过滤软件model
*******************/
class FilterModel extends Model {
	//添加过滤软件
	function add_filter_soft(){
		$pkg = trim($_POST['package']);
		$time = time();
		$sql_str = '';
		foreach($_POST['filter_module'] as $val){
			if($val){
				$start_tm =  strtotime($_POST[$val.'_begintime']);
				$end_tm =  strtotime($_POST[$val.'_endtime']);
				$sql_str .= ",('{$pkg}','{$val}','{$_POST[$val.'_filter_area']}','{$_POST[$val.'_area_value']}','{$start_tm}','{$end_tm}','{$time}','{$time}')";
				if($end_tm < $start_tm){
					return array('code'=>0,"msg"=>"结束时间不能小于开始时间"); 
				}
				if($_POST[$val.'_filter_area'] > 0 && !$_POST[$val.'_area_value']){
					return array('code'=>0,"msg"=>"添加地区没有选择"); 
				}
			}
		}
		$sql_str =  substr($sql_str,1);
		$sql = "INSERT INTO sj_soft_filter (`package`,`filter_module`,`filter_area`,`area`,`start_tm`,`end_tm`,`add_tm`,`update_tm`) VALUES ".$sql_str;
		$this -> query($sql);
		return array('code'=>1,"msg"=>"添加成功"); 
	}
	//获取模块
	function get_module_conf(){
		$filter_map = $this ->table('sj_filter_map') ->order('type asc')->select();
		$config = array();
		foreach($filter_map as $k => $v){
			if($v['type'] == 1){				
				$list = $this ->table($v['table']) -> where("status=1") -> field('name,id')->select();
				if($v['table'] == 'sj_list'){
					$str = "榜单-";
				}
				foreach($list as $key => $val){
					$config[$v['map_key'].$val['id']] = $str.$val['name'];
				}
			}else{
				$config[$v['map_key']] = $v['name'];
			}
		}
		return $config;
	}
	//验证包名存在
	function check_package(){
		$where = array(
			"package" => trim($_GET['pkg']),
			'status' => 1
		);
		$res = $this -> table('sj_soft_filter') -> where($where)->find();
		if($res){
			return array('code'=>0,'msg' => '该软件已存在！');
		}else{
			return array('code'=>1,'msg' => '成功');
		}
	}
	//获取屏蔽列表数据
	function get_filter_list($where){
		//分页		
		import('@.ORG.Page2');
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 20;	
		$param = http_build_query($_GET);
		$total = $this->table('sj_soft_filter')->where($where)->field("count(DISTINCT package) as total")->find();
		$Page = new Page($total['total'],$limit,$param);
		$Page->rollPage = 20;
        $Page->setConfig('header','条记录');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');	
		
		$result = $this->table('sj_soft_filter')->where($where)->limit($Page->firstRow.','.$Page->listRows)->group('package')->field('id,package')->order('id desc') -> select();
		$package = array();
		foreach($result as $k => $v){
			$package[] = $v['package'];
		}
		$where = array("package" => array('in',$package),'status'=>1);
		$list = $this->table('sj_soft_filter')->where($where)->select();
		$arr = array();
		foreach($list as $key => $val){
			$arr[$val['package']][ $val['filter_module']]= $val;
		}
		//获取软件名称
		$soft_info = get_table_data($where,"sj_soft","package","package,softname");	
		//整理数据
		foreach($result as $k => $v){
			$result[$k]['id'] = $v['id'];
			$result[$k]['package'] = $v['package'];
			$result[$k]['package'] = $v['package'];
			$result[$k]['softname'] = $soft_info[$v['package']]['softname'];
		}		
		//var_dump($result);
		return array($result,$total,$Page,$arr);		
	}
	//获取该包名的所有数据
	function get_pkg_data(){
		$pkg = trim($_GET['pkg']);
		$where = array("package" => $pkg,'status'=>1);
		$list = $this->table('sj_soft_filter')->where($where)->select();
		$arr = array();
		foreach($list as $key => $val){
			$arr[$val['package']]['filter_module'][] = $val['filter_module'];
			$arr[$val['package']]["{$val['filter_module']}".'_filter_area'] = $val['filter_area'];
			$arr[$val['package']]["{$val['filter_module']}".'_area'] = $val['area'];
			$arr[$val['package']]["{$val['filter_module']}".'_begintime'] = date('Y-m-d H:i:s',$val['start_tm']);
			$arr[$val['package']]["{$val['filter_module']}".'_endtime'] = date('Y-m-d H:i:s',$val['end_tm']);
		}		
		return $arr;
	}
	//编辑
	function edit_filter_soft(){
        $model = new Model();	
		$filter_module = $this -> get_module_conf();		
		$pkg = trim($_POST['package']);
		$where = array('package' => $pkg);
		$filter_module_list = $this -> table('sj_soft_filter') -> where(array('package' => $pkg,'status'=>1)) -> field('filter_module')->select();
		$filter_module_arr = array();
		foreach($filter_module_list as $v){
			$filter_module_arr[] = $v['filter_module'];
		}
		unset($filter_module_list);
		$log = '';		
		if($filter_module_arr){
			$result = array_diff($filter_module_arr, $_POST['filter_module']);
			if($result){
				$mo_name = array();
				foreach($result as $v){
					$mo_name[] = $filter_module[$v]; 
				}
				$log .= "<br/>删除了模块【".implode("、",$mo_name)."】<br/>";
			}
		}
		$map = array('status' => 0);
		$this -> table('sj_soft_filter') -> where($where) -> save($map);
		$filter_area = array(
			0 => '全部屏蔽',
			1 => '屏蔽指定区域',
			2 => '只在指定区域展示其他都屏蔽',
		);
		$time = time();
		$error = '';
		foreach($_POST['filter_module'] as $val){
			$map = array(
				'package' => $pkg,
				'filter_area' => $_POST[$val.'_filter_area'],
				'filter_module' => $val,
				'area' => $_POST[$val.'_area_value'],
				'status' => 1,
				'update_tm' => $time,
				'start_tm' => strtotime($_POST[$val.'_begintime']),
				'end_tm' => strtotime($_POST[$val.'_endtime']),
			);	
			$where = array(
				'package' => $pkg,
				'filter_module' => $val,
			);
			$res = $this -> table('sj_soft_filter') -> where($where) ->find();
			$start_tm = $res['start_tm'] ? date("Y-m-d H:i:s",$res['start_tm']) : 0;
			$end_tm = $res['end_tm'] ? date("Y-m-d H:i:s",$res['end_tm']) : 0;
			if($res){
				if(in_array($val,$filter_module_arr)){
					if($res['filter_area'] != $_POST[$val.'_filter_area'] || $res['area'] != $_POST[$val.'_area_value'] || $res['start_tm'] != $map['start_tm'] || $res['end_tm'] != $map['end_tm']){
						$log .= "编辑";
						$log .= "模块:【{$filter_module[$val]}】<br/>";
						if($res['filter_area'] != $_POST[$val.'_filter_area']){
							$log .= "指定区域由【{$filter_area[$res['filter_area']]}】改为【{$filter_area[$_POST[$val.'_filter_area']]}】<br/>";
						}
						if($res['area'] != $_POST[$val.'_area_value']){
							if($res['area'] || $_POST[$val.'_area_value']){
								$log .= "添加地区由【{$res['area']}】改为【{$_POST[$val.'_area_value']}】<br/>";
							}
						}
						if($res['start_tm'] != $map['start_tm'] || $res['end_tm'] != $map['end_tm']){
							$log .= "起止时间由【{$start_tm}=>{$end_tm}】改为【{$_POST[$val.'_begintime']}=>{$_POST[$val.'_endtime']}】<br/>";	
						}
					}					
				}else{
					$log .= "添加";	
					$log .= "模块:【{$filter_module[$val]}】<br/>";
					$log .= "指定区域为【{$filter_area[$_POST[$val.'_filter_area']]}】<br/>";
					if($_POST[$val.'_area_value']){
						$log .= "添加地区为【{$_POST[$val.'_area_value']}】<br/>";
					}
					$log .= "起止时间由【{$start_tm}=>{$end_tm}】改为【{$_POST[$val.'_begintime']}=>{$_POST[$val.'_endtime']}】<br/>";						
				}
				$ret = $this -> table('sj_soft_filter') -> where($where) -> save($map);
			}else{
				$log .= "添加";	
				$log .= "模块:【{$filter_module[$val]}】<br/>";
				$log .= "指定区域为【{$filter_area[$_POST[$val.'_filter_area']]}】<br/>";
				if($_POST[$val.'_area_value']){
					$log .= "添加地区为【{$_POST[$val.'_area_value']}】<br/>";
				}
				$log .= "起止时间由【{$start_tm}=>{$end_tm}】改为【{$_POST[$val.'_begintime']}=>{$_POST[$val.'_endtime']}】<br/>";					
				$map['add_tm'] = $time;
				$ret = $this -> table('sj_soft_filter') -> add($map);
			}		
			if(!$ret){
				$error .= "包名为{$pkg}模块为{$val}操作失败";
				return array('code'=>0,"msg"=>"编辑失败"); 
			}
		}
		return array('code'=>1,"msg"=>"编辑成功","log"=>$log); 
	}	
}
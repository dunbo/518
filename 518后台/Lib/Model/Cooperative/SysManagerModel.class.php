<?php
class SysManagerModel extends AdvModel{
	protected $connect_id = 18;
	protected $tablePrefix = 't_';
	
	
	public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_COOPERATIVE');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
	
	// 初始配置信息
	function select_conf(){
		$this->trueTableName = 't_init_config';
		$this->flush();
		$r = $this->where('status=1')->find();
		return $r;
	}
	// 编辑初使信息
	function edit_conf($data,$id){
		$this->trueTableName = 't_init_config';
		$this->flush();
		//$this->fields = array_keys($data);
		if ($id){
		    $old =  $this->where(array('id'=>$id,'status'=>1))->select();
		    $old = $old[0];
		    $change = array();
		    foreach ($old as $k=>$v) {
		        if ($k == 'ad_price' || $k == 'ad_max_down') {
		            if ($data[$k] != $v) 
		                $change[$k] = $data[$k];
		        }
		    }
		    
			//修改所有的渠道相关配置是用事务
			$save_data = array();
			$sr = 1;
            $this->startTrans();		    
		    $r = $this->where(array('id'=>$id))->data($data)->save();
            foreach ($change as $k=>$v) {
                $save_data[$k] = $v; 
            }
            if (!empty($save_data)) {
                $sr = $this->table('t_channel_config')->where(true)->data($save_data)->save();
                $sr = $sr >= 1 ? 1 : 0;
            }
            
            if (($r & $sr) > 0) {
		        $this->commit();
		        return true;
            } else {
                $this->rollback();
                return false;
            }
 		} else {
			return false;
		}
	}
	// 停用恢复
	function changeAdminStauts($id,$status){
		$this->trueTableName = 't_manager';
		$this->flush();
		$data['status'] = $status;
		$data['update_time'] = time();

		return $this->where(array('id' => $id))->save($data);
	
	}	
	// 查看账号范围
	function seeAccount($aid){
		$this->trueTableName = 't_charge';
		$this->flush();
		$charge = $this->where(array('status' => 1))->field('id,charge_name')-> order('create_time DESC') -> select();
		
		$purview = $this->table('t_manager_purview')->where(array('aid' => $aid))->field('charge_type,charge_value')-> order('create_time DESC') -> select();
		$user = $this->table('t_user')->field('uid,user_name,charge_person,create_time,status')-> order('create_time DESC') -> select();
		foreach ($user as $key=>$value){
			$user[$key]['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
			if ($value['status']==1){
				$user[$key]['status'] = '正常';
			} elseif ($value['status']==2){
				$user[$key]['status'] = '暂停';
			}
		}
		foreach ($user as $k=>$v){
			foreach ($purview as $val){
				if ($val['charge_type']==1 && ($val['charge_value']==$v['uid'])){
					$user[$k]['selected'] = 1;
				}
			}
		}
		
		foreach ($charge as $k=>$v){
			foreach ($user as $val){
				if ($val['charge_person']==$v['id']){
	
					$charge[$k]['account'][] = $val; 
				}
			}
			foreach ($purview as $value){
				if ($value['charge_type']==2 &&($v['id']==$value['charge_value'])){
					$charge[$k]['selected'] = 1;
				}
			}
		}
		return $charge;
	}
	// 查看账号范围 添加
	function diffAccount($data){
		$this->trueTableName = 't_manager_purview';
		$this->flush();
		$affectid = $this -> add($data);
		return $affectid;
	}
	// 复制
	function copyAccount($fromId,$toId){
		$this->trueTableName = 't_manager_purview';
		$this->flush();
		
		$accountC = $this->where(array('aid' => $toId))->field('aid,charge_type,charge_value')->select();	// 复制人
		$account = $this->where(array('aid' => $fromId,'charge_type' => 1))->field('aid,charge_type,charge_value')->select();	// 被复制人
		
		$account_id_C = array();
		$charge_id_C = array();
		foreach ($accountC as $v) {
			if ($v['charge_type']==1){
				$account_id_C[] = $v['charge_value'];
			} elseif ($v['charge_type']==2){
				$charge_id_C[] = $v['charge_value'];
			}
		}
		$account_id = array();
		$charge_id = array();
		foreach ($accountC as $v) {
			if ($v['charge_type']==1){
				$account_id[] = $v['charge_value'];
			} elseif ($v['charge_type']==2){
				$charge_id[] = $v['charge_value'];
			}
		}
		$new_account = array_diff($account_id, $account_id_C);
		$new_charge = array_diff($charge_id, $charge_id_C);
		if (!empty($new_account)){
			$data = array('aid'=>1,'charge_type'=>1);
			foreach ($new_account as $v){
				$data['charge_value'] = $v;
				$this->add($data);
			}
		}
		if (!empty($new_charge)){
			$data = array('aid'=>1,'charge_type'=>2);
			foreach ($new_charge as $v){
				$data['charge_value'] = $v;
				$this->add($data);
			}
		}
	}
	// 添加负责人
	function addCharge($chargeName){
		$this->trueTableName = 't_charge';
		$this->flush();
		
		$chargeName_zh = Pinyin($chargeName);
		$chargeName_zh = $chargeName_zh ? $chargeName_zh : '';
		$data = array(
			'charge_name'=>$chargeName,
			'charge_name_zh'=>$chargeName_zh,
			'status'=>1
		);
		$data['create_time']=$data['update_time']=time();
		return $this->add($data);
	}
	// 编辑负责人
	function editCharge(){
		$this->trueTableName = 't_charge';
		$this->flush();
		
		if ($this->checkCharge($_POST['charge'])){
			$data['charge_name'] = $_POST['charge'];
			$data['charge_name_zh'] = Pinyin($_POST['charge']);
			$data['update_time'] = time();
			return $this->where("id={$_POST['id']}")->save($data);
		} else {
			echo '已存在负责人';
		}
	}
	// 检查负责人是否存在　
	function checkCharge($name){
		$this->trueTableName = 't_charge';
		$this->flush();
		return $this->where(array('charge_name' => $name,'status' => 1))->count();
	}
	// 删除账号负责人
	function delcharge($id) {
        //判断是否该负责人没有所属的账号
	    $charge_number = $this->table('t_user')->where(array('charge_person' => $id))->field('COUNT(*) num')->select();
		if ($charge_number[0]['num'] > 0) {
		    return false;
		}
		
		$this->trueTableName = 't_charge';
		$this->flush();
		$data['status'] = 0;
		$data['update_time'] = time();
		$r = $this->where("id={$id}")->save($data);
		return $r;
	}
	// 账号转移 列表
	function chargeAccountList(){
		$chargeid = '';	//负责人id
		
		$this->trueTableName = 't_user';
		$this->flush();
		$user = $this->where(array('charge_person' => $chargeid))->field('uid,user_name,create_time,userstatus')->select();
		
		$chargeName = $this->table('t_charge')->where(array('id' => $chargeid))->field('charge_name')->find();
		
		foreach ($user as $k=>$v){
			$user[$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
			$user[$k]['charge_person'] = $chargeName;
		}
		return $user;
	}
	// 账号转移
	function moveAccount($uid,$copyid,$toid){
		$this->trueTableName = 't_user';
		$this->flush();

		$data['charge_person'] = $toid;
		$re = $this->where("charge_person=$copyid AND uid in({$uid})")->save($data);
		$uid_array = explode(",", $uid);
		//获取拥有copyid的负责人的管理员去掉要转移的账号权限
	    $this->trueTableName = 't_manager_purview';
		$this->flush();
		$re_f = $this->where(array('charge_type' => 1,'charge_value' => $copyid))->select();
		foreach ($re_f as $purview) {
		    foreach ($uid_array as $t_user_uid) {		   
			    $this->where(array('aid' => $purview['aid'],'charge_type' => 2,'charge_value' => $t_user_uid))->delete();
		    }
		}
		//获取拥有toid的负责人的管理员加上要转移的账号权限
		$this->trueTableName = 't_manager_purview';
		$this->flush();
		$re_t = $this->where(array('charge_type' => 1,'charge_value' => $toid))->select();
		foreach ($re_t as $purview) {
		    foreach ($uid_array as $t_user_uid) {		   
			    $data = array(
			        'aid' => $purview['aid'],
			        'charge_type' => 2,
			        'charge_value' => $t_user_uid,
			        'create_time' => time() 
			    );
			    $this->add($data);
		    }
		}
		
		$manager_purview_data = array(
		    'aid' => 't_manager_purview',
		);
		
		return $re;
	}
	// 添加日志
	function addLog($data){
		$this->trueTableName = 't_log';
		$this->flush();	
		/*$data = array(
		 	'actions'=>'',
			'create_time'=>'',
			'referer'=>'',
			'operating'=>'',
			'field'=>'',
			'operator'=>'',
			'account_name'=>'',
			'channel_name'=>'',
			'charge'=>'',
			'before_adjustment'=>'',
			'adjusted'=>''
		);*/
		return $this->add($data);
	}
	
	//获取全部的管理人员
	function getAllManager() {
	    $this->trueTableName = 't_manager';
	    $this->flush();
	    $managers = $this->where('status=1')->select();
	    $re = array();
	    foreach ($managers as $manager) {
	        $re[] = array('aid' => $manager['aid'],'aname' => $manager['aname']);
	    }
	    
	    return $re;
	}
	
	//获取全部的负责人
	function getAllCharge() {
	    $this->trueTableName = 't_charge';
	    $this->flush();
	    $charges = $this->where('status=1')->select();
	    $re = array();
	    foreach ($charges as $charge) {
	        $re[$charge['id']] = array('id' => $charge['id'],'charge_name' => $charge['charge_name']);
	    }
	    
	    return $re;
	}
	
	/**
	 * 
	 * 日志校验函数
	 * @param array $pk_value like array('xx'=>1,'xx'=>2), pk为表的主键，value为主键值的固定格式数组
	 * @param string $table
	 * @param array $new_columnvalue like array('user_name'=>'xxxxx','login_name'=>'xxxxx') 数组的key为表的字段，value为新值
	 */
	function logcheck($pk_value, $table, $new_columnvalue) {
	
	    $this->trueTableName = $table;
	    $this->flush();
	    
	    $pk = $pk_value['pk'];
	    $id = $pk_value['value'];
	    
	    $string = '';
	    $i = 0;
	    foreach ($pk_value as $key=>$value) {
	        if ($i>0)
	            $string .= ' and ';
	        $sting .= " $key = '$value' "; 
	    }
	    
	    $old = $this->where($sting)->select();

	    if (empty($old)) return "get old null!";
	    $old = $old[0];
	    
	    $re_column = array();
	    
	    $sql = "SELECT  COLUMN_NAME, COLUMN_COMMENT
				FROM  `information_schema`.`COLUMNS` 
				WHERE TABLE_SCHEMA='cooperative_operations' AND table_name = '{$table}';
	    ";
	    $column_comment_ary = $this->query($sql);
	    
	    $column_comment = array();
	    foreach ($column_comment_ary as $v) {
	        $column_comment[$v['COLUMN_NAME']] = $v['COLUMN_COMMENT'];
	    }
	    
	    foreach ($new_columnvalue as $column => $v) {
	        if ($old[$column] != $v) {
	            $re_column[$column] = array($column_comment[$column], $old[$column], $v);
	        }
	    }
	    
	    
	    return $re_column;
	}
}






















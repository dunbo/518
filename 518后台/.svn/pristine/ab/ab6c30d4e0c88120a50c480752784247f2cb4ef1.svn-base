<?php
class AdSearchModel extends Model{
	private $ad_pic_count_limit = 8;
	//用户名
	public function get_user_code(){
		$array = array('csv');
		$ytypes = $_FILES['user_file']['name'];
		$info = pathinfo($ytypes);
		$type =  $info['extension'];//获取文件件扩展名
		$error = '';
		if(!in_array($type,$array)){
			$error .= "上传格式错误\n";
			exit(json_encode(array('code'=>0,'msg'=>$error)));
		}else{		
			$tmp_name = $_FILES['user_file']['tmp_name'];
			$data_arr = $this -> get_file_data($tmp_name);
			$list_arr = array();
			foreach($data_arr as $v){
				if($list_arr[$v]){
					$error .= $v.";有重复数据\n"; 
					continue;
				}	
				$list_arr[$v] = 1;
			}
			if($error !=''){
				exit(json_encode(array('code'=>0,'msg'=>$error)));
			}
			list($msec,$sec) = explode(' ',microtime());
			$msec = substr($msec,2);
			$file_str = "/tmp/$msec.csv";
			if(!move_uploaded_file($tmp_name,$file_str)){
				$error .= "上传出错\n";
				exit(json_encode(array('code'=>0,'msg'=>$error)));
			}else{
				exit(json_encode(array('code'=>1,'msg'=>$file_str)));
			}
		}
	}
	public function logic_check_ness($res){
		$useful_key = array('extent_id', 'package', 'prob', 'start_at', 'end_at', 'been_install','type');
        foreach($useful_key as $key=>$value) {
            if (isset($res[$value]))
                $res[$value] = trim($res[$value]);
        }            
        // 调用通用的检查函数
        $content_arr = array();
        $content_arr[0] = $res;
        // 统一的逻辑检查：检查添加软件数据是否合法
	    // function logic_check($content_arr) {
	        // 业务逻辑：区间表、区间软件表
	        $M_extent_table = 'necessary_extent';
	        $M_extent_soft_table = 'necessary_extent_soft';
	        // 加一下前缀（真正的表名），主要用在join sql里
	        $extent_table = 'sj_' . $M_extent_table;
	        $extent_soft_table = 'sj_' . $M_extent_soft_table;
	        // 获得三个表的model
	        $extent_model = M($M_extent_table);
	        $extent_soft_model = M($M_extent_soft_table);
	        $soft_model = M("soft");//软件大表
	        // 业务逻辑：以下为各项具体检查
	        foreach($content_arr as $key=>$record) {
	            // 检查是不是编辑，如果是编辑，给record增加extent_id字段并分配其在表里的值
	            if (isset($record['id'])) {
	                $where = array('id' => array('EQ', $record['id']));
	                $find = $extent_soft_model->where($where)->find();
	                $record['extent_id'] = $find['extent_id'];
	            }
	            // 检测区间ID

	            if (isset($record['extent_id']) && $record['extent_id'] != "") {
	                $where = array(
	                    'extent_id' => array('EQ', $record['extent_id']),
	                    'type' => array('EQ', 1),
	                    'status' => array('EQ', 1),
	                );
	                $find = $extent_model->where($where)->find();

	                if (!$find) {
	                	return array('code'=>1,'message'=>"区间位ID【{$record['extent_id']}】无效;");
	                    $this->append_error_msg($error_msg, $key, 1, "区间位ID【{$record['extent_id']}】无效;");
	                } else {
	                    if (isset($record['extent_name'])) {
	                        // 检查区间ID与区间名是否对应
	                        if ($find['extent_name'] != $record['extent_name']) {
	                            return array('code'=>1,'message'=>"区间位ID与区间名不对应;");
	                            $this->append_error_msg($error_msg, $key, 1, "区间位ID与区间名不对应;");
	                        }
	                    }
	                    // 得到该记录区间的cid、oid和pid，并保存起来，方便后面的区间冲突检查
	                    $content_arr[$key]['cid'] = $find['cid'];
	                    $content_arr[$key]['oid'] = $find['oid'];
	                    //$content_arr[$key]['pid'] = $find['pid'];
	                }
	            } else {
	            	return array('code'=>1,'message'=>"区间位ID不能为空;");
	                $this->append_error_msg($error_msg, $key, 1, "区间位ID不能为空;");
	            }
	            // 检查包名是否在sj_soft表里
	            if (isset($record['package']) && $record['package'] != "") {
	                $where = array(
	                    'package' => $record['package'],
	                    'status' => 1,
	                    'hide' => array('in', array(1, 1024)),
	                );
	                $find = $soft_model->where($where)->find();
	                if (!$find) {
	                	return array('code'=>1,'message'=>"包名【{$record['package']}】不存在于市场软件库中;");
	                    $this->append_error_msg($error_msg, $key, 1, "包名【{$record['package']}】不存在于市场软件库中;");
	                }
	            } else {
	            	return array('code'=>1,'message'=>"包名不能为空;");
	                $this->append_error_msg($error_msg, $key, 1, "包名不能为空;");
	            }
	        }
	        // 检查每一行数据是否与数据库的存储内容相冲突
	        foreach($content_arr as $key=>$record) {
	            // // 业务逻辑：如果填写的区间无效，则不比较
	            // if (!isset($record['cid']) || !isset($record['oid']))
	            //     continue;
	            // // 如果开始时间或结束时间无效，则不比较
	            // if (!isset($record['bk_start_time']) || !isset($record['bk_end_time']))
	            //     continue;
	            // 检查是否与sj_extent_soft_v1表里有相同包名且区间冲突的包
	            // 业务逻辑：获得当前记录的信息：package、cid、oid
	            $es_package = escape_string($record['package']);
	            $cid = escape_string($record['cid']);
	            $oid = escape_string($record['oid']);
	            $start_time = escape_string($record['start_time']);
	            $end_time = escape_string($record['end_time']);
	            // 业务逻辑：构造sql语句，查找出与该记录包名相同、也是在相同属性的区间的所有后台记录
	            $sql_select = "select {$extent_soft_table}.id as id, {$extent_soft_table}.package as package, {$extent_soft_table}.status as status, {$extent_table}.extent_name, {$extent_soft_table}.start_at as start_at, {$extent_soft_table}.end_at as end_at";
	            $sql_from = " from {$extent_soft_table} left join {$extent_table}";
	            $sql_on = " on {$extent_soft_table}.extent_id={$extent_table}.extent_id";
	            $sql_where = " where {$extent_soft_table}.package='{$es_package}' and {$extent_soft_table}.start_at <= {$record['end_at']} and {$extent_soft_table}.end_at >= {$record['start_at']} and {$extent_soft_table}.status!=0 and {$extent_table}.status=1 and {$extent_table}.type=1 and {$extent_table}.cid='{$cid}' and {$extent_table}.oid='{$oid}'";
	            // 如果有传id过来，说明是编辑，这时要排除此id
	            $sql_where_except = "";
	            if (isset($record['id'])) {
	                $except_id = escape_string($record['id']);
	                $sql_where_except = " and {$extent_soft_table}.id != {$except_id}";
	            }
	            // 将select、from、on、where、except拼接起来
	            $sql = $sql_select . ' '. $sql_from . ' ' . $sql_on . ' ' . $sql_where . ' ' .  $sql_where_except;
	            // 执行sql
	            $db_records = $extent_soft_model->query($sql);
	            // echo $extent_soft_model->getLastSql();
	            // echo "<pre>";var_dump($db_records);die;
	            // 有冲突的记录
	            foreach($db_records as $db_key=>$db_record) {
	                $start_at_str = date('Y-m-d H:i:s',$db_record['start_at']);
	                $end_at_str = date('Y-m-d H:i:s',$db_record['end_at']);
	                $status_paused_hint = "";
	                if ($db_record['status'] == 2) {
	                    $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
	                }
	                return array('code'=>1,'message'=>"投放区间与后台区间【{$db_record['extent_name']}】里ID为【{$db_record['id']}】，包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
	                $this->append_error_msg($error_msg, $key, 1, "投放区间与后台区间【{$db_record['extent_name']}】里ID为【{$db_record['id']}】，包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
	            }
	        }
	        return 2;
	        return $error_msg;
	    // }
	}
	// 返回冲突id，否则返回0
     // 冲突逻辑：查找该软件所在区间的页面下所有区间是否有与该软件有排期冲突的软件
    public function check_conflict_FlexibleExtent($record, $id = 0) {
        $content_type = $record['content_type'];
        $start_at = $record['start_at'];
        $end_at = $record['end_at'];
        $extent_id = $record['extent_id'];
        $model = M();
        if ($content_type == 1) {
            // 查找包名
            $content_key = 'package';
            $content_value = $record['package'];
        } else if ($content_type == 2) {
            // 查找活动
            $content_key = 'activity_id';
            $content_value = $record['activity_id'];
        } else if ($content_type == 3) {
            // 查找专题
            $content_key = 'feature_id';
            $content_value = $record['feature_id'];
        } else if ($content_type == 4) {
            // 查找页面
            $content_key = 'page_type';
            $content_value = $record['page_type'];
        } else if ($content_type == 5) {
            // 查找网页
            $content_key = 'website';
            $content_value = $record['website'];
        } else {
            return false;
        }
        // 区间表、区间软件表
        $M_extent_table = 'flexible_extent';
        $M_extent_soft_table = 'flexible_extent_soft';
        // 加一下前缀（真正的表名），主要用在join sql里
        $extent_table = 'sj_' . $M_extent_table;
        $extent_soft_table = 'sj_' . $M_extent_soft_table;
        $extent_model = M($M_extent_table);
        $extent_soft_model = M($M_extent_soft_table);
        // 根据extent_id查找其所在的平台和页面，因为检查冲突时，是要检查该区间所在页面（某个平台）下的所有区间
        $where = array(
            'extent_id' => $extent_id,
        );
        $find = $extent_model->where($where)->find();
        $pid = $find['pid'];
        $belong_page_type = $find['belong_page_type'];
        
        
        // 构造sql
        $sql_select = "select {$extent_soft_table}.id as id, {$extent_soft_table}.{$content_key} as content_key, {$extent_soft_table}.status as status, {$extent_table}.extent_name, {$extent_soft_table}.start_at as start_at, {$extent_soft_table}.end_at as end_at";
        $sql_from = " from {$extent_soft_table} left join {$extent_table}";
        $sql_on = " on {$extent_soft_table}.extent_id={$extent_table}.extent_id";
        $sql_where = " where {$extent_soft_table}.{$content_key}='{$content_value}' and {$extent_soft_table}.start_at <= {$end_at} and {$extent_soft_table}.end_at >= {$start_at} and {$extent_soft_table}.status!=0 and {$extent_table}.status=1 and {$extent_table}.belong_page_type='{$belong_page_type}' and {$extent_table}.pid='{$pid}'";
        // 如果有传id过来，说明是编辑，这时要排除此id
        $sql_where_except = "";
        if ($id) {
            $except_id = escape_string($id);
            // 拼接sql，A为下面的$sql里表sj_extent_soft_v1的别名，注意二者需一致
            $sql_where_except = " and {$extent_soft_table}.id != {$except_id}";
        }
        // 将select、from、on、where、except拼接起来
        $sql = $sql_select . ' '. $sql_from . ' ' . $sql_on . ' ' . $sql_where . ' ' .  $sql_where_except;
        // 执行sql
        $db_records = $extent_soft_model->query($sql);
        // echo $extent_soft_model->getLastSql();
        // echo "<pre>";var_dump($db_records);die;
        $id = array();
        if (!empty($db_records)) {
            foreach ($db_records as $value) {
                $id[] = $value['id'];
            }
            return $id;
        } else {
            return 0;
        }
    }
    // 返回冲突id，否则返回0（不管什么内容，相同页面同一类型的广告一个排期可以添加多个）
    function check_conflict_AnimationAd($record, $id = 0) {
        $content_type = $record['content_type'];
		 //广告类型
		if($record['image_type']==1)
		{
		  $image_type=2; 
		}
		if($record['image_type']==2)
		{
		  $image_type=1; 
		}
        $start_at = $record['start_at'];
        $end_at = $record['end_at'];
        $oid = $record['oid'];
        $cid = $record['cid'];
        $show_place = $record['show_place'];
        // echo "<pre>";var_dump($record);die;
        $model = M();
         // 同一类型的广告不能重复  不管内容了
        if ($content_type == 1) {
            // 查找包名
            $content_key = 'package';
            $content_value = $record['package'];
        } else if ($content_type == 2) {
            // 查找活动
            $content_key = 'activity_id';
            $content_value = $record['activity_id'];
        } else if ($content_type == 3) {
            // 查找专题
            $content_key = 'feature_id';
            $content_value = $record['feature_id'];
        } else if ($content_type == 4) {
            // 查找专题
            $content_key = 'page_type';
            $content_value = $record['page_type'];
        } else if ($content_type == 5) {
            // 查找网页
            $content_key = 'website';
            $content_value = $record['website'];
        } else {
            return false;
        }
        //同一位置不能同时有动画广告和弹框广告
        $where = array(
            //"{$content_key}" => $content_value,
			'image_type'=>$image_type,//广告类型
            //'oid' => $oid,
            // 'cid' => $cid,
            'status' => 1,
            'start_at' => array('elt', $end_at),
            'end_at' => array('egt', $start_at),
            'show_place' => array('exp', "&{$show_place}!=0"),//不能在同一个页面
        );
        if ($id) {
            $where['id'] = array('neq', $id);
        }
		//同一位置同一类型的广告不能重复
		 $where_same = array(
            "{$content_key}" => $content_value,
			'image_type'=>$record['image_type'],//广告类型
            'oid' => $oid,
            'cid' => $cid,
            'status' => 1,
            'start_at' => array('elt', $end_at),
            'end_at' => array('egt', $start_at),
            'show_place' => array('exp', "&{$show_place}!=0"),//不能在同一个页面
        );
        if ($id) {
            $where_same['id'] = array('neq', $id);
        }
		
        $find = $model->table('sj_animation_ad')->where($where)->find();
		$find_same = $model->table('sj_animation_ad')->where($where_same)->find();
		// echo "<pre>";var_dump($find);
		// echo "<pre>";var_dump($find_same);
		// echo $model->getLastSql();die;
        if ($find['id'])
            return $find['id'];
		if($find_same['id'])
		   return $find_same['id'];
		return 0;
    }

    function logic_check_CategoryExtent($res) {
		$useful_key = array('extent_id', 'package', 'phone_dis', 'old_phone', 'prob', 'start_at', 'end_at','title', 'image_url', 'content_type', 'activity_id','type');
        foreach($useful_key as $key=>$value) {
            if (isset($res[$value]))
                $res[$value] = trim($res[$value]);
        }
        // 调用通用的检查函数
        $content_arr = array();
        $content_arr[0] = $res;
        // 业务逻辑：区间表、区间软件表
        $M_extent_table = 'category_extent';
        $M_extent_soft_table = 'category_extent_soft';
        // 加一下前缀（真正的表名），主要用在join sql里
        $extent_table = 'sj_' . $M_extent_table;
        $extent_soft_table = 'sj_' . $M_extent_soft_table;
        // 获得三个表的model
        $extent_model = M($M_extent_table);
        $extent_soft_model = M($M_extent_soft_table);
        $soft_model = M("soft");//软件大表
        // 业务逻辑：以下为各项具体检查

        foreach($content_arr as $key=>$record) {
            // 检查是不是编辑，如果是编辑，给record增加extent_id字段并分配其在表里的值
            if (isset($record['id'])) {
                $where = array('id' => array('EQ', $record['id']));
                $find = $extent_soft_model->where($where)->find();
                $content_arr[$key]['extent_id'] = $find['extent_id'];
                $record['extent_id'] = $find['extent_id'];
            }
            // 检测区间ID
            if (isset($record['extent_id']) && $record['extent_id'] != "") {
                $where = array(
                    'extent_id' => array('EQ', $record['extent_id']),
                    'status' => array('EQ', 1),
                );
                $find = $extent_model->where($where)->find();

                if (!$find) {
                	return array('code'=>1,'message'=>"区间位ID【{$record['extent_id']}】无效;");
                    $this->append_error_msg($error_msg, $key, 1, "区间位ID【{$record['extent_id']}】无效;");
                } else {
                    //榜单验证
                    $category_type = $find['category_type'];
                    $general_page_type = ContentTypeModel::getGeneralPageType($category_type);
                    if($general_page_type==5)
                    {
                        $tmp = explode('_',$category_type);
                        $bdid = $tmp[1];
                        $res = $extent_model->table('sj_list')->where('id='.$bdid)->find();
                            $package = $record['package'];
                            $softinfo = $extent_model->table('sj_soft')->field('category_id')->where('package="'.$package.'"')->find();
                            $cid = str_replace(',','',$softinfo['category_id']);
                            $sql = "SELECT parentid FROM sj_category WHERE category_id = $cid";
                            $ret = $extent_model->query($sql);
                            if($ret[0]['parentid']>3)
                            {
                                $sql = "SELECT parentid FROM sj_category WHERE category_id = (SELECT parentid FROM sj_category WHERE category_id = $cid)";
                                $ret = $extent_model->query($sql);
                            }
                        if($res['type']!=0)
                        {
                            // if($res['type']!=$ret[0]['parentid'])
                            // {	
                            // 	return array('code'=>1,'message'=>"添加失败,软件与榜单限制分类不符;");
                            //     $this->append_error_msg($error_msg, $key, 1, "添加失败,软件与榜单限制分类不符;");
                            // }
                        }else if($ret[0]['parentid']==3)
                        {	
                        	return array('code'=>1,'message'=>"添加失败,不能添加电子书类型的软件;");
                            $this->append_error_msg($error_msg, $key, 1, "添加失败,不能添加电子书类型的软件;");
                        }
                    }


                    if (isset($record['general_page_type'])) {
                        // 批量导入时会set这个字段
                        if ($record['general_page_type'] != '') {
                            // 检查这个区间ID所在的频道是否在这个频道类型中
                            $category_type = $find['category_type'];
                            $general_page_type = ContentTypeModel::getGeneralPageType($category_type);
                            if ($record['general_page_type'] != $general_page_type) {
                                return array('code'=>1,'message'=>"区间位ID与频道类型不对应;");
                                $this->append_error_msg($error_msg, $key, 1, "区间位ID与频道类型不对应;");
                            }
                        } else {
                        	return array('code'=>1,'message'=>"频道类型不能为空;");
                            $this->append_error_msg($error_msg, $key, 1, "频道类型不能为空;");
                        }
                    }
                    if (isset($record['category_name'])) {
                        // 检查区间ID与频道是否对应
                        $category_name = ContentTypeModel::convertPageType2PageNameOfCategory($find['category_type']);
                        if ($category_name != $record['category_name']) {
                            return array('code'=>1,'message'=>"区间位ID与频道不对应，该区间ID在频道【{$category_name}】中，不在频道【{$record['category_name']}】里;");
                            $this->append_error_msg($error_msg, $key, 1, "区间位ID与频道不对应，该区间ID在频道【{$category_name}】中，不在频道【{$record['category_name']}】里;");
                        }
                    }
                    if (isset($record['extent_name'])) {
                        // 检查区间ID与区间名是否对应
                        if ($find['extent_name'] != $record['extent_name']) {
                        	return array('code'=>1,'message'=>"区间位ID与区间名不对应;");
                            $this->append_error_msg($error_msg, $key, 1, "区间位ID与区间名不对应;");
                        }
                    }
                    // 得到该记录区间的cid、oid和pid，并保存起来，方便后面的区间冲突检查
                    // 保证同一频道下所有区间时间不能有冲突，现需求暂不检查属性
                    $content_arr[$key]['pid'] = $find['pid'];
                    $content_arr[$key]['category_type'] = $find['category_type'];
                }
            } else {
            	return array('code'=>1,'message'=>"区间位ID不能为空;");
                $this->append_error_msg($error_msg, $key, 1, "区间位ID不能为空;");
            }
            // 判断应该检查包名还是活动ID
            $content_arr[$key]['which_checked'] = 1;
            if ($content_arr[$key]['which_checked'] == 1) {
                // 检查包名是否在sj_soft表里
                if (isset($record['package']) && $record['package'] != "") {
                    $where = array(
                        'package' => $record['package'],
                        'status' => 1,
                        'hide' => array('in', array(0, 1, 1024)),
                    );
                    $find = $soft_model->where($where)->find();
                    if (!$find) {
                    	return array('code'=>1,'message'=>"包名【{$record['package']}】不存在于市场软件库中;");
                        $this->append_error_msg($error_msg, $key, 1, "包名【{$record['package']}】不存在于市场软件库中;");
                    }
                } else {
                	return array('code'=>1,'message'=>"包名不能为空;");
                    $this->append_error_msg($error_msg, $key, 1, "包名不能为空;");
                }
            } else if ($content_arr[$key]['which_checked'] == 2) {
                // 检查活动ID是否在sj_activity里
                if (isset($record['activity_id']) && $record['activity_id'] != "") {
                    $where = array(
                        'id' => $record['activity_id'],
                        'status' => 1,
                    );
                    $activity_model = M('activity');
                    $find = $activity_model->where($where)->find();
                    if (!$find) {
                    	return array('code'=>1,'message'=>"活动ID【{$record['activity_id']}】不存在;");
                        $this->append_error_msg($error_msg, $key, 1, "活动ID【{$record['activity_id']}】不存在;");
                    }
                } else {
                	return array('code'=>1,'message'=>"活动ID不能为空;");
                    $this->append_error_msg($error_msg, $key, 1, "活动ID不能为空;");
                }
            } else if ($content_arr[$key]['which_checked'] == 3) {
                // 检查专题ID是否存在
                if (isset($record['feature_id']) && $record['feature_id'] != "") {
                    $where = array(
                        'feature_id' => $record['feature_id'],
                        //'pid' => $content_arr[$key]['bk_pid'],
                        'status' => 1,
                    );
                    $feature_model = M('feature');
                    $find = $feature_model->where($where)->find();
                    if (!$find) {
                    	return array('code'=>1,'message'=>"专题ID【{$record['feature_id']}】不存在;");
                        $this->append_error_msg($error_msg, $key, 1, "专题ID【{$record['feature_id']}】不存在;");
                    }
                } else {
                	return array('code'=>1,'message'=>"专题ID不能为空;");
                    $this->append_error_msg($error_msg, $key, 1, "专题ID不能为空;");
                }
            } else {
                // 检查页面是否存在
                if (isset($record['category_type']) && $record['category_type'] != "") {
                    if (!array_key_exists($record['category_type'], $category_map)) {
                        return array('code'=>1,'message'=>"页面【{$record['category_type']}】不存在;");
                        $this->append_error_msg($error_msg, $key, 1, "页面【{$record['category_type']}】不存在;");
                    }
                } else {
                	return array('code'=>1,'message'=>"页面不能为空;");
                    $this->append_error_msg($error_msg, $key, 1, "页面不能为空;");
                }
            }
            // 检查高低配区分展示的值
            if (isset($record['phone_dis']) && $record['phone_dis'] != "") {
            } else {
            	return array('code'=>1,'message'=>"高低配区分展示值不能为空;");
                $this->append_error_msg($error_msg, $key, 1, "高低配区分展示值不能为空;");
            }
        }
        // 检查每一行数据是否与数据库的存储内容相冲突
        foreach($content_arr as $key=>$record) {
            // 业务逻辑：如果填写的区间无效，则不比较
            // if (!isset($record['bk_category_type']))
            //     continue;
            // 如果开始时间或结束时间无效，则不比较
            // if (!isset($record['bk_start_time']) || !isset($record['bk_end_time']))
            //     continue;
        	// echo "<pre>";var_dump($record);die;
            if ($record['which_checked'] == 1) {
                // 检查是否与sj_extent_soft_v1表里有相同包名且区间冲突的包
                // 业务逻辑：获得当前记录的信息：package、cid、oid、pid
                $es_package = escape_string($record['package']);
                //$cid = escape_string($record['cid']);
                //$oid = escape_string($record['oid']);
                $pid = escape_string($record['pid']);
                $start_time = escape_string($record['start_at']);
                $end_time = escape_string($record['end_at']);
                // 业务逻辑：获得当前记录的信息：频道
                $category_type = escape_string($record['category_type']);
                // 业务逻辑：构造sql语句，查找出与该记录包名相同、也是在相同属性的区间的所有后台记录
                $sql_select = "select {$extent_soft_table}.id as id, {$extent_soft_table}.package as package, {$extent_soft_table}.status as status, {$extent_table}.extent_name, {$extent_soft_table}.start_at as start_at, {$extent_soft_table}.end_at as end_at";
                $sql_from = " from {$extent_soft_table} left join {$extent_table}";
                $sql_on = " on {$extent_soft_table}.extent_id={$extent_table}.extent_id";
                $sql_where = " where {$extent_soft_table}.package='{$es_package}' and {$extent_soft_table}.start_at <= {$end_time} and {$extent_soft_table}.end_at >= {$start_time} and {$extent_soft_table}.status!=0 and {$extent_table}.status=1 and {$extent_table}.category_type='{$category_type}' and {$extent_table}.pid='{$pid}'";
                // 如果有传id过来，说明是编辑，这时要排除此id    如果传过来有life=1则说明是复制上线
                $sql_where_except = "";
                if (isset($record['id'])) {
                    $except_id = escape_string($record['id']);
                    // 拼接sql，A为下面的$sql里表sj_extent_soft_v1的别名，注意二者需一致
                    $sql_where_except = " and {$extent_soft_table}.id != {$except_id}";
                }
                // 将select、from、on、where、except拼接起来
                $sql = $sql_select . ' '. $sql_from . ' ' . $sql_on . ' ' . $sql_where . ' ' .  $sql_where_except;
                // 执行sql
                $db_records = $extent_soft_model->query($sql);
                // 有冲突的后台记录
                // echo  $extent_soft_model->getLastSql();
                // echo "<pre>";var_dump($db_records);die;
                foreach($db_records as $db_key=>$db_record) {
                    $start_at_str = date('Y-m-d H:i:s',$db_record['start_at']);
                    $end_at_str = date('Y-m-d H:i:s',$db_record['end_at']);
                    $status_paused_hint = "";
                    if ($db_record['status'] == 2) {
                        $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                    }
                    return array('code'=>1,'message'=>"投放区间与后台区间【{$db_record['extent_name']}】里ID为【{$db_record['id']}】，包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
                    $this->append_error_msg($error_msg, $key, 1, "投放区间与后台区间【{$db_record['extent_name']}】里ID为【{$db_record['id']}】，包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
                }
            } else if ($record['which_checked'] == 2) {
                // 说明填写的是活动ID
                $es_activity_id = escape_string($record['activity_id']);
                $start_time = escape_string($record['start_time']);
                $end_time = escape_string($record['end_time']);
                $category_type = escape_string($record['category_type']);
                $sql_select = "select {$extent_soft_table}.id as id, {$extent_soft_table}.activity_id as activity_id, {$extent_soft_table}.status as status, {$extent_table}.extent_name, {$extent_soft_table}.start_at as start_at, {$extent_soft_table}.end_at as end_at";
                $sql_from = " from {$extent_soft_table} left join {$extent_table}";
                $sql_on = " on {$extent_soft_table}.extent_id={$extent_table}.extent_id";
                $sql_where = " where {$extent_soft_table}.activity_id='{$es_activity_id}' and {$extent_soft_table}.start_at <= {$end_time} and {$extent_soft_table}.end_at >= {$start_time} and {$extent_soft_table}.status!=0 and {$extent_table}.status=1 and {$extent_table}.category_type='{$category_type}'";
                // 如果有传id过来，说明是编辑，这时要排除此id
                $sql_where_except = "";
                if (isset($record['id'])) {
                    $except_id = escape_string($record['id']);
                    // 拼接sql，A为下面的$sql里表sj_extent_soft_v1的别名，注意二者需一致
                    $sql_where_except = " and {$extent_soft_table}.id != {$except_id}";
                }
                // 将select、from、on、where、except拼接起来
                $sql = $sql_select . ' '. $sql_from . ' ' . $sql_on . ' ' . $sql_where . ' ' .  $sql_where_except;
                // 执行sql
                $db_records = $extent_soft_model->query($sql);
                // 有冲突的后台记录
                foreach($db_records as $db_key=>$db_record) {
                    $start_at_str = date('Y-m-d H:i:s',$db_record['start_at']);
                    $end_at_str = date('Y-m-d H:i:s',$db_record['end_at']);
                    $status_paused_hint = "";
                    if ($db_record['status'] == 2) {
                        $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                    }
                    return array('code'=>1,'message'=>"投放区间与后台区间【{$db_record['extent_name']}】里ID为【{$db_record['id']}】，活动ID为【{$db_record['activity_id']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
                    $this->append_error_msg($error_msg, $key, 1, "投放区间与后台区间【{$db_record['extent_name']}】里ID为【{$db_record['id']}】，活动ID为【{$db_record['activity_id']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
                }
            } else if ($record['which_checked'] == 3) {
                // 说明填写的是专题ID
                $es_feature_id = escape_string($record['feature_id']);
                $start_time = escape_string($record['start_time']);
                $end_time = escape_string($record['end_time']);
                $category_type = escape_string($record['category_type']);
                $sql_select = "select {$extent_soft_table}.id as id, {$extent_soft_table}.feature_id as feature_id, {$extent_soft_table}.status as status, {$extent_table}.extent_name, {$extent_soft_table}.start_at as start_at, {$extent_soft_table}.end_at as end_at";
                $sql_from = " from {$extent_soft_table} left join {$extent_table}";
                $sql_on = " on {$extent_soft_table}.extent_id={$extent_table}.extent_id";
                $sql_where = " where {$extent_soft_table}.feature_id='{$es_feature_id}' and {$extent_soft_table}.start_at <= {$end_time} and {$extent_soft_table}.end_at >= {$start_time} and {$extent_soft_table}.status!=0 and {$extent_table}.status=1 and {$extent_table}.category_type='{$category_type}'";
                // 如果有传id过来，说明是编辑，这时要排除此id
                $sql_where_except = "";
                if (isset($record['id'])) {
                    $except_id = escape_string($record['id']);
                    // 拼接sql，A为下面的$sql里表sj_extent_soft_v1的别名，注意二者需一致
                    $sql_where_except = " and {$extent_soft_table}.id != {$except_id}";
                }
                // 将select、from、on、where、except拼接起来
                $sql = $sql_select . ' '. $sql_from . ' ' . $sql_on . ' ' . $sql_where . ' ' .  $sql_where_except;
                // 执行sql
                $db_records = $extent_soft_model->query($sql);
                // 有冲突的后台记录
                foreach($db_records as $db_key=>$db_record) {
                    $start_at_str = date('Y-m-d H:i:s',$db_record['start_at']);
                    $end_at_str = date('Y-m-d H:i:s',$db_record['end_at']);
                    $status_paused_hint = "";
                    if ($db_record['status'] == 2) {
                        $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                    }
                    return array('code'=>1,'message'=>"投放区间与后台区间【{$db_record['extent_name']}】里ID为【{$db_record['id']}】，活动ID为【{$db_record['feature_id']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
                    $this->append_error_msg($error_msg, $key, 1, "投放区间与后台区间【{$db_record['extent_name']}】里ID为【{$db_record['id']}】，活动ID为【{$db_record['feature_id']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
                }
            } else {
                // 说明填写的是页面
                $es_category_type = escape_string($record['category_type']);
                $start_time = escape_string($record['start_time']);
                $end_time = escape_string($record['end_time']);
                $category_type = escape_string($record['category_type']);
                $sql_select = "select {$extent_soft_table}.id as id, {$extent_soft_table}.category_type as category_type, {$extent_soft_table}.status as status, {$extent_table}.extent_name, {$extent_soft_table}.start_at as start_at, {$extent_soft_table}.end_at as end_at";
                $sql_from = " from {$extent_soft_table} left join {$extent_table}";
                $sql_on = " on {$extent_soft_table}.extent_id={$extent_table}.extent_id";
                $sql_where = " where {$extent_soft_table}.category_type='{$es_category_type}' and {$extent_soft_table}.start_at <= {$end_time} and {$extent_soft_table}.end_at >= {$start_time} and {$extent_soft_table}.status!=0 and {$extent_table}.status=1 and {$extent_table}.category_type='{$category_type}'";
                // 如果有传id过来，说明是编辑，这时要排除此id
                $sql_where_except = "";
                if (isset($record['id'])) {
                    $except_id = escape_string($record['id']);
                    // 拼接sql，A为下面的$sql里表sj_extent_soft_v1的别名，注意二者需一致
                    $sql_where_except = " and {$extent_soft_table}.id != {$except_id}";
                }
                // 将select、from、on、where、except拼接起来
                $sql = $sql_select . ' '. $sql_from . ' ' . $sql_on . ' ' . $sql_where . ' ' .  $sql_where_except;
                // 执行sql
                $db_records = $extent_soft_model->query($sql);
                // 有冲突的后台记录
                // echo $extent_soft_model->getLastSql();
                // echo "<pre>";var_dump($db_records);die;
                foreach($db_records as $db_key=>$db_record) {
                    $start_at_str = date('Y-m-d H:i:s',$db_record['start_at']);
                    $end_at_str = date('Y-m-d H:i:s',$db_record['end_at']);
                    $status_paused_hint = "";
                    if ($db_record['status'] == 2) {
                        $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                    }
                    return array('code'=>1,'message'=>"投放区间与后台区间【{$db_record['extent_name']}】里ID为【{$db_record['id']}】，活动ID为【{$db_record['category_type']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
                    $this->append_error_msg($error_msg, $key, 1, "投放区间与后台区间【{$db_record['extent_name']}】里ID为【{$db_record['id']}】，活动ID为【{$db_record['category_type']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
                }

            }
        }
        return 2;
        return $error_msg;
    }


     // 页面添加或编辑、批量导入共用的逻辑检查
    function logic_check_ExtentV1($res) {
    	 // echo "<pre>"; var_dump($res);
	    // 	 $column_convert_arr = array(
	    //             'id' => 'id',
	    //             'package' => 'package',
	    //             'phone_dis' => 'phone_dis',
	    //             'old_phone' => 'old_phone',
	    //             'prob' => 'prob',
	    //             'start_at' => 'start_at',
	    //             'end_at' => 'end_at',
	    //             'type' => 'type',
					// 'life'=>'life',
	    //         );
        // $check_column_arr = array();
        // foreach($column_convert_arr as $key=>$value) {
        //     if (array_key_exists($key, $res)) {
        //         $check_column_arr[$value] = trim($res[$key]);
        //     }
        // }
        // 调用通用的检查函数
        $content_arr = array();
        $content_arr[0] = $res;
        // 业务逻辑：区间表、区间软件表
        $M_extent_table = 'extent_v1';
        $M_extent_soft_table = 'extent_soft_v1';
        // 加一下前缀（真正的表名），主要用在join sql里
        $extent_table = 'sj_' . $M_extent_table;
        $extent_soft_table = 'sj_' . $M_extent_soft_table;
        // 获得三个表的model
        $extent_model = M($M_extent_table);
        $extent_soft_model = M($M_extent_soft_table);
        $soft_model = M("soft");//软件大表
        // 业务逻辑：以下为各项具体检查

        foreach($content_arr as $key=>$record) {
            // 检查是不是编辑
            if (isset($record['id'])) {
                $where = array('id' => array('EQ', $record['id']));
                $find = $extent_soft_model->where($where)->find();
                // 获得区间名
                $content_arr[$key]['extent_id'] = $find['extent_id'];
                // 也赋给$record
                $record['extent_id'] = $find['extent_id'];
            }
            // 检测区间ID
            if (isset($record['extent_id']) && $record['extent_id'] != "") {
                $where = array(
                    'extent_id' => array('EQ', $record['extent_id']),
                    'type' => array(array('EQ', 1), array('EQ', 3), 'OR'),
                    'parent_union_id' => array('EQ', 0),
                    'status' => array('EQ', 1),
                );
                $find = $extent_model->where($where)->find();
               
                if (!$find) {
                	return array('code'=>1,'message'=>"区间位ID【{$record['extent_id']}】无效;");
                    $this->append_error_msg($error_msg, $key, 1, "区间位ID【{$record['extent_id']}】无效;");
                } else {
                    // if (isset($record['extent_name'])) {
                    //     // 检查区间ID与区间名是否对应
                    //     if ($find['extent_name'] != $record['extent_name']) {
                    //         return array('code'=>1,'message'=>"区间位ID与区间名不对应;");
                    //         $this->append_error_msg($error_msg, $key, 1, "区间位ID与区间名不对应;");
                    //     }
                    // }
                    // 得到该记录区间的cid、oid，并保存起来，方便后面的区间冲突检查
                    $content_arr[$key]['cid'] = $find['cid'];
                    $content_arr[$key]['oid'] = $find['oid'];
                    $content_arr[$key]['pid'] = $find['pid'];
                }
            } else {
            	return array('code'=>1,'message'=>"区间位ID不能为空;");
                $this->append_error_msg($error_msg, $key, 1, "区间位ID不能为空;");
            }
            // 检查包名是否在sj_soft表里
            if (isset($record['package']) && $record['package'] != "") {
                $where = array(
                    'package' => $record['package'],
                    'status' => 1,
                    'hide' => array('in', array(1, 1024)),
                );
                $find = $soft_model->where($where)->find();

                if (!$find) {
                	return array('code'=>1,'message'=>"包名【{$record['package']}】不存在于市场软件库中;");
                    $this->append_error_msg($error_msg, $key, 1, "包名【{$record['package']}】不存在于市场软件库中;");
                }
            } else {
            	return array('code'=>1,'message'=>"包名不能为空;");
                $this->append_error_msg($error_msg, $key, 1, "包名不能为空;");
            }
        }

        // 业务逻辑：检查每一行数据是否与数据库的存储内容相冲突
        foreach($content_arr as $key=>$record) {
            // 如果开始时间或结束时间无效，则不比较
            // if (!isset($record['bk_start_time']) || !isset($record['bk_end_time']))
            //     continue;
            // // 如果填写的区间无效，则不比较
            // if (!isset($record['bk_cid']) || !isset($record['bk_oid']) || !isset($record['bk_pid']))
            //     continue;
            // 检查是否与sj_extent_soft_v1表里有相同包名且区间冲突的包
            // 业务逻辑：获得当前记录的信息：package、cid、oid
            $es_package = escape_string($record['package']);
            $cid = escape_string($record['cid']);
            $oid = escape_string($record['oid']);
            $pid = escape_string($record['pid']);
            $start_time = escape_string($record['start_at']);
            // $start_time = escape_string($record['start_time']);
            // $end_time = escape_string($record['end_time']);
            $end_time = escape_string($record['end_at']);
            // 构造sql语句，查找出与该记录包名相同、也是在相同属性的区间的所有后台记录
            $sql_select = "select {$extent_soft_table}.id as id, {$extent_soft_table}.package as package, {$extent_table}.extent_name, {$extent_soft_table}.start_at as start_at, {$extent_soft_table}.end_at as end_at, {$extent_soft_table}.status as status";
            $sql_from = " from {$extent_soft_table} left join {$extent_table}";
            $sql_on = " on {$extent_soft_table}.extent_id={$extent_table}.extent_id";
            $sql_where = " where {$extent_soft_table}.package='{$es_package}' and {$extent_soft_table}.start_at <= {$end_time} and {$extent_soft_table}.end_at >= {$start_time} and {$extent_soft_table}.status!=0 and {$extent_table}.status=1 and {$extent_table}.cid='{$cid}' and {$extent_table}.oid='{$oid}' and {$extent_table}.pid='{$pid}' and {$extent_table}.parent_union_id=0 ";
            // 如果有传id过来，说明是编辑，这时要排除此id
            $sql_where_except = "";
            if (isset($record['id'])) {
                $except_id = escape_string($record['id']);
                // 拼接sql，A为下面的$sql里表sj_extent_soft_v1的别名，注意二者需一致
                $sql_where_except = " and {$extent_soft_table}.id != {$except_id}";
            }
            // 将select、from、on、where、except拼接起来
            $sql = $sql_select . ' '. $sql_from . ' ' . $sql_on . ' ' . $sql_where . ' ' .  $sql_where_except;
            // 执行sql
            $db_records = $extent_soft_model->query($sql);
            // 有冲突的后台记录
            // echo  $extent_soft_model->getLastSql();
            // echo "<pre>";var_dump($db_records);
            // echo "<pre>";var_dump($db_records);die;
            foreach($db_records as $db_key=>$db_record) {
                $start_at_str = date('Y-m-d H:i:s',$db_record['start_at']);
                $end_at_str = date('Y-m-d H:i:s',$db_record['end_at']);
                $status_paused_hint = "";
                if ($db_record['status'] == 2) {
                    $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                }
                return array('code'=>1,'message'=>"投放区间与后台区间【{$db_record['extent_name']}】里ID为【{$db_record['id']}】，包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
                $this->append_error_msg($error_msg, $key, 1, "投放区间与后台区间【{$db_record['extent_name']}】里ID为【{$db_record['id']}】，包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
            }
        }
        return 2;
        return $error_msg;
    }


    function logic_check_Search_weight($res) {
	   //  	$column_convert_arr = array(
	   //          'id' => 'id',
	   //          'kid' => 'kid',
	   //          'type' => 'type',
	   //          'pos' => 'pos',
	   //          'start_tm' => 'start_tm',
	   //          'stop_tm' => 'stop_tm',
				// 'co_type' =>'co_type',
	   //      );
	   //      $check_column_arr = array();
	   //      foreach($column_convert_arr as $key=>$value) {
	   //          if (array_key_exists($key, $res)) {
	   //              $check_column_arr[$value] = $res[$key];
	   //          }
	   //      }
        // 调用通用的检查函数
        $content_arr = array();
        $content_arr[0] = $res;
        // 业务逻辑：区间表、区间软件表
        $M_keyword_table = 'search_key';
        $M_keyword_soft_table = 'search_key_package';
        // 获得三个表的model
        $keyword_model = M($M_keyword_table);
        $keyword_soft_model = M($M_keyword_soft_table);
        $soft_model = M("soft");//软件大表
        // 业务逻辑：以下为各项具体检查

        foreach($content_arr as $key=>$record) {
            // 关键字ID

            if ($record['type'] !== false) {
                if (isset($record['type']) && $record['type'] !== "") {
                    if ($record['package'] !== false) {
                        if (isset($record['package']) && $record['package'] != "") {
                            if ($record['type'] == 0) {
                                // 检查包名是否在sj_soft表里
                                $where = array(
                                    'package' => $record['package'],
                                    'status' => 1,
                                    'hide' => array('EQ', 1),
                                );
                                $find = $soft_model->where($where)->find();
                                
                                if (!$find) {
                                	return array('code'=>1,'message'=>"包名【{$record['package']}】不存在于市场软件库中;");
                                    $this->append_error_msg($error_msg, $key, 1, "包名【{$record['package']}】不存在于市场软件库中;");
                                }
                            } 
                        } else {
                        	return array('code'=>1,'message'=>"包名/页面不能为空;");
                            $this->append_error_msg($error_msg, $key, 1, "包名/页面不能为空;");
                        }
                    }
                } else {
                	return array('code'=>1,'message'=>"类型不能为空;");
                    $this->append_error_msg($error_msg, $key, 1, "类型不能为空;");
                }
            }
        }
        $model = M();
        $res=$keyword_model->where(array('id'=>$content_arr[0]['kid'],'status'=>1))->find(); 
        $content_arr[0]['srh_key']=$res['srh_key'];
        // 检查关键字在阿拉丁上是否已绑定包名
        foreach ($content_arr as $key => $record) {
        	// echo "<pre>";var_dump($record);
            // 根据kid查找关键字名，然后对阿拉丁表里查找此时间段有没有绑定这个关键字
            $where = array(
                'srh_key' => array('eq', $record['srh_key']),
                'status' => array('NEQ', 0)
            );
            $find = $model->table('sj_search_key')->where($where)->find();
           
           if (!$find)
                continue;
            $srh_key = $find['srh_key'];
            $where = array(
                'associate' => array('like', "%;{$srh_key};%"),
                'begin' => array('elt', $record['stop_tm']),
                'end' => array('egt', $record['start_tm']),
                'stat' => 1,
            );
            $find = $model->table('sj_soft_associate_hot_word')->where($where)->find();
            //  echo $model->getLastSql();
            // var_dump($find);die;
            if (!$find)
                continue;
            $ald_package = $find['package'];
            // 找到此关键字有配置阿拉丁，判断当前要添加的内容是页面还是包名
            if ($record['type'] == 1) {
            	return array('code'=>1,'message'=>"此关键字已设置了阿拉丁推荐，不可以添加页面;");
                $this->append_error_msg($error_msg, $key, 1, "此关键字已设置了阿拉丁推荐，不可以添加页面;");
                continue;
            }
            if (!isset($record['pos']))
                continue;
            if ($record['pos'] == 1) {
                if ($ald_package == $record['package'])
                    continue;
                return array('code'=>1,'message'=>"此关键字已设置了阿拉丁推荐;");
                $this->append_error_msg($error_msg, $key, 1, "此关键字已设置了阿拉丁推荐;");
            } else {
                // 不是第一位，判断会不会设置成和阿拉丁一样
                if ($ald_package != $record['package'])
                    continue;
                return array('code'=>1,'message'=>"{$record['package']}软件已在此关键字设置了阿拉丁推荐，无法在相同关键字推荐相同软件");
                $this->append_error_msg($error_msg, $key, 1, "{$record['package']}软件已在此关键字设置了阿拉丁推荐，无法在相同关键字推荐相同软件");
            }
        } 
        // 检查每一行数据是否与数据库的存储内容相冲突
        foreach($content_arr as $key=>$record) {
			//if(!$record['kid'])
			//	continue; //如果没有关键词id说明是新增加的关键词  数据库中肯定没有有关软件 直接不用比较
            //根据srh_key获取srh_key的id
			$srh_key_result=$model->table('sj_search_key')->where(array('srh_key' => $record['srh_key'],'status'=>1))->find();
			if(!$srh_key_result['id'])
				continue; //关键词没有ID说明是新增加的词，数据库中肯定没有不用比较
			 $where = array(
                'kid' => array('EQ', $srh_key_result['id']),
                'status' => array('NEQ', 0),
            );
            if (isset($record['id']))
                $where['id'] = array('NEQ', $record['id']);
            $db_records = $keyword_soft_model->where($where)->select();
            // var_dump($db_records);
            if ($record['type'] !== false && $record['type'] == 0) {
                // 如果填写的是包，需要判断后台里该关键字填写的是什么，如果是页面，报错
                if (!$db_records)
                    continue;
                if ($db_records[0]['type'] == 1) {
                	return array('code'=>1,'message'=>"后台中该关键字已添加页面，不能再添加包;");
                    $this->append_error_msg($error_msg, $key, 1, "后台中该关键字已添加页面，不能再添加包;");
                } else {
                    // 检查时间、位置冲突
                    // 如果填写时间的不完善，则不比较
                    if (!isset($record['start_tm']) || !isset($record['stop_tm']))
                        continue;
                    foreach($db_records as $db_record) {
                        if ($record['package'] == $db_record['package']) {
                            // 将开始时间和结束时间转成时间戳
                            $start1 = $record['start_tm']; $end1 = $record['stop_tm'];
                            $start2 = $db_record['start_tm']; $end2 = $db_record['stop_tm'];
                            if ($start1 <= $end2 && $start2 <= $end1) {
                                $start_at_str = date('Y-m-d H:i:s',$db_record['start_tm']);
                                $end_at_str = date('Y-m-d H:i:s',$db_record['stop_tm']);
                                $status_paused_hint = "";
                                if ($db_record['status'] == 2) {
                                    $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                                }
                                return array('code'=>1,'message'=>"同一包名下，投放时间与后台id为【{$db_record['id']}】，包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
                                $this->append_error_msg($error_msg, $key, 1, "同一包名下，投放时间与后台id为【{$db_record['id']}】，包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
                            }
                        }
                        if ($record['pos'] == $db_record['pos']) {
                            // 将开始时间和结束时间转成时间戳
                            $start1 = $record['start_tm']; $end1 = $record['stop_tm'];
                            $start2 = $db_record['start_tm']; $end2 = $db_record['stop_tm'];
                            if ($start1 <= $end2 && $start2 <= $end1) {
                                $start_at_str = date('Y-m-d H:i:s',$db_record['start_tm']);
                                $end_at_str = date('Y-m-d H:i:s',$db_record['stop_tm']);
                                $status_paused_hint = "";
                                if ($db_record['status'] == 2) {
                                    $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                                }
                                return array('code'=>1,'message'=>"同一位置下，投放时间与后台id为【{$db_record['id']}】，包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
                                $this->append_error_msg($error_msg, $key, 1, "同一位置下，投放时间与后台id为【{$db_record['id']}】，包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
                            }
                        }
                    }
                }
            }
        }
        return 2;
        return $error_msg;
    }

     // 统一的逻辑检查：检查添加软件数据是否合法
    function logic_check_Advertisement($res) {
    	$column_convert_arr = array(
            'id' => 'id',
            'feature_id' => 'feature_id',
            'package' => 'package',
            'special' => 'special',
            'remark' => 'remark',
            'rank' => 'rank',
            'start_tm' => 'start_tm',
            'end_tm' => 'end_tm',
			'type' => 'type',
			'recommend_soft_name' => 'recommend_soft_name',
			'recommend_reason' =>'recommend_reason',
			'recommend_person' =>'recommend_person',
        );
        $check_column_arr = array();
        foreach($column_convert_arr as $key=>$value) {
            if (array_key_exists($key, $res)) {
                $check_column_arr[$value] = $res[$key];
            }
        }
        foreach($check_column_arr as $key=>$value) {
            $check_column_arr[$key] = trim($value);
        }
        // 调用通用的检查函数
        $content_arr = array();
        $content_arr[0] = $check_column_arr;
        // echo "<pre>";var_dump($content_arr[0]);
        // 业务逻辑：
        $feature_soft_model = M('feature_soft');
        $feature_model = M('feature');
        $soft_model = M("soft");//软件大表
        // 业务逻辑：以下为各项具体检查
        foreach($content_arr as $key=>$record) {
            // 检查是不是编辑，如果是编辑，给record增加extent_id字段并分配其在表里的值
            if (isset($record['id'])) {
                $where = array('id' => array('EQ', $record['id']));
                $find = $feature_soft_model->where($where)->find();
                $content_arr[$key]['feature_id'] = $find['feature_id'];
                $record['feature_id'] = $find['feature_id'];
            }
            // 检查专题id是否有效
            if (isset($record['feature_id']) && $record['feature_id'] != "") {
                $where = array(
                    'feature_id' => array('EQ', $record['feature_id']),
                    //'status' => array('EQ', 1),//停用的专题也就是status=0的专题也可以添加数据，产品文档要求的
                );
                $find = $feature_model->where($where)->find();
                if (!$find) {
                	return array('code'=>1,'message'=>"专题id不存在;");
                    $this->append_error_msg($error_msg, $key, 1, "专题id不存在;");
                } else {
                    $content_arr[$key]['bk_feature_id'] = $record['feature_id'];
                    // 如果传了专题类别名称，还需要检查与feature_id是否匹配
                    if (isset($record['name'])) {
                        if ($find['name'] != $record['name']) {
                        	return array('code'=>1,'message'=>"专题类别ID与专题类别名称不对应;");
                            $this->append_error_msg($error_msg, $key, 1, "专题类别ID与专题类别名称不对应;");
                        }
                    }
					//如果传了所属段落，还要检查feature_id是否有段落匹配
					if($record['feature_graphic_rank']!=0)
					{
						$graphic_where = array(
							'feature_id' => array('EQ', $record['feature_id']),
							'rank' => $record['feature_graphic_rank'],
							'status' =>1,
							//'status' => array('EQ', 1),//停用的专题也就是status=0的专题也可以添加数据，产品文档要求的
						);
						$feature_graphic = $feature_model->table('sj_feature_graphic')->where($graphic_where)->select();
						if(!$feature_graphic)
						{
							$section = array('','一','二','三','四','五','六','七','八','九','十');
							return array('code'=>1,'message'=>"图文第".$section[$record['feature_graphic_rank']]."段落不存在;");
							$this->append_error_msg($error_msg, $key, 1, "图文第".$section[$record['feature_graphic_rank']]."段落不存在;");
						}
						else
						{
							$content_arr[$key]['bk_feature_graphic_id'] = $feature_graphic[0]['id'];
						}
					} else {
						$content_arr[$key]['bk_feature_graphic_id'] = 0;
					}
                }
            } else {
            	return array('code'=>1,'message'=>"专题id不能为空;");
                $this->append_error_msg($error_msg, $key, 1, "专题id不能为空;");
            }
            // 检查包名是否在sj_soft表里
            if (isset($record['package']) && $record['package'] != "") {
                $where = array(
                    'package' => $record['package'],
                    'status' => 1,
                    //'hide' => array('in', array(1, 1024)),
                );
                $find = $soft_model->where($where)->find();
                if (!$find) {
                	return array('code'=>1,'message'=>"包名【{$record['package']}】不存在于市场软件库中;");
                    $this->append_error_msg($error_msg, $key, 1, "包名【{$record['package']}】不存在于市场软件库中;");
                }
            } else {
            	return array('code'=>1,'message'=>"包名不能为空;");
                $this->append_error_msg($error_msg, $key, 1, "包名不能为空;");
            }
        }  
        // 检查每一行数据是否与数据库的存储内容相冲突
        foreach($content_arr as $key=>$record) {
            $start_time = $record['start_tm'];
            $end_time = $record['end_tm'];
            $where = array(
                'package' => array('EQ', $record['package']),
                'status' => array('NEQ', 0),
                'feature_id' => array('EQ', $record['feature_id']),
                'start_tm' => array('ELT', $end_time),
                'end_tm' => array('EGT', $start_time),
            );
            if (isset($record['id'])) {
                $where['id'] = array('NEQ', $record['id']);
            }
            $db_records = $feature_soft_model->where($where)->select();
            // 有冲突的后台记录
            foreach($db_records as $db_key=>$db_record) {
                $start_at_str = date('Y-m-d H:i:s',$db_record['start_tm']);
                $end_at_str = date('Y-m-d H:i:s',$db_record['end_tm']);
                $status_paused_hint = "";
                if ($db_record['status'] == 2) {
                    $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                }
                return array('code'=>1,'message'=>"投放区间与ID为【{$db_record['id']}】，包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
                $this->append_error_msg($error_msg, $key, 1, "投放区间与ID为【{$db_record['id']}】，包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
            }
        }
        return 2;
        return $error_msg;
    }


    // 返回冲突id，否则返回0
    function check_conflict_textpage($record, $id = 0) {
        $content_type = $record['content_type'];
        $show_place = $record['show_place'];
        $oid = $record['oid'];
        $cid = $record['cid'];
        $start_time = $record['start_time'];
        $end_time = $record['end_time'];
		// $life=$record['life'];
		$single_soft=$record['single_soft'];
        $model = M();
        if ($content_type == 1) {
            // 查找包名
            $content_key = 'package';
            $content_value = $record['package'];
        } else if ($content_type == 2) {
            // 查找活动
            $content_key = 'activity_id';
            $content_value = $record['activity_id'];
        } else if ($content_type == 3) {
            // 查找专题
            $content_key = 'feature_id';
            $content_value = $record['feature_id'];
        } else if ($content_type == 4) {
            // 查找专题
            $content_key = 'page_type';
            $content_value = $record['page_type'];
        } else if ($content_type == 5) {
            // 查找网页
            $content_key = 'website';
            $content_value = $record['website'];
        } else if ($content_type == 6) {
            // 查找礼包id
            $content_key = 'gift_id';
            $content_value = $record['gift_id'];
        }else if ($content_type == 7) {
            // 查找攻略
            $content_key = 'strategy_id';
            $content_value = $record['strategy_id'];
        }else {
            return false;
        }
        $where = array(
            "{$content_key}" => $content_value,
            'status' => 1,
            'oid' => $oid,
            'cid' => $cid,
            'start_time' => array('elt', $end_time),
            'end_time' => array('egt', $start_time),
        );
        if ($id) {
            $where['id'] = array('neq', $id);
        }
        $conflict_list = $model->table('sj_text_page')->where($where)->select();
        if (!empty($conflict_list)) {
            $id_arr = array();
            foreach ($conflict_list as $value) 
			{
                if ($show_place & $value['show_place']) 
				{
				  if($value['show_place'] & 32768 && $show_place & 32768)
				  {
				     if($value['single_soft']==$single_soft)
					 {
					   $id_arr[] = $value['id'];
					 }
				  }
				  else
				  {
                    // 如果位置有重叠
                    $id_arr[] = $value['id'];
				  }
                }
				else
				{
				 if($show_place & 1 && $value['show_place'] & 32768||$show_place & 32768 && $value['show_place'] & 1)
				   {
					 $id_arr[] = $value['id'];
				   }
				}
            }
            if (!empty($id_arr))
                return implode(',', $id_arr);
        }
        return 0;
    }

    function logic_check_lading($res) 
	{
        // 业务逻辑：分区表、区间软件表
        $M_category_table = 'lading_category';
        $M_lading_soft_table = 'lading_soft';
        // 加一下前缀（真正的表名），主要用在join sql里
        $lading_category_table = 'sj_' . $M_category_table;
        $lading_soft_table = 'sj_' . $M_lading_soft_table;
        // 获得三个表的model
        $lading_category_model = M($M_category_table);
        $lading_soft_model = M($M_lading_soft_table);
        $soft_model = M("soft");//软件大表
        $content_arr=array();
		$ca_id=$soft_model->table('sj_lading_category')->where(array('id'=>$res['category_id']))->find();
		$res['category_name']=$ca_id['category_name'];
		$content_arr[0]=$res;
        // 业务逻辑：以下为各项具体检查
        foreach($content_arr as $key=>$record) 
		{
            // 检查是不是编辑
            if (isset($record['id'])) 
			{
                $where = array('id' => array('EQ', $record['id']));
                $find = $lading_soft_model->where($where)->find();
                // 获得区间名
                $content_arr[$key]['category_id'] = $find['category_id'];
                // 也赋给$record
                $record['category_id'] = $find['category_id'];
            }
            // 检测广告位
            if (isset($record['category_name']) && $record['category_name'] != ""||isset($record['category_id']) && $record['category_id'] != "") 
			{
				if($record['category_name'])
				{
					$where = array(
						'category_name' => array('EQ', $record['category_name']),
						'status' => array('EQ', 1),
					);
				}
				if($record['category_id'])
				{
					$where = array(
						'id' => array('EQ', $record['category_id']),
						'status' => array('EQ', 1),
					);
				}
                $find = $lading_category_model->where($where)->find();
                if (!$find) 
				{
					return array('code'=>1,'message'=>"广告位【{$record['category_name']}】无效;");
                    $this->append_error_msg($error_msg, $key, 1, "广告位【{$record['category_name']}】无效;");
                }
				else 
				{
					$content_arr[$key]['category_id']=$find['id'];
					$content_arr[$key]['cid']=$find['cid'];
                }
            } 
			else 
			{
				return array('code'=>1,'message'=>"广告位不能为空;");
                $this->append_error_msg($error_msg, $key, 1, "广告位不能为空;");
            }
            // 检查包名是否在sj_soft表里
            if(isset($record['package']) && $record['package'] != "") 
			{
                $where = array(
                    'package' => $record['package'],
                    'status' => 1,
                    'hide' => array('in', array(1, 1024)),
                );
                $find = $soft_model->where($where)->find();
                
                if (!$find) 
				{
					return array('code'=>1,'message'=>"包名【{$record['package']}】不存在于市场软件库中;");
                    $this->append_error_msg($error_msg, $key, 1, "包名【{$record['package']}】不存在于市场软件库中;");
                }
				else
				{
					$content_arr[$key]['softname']=$find['softname'];
				}
            } 
			else 
			{
				return array('code'=>1,'message'=>"包名不能为空;");
                $this->append_error_msg($error_msg, $key, 1, "包名不能为空;");
            }
        }
        // 业务逻辑：检查每一行数据是否与数据库的存储内容相冲突
        foreach($content_arr as $key=>$record) 
		{
            // 如果开始时间或结束时间无效，则不比较
            //同一时间内同一渠道同一包名是否有冲突 
            // 业务逻辑：获得当前记录的信息：package、cid、
            $es_package = escape_string($record['package']);
            $cid = escape_string($record['cid']);
            $start_time = escape_string($record['start_tm']);
            $end_time = escape_string($record['end_tm']);
			$id=escape_string($record['category_id']);
			$softname=escape_string($record['softname']);
			$recommend=escape_string($record['recommend']);
			
			// 构造sql语句，查找出与该记录包名相同、也是在相同渠道的所有后台记录
			$my_cid_result = $lading_category_model ->where(array('id' => $id)) -> select();
			$my_category_result = $lading_category_model -> where(array('cid' => $my_cid_result[0]['cid'],'status' => 1)) -> select();

			foreach($my_category_result as $key1 => $val){
				$my_category_str_go .= $val['id'].',';
			}
			$my_category_str = substr($my_category_str_go,0,-1);
			
			$have_package_where['_string'] = "category_id in ({$my_category_str}) and package = '{$es_package}' and start_tm <= {$end_time} and end_tm >= {$start_time} and status = 1 ";
			// 如果是编辑，需在后台记录中排除自己
            if (isset($record['id'])) 
			{
				$edit_id=$record['id'];
				$have_package_where['_string'] = "category_id in ({$my_category_str}) and package = '{$es_package}' and start_tm <= {$end_time} and end_tm >= {$start_time} and status = 1 and id !='{$edit_id}'";
            }
			$have_package_result = $lading_soft_model -> where($have_package_where) -> select();
			// 有冲突的后台记录
			foreach($have_package_result as $db_key=>$db_record)
			{
				$start_at_str = date('Y-m-d H:i:s',$db_record['start_tm']);
                $end_at_str = date('Y-m-d H:i:s',$db_record['end_tm']);
                $status_paused_hint = "";
                if ($db_record['status'] == 2) {
                    $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                }
				$fenlei=$lading_category_model -> where(array("id" =>$db_record['category_id'])) -> select();
	
                return array('code'=>1,'message'=>"投放广告位与后台广告位ID为【{$db_record['id']}】，分类名称为【{$fenlei[0]['category_name']}】，软件包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
                $this->append_error_msg($error_msg, $key, 1, "投放广告位与后台广告位ID为【{$db_record['id']}】，分类名称为【{$fenlei[0]['category_name']}】，软件包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
			}

			$have_softname_where['_string'] = "category_id in ({$my_category_str}) and softname = '{$softname}' and start_tm <= {$end_time} and end_tm >= {$start_time} and status = 1 ";
			// 如果是编辑，需在后台记录中排除自己
            if (isset($record['id'])) 
			{
				$edit_id=$record['id'];
				$have_softname_where['_string'] = "category_id in ({$my_category_str}) and softname = '{$softname}' and start_tm <= {$end_time} and end_tm >= {$start_time} and status = 1 and id !={$edit_id}";
            }
			$have_softname_result = $lading_soft_model -> where($have_softname_where) -> select();
			// 有冲突的后台记录
			foreach($have_softname_result as $db_key=>$db_record)
			{
				$start_at_str = date('Y-m-d H:i:s',$db_record['start_tm']);
                $end_at_str = date('Y-m-d H:i:s',$db_record['end_tm']);
                $status_paused_hint = "";
                if ($db_record['status'] == 2) {
                    $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                }
				$fenlei=$lading_category_model -> where(array("id" =>$db_record['category_id'])) -> select();
				return array('code'=>1,'message'=>"投放广告位与后台广告位ID为【{$db_record['id']}】，分类名称为【{$fenlei[0]['category_name']}】，软件名称为【{$db_record['softname']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
                $this->append_error_msg($error_msg, $key, 1, "投放广告位与后台广告位ID为【{$db_record['id']}】，分类名称为【{$fenlei[0]['category_name']}】，软件名称为【{$db_record['softname']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
			}
			$have_comment_where['_string'] = "category_id in ({$my_category_str}) and recommend = '{$recommend}' and start_tm <= {$end_time} and end_tm >= {$start_time} and status = 1 ";
			// 如果是编辑，需在后台记录中排除自己
            if (isset($record['id'])) 
			{
				$edit_id=$record['id'];
				$have_comment_where['_string'] = "category_id in ({$my_category_str}) and recommend = '{$recommend}' and start_tm <= {$end_time} and end_tm >= {$start_time} and status = 1 and id != {$edit_id}";
            }
			$have_comment_result = $lading_soft_model -> where($have_comment_where) -> select();
			// 有冲突的后台记录
			foreach($have_comment_result as $db_key=>$db_record)
			{
				$start_at_str = date('Y-m-d H:i:s',$db_record['start_tm']);
                $end_at_str = date('Y-m-d H:i:s',$db_record['end_tm']);
                $status_paused_hint = "";
                if ($db_record['status'] == 2) {
                    $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                }
				$fenlei=$lading_category_model -> where(array("id" =>$db_record['category_id'])) -> select();
				return array('code'=>1,'message'=>"投放广告位与后台广告位ID为【{$db_record['id']}】，分类名称为【{$fenlei[0]['category_name']}】，推荐语句为【{$db_record['recommend']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
                $this->append_error_msg($error_msg, $key, 1, "投放广告位与后台广告位ID为【{$db_record['id']}】，分类名称为【{$fenlei[0]['category_name']}】，推荐语句为【{$db_record['recommend']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
			}
        }
        return 2;
        return $error_msg;
    }

    // 统一的逻辑检查：检查添加软件数据是否合法
    function logic_check_Search_tips($res) 
	{
		$keyword_model = M('search_tips');
		$srh_key_result_two=$keyword_model->where(array('id' => $res['kid'] ))->find();
        if(!$srh_key_result_two){
        	return array('code'=>1,'message'=>"关键词不存在;");
        }
        $res['srh_key']=$srh_key_result_two['srh_key'];
        $content_arr=array();
        $content_arr[0]=$res;

        // 业务逻辑：区间表、区间软件表
        // 获得三个表的model
        $keyword_soft_model = M('search_tips_package');
        $soft_model = M("soft");//软件大表
        // 业务逻辑：以下为各项具体检查
        foreach($content_arr as $key=>$record) 
		{
			//检查包名
			if (isset($record['package']) && $record['package'] != "") 
			{ 
				// 检查包名是否在sj_soft表里
				$where = array(
					'package' => $record['package'],
					'status' => 1,
					'hide' => array('EQ', 1),
				);
				$find = $soft_model->where($where)->find();
				if (!$find) 
				{
					return array('code'=>1,'message'=>"包名【{$record['package']}】不存在于市场软件库中;");
					$this->append_error_msg($error_msg, $key, 1, "包名【{$record['package']}】不存在于市场软件库中;");
				}
			} else {
				$this->append_error_msg($error_msg, $key, 1, "包名不能为空;");
			}
        }
        // 检查每一行数据是否与数据库的存储内容相冲突
        foreach($content_arr as $key=>$record) 
		{
            $where = array(
                // 'kid' => array('EQ', $srh_key_result['id']),
                'kid' => array('EQ', $record['kid']),
                'status' => array('NEQ', 0),
            );
            if (isset($record['id']))
                $where['id'] = array('NEQ', $record['id']);
            $db_records = $keyword_soft_model->where($where)->select();
           // echo $keyword_soft_model->getLastSql();
           // var_dump($db_records);
			if (!$db_records)
				continue;
			// 检查时间、位置（排序）冲突
			foreach($db_records as $db_record) 
			{
				if ($record['package'] == $db_record['package']) 
				{
					// 将开始时间和结束时间转成时间戳
					$start1 = $record['start_tm']; 
					$end1 = $record['end_tm'];
					$start2 = $db_record['start_tm']; 
					$end2 = $db_record['end_tm'];
					if ($start1 <= $end2 && $start2 <= $end1) 
					{
						$start_at_str = date('Y-m-d H:i:s',$db_record['start_tm']);
						$end_at_str = date('Y-m-d H:i:s',$db_record['end_tm']);
						$status_paused_hint = "";
						if ($db_record['status'] == 2) 
						{
							$status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
						}
						return array('code'=>1,'message'=>"同一包名下，投放时间与后台id为【{$db_record['id']}】，包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
						$this->append_error_msg($error_msg, $key, 1, "同一包名下，投放时间与后台id为【{$db_record['id']}】，包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
					}
				}
				if ($record['rank'] == $db_record['rank']) 
				{
					// 将开始时间和结束时间转成时间戳
					$start1 = $record['start_tm']; 
					$end1 = $record['end_tm'];
					$start2 = $db_record['start_tm']; 
					$end2 = $db_record['end_tm'];
					if ($start1 <= $end2 && $start2 <= $end1) 
					{
						$start_at_str = date('Y-m-d H:i:s',$db_record['start_tm']);
						$end_at_str = date('Y-m-d H:i:s',$db_record['end_tm']);
						$status_paused_hint = "";
						if ($db_record['status'] == 2)
						{
							$status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
						}
						return array('code'=>1,'message'=>"同一位置下，投放时间与后台id为【{$db_record['id']}】，包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
						$this->append_error_msg($error_msg, $key, 1, "同一位置下，投放时间与后台id为【{$db_record['id']}】，包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
					}
				}
			}  
        }
        return 2;
        return $error_msg;
    }

    // 统一的逻辑检查：检查添加软件数据是否合法
    function logic_check_download($res) {
        // 业务逻辑：
        $content_arr=array();
        $content_arr[0]=$res;
        $recommend_model = M('download_recommend');
        $recommend_soft_model = M('download_recommend_soft');
        $soft_model = M("soft");//软件大表
        // 业务逻辑：以下为各项具体检查
        foreach($content_arr as $key=>$record) {
            // 检查是不是编辑，如果是编辑，给record增加extent_id字段并分配其在表里的值
            if (isset($record['id'])) {
                $where = array('id' => array('EQ', $record['id']));
                $find = $recommend_soft_model->where($where)->find();
                $content_arr[$key]['recommend_id'] = $find['recommend_id'];
                $record['recommend_id'] = $find['recommend_id'];
            }
            // 检查推荐位包名ID是否有效
            if (isset($record['recommend_id']) && $record['recommend_id'] != "") {
                $where = array(
                    'id' => array('EQ', $record['recommend_id']),
                    'status' => array('EQ', 1),
                );
                $find = $recommend_model->where($where)->find();
                
                if (!$find) {
                	return array('code'=>1,'message'=>"推荐位包名id不存在;");
                    $this->append_error_msg($error_msg, $key, 1, "推荐位包名id不存在;");
                } else {
                    $content_arr[$key]['recommend_id'] = $record['recommend_id'];
                    // 如果传了推荐位包名，还需要检查与recommend_id是否匹配
                    if (isset($record['recommend_package'])) {
                        if ($find['package'] != $record['recommend_package']) {
                            return array('code'=>1,'message'=>"推荐位包名id与推荐位包名不对应;");
                            $this->append_error_msg($error_msg, $key, 1, "推荐位包名id与推荐位包名不对应;");
                        }
                    }
                }
            }
            // 检查包名是否在sj_soft表里
            if (isset($record['package']) && $record['package'] != "") {
                $where = array(
                    'package' => $record['package'],
                    'status' => 1,
                    //'hide' => array('in', array(1, 1024)),
                );
                $find = $soft_model->where($where)->find();
                
                if (!$find) {
                	return array('code'=>1,'message'=>"包名【{$record['package']}】不存在于市场软件库中;");
                    $this->append_error_msg($error_msg, $key, 1, "包名【{$record['package']}】不存在于市场软件库中;");
                }
            } else {
            	return array('code'=>1,'message'=>"包名不能为空;");
                $this->append_error_msg($error_msg, $key, 1, "包名不能为空;");
            }
        }
        
        
        // 检查每一行数据是否与数据库的存储内容相冲突

        foreach($content_arr as $key=>$record) {
            $start_time = $record['start_tm'];
            $end_time = $record['end_tm'];
            $where = array(
                'package' => array('EQ', $record['package']),
                'status' => array('NEQ', 0),
                'recommend_id' => array('EQ', $record['recommend_id']),
                'start_tm' => array('ELT', $end_time),
                'end_tm' => array('EGT', $start_time),
            );
            if (isset($record['id'])) {
                $where['id'] = array('NEQ', $record['id']);
            }
            $db_records = $recommend_soft_model->where($where)->select();
            // echo "<pre>";var_dump($db_records);
            // echo $recommend_soft_model->getLastSql();
            // 有冲突的后台记录
            foreach($db_records as $db_key=>$db_record) {
                $start_at_str = date('Y-m-d H:i:s',$db_record['start_tm']);
                $end_at_str = date('Y-m-d H:i:s',$db_record['end_tm']);
                $status_paused_hint = "";
                if ($db_record['status'] == 2) {
                    $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                }
                return array('code'=>1,'message'=>"投放区间与ID为【{$db_record['id']}】，包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
                $this->append_error_msg($error_msg, $key, 1, "投放区间与ID为【{$db_record['id']}】，包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
            }
        }
        return 2;
        return $error_msg;
    }

    function logic_check_adextent($res)
	{
		// echo "<pre>";var_dump($res);die;
		$model = new Model();
		$id=$res['id'];
		$myself = $res;
		$ad_name = $res['ad_name'];
		
		if($extent_have){
			return array('code'=>1,'message'=>"该区间名称在此分类下已存在");
			$this -> error("该区间名称在此分类下已存在");
		}
		// echo $model ->getLastSql();
		// echo "<pre>";var_dump($res);
		// echo "<pre>";var_dump($extent_have);die;
		if(!$ad_name){
			return array('code'=>1,'message'=>"请填写轮播图名称");
			$this -> error("请填写轮播图名称");
		}
		$ad_type = $res['ad_type'];
		$note = $res['note'];
		$start_tm = $res['start_tm'];
		$end_tm = $res['end_tm'];
		if(isset($res['co_type'])){
			$co_type = $res['co_type'];
		}else{
			$co_type = 0;
		}
		//V6.2添加同一位置不能超过8张
		$find_result = $model -> table('sj_ad_extent') -> where(array('extent_id' => $myself['extent_id'],'status' =>1)) -> find();
		$child_type = $find_result['child_type'];
		$cid = $find_result['cid'];
		
		$cid_str=preg_replace('/^,/','',$cid);
		$cid_str=preg_replace('/,$/','',$cid_str);
		if($cid_str==""||$cid_str=="0")
		{
			$max_time=max($start_tm,time());
			$end_time=$end_tm;
			
			$where=array(
					'a.start_tm'=>array('elt',$end_time),
					'a.end_tm'=>array('egt',$max_time),
					'a.status'=>1,
					'b.status'=>1,
					'b.pid'=>1,
					//'b.child_type'=>array('exp','in(1,4,5)'),
					'b.child_type'=>$child_type,
					'b.cid'=>array('in',array('',',0,','0')),
					'a.id'=>array('neq',$id),
				);
			// echo "<pre>";var_dump($where);die;
			$ad_pic_result=$model->table('sj_ad_new a')->join('sj_ad_extent b on a.extent_id = b.extent_id')->where($where)->select();
			// echo $model->getLastSql();
			// echo "<pre>";var_dump($ad_pic_result);die;
			$n=count($ad_pic_result);
			if($n>=$this->ad_pic_count_limit)
			{
				$time_arr=array();
				foreach($ad_pic_result as $k => $val)
				{	
					$time_arr[$k][0]=$val['start_tm'];
					$time_arr[$k][1]=$val['end_tm'];	
				}
				usort($time_arr, array('AdextentAction','reorder'));
				
				for($i=0;$i<$n;$i++) 
				{
					$last_tmp = array();
					$time = 1;
					$tmp = $time_arr[$i];
					for($j=$i;$j<$n-1;$j++) 
					{
						$last_tmp = $tmp;
						$tmp = $this->my_intersect($tmp, $time_arr[$j+1]);
						if (!$tmp) 
						{
							break;
						}
						$time++;
					}
					if($time>$this->ad_pic_count_limit)
					{
						return array('code'=>1,'message'=>"同一时间通用渠道的轮播图不能超过{$this->ad_pic_count_limit}张");
						$this->error("同一时间通用渠道的轮播图不能超过{$this->ad_pic_count_limit}张");
					}
				}
			}
		}

		if($res['beid']){
			$beid_where['_string'] = "beid = {$res['beid']} and end > ".time()." and status = 1";
			$beid_result = $model -> table('sj_push_be_detail') -> where($beid_where) -> select();
		
			if(!$beid_result){
				return array('code'=>1,'message'=>"填写的行为id不存在，请检查");
				$this -> error("填写的行为id不存在，请检查");
			} 
		}
		
		$featureid = 0;
		$package   = '';
		$href      = '';
		$activityid = 0;
		$page_type='';
		$page_type_name='';
		$page_title='';
		$beid = trim($res['beid']);
		$self_extent = $model -> table('sj_ad_extent') -> where(array('extent_id' => $myself['extent_id'])) -> find();
		$same_extent_where['_string'] = "pid = {$self_extent['pid']} and child_type = {$self_extent['child_type']} and status=1";//同一区间的也要判断是否存在 去掉and extent_id != {$myself['extent_id']} added by shitingting
		$same_extent_result = $model -> table('sj_ad_extent') -> where($same_extent_where) -> select();
		foreach($same_extent_result as $key => $val){
			$same_extent_str_go .= $val['extent_id'].',';
		}
		$same_extent_str = substr($same_extent_str_go,0,-1);
		switch ($res['ad_type'])
		{
			case 1:
				$featureid  =   $res['featureid'];
			
				if(!$featureid){
					return array('code'=>1,'message'=>"请选择专题");
					$this -> error("请选择专题");
				}
				if($featureid){
					$feature_have_result = $model -> table('sj_feature') -> where(array('feature_id' => $featureid,'status' => 1)) -> select();
					if(!$feature_have_result){
						return array('code'=>1,'message'=>"专题id不存在");
						$this -> error("专题id不存在");
					}
				}
				$feature_have_where['_string'] = "featureid = {$featureid} and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm)." and status = 1 and extent_id in ({$same_extent_str}) and id !={$id}";
				$feature_have_been = $model -> table('sj_ad_new') -> where($feature_have_where) -> select();
				if($feature_have_been){
					return array('code'=>1,'message'=>"该专题已存在于其他轮播图");
					$this -> error("该专题已存在于其他轮播图");
				}
				break;
			case 2:
				$package   =  preg_replace('/[\s]+/','',$res['package']);
				$have_package = $model -> table('sj_soft') -> where(array('package' => $package,'status' => 1,'hide' => 1)) -> find();
				// echo "<pre>";var_dump($have_package);die;
				if(!$have_package){
					return array('code'=>1,'message'=>"包名不存在");
					$this -> error("包名不存在");
				}
				$package_have_where['_string'] = "package = '{$package}' and start_tm <= ".$end_tm." and end_tm >= ".$start_tm." and status = 1 and extent_id in ({$same_extent_str}) and id !={$id}";
				$package_have_been = $model -> table('sj_ad_new') -> where($package_have_where) -> select();
				// echo $model ->getLastSql();
				// echo "<pre>";var_dump($package_have_been);
				if($package_have_been){
					return array('code'=>1,'message'=>"该包名已存在于其他轮播图");
					$this -> error("该包名已存在于其他轮播图");
				}
				break;
			case 3:
				$href = trim($res['href']);
				$page_title = trim($res['page_title']);
				if(!$res['page_title']){
					return array('code'=>1,'message'=>"请填写网页标题");
					$this -> error("请填写网页标题");
				}
				if(!$res['href']){
					return array('code'=>1,'message'=>"请填写网页链接");
					$this -> error("请填写网页链接");
				}
				if($res['open_type'])
				{
					$open_type = $res['open_type'];
				}
				else
				{
					return array('code'=>1,'message'=>"请选择网页广告打开方式");
					$this-> error("请选择网页广告打开方式");
				}
				//增加对网址的判断  				2015-3-27 added by shitingting
				$url_have_where['_string'] = "href = '{$href}' and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm)." and status = 1  and extent_id in ({$same_extent_str}) and id !={$id}";
				$url_have_been = $model -> table('sj_ad_new') -> where($url_have_where) -> select();
				if($url_have_been){
					return array('code'=>1,'message'=>"该网址已存在于其他轮播图");
					$this -> error("该网址已存在于其他轮播图");
				}
				break;
			case 4:
				$activityid = $res['activityid'];
				
				if(!$activityid){
					return array('code'=>1,'message'=>"请选择活动");
					$this -> error("请选择活动");
				}
				if($activityid){
					$activity_have_result = $model -> table('sj_activity') -> where(array('id' => $activityid,'status' => 1)) -> select();
					if(!$activity_have_result){
						return array('code'=>1,'message'=>"活动id不存在");
						$this -> error("活动id不存在");
					}
				}
				$activity_have_where['_string'] = "activityid = {$activityid} and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm)." and status = 1 and extent_id in ({$same_extent_str}) and id !={$id}";
				$activity_have_been = $model -> table('sj_ad_new') -> where($activity_have_where) -> select();
				if($activity_have_been){
					return array('code'=>1,'message'=>"该活动已存在于其他轮播图");
					$this -> error("该活动已存在于其他轮播图");
				}
				break;
			case 5:
				//V6.0添加跳转页面
                $page_type=$res['page_type'];
				if($page_type==4)
				{
					$page_name=trim($res['page_name4']);
				}
				elseif($page_type==1)
				{
					$page_name=trim($res['page_name1']);
				}
				if(!$page_name)
				{
					return array('code'=>1,'message'=>"页面不能为空");
					$this -> error("页面不能为空");
				}
				else
				{
					$page_type_name = ContentTypeModel::convertPageName2PageType($page_name, $page_type);
					if (!$page_type_name) 
					{
						return array('code'=>1,'message'=>"页面不存在");
						$this -> error("页面不存在");
					}
					else
					{	
						if($page_type_name=="fixed_bbs_detailpage")
						{
							$bbs_detail_page_id = trim($res['bbs_detail_page_id']);
							if(!$res['bbs_detail_page_id']){
								return array('code'=>1,'message'=>"请填写帖子TID");
								$this -> error("请填写帖子TID");
							}
							$page_type_name = "bbs_detailpage_".$bbs_detail_page_id;
						}
						$page_have_where['_string'] = "page_name = '{$page_type_name}' and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm)." and status = 1 and extent_id in ({$same_extent_str}) and id !={$id}";
						$page_have_been = $model -> table('sj_ad_new') -> where($page_have_where) -> select();
						if($page_have_been)
						{
							return array('code'=>1,'message'=>"该页面已存在于其他轮播图");
							$this -> error("该页面已存在于其他轮播图");
						}	
					}
				}
				break;
		}
		return 2;
	}


	//8张轮播图的有关函数
	function my_intersect($a, $b) 
	{
		$c = max($a[0], $b[0]);
		$d = min($a[1], $b[1]);
		$s1 = date('Y-m-d H:i:s', $a[0]);
		$e1 = date('Y-m-d H:i:s', $a[1]);	
		$s2 = date('Y-m-d H:i:s', $b[0]);
		$e2 = date('Y-m-d H:i:s', $b[1]);	
		$s3 = date('Y-m-d H:i:s', $c);
		$e3 = date('Y-m-d H:i:s', $d);	
		if ($c<=$d) {
			return array($c, $d);
		} else {
			//var_dump($c, $d);
			//echo "{$s1}~{$e1} {$s2}~{$e2} $s3, $e3, +++++++\n";	
			return false;
		}
	}


	//执行编辑
	function logic_check_Search_words($res){
		// echo "<pre>";var_dump($res);
		$t1=$res['start_tm'];
		$t2=$res['end_tm'];
		$model = new Model();
		foreach($res as $k=>$v){
			if($k=='end_tm'){

				$res['fromdate']=date('Y-m-d 00:00:00',$res['end_tm']);
			}
			if($k=='start_tm'){
				$res['todate']=date('Y-m-d 00:00:00',$res['start_tm']);
			}
		}
		$ress=array();
		foreach($res as $k=>$v){
			$ress[$k][]=$v;
		}
		$res=$ress;
		
		$package = $res['package'];
		$key_word = $res['key_word'];
		$fromdate = $res['fromdate'];
		$todate = $res['todate'];
		$key_type = $res['key_type'];
		$hot_id = $res['hot_id'];
		$id = $res['id'];
		$rank = $res['rank'];
		$type = $res['type'];
		$show_pic = $res['show_pic'];
		
		foreach($rank as $k=>$v){
			$rank_data[]['rank'] = $v;
		}
		// echo "<pre>";var_dump($rank_data);die;
		foreach ($fromdate as $k=>$v){
			$rank_data[$k]['fromdate'] = $v;	
		}
		foreach ($todate as $k2=>$v2){
			$rank_data[$k2]['todate'] = $v2;	
		}
		foreach($type as $k3=>$v3){
			$rank_data[$k3]['type'] = $v3;
		}
		
		foreach($rank_data as $k=>$v){
			$new_rank[$v['rank']][] = $v;
		}
		
		foreach($new_rank as $key =>$val){
			if($key==0 || $key<1){
				return array('code'=>1,'message'=>"您填写的排序有问题");
				$this -> error("您填写的排序有问题");
				exit;

			}
		}

		$r = true;
		
		foreach ($new_rank as $k=>$v){
			foreach($v as $key=>$val){
				$r = $this->check_xx($v,$val,$key);
				if ($r == false) {
					break;
				}
			}
			if ($r == false) {
				break;
			}
		}

		if($r==false){
			return array('code'=>1,'message'=>"当前排期内排序发生冲突");
			$this -> error("当前排期内排序发生冲突");
			exit;
		}

		
		foreach($rank as $k=>$v){
			if($v==''){
				return array('code'=>1,'message'=>"排序不能为空");
				$this -> error("排序不能为空");
			}
		}

		foreach ($location as $key1 => $value) {
			$data[]['location'] = $value;
		}
		foreach ($fromdate as $k=>$v){
			$data[$k]['fromdate'] = $v;	
		}
		foreach ($todate as $k2=>$v2){
			$data[$k2]['todate'] = $v2;	
		}
		

		foreach($show_pic as $k_pic =>$v_pic)
		{
			$show_pic[$k_pic] = $v_pic;
		}
		foreach($package as $key => $val){
			if($val == "为空表示不关联"||empty($val))
			{
				$val = "";
				$show_pic[$key] = 0;//只有关联包名的时候才能显示选择大图或者小图
			}
			else
			{
				if($show_pic[$key]==0)
				{
					return array('code'=>1,'message'=>"关联包必须选择显示图片样式");
					$this -> error("关联包必须选择显示图片样式");
				}
			}
			$package[$key] = $val;
		}
	
		//$id = $_POST['id'];
		foreach($package as $key => $val){
			$been_result = $model -> table('sj_soft') -> where(array('package' => $val,'hide' => 1)) -> select();
			
			if($val && $val != "为空表示不关联"){
				if(!$been_result){
					return array('code'=>1,'message'=>"关联包名'{$val}'不存在");
					$this -> error("关联包名'{$val}'不存在");
				}
			}
		}

		foreach($key_word as $key => $val){
			if($this -> strlen_az($val) > 20 || $this -> strlen_az($val) < 1){
				return array('code'=>1,'message'=>"请填写不超过10个汉字的搜索热词");
				$this -> error('请填写不超过10个汉字的搜索热词');
			}
			if(!preg_match("/^[0-9a-zA-Z\x{4e00}-\x{9fa5}]+$/u",$val)){
				return array('code'=>1,'message'=>"搜索热词'{$val}'不可含有特殊字符");
				$this -> error("搜索热词'{$val}'不可含有特殊字符");
			}
		}
		foreach($rank as $key =>$val){

		}

		foreach($fromdate as $key => $val){
			if(!$val || !$todate[$key]){
				return array('code'=>1,'message'=>"请填写开始时间和结束时间！");
				$this -> error("请填写开始时间和结束时间！");
			}
			$vals = strtotime(date('Y-m-d 00:00:00',strtotime($val)));
			$eval = strtotime(date('Y-m-d 23:59:59',strtotime($todate[$key])));
			$start_tm[$key] = $vals;
			$end_tm[$key] = $eval;
		}
		  //已过期的信息复制上线判断

		if($res['life']==1)
		{
		  $time_now = strtotime(date('Y-m-d 00:00:00',time()));
		  if($eval<time())
		  {
		  	return array('code'=>1,'message'=>"您修改的复制上线的日期还是无效日期");
			$this->error("您修改的复制上线的日期还是无效日期");
		  }
		  if($vals <$time_now)
		  {
		  	return array('code'=>1,'message'=>"您修改的复制上线的开始日期必须大于当前日期");
		    $this->error("您修改的复制上线的开始日期必须大于当前日期");
		  }
		}
		$my_result = $model -> table('sj_search_keywords') -> where(array('status' => 1)) -> select();

		foreach($id as $key => $val){
			//检查排序是否冲突
		   if($res['life']==1)
		    {
			  $searchkey_have_where = "key_word = '{$key_word[$key]}' and start_tm <= {$end_tm[$key]} and end_tm >= {$start_tm[$key]}  and status = 1";
			  // $searchkey_have_where = "key_word = '{$key_word[$key]}' and start_tm <= {$end_tm[$key]} and end_tm >= {$start_tm[$key]} and end_tm >= ".time()." and status = 1";
			}
			else
			{
			  $searchkey_have_where = "key_word = '{$key_word[$key]}' and start_tm <= {$t2} and end_tm >= {$t1} and status = 1 and id != {$val}";
			  // $searchkey_have_where = "key_word = '{$key_word[$key]}' and start_tm <= {$end_tm[$key]} and end_tm >= {$start_tm[$key]} and end_tm >= ".time()." and status = 1 and id != {$val}";
			}
			$searchkey_have_result = $model -> table('sj_search_keywords') -> where($searchkey_have_where) -> select();
			// echo $model ->getLastSql();
			// echo "<pre>";var_dump($searchkey_have_result);die;
			if($searchkey_have_result){
				if($package[$key]){
					return array('code'=>1,'message'=>"当前排期内已存在该搜索热词{$key_word[$key]},包名为{$package[$key]}！");
					$this -> error("当前排期内已存在该搜索热词{$key_word[$key]},包名为{$package[$key]}！");
				}else{
					return array('code'=>1,'message'=>"当前排期内已存在该搜索热词{$key_word[$key]}");
					$this -> error("当前排期内已存在该搜索热词{$key_word[$key]}");
				}
			}


			if($package[$key]){
				if($res['life']==1)
				{
				  $package_have_where = "package = '{$package[$key]}' and start_tm <= {$end_tm[$key]} and end_tm >={$start_tm[$key]} and end_tm >=".time()." and status = 1";
				}
				else
				{
				  $package_have_where = "package = '{$package[$key]}' and start_tm <= {$end_tm[$key]} and end_tm >={$start_tm[$key]} and end_tm >=".time()." and status = 1 and id != {$val}";
				}
				$package_have_result = $model -> table('sj_search_keywords') -> where($package_have_where) -> select();
				// echo $model ->getLastSql();echo "<br>";
				// echo "<pre>";var_dump($package_have_result);die;
				if($package_have_result){
					return array('code'=>1,'message'=>"当前排序内已存在该关联包名{$package[$key]},搜索热词为{$key_word[$key]}！");
					$this -> error("当前排序内已存在该关联包名{$package[$key]},搜索热词为{$key_word[$key]}！");
				}
			}
		}
		return 2;
	}
	

	//判断重复
	function check_xx($v,$val,$k) {
		$start = strtotime($val['fromdate']);
		$end = strtotime($val['todate']);
		$r = true;

		foreach ($v as $key => $value) {
			if ($k != $key) {
				$s = strtotime($value['fromdate']);
				$e = strtotime($value['todate']);

				if (($start >= $s && $start <= $e) || ($end >= $s && $end <= $e) || ($end >= $e && $start <= $s)) {
					$r = false;
					break;
				}
			}
		}

		return $r;
	}

	function strlen_az($string, $charset='utf-8')
	{
		$n = $count = 0;
		$length = strlen($string);
		if (strtolower($charset) == 'utf-8')
		{
			while ($n < $length)
			{
				$currentByte = ord($string[$n]);
				if ($currentByte == 9 || $currentByte == 10 || (32 <= $currentByte && $currentByte <= 126))
				{
					$n++;
					$count++;
				} elseif (194 <= $currentByte && $currentByte <= 223)
				{
					$n += 2;
					$count += 2;
				} elseif (224 <= $currentByte && $currentByte <= 239)
				{
					$n += 3;
					$count += 2;
				} elseif (240 <= $currentByte && $currentByte <= 247)
				{
					$n += 4;
					$count += 2;
				} elseif (248 <= $currentByte && $currentByte <= 251)
				{
					$n += 5;
					$count += 2;
				} elseif ($currentByte == 252 || $currentByte == 253)
				{
					$n += 6;
					$count += 2;
				} else
				{
					$n++;
					$count++;
				}
				if ($count >= $length)
				{break;
				}
			}
			return $count;
		} else {
			for ($i = 0; $i < $length; $i++)
			{
				if (ord($string[$i]) > 127) {
					$i++;
					$count++;
				}
				$count++;
			}
			return $count;
		}
	}


	  // 统一的逻辑检查：检查添加软件数据是否合法
    function logic_check_softrecomend($res) {
        // 业务逻辑：软件推荐表、配置表
        $M_soft_recommend = 'soft_recommend';
        $recommend_model = M($M_soft_recommend);
        $config_model = D('Sj.Config');
        
        // 业务逻辑：区间表、区间软件表
        $M_extent_table = 'extent_v1';
        $M_extent_soft_table = 'extent_soft_v1';
        // 加一下前缀（真正的表名），主要用在join sql里
        $extent_table = 'sj_' . $M_extent_table;
        $extent_soft_table = 'sj_' . $M_extent_soft_table;
        // 获得三个表的model
        $extent_model = M($M_extent_table);
        $extent_soft_model = M($M_extent_soft_table);
        $soft_model = M("soft");//软件大表
        // 业务逻辑：以下为各项具体检查
        $content_arr=array();
        $content_arr[0]=$res;
        foreach($content_arr as $key=>$record) {
            // 检查包名是否在sj_soft表里
            if (isset($record['soft_package']) && $record['soft_package'] != "") {
                $where = array(
                    'package' => $record['soft_package'],
                    'status' => 1,
                );
                $find = $soft_model->where($where)->find();
                
                if (!$find) {
                	return array('code'=>1,'message'=>"包名【{$record['soft_package']}】不存在于市场软件库中;");
                    $this->append_error_msg($error_msg, $key, 1, "包名【{$record['soft_package']}】不存在于市场软件库中;");
                }
            } else {
            	return array('code'=>1,'message'=>"包名不能为空;");
                $this->append_error_msg($error_msg, $key, 1, "包名不能为空;");
            }
            // 检查位置
            if (isset($record['rank']) && $record['rank'] != "") {
                if (!preg_match("/^\d+$/", $record['rank'])) {
                    // 不是数字
                    return array('code'=>1,'message'=>"位置值应为整数;");
                    $this->append_error_msg($error_msg, $key, 1, "位置值应为整数;");
                } else if ($record['rank'] > 10 || $record['rank'] < 1) {
                    return array('code'=>1,'message'=>"位置值应在1-10之间;");
                    // 不在1-10之间
                    $this->append_error_msg($error_msg, $key, 1, "位置值应在1-10之间;");
                } else {
                    // 判断是否处于特殊位置

                    $special_rank_where = array(
                        'config_type' => 'suggest_config',
                        'status' => 1
                    );
                    $config = $config_model->where($special_rank_where)->find();
                    $config = json_decode($config['configcontent'], true);
                    $str = "";
                    foreach($config as $m=>$n){
                        $str .=$n.",";
                    }

                    if(in_array($record['rank'],$config)){
                        return array('code'=>1,'message'=>"位置选择有误，请不要选择".$str.";");
                        $this->append_error_msg($error_msg, $key, 1, "位置选择有误，请不要选择".$str.";");
                    }
                }
            } else {
            	return array('code'=>1,'message'=>"位置值不能为空;");
                $this->append_error_msg($error_msg, $key, 1, "位置值不能为空;");
            }
        }
        
        // 检查每一行数据是否与数据库的存储内容相冲突
        foreach($content_arr as $key=>$record) {
            $where = array(
                'soft_package' => $record['soft_package'],
                'status' => array(array('EQ', 1), array('EQ', 2), 'OR'),
            );
            if (isset($record['id'])) {
                $where['id'] = array('NEQ', $record['id']);
            }
            $find = $recommend_model->where($where)->find();
            if($find){
                $status_paused_hint = "";
                if ($find['status'] == 2) {
                    $status_paused_hint = "，其处于已停用状态，请前往批量明细列表中操作";
                }
                return array('code'=>1,'message'=>"该包名已经添加过{$status_paused_hint};");
                $this->append_error_msg($error_msg, $key, 1, "该包名已经添加过{$status_paused_hint};");
            }
        }
        return 2;
        return $error_msg;
    }
    function check_shield($package,$start_tm,$end_tm,$biaoshi3=0){
        if($start_tm<time()){
            $start_tm=time();
        }
        $str="";
        if($package!=trim($package)){
            return '输入包名有误，可能前后包含空格，请处理后重新提交';
        }
        if(is_array($biaoshi3)){
            $biaoshi2=$biaoshi3[0];
        }else{
            $biaoshi=$biaoshi3;
        }
        if(!$package){
            return false;
        }
        if(!$biaoshi2){
             $str.=$this->check_white_library($package,$start_tm,$end_tm);
        }
        //判断软件是否安全
        $where=array();
        $where['safe']=array('gt',1);
        $where['package']=$package;
        $where['status']=1;
        $where['hide']=1;
        $data=$this->table("sj_soft")->where($where)->find();
        if($data){
            $this -> writelog('shield_need.log',$this->getLastSql(),'query');
            if(!($this->table("sj_safe_white_package")->where(array('status'=>1,'package'=>$package))->find())){
                $this -> writelog('shield_need.log',$this->getLastSql(),'query');
                $str.= $package."软件处于【软件管理-不安全列表】。";
            }
        }
        $where=array();
        $where['hide']=1;
        $where['package']=$package;
        $where['status']=1;
        $data=$this->table("sj_soft")->where($where)->find();
        if(!$data){
            $this -> writelog('shield_need.log',$this->getLastSql(),'query');
            $str.= $package."软件处于未上架状态。";
        }
        $where=array();
        $where['shield']=1;
        $where['package']=$package;
        $where["shield_start"]=array('lt',$end_tm);
        $where["shield_end"]=array('gt',$start_tm);
        $data=$this->table("sj_soft_note")->where($where)->find();
        if($data){
            $this -> writelog('shield_need.log',$this->getLastSql(),'query');
            $shield_start=date("Y-m-d H:i:s",$data['shield_start']);
            $shield_end=date("Y-m-d H:i:s",$data['shield_end']);
            // $str.= $package."软件处于【软件管理-屏蔽】。屏蔽开始时间:".$shield_start."。屏蔽结束时间:".$shield_end.'。';
            $str.= $package."软件处于【软件管理-屏蔽】。";
        }
        if($biaoshi){
            //仅搜索时显示
            $where=array();
            // $where['status']=1;
            $where['package']=$package;
            $where["only_search"]=1;
            $data=$this->table("sj_soft_note")->where($where)->find();

            if($data){
                $this -> writelog('shield_need.log',$this->getLastSql(),'query');
                $str.= $package."软件处于【软件管理-仅搜索可见】。";
            }
        }
        if($biaoshi){
            //搜索适配列表
            $where=array();
            $where['status']=1;
            $where['package']=$package;
            $where["start_time"]=array('lt',$end_tm);
            $where["end_time"]=array('gt',$start_tm);
            $data=$this->table("sj_search_adapter")->where($where)->find();
            if($data){
                $this -> writelog('shield_need.log',$this->getLastSql(),'query');
                $shield_start=date("Y-m-d H:i:s",$data['start_time']);
                $shield_end=date("Y-m-d H:i:s",$data['end_time']);
                // $str.= $package."软件处于【软件排行屏蔽列表-搜索适配列表】。屏蔽开始时间:".$shield_start."。屏蔽结束时间:".$shield_end.'。';
                $str.= $package."软件处于【软件排行屏蔽列表-搜索适配列表】。";
            }
        }

        if($str){
            return $str;
        }

        
    }
    function check_ad($package,$start_tm,$end_tm,$biaoshi=0,$bs_new=0){
        if(is_array($bs_new)){
            $exclude_fea=$bs_new['exclude_fea'];
            $out=$bs_new['whitelist_out_handle'];
            $bs_new=$bs_new['bs_new'];
        }
        if($start_tm<time() && $bs_new!=2){
            $start_tm=time();
        }
        $package=trim($package);
        //实例
        if(!$biaoshi){
             // unset($table_config['16']);
             $table_config=array(
                "0"=>array("sj_download_recommend_soft",'start_tm','end_tm','下载推荐','package'),
                "1"=> array("sj_think_words",'start_time','end_time',"搜索suggest设置",'package'),
                "2"=>array("sj_animation_ad",'start_at','end_at',"悬浮窗管理",'package'),
                // "3"=>array("sj_feature_soft",'start_tm','end_tm',"专题配置",'package'),
                "4"=>array("sj_activity",'start_tm','end_tm',"活动分区",'package'),
                "5"=>array("sj_extent_soft_v1",'start_at','end_at',"市场首页软件列表",'package'),
                "6"=> array("sj_category_extent_soft",'start_at','end_at',"频道列表软件推荐",'package'),
                // "7"=>array("sj_soft_recommend",'start_tm','end_tm',"软件推荐设置",'soft_package'),
                "8"=>array("sj_lading_soft",'start_tm','end_tm',"一键装机运营",'package'),
                "9"=>array("sj_ad_new",'start_tm','end_tm',"新版轮播图",'package'),
                "10"=> array("sj_splash_manage",'start_tm','end_tm',"闪屏管理",'package'),
                "11"=>array("sj_flexible_extent_soft",'start_at','end_at',"灵活运营样式",'package'),
                "12"=>array("sj_search_keywords",'start_tm','end_tm',"搜索热词推荐",'package'),
                "13"=> array("sj_text_page",'start_time','end_time',"文字链推广位",'package'),
                "14"=> array("sj_return_back_ad",'start_at','end_at',"返回运营",'package'),
                "15"=>array("sj_necessary_extent_soft",'start_at','end_at',"必备频道软件推荐",'package'),
                "16"=>array("sj_search_key_package",'start_tm','stop_tm',"搜索结果运营",'package'),
                "17"=>array("sj_search_tips_package",'start_tm','end_tm',"搜索提示运营",'package'),
                "18"=>array("sj_market_push",'start_tm','end_tm',"市场手机---PUSH",'soft_package'),
                "19"=>array("sj_soft_associate_hot_word",'begin','end',"搜索阿拉丁",'package'),
                "20"=>array("sj_search_related_content",'start_tm','end_tm',"搜索相关词管理",'package'),
                "21"=>array("sj_desk_icon",'start_time','end_time',"桌面预下载配置",'package'),
                "22"=>array("sj_custom_push_silence",'start_at','end_at',"静默下载安装",'silence_package'),
                "23"=>array("sj_custom_push",'start_at','end_at',"定制推送列表",'package'),
            );
        }
		else if($biaoshi==2)//活动id在运营位
		{
            $table_config=array(
                "0"=>array("sj_animation_ad",'start_at','end_at',"悬浮窗管理",'activity_id'),
				"1"=>array("sj_custom_push",'start_at','end_at',"定制推送管理",'activity_id'),
				"2"=>array("sj_exit_ad",'ad_start_tm','ad_end_tm',"退出广告位管理",'activity_id'),
				"3"=>array("sj_flexible_extent_soft",'start_at','end_at',"灵活运营样式",'activity_id'),
				"4"=>array("sj_market_push",'start_tm','end_tm',"市场手机---PUSH",'activity_id'),
				"5"=> array("sj_return_back_ad",'start_at','end_at',"返回运营",'activity_id'),
				"6"=> array("sj_splash_manage",'start_tm','end_tm',"闪屏管理",'activity_id'),
				"7"=> array("sj_text_page",'start_time','end_time',"文字链推广位",'activity_id'),
				"8"=> array("sj_textquickentry_extent_soft",'start_at','end_at',"文字快捷入口",'activity_id'),
				"9"=>array("sj_ad_new",'start_tm','end_tm',"新版轮播图",'activityid'),
			);
        }
		else
		{
            $table_config=array(
                // "0"=>array("sj_download_recommend_soft",'start_tm','end_tm','下载推荐','package'),
                "1"=> array("sj_think_words",'start_time','end_time',"搜索suggest设置",'package'),
                // "2"=>array("sj_animation_ad",'start_at','end_at',"悬浮窗管理",'package'),
                // "3"=>array("sj_feature_soft",'start_tm','end_tm',"专题配置",'package'),
                // "4"=>array("sj_activity",'start_tm','end_tm',"活动分区",'package'),
                // "5"=>array("sj_extent_soft_v1",'start_at','end_at',"市场首页软件列表",'package'),
                // "6"=> array("sj_category_extent_soft",'start_at','end_at',"频道列表软件推荐",'package'),
                // "7"=>array("sj_soft_recommend",'start_tm','end_tm',"软件推荐设置",'soft_package'),
                // "8"=>array("sj_lading_soft",'start_tm','end_tm',"一键装机运营",'package'),
                // "9"=>array("sj_ad_new",'start_tm','end_tm',"新版轮播图",'package'),
                // "10"=> array("sj_splash_manage",'start_tm','end_tm',"闪屏管理",'package'),
                // "11"=>array("sj_flexible_extent_soft",'start_at','end_at',"灵活运营样式",'package'),
                "12"=>array("sj_search_keywords",'start_tm','end_tm',"搜索热词推荐",'package'),
                // "13"=> array("sj_text_page",'start_time','end_time',"文字链推广位",'package'),
                // "14"=> array("sj_return_back_ad",'start_at','end_at',"返回运营",'package'),
                // "15"=>array("sj_necessary_extent_soft",'start_at','end_at',"必备频道软件推荐",'package'),
                "16"=>array("sj_search_key_package",'start_tm','stop_tm',"搜索结果运营",'package'),
                "17"=>array("sj_search_tips_package",'start_tm','end_tm',"搜索提示运营",'package'),
                // "18"=>array("sj_market_push",'start_tm','end_tm',"市场手机---PUSH",'soft_package'),
                "19"=>array("sj_soft_associate_hot_word",'begin','end',"搜索阿拉丁",'package'),
                "20"=>array("sj_search_related_content",'start_tm','end_tm',"搜索相关词管理",'package'),
            );
        }
        $str="";
        foreach($table_config as $k=>$V){
            if($exclude_fea==1 && $V[0]=='sj_feature_soft'){
                continue;
            }
            $where=array();
            // $where['status']=1;
            if($V[0]=='sj_soft_associate_hot_word'){
                $where['stat']=1;
            }else{
                $where['status']=1;
            }
            // $pkg=$table_config[$k][4];
            // $where["$pkg"]=array('eq',$package);
            // if($V[0]=='sj_custom_push_silence'){
            //     $where[$V[4]]=array('like',"%{$package}%");
            // }else{
            $where[$V[4]]=array('eq',$package);
            // }
            if($bs_new==2){
                if($V[0]=='sj_feature_soft'){
                    continue;
                }
                $t=time();
                if($start_tm>$t){
                    $where[$V[2]]=array('exp',">={$end_tm} or ({$V[1]} <= {$start_tm} and {$V[2]} > {$t})");
                }else{
                    $where[$V[2]]=array('exp',">={$end_tm}");
                }
            }else{
                if(!$bs_new){
                    $where["$V[1]"]=array('lt',$end_tm);
                }
                $where[$V[2]]=array('gt',$start_tm);
            }
            $datas=$this->table($V[0])->where($where)->select();
            // if($V[0]=='sj_extent_soft_v1'){
            //     echo $this->getLastSql();echo "<br>";die;
            // }
            foreach($datas as $data){
                if($data){
                    $start=date("Y-m-d H:i:s",$data[$V[1]]);
                    $end=date("Y-m-d H:i:s",$data[$V[2]]);
                    if($V[0]=='sj_market_push'){
                        if($data['push_type']==1){
                            $V[3]='PUSH通知栏';
                        }else if($data['push_type']==2){
                            $V[3]='弹窗广告';
                        }else if($data['push_type']==3){
                            $V[3]='被动预下载';
                        }else if($data['push_type']==4){
                            $V[3]='隐形push';
                        }
                    }
                    if($out){
                        fputcsv($out,array($package,$V[3],$start,$end,$data['id'],$V[0]));
                    }
					if($biaoshi==2)//活动
					{
						$str.="【".$V[3]."运营位】正在使用本活动，无法停用；";
					}
					else
					{
						$str.=$package."软件在".$V[3]."广告位。广告开始时间:".$start."。广告结束时间:".$end."\n";
					}
                }
            }
        }
        // echo $this->getLastSql();echo "<br>";die;
        if($str){
            return $str;
        }
    }
    // 批量导入时检查屏蔽
    function logic_check_shield($content_arr,$start,$end,$table='',$bs=0) {
        // 初始化错误数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $this->init_error_msg($error_msg, $key);
        }
        foreach($content_arr as $key=>$record) {
            // 检查开始
            if (isset($record["$start"]) && $record["$start"] != "") {
                if (preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record["$start"])) {
                    $time = strtotime($record["$start"]);
                    if ($time) {
                        $content_arr[$key]['bk_start_time'] = $time;
                    }
                }
            } 
            // 检查结束时间
            if (isset($record["$end"]) && $record["$end"] != "") {
                if (preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record["$end"])) {
                    $time = strtotime($record["$end"]);
                    if ($time) {
                        $content_arr[$key]['bk_end_time'] = $time;
                    }
                }
            } 
            $content_arr[$key]['package'] = trim($record['package']);
        }
        //屏蔽软件上排期时报警需求 新增  yuesai
        // $AdSearch = D("Sj.AdSearch");
        foreach($content_arr as $key=>$record) {
            if($record['bk_start_time'] && $record['bk_end_time']){
                $shield_error=$this->check_shield($record['package'],$record['bk_start_time'],$record['bk_end_time'],$bs);
                if($table=='sj_extent_soft_v1'){
                    $shield_error.=$this->check_shield_old($record['package'],0,1);
                }else if($table=='sj_category_extent_soft'){
                    $shield_error.=$this->check_shield_old($record['package'],$record['$extent_id'],0,'sj_category_extent');
                }
                if($shield_error){
                    $this->append_error_msg($error_msg, $key, 1, $shield_error);
                }
            }
        }
        return $error_msg;
    }
    // 初始单条错误信息，初始化信息：flag为0，msg为空
    function init_error_msg(&$error_msg, $key) {
        if (!isset($error_msg))
            $error_msg = array();
        $error_msg[$key] = array('flag' => 0,'msg' => '');
    }

    // 添加错误信息
    function append_error_msg(&$error_msg, $key, $flag, $msg) {
        if (!isset($error_msg[$key])) {
            $this->init_error_msg($error_msg, $key);
        }
        $error_msg[$key]['flag'] |= $flag;
        $error_msg[$key]['msg'] .= $msg;
    }
    //判断软件是否处于旧版屏蔽列表
    function check_shield_old($package,$extent_id,$biaoshi=0,$table,$bs_new=0){
        $package=trim($package);
        $str="";
        if($biaoshi){
            $where=array();
            $where['filter_area']=array('like','%SUGGEST%');
            $where['package']=$package;
            $where['status']=1;
            $data=$this->table("sj_top_filter")->where($where)->find();
            if($data){
                $this -> writelog('shield_need.log',$this->getLastSql(),'query');
                // $str.= $package."软件处于<市场运营基础配置-软件屏蔽列表>。";
                $str.= $package."软件处于【市场运营基础配置-软件屏蔽列表】。";
            }
            if($bs_new){
                return "SUGGEST";
            }
        }else{
            //配置匹配数组
            $topFilterMap=array(
                'SUGGEST' => array('fixed_discovery_recommend','otherfixed_homepage_recommend'),
                'HOT' => 'top_hot',
                'HOT1D' => 'top_1d',
                'HOT7D' => 'top_7d',
                'HOT30D' => 'top_30d',
                'MAYBE_LIKE' => array('fixed_user_also_download','fixed_user_also_download_recommend'),
            );
            $topFilterMap_two=array(
                // 'CATEGORY_HOT' => '类别最热排行',
                'CATEGORY_HOT' => "/^top_\d+_hot$/",
                // 'BDLIST' => '榜单',
                'BDLIST' => "/^bdlist_\d+$/",
            );
            $where=array();
            $where['extent_id']=$extent_id;
            $where['status']=1;
            $data=$this->table("$table")->where($where)->find();
            if($table=='sj_category_extent'){
                $category_type=trim($data['category_type']);
            }else if($table=='sj_flexible_extent'){
                $category_type=trim($data['belong_page_type']);
            }
            $filter_area="";
            foreach($topFilterMap as $k=>$v){
                if(is_array($v)){
                    if(in_array($category_type, $v)){
                        $filter_area=$k;
                        continue;
                    }
                }else{
                    if($category_type==$v){
                        $filter_area=$k;
                        continue;
                    }
                }
            }
            if(!$filter_area){
                foreach($topFilterMap_two as $k=>$v){
                    if(preg_match($v,$category_type)){
                        $filter_area=$k;
                        continue;
                    }
                }
            }
            if($bs_new){
                return $filter_area;
            }
            if(isset($filter_area)){
                $where=array();
                $where['filter_area']=array('like','%'.$filter_area.'%');
                $where['package']=$package;
                $where['status']=1;
                $data=$this->table("sj_top_filter")->where($where)->find();
                $this -> writelog('shield_need.log',$this->getLastSql(),'query');
                if($data){
                    $str.= $package."软件处于【市场运营基础配置-软件屏蔽列表】。";
                }
            }
        }
        return $str;
    }
    function check_ad_old($package){
        $package=trim($package);
        $str="";
        $filter_area=array();
        $where=array();
        $where['package']=$package;
        $where['status']=1;
        $data=$this->table("sj_extent_soft_v1")->where($where)->find();
        if($data){
            if(in_array("SUGGEST", $_POST['filter_area'])){
                $start=date("Y-m-d H:i:s",$data["start_at"]);
                $end=date("Y-m-d H:i:s",$data["end_at"]);
                $str.=$package."软件在市场首页软件列表广告位。广告开始时间:".$start."。广告结束时间:".$end."\n";
            }
        }
        $where=array();
        $where['package']=$package;
        $where['status']=1;
        $where['end_at']=array('gt',time());
        $data=$this->table("sj_category_extent_soft")->where($where)->select();
        if($data){
            foreach($data as $v){
                $filter_area_one=  $this->check_shield_old($package,$v['extent_id'],0,"sj_category_extent",1);
                if(in_array($filter_area_one, $_POST['filter_area'])){
                    $start=date("Y-m-d H:i:s",$v["start_at"]);
                    $end=date("Y-m-d H:i:s",$v["end_at"]);
                    $str.=$package."软件在频道列表软件推荐广告位。广告开始时间:".$start."。广告结束时间:".$end."\n";
                }
            }
        }
        $where=array();
        $where['package']=$package;
        $where['status']=1;
        $where['end_at']=array('gt',time());
        $data=$this->table("sj_flexible_extent_soft")->where($where)->select();
        if($data){
            foreach($data as $v){
                $filter_area_two=  $this->check_shield_old($package,$v['extent_id'],0,"sj_flexible_extent",1);
                if(in_array($filter_area_two, $_POST['filter_area'])){
                    $start=date("Y-m-d H:i:s",$v["start_at"]);
                    $end=date("Y-m-d H:i:s",$v["end_at"]);
                    $str.=$package."软件在灵活运营样式广告位。广告开始时间:".$start."。广告结束时间:".$end."\n";
                }
            }
        }
        return $str;

    }
	//相关词 通用逻辑判断
	// 统一的逻辑检查：检查添加软件数据是否合法
    function logic_check_related($content_arr) 
	{
        // 初始化错误信息数组
        $error_msg = array();
        foreach($content_arr as $key => $value) 
		{
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：区间表、区间软件表
        // 获得三个表的model
        $keyword_model = M('search_related');
        $keyword_soft_model = M('search_related_content');
        $soft_model = M("soft");//软件大表
        // 业务逻辑：以下为各项具体检查
        foreach($content_arr as $key=>$record) 
		{
			// 检查开始时间
            if (isset($record['start_tm']) && $record['start_tm'] != "") 
			{
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['start_tm'])) 
				{
                    $this->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                } 
				else 
				{
                    $time = strtotime($record['start_tm']);
                    if ($time) 
					{
                        $content_arr[$key]['bk_start_time'] = $time;
                    }
					else 
					{
                        $this->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                    }
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "开始时间不能为空;");
            }
            // 检查结束时间
            if (isset($record['end_tm']) && $record['end_tm'] != "") 
			{
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['end_tm'])) 
				{
                    $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                } else {
                    $time = strtotime($record['end_tm']);
                    if ($time) 
					{
                        $content_arr[$key]['bk_end_time'] = $time;
                    } else {
                        $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                    }
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "结束时间不能为空;");
            }
            // 检查开始时间是否小于结束时间
            if (isset($content_arr[$key]['bk_start_time']) && isset($content_arr[$key]['bk_end_time'])) 
			{
                if ($content_arr[$key]['bk_start_time'] > $content_arr[$key]['bk_end_time']) {
                    $this->append_error_msg($error_msg, $key, 1, "开始时间需小于结束时间;");
                }
            }
			if(isset($record['guide_page']))
			{
				if($record['guide_page']==2)
				{
					//检查包名
					if (isset($record['package']) && $record['package'] != "") 
					{ 
						// 检查包名是否在sj_soft表里
						$where = array(
							'package' => $record['package'],
							'status' => 1,
							'hide' => array('EQ', 1),
						);
						$find = $soft_model->where($where)->find();
						if (!$find) 
						{
							$this->append_error_msg($error_msg, $key, 1, "包名【{$record['package']}】不存在于市场软件库中;");
						}
					} else {
						$this->append_error_msg($error_msg, $key, 1, "包名不能为空;");
					}
				}
			}else{
				$this->append_error_msg($error_msg, $key, 1, "导向页面不能为空;");
			}
		
			// 检查排序是否为数字
			if (isset($record['rank']) && $record['rank'] != "") 
			{
				if (!preg_match("/^\d+$/", $record['rank'])) 
				{
					$this->append_error_msg($error_msg, $key, 1, "排序应为整数;");
				}
				if($record['rank']<1||$record['rank']>20)
				{
					$this->append_error_msg($error_msg, $key, 1, "排序应为1~20的整数;");
				}
			}
			else 
			{
				$this->append_error_msg($error_msg, $key, 1, "排序值不能为空;");
			}
			//关键字 	单条记录的关键字为默认，批量上传检查是否有关键字，没有添加关键字
			if (isset($record['srh_key']) && $record['srh_key'] != "") 
			{
                /*$where = array(
                    'srh_key' => array('EQ', $record['srh_key']),
                    'status' => array('EQ', 1),
                );
                $find = $keyword_model->where($where)->find();
                if (!$find) 
				{
					//没有关键字就添加关键字
					$data=array(
						'srh_key' => $record['srh_key'],
						//'start_tm' => $content_arr[$key]['bk_start_time'],
						//'end_tm' => $content_arr[$key]['bk_end_time'],
						'create_tm' => time(),
						'update_tm' => time(),
						'status' => 1,
					);
					$result= $keyword_model->add($data);
					if($result)
					{
						$this->writelog("市场搜索管理_搜索相关词管理_添加关键字{$record['srh_key']}id:".$result,"sj_search_related");
					}
					else
					{
						$this->append_error_msg($error_msg, $key, 1, "关键字【{$record['srh_key']}】添加失败;");
					}
                } */
            }
			else
			{
                $this->append_error_msg($error_msg, $key, 1, "关键字不能为空;");
            }
			//相关词	单条记录的关键字为默认，批量上传检查是否有关键字，没有添加关键字
			if (isset($record['related_keys']) && $record['related_keys'] != "") 
			{
				//相关词8个汉字16个字符内
				if($this->strlen_az($record['related_keys'])>16)
				{
					$this->append_error_msg($error_msg, $key, 1, "相关词请输入8个汉字，16个字符内;");
				}
            }
			else
			{
                $this->append_error_msg($error_msg, $key, 1, "相关词不能为空;");
            }
        }
        
        // 检查行与行之间的数据是否冲突（主要检查相同包名的区间是否有冲突）
        $c_rank = $db_rank = $last_rank = array();
        foreach($content_arr as $key1=>$record1) 
		{
            // 如果1填写时间的不完善，则不比较
            if (!isset($record1['bk_start_time']) || !isset($record1['bk_end_time']))
                continue;
            foreach($content_arr as $key2=>$record2) {
                $k1 = $key1 + 1;
                $k2 = $key2 + 1;
                // 比较过的不比较
                if ($key1 >= $key2)
                    continue;
                // 关键字不一样，不比较
                if ($record1['srh_key'] != $record2['srh_key'])
                    continue;
               
                // 如果2填写时间的不完善，则不比较
                if (!isset($record2['bk_start_time']) || !isset($record2['bk_end_time']))
                    continue;
				 // 相关词相同
                if ($record1['related_keys'] == $record2['related_keys']) 
				{ 
					// 时间是否交叉
					if ($record1['bk_start_time'] <= $record2['bk_end_time'] && $record2['bk_start_time'] <= $record1['bk_end_time']) {
						$k1 = $key1 + 1; $k2 = $key2 + 1;
						$this->append_error_msg($error_msg, $key1, 1, "同一相关词下，投放区间与第{$k2}行有重叠;");
						$this->append_error_msg($error_msg, $key2, 1, "同一相关词下，投放区间与第{$k1}行有重叠;");
					}
                }
                // 排序相同
                if ($record1['rank'] == $record2['rank']) 
				{
					// 时间是否交叉
					if ($record1['bk_start_time'] <= $record2['bk_end_time'] && $record2['bk_start_time'] <= $record1['bk_end_time']) {
						$k1 = $key1 + 1; $k2 = $key2 + 1;
						$this->append_error_msg($error_msg, $key1, 1, "同一排序下，投放区间与第{$k2}行有重叠;");
						$this->append_error_msg($error_msg, $key2, 1, "同一排序下，投放区间与第{$k1}行有重叠;");
					}  
                }
            }
            $c_rank[$record1['srh_key']][] = $record1['rank'];
        }

        // 检查每一行数据是否与数据库的存储内容相冲突
        foreach($content_arr as $key=>$record) 
		{
			//根据srh_key获取srh_key的id
			$srh_key_result=$keyword_model->where(array('srh_key' => $record['srh_key'],'status'=>1 ))->find();
			if(!$srh_key_result['id'])
				continue; //关键词没有ID说明是新增加的词，数据库中肯定没有不用比较
            $where = array(
                'kid' => array('EQ', $srh_key_result['id']),
                'status' => array('NEQ', 0),
            );
            if (isset($record['id']))
                $where['id'] = array('NEQ', $record['id']);
            $db_records = $keyword_soft_model->where($where)->select();
			if (!$db_records)
				continue;
			// 检查时间、位置（排序）冲突
			// 如果填写时间的不完善，则不比较
			if (!isset($record['bk_start_time']) || !isset($record['bk_end_time']))
				continue;
            foreach($db_records as $db_record){
                if($db_record['end_tm']>time())
                $db_rank[$record['srh_key']][] = $db_record['rank'];
            }
            if(count($last_rank[$record['srh_key']])==0){
                $last_rank[$record['srh_key']] = array_unique(array_merge($db_rank[$record['srh_key']],$c_rank[$record['srh_key']]));
            }
            if(isset($rank[$record['srh_key']])) $last_rank[$record['srh_key']][] = $rank[$record['srh_key']];
			foreach($db_records as $db_k => $db_record) 
			{
				if ($record['related_keys'] == $db_record['related_keys']) 
				{
					// 将开始时间和结束时间转成时间戳
					$start1 = $record['bk_start_time']; 
					$end1 = $record['bk_end_time'];
					$start2 = $db_record['start_tm']; 
					$end2 = $db_record['end_tm'];
					if ($start1 <= $end2 && $start2 <= $end1) 
					{
						$start_at_str = date('Y-m-d H:i:s',$db_record['start_tm']);
						$end_at_str = date('Y-m-d H:i:s',$db_record['end_tm']);
						$status_paused_hint = "";
						if ($db_record['status'] == 2) 
						{
							$status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
						}
						$this->append_error_msg($error_msg, $key, 1, "同一相关词下，关键字为【{$record['srh_key']}】,投放时间与后台id为【{$db_record['id']}】，相关词为【{$db_record['related_keys']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
					}
				}
				if ($record['rank'] == $db_record['rank']) 
				{
					// 将开始时间和结束时间转成时间戳
					$start1 = $record['bk_start_time']; 
					$end1 = $record['bk_end_time'];
					$start2 = $db_record['start_tm']; 
					$end2 = $db_record['end_tm'];
					if ($start1 <= $end2 && $start2 <= $end1) 
					{
						$start_at_str = date('Y-m-d H:i:s',$db_record['start_tm']);
						$end_at_str = date('Y-m-d H:i:s',$db_record['end_tm']);
						$status_paused_hint = "";
						if ($db_record['status'] == 2)
						{
							$status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
						}
                        $rank[$record['srh_key']]= get_rank($last_rank[$record['srh_key']]);
						$this->append_error_msg($error_msg, $key, 1, "同一位置下，投放时间与后台id为【{$db_record['id']}】，相关词为【{$db_record['related_keys']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;rank={$rank[$record['srh_key']]}");
					}
				}
			}
        }
        return $error_msg;
    }
    function check_white_library($package,$start_tm,$end_tm){
        $where=array();
        $where['package']=$package;
        // $where['status']=1;
        $where['status']=array('in',array('1','2'));

        // $where['is_accept_ad']=1;
        $data=$this->table("sj_soft_whitelist")->where($where)->find();
        if($data){
            if($data['is_accept_ad']==1){
                if($data['end_tm']){
                    if(!($data['start_tm']<=$start_tm && $data['end_tm']>=$end_tm)){
                        //报错
                        $data1=1;
                    }
                }else{
                    if(!($data['start_tm']<=$start_tm)){
                        //报错
                        $data1=1;
                    }
                }
            }else if($data['is_accept_ad']==0){
                if(!$data['start_tm'] && !$data['end_tm']){
                     $data1=2;
                }else{
                    if($data['end_tm']>time()){
                        if(!($data['start_tm']<=$start_tm && $data['end_tm']>=$end_tm)){
                            //报错
                            $data1=1;
                        }
                    }else{
                        $data1=2;
                    }
                    
                }
            }
        }else{
            $data1=2;
        }
        $this -> writelog('shield_need.log',$this->getLastSql(),'query');
        $where=array();
        $where['package']=$package;
        $where['status']=1;
        $data2=$this->table("sj_ad_library")->where($where)->find();
        $this -> writelog('shield_need.log',$this->getLastSql(),'query');
        if($data1 && !$data2){
            if($data1==2){
                return $package."运营软件不在广告库/游戏联运库,请联系市场部。";
            }else if($data1==1){
                return "包名{$package},时间超出可配置范围，游戏联运库广告推广周期".date('Y-m-d H:i:s',$data['start_tm'])." 至".date('Y-m-d H:i:s',$data['end_tm']);
            }
            return $package."运营软件不在广告库/游戏联运库,请联系市场部。";
        }
    }
    function writelog($file,$str,$sign) {
        $log = $sign.'|'.$str.'|'.date('Y-m-d H:i:s')."\n";
        define('SHIELD_LOG','/data/att/permanent_log/dev.anzhi.com/');
        $log_path = SHIELD_LOG.date('Y-m-d').'/';
        if(!is_dir($log_path))  mkdir($log_path,0777,true);
        file_put_contents($log_path.$file,$log,FILE_APPEND);
    }
    // 校验软件屏蔽列表--new
    function pub_check_soft_filter($package){
        header('Content-Type: text/html; charset=utf-8');
        $model = M('soft_filter');
        $where=array('status' => 1,'package'=>$package);
        $time=time();
        $where['end_tm']=array('egt',$time.' && start_tm<='.$time);
        $data = $model->where($where)->field('package')->find();
        if($data){
            return array('code'=>1,'message'=>$data['package']."软件处于【市场运营基础配置-软件屏蔽列表_new】。");
            // echo $data['package']."软件处于<市场运营基础配置-软件屏蔽列表_new>。";
        }
    }
    // 校验软件屏蔽列表--new  生成可用于忽略的文件
    function generate_ignore_file($arr_shields,$table){
        //写入文件
        $file_dir="/tmp/shield_failure/";
        if(!is_dir($file_dir)){
            if(!mkdir(iconv("UTF-8", "GBK", $file_dir),0777,true)){
                jsmsg("创建目录失败", -1);
            }
        }
        $file_name=$table.time().".txt";
        $file_2=$file_dir.$file_name;
        $file_hwnd=fopen($file_2,"w");
        fwrite($file_hwnd,serialize($arr_shields)); //输入序列化的数据
        fclose($file_hwnd);
        return array('code'=>1,'filename'=>$file_name);
    }
    function logic_check_related_batch($res) 
    {
        // 业务逻辑：区间表、区间软件表
        // 获得三个表的model
        $keyword_model = M('search_related');
        $keyword_soft_model = M('search_related_content');
        $soft_model = M("soft");//软件大表
        $srh_key_result_two=$keyword_model->where(array('id' => $res['kid'] ))->find();
        if(!$srh_key_result_two){
            return array('code'=>1,'message'=>"关键词不存在;");
        }
        $res['srh_key']=$srh_key_result_two['srh_key'];
        $content_arr=array();
        $content_arr[0]=$res;
        // 业务逻辑：以下为各项具体检查
        // echo "<pre>";var_dump($content_arr);die;
        foreach($content_arr as $key=>$record) 
        {
            // 检查开始时间
            if (isset($record['start_tm']) && $record['start_tm'] != "") 
            {
                $content_arr[$key]['bk_start_time'] = $record['start_tm'];
 
            } else {
                return array('code'=>1,'message'=>"开始时间不能为空;");
                $this->append_error_msg($error_msg, $key, 1, "开始时间不能为空;");
            }
            // 检查结束时间
            if (isset($record['end_tm']) && $record['end_tm'] != "") 
            { 
                $content_arr[$key]['bk_end_time'] = $record['end_tm'];
                
            } else {
                 return array('code'=>1,'message'=>"结束时间不能为空;");
                $this->append_error_msg($error_msg, $key, 1, "结束时间不能为空;");
            }
            // 检查开始时间是否小于结束时间
            if (isset($content_arr[$key]['bk_start_time']) && isset($content_arr[$key]['bk_end_time'])) 
            {
                if ($content_arr[$key]['bk_start_time'] > $content_arr[$key]['bk_end_time']) {
                    return array('code'=>1,'message'=>"开始时间需小于结束时间;");
                    $this->append_error_msg($error_msg, $key, 1, "开始时间需小于结束时间;");
                }
            }
            if(isset($record['guide_page']))
            {
                if($record['guide_page']==2)
                {
                    //检查包名
                    if (isset($record['package']) && $record['package'] != "") 
                    { 
                        // 检查包名是否在sj_soft表里
                        $where = array(
                            'package' => $record['package'],
                            'status' => 1,
                            'hide' => array('EQ', 1),
                        );
                        $find = $soft_model->where($where)->find();
                        if (!$find) 
                        {
                            return array('code'=>1,'message'=> "包名【{$record['package']}】不存在于市场软件库中;");
                            $this->append_error_msg($error_msg, $key, 1, "包名【{$record['package']}】不存在于市场软件库中;");
                        }
                    } else {
                         return array('code'=>1,'message'=>"包名不能为空;");
                        $this->append_error_msg($error_msg, $key, 1, "包名不能为空;");
                    }
                }
            }else{
                return array('code'=>1,'message'=>"导向页面不能为空;");
                $this->append_error_msg($error_msg, $key, 1, "导向页面不能为空;");
            }
        
            // 检查排序是否为数字
            if (isset($record['rank']) && $record['rank'] != "") 
            {
                if (!preg_match("/^\d+$/", $record['rank'])) 
                {
                    return array('code'=>1,'message'=>"排序应为整数;");
                    $this->append_error_msg($error_msg, $key, 1, "排序应为整数;");
                }
                if($record['rank']<1||$record['rank']>20)
                {
                    return array('code'=>1,'message'=>"排序应为1~20的整数;");
                    $this->append_error_msg($error_msg, $key, 1, "排序应为1~20的整数;");
                }
            }
            else 
            {
                return array('code'=>1,'message'=>"排序值不能为空;");
                $this->append_error_msg($error_msg, $key, 1, "排序值不能为空;");
            }
            //关键字   单条记录的关键字为默认，批量上传检查是否有关键字，没有添加关键字
            if (!(isset($record['srh_key']) && $record['srh_key'] != "") )
            {
                return array('code'=>1,'message'=>"关键字不能为空;");
                $this->append_error_msg($error_msg, $key, 1, "关键字不能为空;");
            }
            //相关词   单条记录的关键字为默认，批量上传检查是否有关键字，没有添加关键字
            if (isset($record['related_keys']) && $record['related_keys'] != "") 
            {
                //相关词8个汉字16个字符内
                if($this->strlen_az($record['related_keys'])>16)
                {
                    return array('code'=>1,'message'=>"相关词请输入8个汉字，16个字符内;");
                    $this->append_error_msg($error_msg, $key, 1, "相关词请输入8个汉字，16个字符内;");
                }
            }
            else
            {

                return array('code'=>1,'message'=>"相关词不能为空;");
                $this->append_error_msg($error_msg, $key, 1, "相关词不能为空;");
            }
        }

        // 检查每一行数据是否与数据库的存储内容相冲突
        foreach($content_arr as $key=>$record) 
        {
            //根据srh_key获取srh_key的id
            $srh_key_result=$keyword_model->where(array('srh_key' => $record['srh_key'],'status'=>1 ))->find();
            if(!$srh_key_result['id'])
                continue; //关键词没有ID说明是新增加的词，数据库中肯定没有不用比较
            $where = array(
                'kid' => array('EQ', $srh_key_result['id']),
                'status' => array('NEQ', 0),
            );
            if (isset($record['id']))
                $where['id'] = array('NEQ', $record['id']);
            $db_records = $keyword_soft_model->where($where)->select();
            if (!$db_records)
                continue;
            // 检查时间、位置（排序）冲突
            // 如果填写时间的不完善，则不比较
            if (!isset($record['bk_start_time']) || !isset($record['bk_end_time']))
                continue;
            foreach($db_records as $db_record){
                if($db_record['end_tm']>time())
                $db_rank[$record['srh_key']][] = $db_record['rank'];
            }
            if(count($last_rank[$record['srh_key']])==0){
                $last_rank[$record['srh_key']] = array_unique(array_merge($db_rank[$record['srh_key']],$c_rank[$record['srh_key']]));
            }
            if(isset($rank[$record['srh_key']])) $last_rank[$record['srh_key']][] = $rank[$record['srh_key']];
            foreach($db_records as $db_k => $db_record) 
            {
                if ($record['related_keys'] == $db_record['related_keys']) 
                {
                    // 将开始时间和结束时间转成时间戳
                    $start1 = $record['bk_start_time']; 
                    $end1 = $record['bk_end_time'];
                    $start2 = $db_record['start_tm']; 
                    $end2 = $db_record['end_tm'];
                    if ($start1 <= $end2 && $start2 <= $end1) 
                    {
                        $start_at_str = date('Y-m-d H:i:s',$db_record['start_tm']);
                        $end_at_str = date('Y-m-d H:i:s',$db_record['end_tm']);
                        $status_paused_hint = "";
                        if ($db_record['status'] == 2) 
                        {
                            $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                        }
                        return array('code'=>1,'message'=>"同一相关词下，关键字为【{$record['srh_key']}】,投放时间与后台id为【{$db_record['id']}】，相关词为【{$db_record['related_keys']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
                        $this->append_error_msg($error_msg, $key, 1, "同一相关词下，关键字为【{$record['srh_key']}】,投放时间与后台id为【{$db_record['id']}】，相关词为【{$db_record['related_keys']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
                    }
                }
                if ($record['rank'] == $db_record['rank']) 
                {
                    // 将开始时间和结束时间转成时间戳
                    $start1 = $record['bk_start_time']; 
                    $end1 = $record['bk_end_time'];
                    $start2 = $db_record['start_tm']; 
                    $end2 = $db_record['end_tm'];
                    if ($start1 <= $end2 && $start2 <= $end1) 
                    {
                        $start_at_str = date('Y-m-d H:i:s',$db_record['start_tm']);
                        $end_at_str = date('Y-m-d H:i:s',$db_record['end_tm']);
                        $status_paused_hint = "";
                        if ($db_record['status'] == 2)
                        {
                            $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                        }
                        $rank[$record['srh_key']]= get_rank($last_rank[$record['srh_key']]);
                        return array('code'=>1,'message'=>"同一位置下，投放时间与后台id为【{$db_record['id']}】，相关词为【{$db_record['related_keys']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;rank={$rank[$record['srh_key']]}");
                        $this->append_error_msg($error_msg, $key, 1, "同一位置下，投放时间与后台id为【{$db_record['id']}】，相关词为【{$db_record['related_keys']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;rank={$rank[$record['srh_key']]}");
                    }
                }
            }
        }
        return 2;
        return $error_msg;
    }
	function get_error_file($error_msg,$content_arr,$mark){
		$dir = "/tmp/search_file/";
		$path_err = $dir.$mark.'_err.csv';
		$path_succ = $dir.$mark.'_succ.csv';
		if(!file_exists($dir)){
			mkdir($dir, 0755, true);
		}		
		if($mark == 'tips'){
			$head = "关键字,软件包名,自定义名称,排序,开始时间(yyyy/MM/dd),结束时间(yyyy/MM/dd),合作形式\n";
			$start_field = 'start_tm';
			$end_field = 'end_tm';
			$type_field = 'co_type';
		}else if($mark == 'related'){
			$head = "关键字,相关词,'导向页面(1:搜索结果页2:软件详情页)',包名,排序,开始时间(yyyy/MM/dd),结束时间(yyyy/MM/dd),合作形式\n";
			$start_field = 'start_tm';
			$end_field = 'end_tm';	
			$type_field = 'co_type';			
		}else{
			$start_field = 'start_time';
			$end_field = 'end_time';	
			$type_field = 'type';			
			$head = "广告位包名,搜索词(多个搜索词以，分隔),开始时间(yyyy/MM/dd),结束时间(yyyy/MM/dd),合作形式,软件排序（1或2）,行为id\n";
		}
		$str = $head;
		foreach($error_msg as $kk => $vv){
			//if($vv['flag'] && !$vv['rank']){
			if($vv['flag']){
				$content_arr[$kk][$start_field] = "T".$content_arr[$kk][$start_field];
				$content_arr[$kk][$end_field] = "T".$content_arr[$kk][$end_field];
				$str .= implode(',',$content_arr[$kk])."\n";
				unset($content_arr[$kk]);
			}
			//if($vv['rank']) $content_arr[$kk]['rank'] = $vv['rank'];
		}
		$str = mb_convert_encoding($str,'gbk','utf-8');  
		file_put_contents($path_err,$str);
		if(!$content_arr) return false;
		$util = D("Sj.Util");
		$typelist = $util->get_cooperation();
		$type_arr = array_flip($typelist);
		$str = $head;
		foreach($content_arr as $kkk => $vvv){
			$vvv[$start_field] = "T".$vvv[$start_field];
			$vvv[$end_field] = "T".$vvv[$end_field];
			$vvv[$type_field] = $type_arr[$vvv[$type_field]];
			$str .= implode(',',$vvv)."\n";
		}	
		$str = mb_convert_encoding($str,'gbk','utf-8');  
		file_put_contents($path_succ,$str);			
	}
}
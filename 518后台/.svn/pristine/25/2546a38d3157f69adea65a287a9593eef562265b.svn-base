<?php
class MarketPushModel extends Model {
   
	
	//公用的保存 机型、运营商、版本、ABI、、、
	function common_save($data,$id,&$info_list)
	{
		//专题ID 
		if($info_list['feature_id']){
            $where['feature_id'] =  $info_list['feature_id'];
            $where['pid'] =  array("like","%,1,%");
            $where['status'] =  1;
        
            $feature_have_result = $this -> table('sj_feature') -> where($where) -> select();
            if(!$feature_have_result){
              return "专题id不存在";
            }
        }
		
		//活动ID
        if($info_list['activity_id']){
            $activity_have_result = $this -> table('sj_activity') -> where(array('id' => $info_list['activity_id'],'status' => 1)) -> select();
            if(!$activity_have_result){
               return "活动id不存在";
            }
        }

		//行为ID
        if($info_list['beid']){
            $beid_where["_string"] = "beid = {$info_list['beid']} and start < {$info_list['end_tm']} and end > {$info_list['start_tm']} and status = 1";
            $beid_result = $this -> table('sj_push_be_detail') -> where($beid_where) -> select();
            $beid_behavior_where['_string'] = "beid = {$info_list['beid']} and status = 1";
            $beid_behavior_result = $this -> table('sj_push_behavior') -> where($beid_behavior_where) -> select();

            if(!$beid_result || !$beid_behavior_result){
                return "填写的行为id不存在，请检查";
            }
        }
		
		$info_list['status']=1;
		if(!$id)
		{
			$info_list['create_tm']=time();
		}
        
        $info_list['update_tm']=time();
        $info_list['oid'] = ','. implode(',', $data['oid']). ',';
        $info_list['firmware'] = ','. implode(',', $data['firmware']). ',';
        $info_list['version_code'] = ','. implode(',', $data['version_code']). ',';

        $abi = 0;
        foreach($data['abi'] as $v) {
            $abi += $v;
        }
        $info_list['abi'] = $abi;
        $channel_id_array=$data['cid'];
        $cids = array_unique($channel_id_array);
		if (count($cids) > 0) {
			$s = implode(',', $cids);
			$s = ",{$s},";
			$info_list['channel_id'] = $s;
		}
		else  //编辑的时候增加的
		{
			$info_list['channel_id'] = NULL;
		}

        $device_did_array=$data['did'];
        $dids = array_unique($device_did_array);
            if (count($dids) > 0) {
                    $d= implode(',', $dids);
                    $d = ",{$d},";
                    $info_list['device_did'] = $d;
                }
		
        $info_list['push_area'] = trim($data['area_value']);
		return true;
	}
	//添加的时候 排除用户和覆盖用户 csv文件处理
	function save_file_csv($data,$files,&$info_list)
	{
		if($files['upload_file']['size'])
		{
			$filename=$files['upload_file']['name'];
			if(!$filename&&!$data['csv_count'])
			{
				$info_list['pre_dl_count'] = 0;
				$info_list['pre_csv_url'] = "";
				$info_list['is_upload_csv'] = 0; //标注是否上传csv
			}
			if($filename&&!$data['csv_count'])
			{
				return "选择好的文件请点击上传才有效";
			}
			if($data['csv_count']&&$data['csv_url'])
			{
				$info_list['pre_dl_count'] = trim($data['csv_count']);
				$info_list['pre_csv_url'] = trim($data['csv_url']);
				$info_list['is_upload_csv'] = 1;
			}
			unset($files['upload_file']);
		}
		if($files['paichu_upload_file']['size'])
		{
			$paichu_filename=$files['paichu_upload_file']['name'];
			if(!$paichu_filename&&!$data['paichu_csv_count'])
			{
				$info_list['pre_dl_count'] = 0;
				$info_list['pre_csv_url'] = "";
				$info_list['is_upload_csv'] = 0; //标注是否上传csv
				
				/*$info_list['paichu_count'] = 0;
				$info_list['paichu_csv_url'] = "";
				$info_list['is_upload_paichu_csv'] = 0; *///标注是否上传排除csv
			}
			if($paichu_filename&&!$data['paichu_csv_count'])
			{
				return "选择好的文件请点击上传才有效";
			}
			if($data['paichu_csv_count']&&$data['paichu_csv_url'])
			{
				$info_list['pre_dl_count'] = trim($data['paichu_csv_count']);
				$info_list['pre_csv_url'] = trim($data['paichu_csv_url']);
				$info_list['is_upload_csv'] = 1; 
			}
			unset($files['paichu_upload_file']);
		}
		return true;
	}
	//push 设置单条内容保存
	function save_push_set($data,&$info_list)
	{
		if($info_list['push_type']!=2){
			$info_list['need_limit'] = $data['need_limit'];
			if($data['need_limit']==0)
			{
				$info_list['pre_dl_count'] = "";
				$info_list['pre_csv_url'] = "";
			}
			//市场不启动是推送
			if($data['offline_push'])
			{
				$info_list['offline_push']=$data['offline_push'];
			}
			else
			{
				$info_list['offline_push']=1; //编辑的时候增加
			}
			//推送方式
			if($data['push_way'])
			{
				$info_list['push_way'] = $data['push_way'];
				if($info_list['push_type'] ==3)
				{
					$info_list['push_way'] = 2;
				}
			}
			//推送开始时间结束时间
			if($data['fromdate'] && $data['todate']){
				$info_list['start_tm']=strtotime($data['fromdate']);
				$info_list['end_tm']=strtotime($data['todate']);
				if($info_list['start_tm'] > $info_list['end_tm']){
					return "开始时间不能大于结束时间";
				}
			}else{
				return "请选择开始时间和结束时间";
			}

			//每日推送时间段
			if (empty($data['daily_fromtime']) ^ empty($data['daily_totime'])) {
				return "请填写完整的推送时间段";
			}
			if (!empty($data['daily_fromtime']) && !empty($data['daily_totime'])) {
				if (!preg_match('/^[0-9]{2}:[0-9]{2}:[0-9]{2}$/', $data['daily_fromtime'])
					|| !preg_match('/^[0-9]{2}:[0-9]{2}:[0-9]{2}$/', $data['daily_totime'])
					|| !strtotime($data['daily_fromtime']) || !strtotime($data['daily_totime'])
				) {
					return "推送时间段时间格式错误";
				}
				if ($data['daily_totime'] <= $data['daily_fromtime']) {
					return "推送时间段结束时间应大于开始时间";
				}
			}
			if($data['daily_fromtime'])
			{
				$info_list['daily_start_tm'] = $data['daily_fromtime'];
			}
			if($data['daily_totime'])
			{
				$info_list['daily_end_tm'] = $data['daily_totime'];
			}
		}

		//PUSH激活时间选择
		$activation_time=$data['activation_time'];
		$info_list['activation_time'] = $activation_time;
		//编辑的时候添加，激活时间不选择 都为空
		if($activation_time==0)//不选
		{
			$info_list['activation_day_start'] = '';
			$info_list['activation_day_end'] = '';
			$info_list['activation_date_start'] = '';
			$info_list['activation_date_end'] = '';
		}
		if($activation_time==1)//按激活天数
		{
			//从0开始 例如三天内的 就是0到3
			$days_start = trim($data['activation_day_start']);
			$days_end = trim($data['activation_day_end']);
			if($days_start==''||$days_end=='')
			{
				return "激活时间的开始天数和结束天数必填";
			}
			else
			{
				$matches="/^[0-9]\d*$/";
				if(!preg_match($matches,$days_start))
				{
					return "激活时间开始天数必须为正整数";
				}
				if(!preg_match($matches,$days_end))
				{
					return "激活时间的结束天数必须为正整数";
				}
				if($days_start>$days_end)
				{
					return "激活时间的开始天数不能大于等于结束天数";
				}
			}
			$info_list['activation_day_start'] = $days_start;
			$info_list['activation_day_end'] = $days_end;
			//编辑的时候增加  编辑日期为空
			$info_list['activation_date_start'] = '';
			$info_list['activation_date_end'] = '';
		}
		if($activation_time==2)//按激活日期
		{
			$date_start = strtotime($data['activation_date_start']);
			$date_end = strtotime($data['activation_date_end']);
			if(empty($date_start)||empty($date_end))
			{
				return "请选择激活时间的开始日期和结束日期";
			}
			else
			{
				if($date_start>$date_end)
				{
					return "激活时间的开始时间不能大于结束时间";
				}
			}
			$info_list['activation_date_start'] = $date_start;
			$info_list['activation_date_end'] = $date_end;
			//编辑的时候增加 天数为空
			$info_list['activation_day_start'] = '';
			$info_list['activation_day_end'] = '';
		}
		if(isset($data['is_getui'])) $info_list['is_getui'] = $data['is_getui'];
		return true;
	}
	//push 内容单条保存处理
	function save_push_content($data,$i,$id,&$info_list)
	{
		//合作形式
		if(isset($data['co_type_push'][$i])){
			$info_list['co_type'] = trim($data['co_type_push'][$i]);
		}else{
			$info_list['co_type'] = 0;
		}
		//push类型  添加的时候才有  编辑直接从库中取
		if(!$id) 
		{
			$info_list['info_type']=trim($data['info_type'][$i]);
		}
		
		//信息标题
		if($data['info_title'][$i])
		{
		    $data['info_title'][$i]= preg_replace('#<span style="color:([^;]+);">#', '<font color="\1">', $data['info_title'][$i]);
		    $data['info_title'][$i]= preg_replace('#</span>#', '</font>', $data['info_title'][$i]);
		    $data['info_title'][$i]= preg_replace('#<span style="">#', '<font>', $data['info_title'][$i]);
		    $data['info_title'][$i]= preg_replace('#<span>#', '<font>', $data['info_title'][$i]);
		    // $data['info_title'][$i]= preg_replace('#<span style="color:([^;]+);">([^<]+)</span>#', '<font color="\1">\2</font>', $data['info_title'][$i]);
			$info_list['info_title']=trim($data['info_title'][$i]);
		}
		// var_dump($info_list['info_title']);die;
		if(!$info_list['info_title'])
		{

			return "标题信息为必填项";
		}
		//信息内容
		if($data['info_content'][$i]){
			$data['info_content'][$i] = str_replace("\r\n", "\n", $data['info_content'][$i]);
			$data['info_content'][$i]= preg_replace('#<span style="color:([^;]+);">#', '<font color="\1">', $data['info_content'][$i]);
		    $data['info_content'][$i]= preg_replace('#</span>#', '</font>', $data['info_content'][$i]);
		    $data['info_content'][$i]= preg_replace('#<span style="">#', '<font>', $data['info_content'][$i]);
		    $data['info_content'][$i]= preg_replace('#<span>#', '<font>', $data['info_content'][$i]);
			// $data['info_content'][$i]=preg_replace('#<span style="color:([^;]+);">([^<]+)</span>#', '<font color="\1">\2</font>', $data['info_content'][$i]);
			$info_list['info_content']=trim($data['info_content'][$i]);
		}
		
		if(!$info_list['info_content'])
		{
			return "信息内容为必填项";
		}
		
		//覆盖人数
		if($data['cover_num'] == '全部'){
			$info_list['cover_num'] = 0;
		}else{
			$info_list['cover_num'] = trim($data['cover_num']);
		}
		//勾起包名
		if($data['hook_package'][$i]){
			$info_list['hook_package'] = trim($data['hook_package'][$i]);
		}
		//增加的时候没有上面判断
		//$info_list['cover_num'] = $data['cover_num'];
		if($info_list['cover_num']){
			if(!preg_match('/^\d+$/',$info_list['cover_num']) && $info_list['cover_num'] != "全部"){
				return "覆盖人数格式错误";
			}
		}
		
		//推送类别
		if(isset($data['push_info_type'][$i])){
			$info_list['push_info_type'] = $data['push_info_type'][$i];
		}else{
			$info_list['push_info_type'] = 0;
		}

		//推送分类
		if(isset($data['push_info_category'][$i])){
			$info_list['push_info_category'] = $data['push_info_category'][$i];
		}else{
			$info_list['push_info_category'] = 0;
		}
		
		//公告按钮名称
		$info_list['notice_name'] = trim($data['notice_name'][$i]);
		
		if($info_list['info_type'] == 1 || $info_list['info_type'] == 5){
			if(!$info_list['notice_name'] || strlen($info_list['notice_name']) > 24){
				return "请填写8个汉字以内的公告按钮名称";
			}
		}
		//公告按钮跳转
		$info_list['notice_type'] = $data['notice_type'][$i];
		
		
	   
		//未安装下载
		$info_list['uninstalled_download'] = $data['uninstalled_download'.$i];
		if($data['uninstalled_silent'.$i])
		{
			$info_list['uninstalled_silent'] = $data['uninstalled_silent'.$i];
		}
		else
		{
			$info_list['uninstalled_silent'] = 0;
		}
		//已安装选择
		$info_list['installed_select'] = $data['installed_select'.$i];
		//已安装启动软件至某个页面
		$info_list['detail_page'] = $data['detail_page'];
		//低版本选择
		$info_list['lower_version_select'] = $data['lower_version_select'.$i];
		if($data['lower_version_silent'.$i])
		{
			$info_list['lower_version_silent'] = $data['lower_version_silent'.$i];
		}
		else
		{
			$info_list['lower_version_silent'] = 0;
		}
		//页面标题
		$info_list['page_name'] = $data['page_name'][$i];
		//页面地址必须是 http://开始的
		if($data['page_link'][$i])
		{
			if (!$this->check_url($data['page_link'][$i])) 
			{
				return "请填写有效的链接地址";
			}
		}			
		$info_list['page_link'] = trim($data['page_link'][$i]);
		$info_list['open_type'] = trim($data['open_type'.$i]);
		//软件包名
		$info_list['soft_package']=trim($data['soft_package'][$i]);
		//专题ID
		$info_list['feature_id']=(int)(trim($data['feature_id'][$i]));
		//活动ID
		$info_list['activity_id']=(int)(trim($data['activity_id'][$i]));
		//行为ID
		$info_list['beid']=(int) (trim($data['beid'][$i]));
		if($data['beid'][$i]){
			if(!preg_match('/^\d+$/',$data['beid'][$i])){
				return "行为id格式错误";
			}
		}
		//页面类型内容 赋值给info_list数组方便以后判断类型的时候用
		/*$info_list['market_page_type']=$data['pic_market_page_type'][$i];
		$info_list['page_name4']=$data['pic_page_name4'][$i];
		$info_list['page_name1']=$data['pic_page_name1'][$i];*/
		if($info_list['info_type']==10)//推荐内容 只有页面和应用内览的时候才有
		{
			$arr_key = $i;
			$map=array();
			$rcontent=ContentTypeModel::saveRecommendContent_new($data, '', $map, $arr_key);
			if($rcontent!==true)
			{
				return $rcontent;
			}else{
				$info_list['market_page_name'] = $map['page_type'];
				$info_list['content_type'] = $map['content_type'];
				if($map['parameter_field'])
				{
					$info_list['parameter_field'] = trim($map['parameter_field']);
				}
			}
		}elseif($info_list['info_type']==8){
			if($info_list['open_type']==1){
				$is_sync_accout = $data['is_sync_accout1'.$i];
				$info_list['parameter_field'] = json_encode(array('website_is_sync_accout'=>$is_sync_accout,'website_is_actionbar'=>$data['is_actionbar1'.$i],'website_screen_show'=>$data['screen_show1'.$i],'website_is_h5'=>$data['is_h5_1'.$i]));
			}
		}else{
			$info_list['parameter_field'] = '';
		}
		//多条推送概率
		if($info_list['push_odds'] ==100)
		{	
		}
		else
		{
			$info_list['push_odds']=$data['odds'][$i];
		}
		
		//商务活跃增加合作渠道
		if($info_list['push_info_type']==6)
		{
			$business_cid_array=$data['co_cid'][$i];
			$business_cids = array_unique($business_cid_array);
			if (count($business_cids) > 0) 
			{
				$c = implode(',', $business_cids);
				$c = ",{$c},";
				$info_list['business_co_channel_id'] = $c;
			}
			else
			{
				$info_list['business_co_channel_id'] = '';
			}
		}
		else  //编辑的时候增加的
		{
			$info_list['business_co_channel_id'] = '';
		}
		return true;
	}
	//单条弹窗内容
	function save_pop_content($data,$id,&$info_list)
	{
		$info_list['need_limit'] = $data['need_limit'];
		if($data['need_limit']==0)
		{
			$info_list['pre_dl_count'] = "";
			$info_list['pre_csv_url'] = "";
		} 
		//合作形式
		if(isset($data['co_type_pop'])){
			$info_list['co_type'] = $data['co_type_pop'];
		}else{
			$info_list['co_type'] = 0;
		}
		$info_list['cpm_name'] = trim($data['cpm_name']);
		if($id)//编辑判断
		{
			$cpm_where['_string'] = "id != {$id} and cpm_name = '{$info_list['cpm_name']}' and status = 1 and push_type = 2";
			$cpm_result = $this -> table('sj_market_push') -> where($cpm_where) -> select();
			if($info_list['cpm_name']){
				if($cpm_result){
					$this -> error("该弹窗广告名称已存在");
				}
			}else{
				$this -> error("请填写弹窗广告名称");
			}
		}
		else
		{
			if(!$info_list['cpm_name'])
			{
				return "请填写弹窗广告名称";
			}
			else
			{
				$cpm_result = $this -> table('sj_market_push')->where(array('cpm_name' => $info_list['cpm_name'],'status' => 1,'push_type' => 2)) -> select();
				if($cpm_result){
					return "此弹窗广告名称已存在";
				}
			}
		}
		
		//覆盖人数
		if($data['cover_num'] == '全部'){
			$info_list['cover_num'] = 0;
		}else{
			$info_list['cover_num'] = trim($data['cover_num']);
		}
		//增加的时候没有上面判断
		//$info_list['cover_num'] = $data['cover_num'];
		if($info_list['cover_num']){
			if(!preg_match('/^\d+$/',$info_list['cover_num']) && $info_list['cover_num'] != "全部"){
				return "覆盖人数格式错误";
			}
		}
		if($id)//编辑
		{
			$info_list['notice_type'] = $this ->table('sj_market_push')->where(array('id' => $id))->getfield("notice_type");
		}
		else
		{
			$info_list['notice_type'] = trim($data['notice_types']);
		}
		
		$info_list['uninstalled_download'] = trim($data['uninstalled_downloads']);
		if($data['uninstalled_silents'])
		{
			$info_list['uninstalled_silent'] = trim($data['uninstalled_silents']);
		}
		else
		{
			$info_list['uninstalled_silent'] = 0;
		}
		$info_list['installed_select'] = trim($data['installed_selects']);
		$info_list['detail_page'] = trim($data['detail_pages']);
		$info_list['lower_version_select'] = trim($data['lower_version_selects']);
		if($data['lower_version_silents'])
		{
			$info_list['lower_version_silent'] = trim($data['lower_version_silents']);
		}
		else
		{
			$info_list['lower_version_silent'] = 0;
		}
		$info_list['page_name'] = trim($data['page_names']);
		$info_list['page_link'] = trim($data['page_links']);
		$info_list['open_type'] = trim($data['open_types']);
		$info_list['soft_package']=trim($data['soft_packages']);
		$info_list['feature_id']=(int)(trim($data['feature_ids']));
		$info_list['activity_id']=(int)(trim($data['activity_ids']));
		$info_list['beid']=(int) (trim($data['beids']));
		/*if($info_list['pre_csv_url'])
		{
			$info_list['need_limit']=1;
		}
		//编辑的时候增加的
		if(!$info_list['pre_csv_url']&&!trim($data['have_pre_dl']))
		{
			$info_list['need_limit']=0;
		}*/
		
		if($data['beids']){
			if(!preg_match('/^\d+$/',$data['beids'])){
				return "行为id格式错误";
			}
		}
		//时间
		if($data['start_tm'] && $data['end_tm'])
		{
			$info_list['start_tm']=strtotime($data['start_tm']);
			$info_list['end_tm']=strtotime($data['end_tm']);
			if($info_list['start_tm'] > $info_list['end_tm']){
				return "开始时间不能大于结束时间";
			}
		}else{
			return "请选择开始时间和结束时间";
		}
			
		//弹窗判断同一时间段是否重复
		if($id)
		{
			$have_where['_string'] = "start_tm <= {$info_list['end_tm']} and end_tm >= {$info_list['start_tm']} and status = 1 and push_type = 2 and id != {$id}";
		}
		else
		{
			$have_where['_string'] = "start_tm <= {$info_list['end_tm']} and end_tm >= {$info_list['start_tm']} and status = 1 and push_type = 2";
		}
		$have_result = $this -> table('sj_market_push') -> where(
		$have_where) -> select();

		if($have_result){
			// return "对不起，该时间段已有弹窗广告";
		}
		//页面类型 赋值给info_list数组方便以后判断类型的时候用
		//cpm_page_name1
		//cpm_page_name4
		//cpm_market_page_type
		/*$info_list['market_page_type']=$data['cpm_market_page_type'];
		$info_list['page_name4']=$data['cpm_page_name4'];
		$info_list['page_name1']=$data['cpm_page_name1'];*/
		if($info_list['notice_type']==5) //页面的情况
		{
			$cpm="cpm";
			$map=array();
			$rcontent=ContentTypeModel::saveRecommendContent_new($data,'',$map,$cpm);
			if($rcontent!==true)
			{
				return $rcontent;
			}else {
				$info_list['market_page_name'] = $map['page_type'];
				$info_list['content_type'] = $map['content_type'];
				if($map['parameter_field'])
				{
					$info_list['parameter_field'] = $map['parameter_field'];
				}
			}
		}elseif($info_list['notice_type']==4){
			if($info_list['open_type']==1){
				$is_sync_accout = $data['is_sync_accout2'];
				$info_list['parameter_field'] = json_encode(array('website_is_sync_accout'=>$is_sync_accout,'website_is_actionbar'=>$data['is_actionbar2'],'website_screen_show'=>$data['screen_show2'],'website_is_h5'=>$data['is_h5_2']));
			}else {
				$info_list['parameter_field'] = '';
			}
		}else{
			$info_list['parameter_field'] = '';
		}

		return true;
	}

	//预约闹钟内容
	function save_clock_content($data,$id,&$info_list){
		$info_list['push_way'] = 2;
		$info_list['info_type'] = 13;
		//覆盖用户
		$info_list['need_limit'] = $data['need_limit'];
		if($data['need_limit']==0){
			$info_list['pre_dl_count'] = "";
			$info_list['pre_csv_url'] = "";
		} 
		//合作形式
		if(isset($data['co_type_clock'])){
			$info_list['co_type'] = $data['co_type_clock'];
		}else{
			$info_list['co_type'] = 0;
		}
		//闹钟名称
		$info_list['cpm_name'] = trim($data['clock_name']); 
		if($id){
			$cpm_where['_string'] = "id != {$id} and cpm_name = '{$info_list['cpm_name']}' and status = 1 and push_type = 6";
			$cpm_result = $this -> table('sj_market_push') -> where($cpm_where) -> select();
			if($info_list['cpm_name']){
				if($cpm_result){
					return "该预约闹钟名称已存在";
				}
			}else{
				return "请填写预约闹钟名称";
			}
		}else{
			if(!$info_list['cpm_name']){
				return "请填写预约闹钟名称";
			}else{
				$cpm_result = $this -> table('sj_market_push')->where(array('cpm_name' => $info_list['cpm_name'],'status' => 1,'push_type' => 6)) -> select();
				if($cpm_result){
					return "此预约闹钟名称已存在";
				}
			}
		}
		
		if($id){
			$info_list['notice_type'] = $this ->table('sj_market_push')->where(array('id' => $id))->getfield("notice_type");
		}else{
			$info_list['notice_type'] = trim($data['notice_types']);
		}
		
		$info_list['uninstalled_download'] = trim($data['uninstalled_downloads']);
		if($data['uninstalled_silents']){
			$info_list['uninstalled_silent'] = trim($data['uninstalled_silents']);
		}else{
			$info_list['uninstalled_silent'] = 0;
		}
		$info_list['installed_select'] = trim($data['installed_selects']);
		$info_list['detail_page'] = trim($data['detail_pages']);
		$info_list['lower_version_select'] = trim($data['lower_version_selects']);
		if($data['lower_version_silents']){
			$info_list['lower_version_silent'] = trim($data['lower_version_silents']);
		}else{
			$info_list['lower_version_silent'] = 0;
		}
		$info_list['page_name'] = trim($data['page_names']);
		$info_list['page_link'] = trim($data['page_links']);
		$info_list['open_type'] = trim($data['open_types']);
		$info_list['soft_package']=trim($data['soft_packages']);
		$info_list['feature_id']=(int)(trim($data['feature_ids']));
		$info_list['activity_id']=(int)(trim($data['activity_ids']));
		$info_list['beid']=(int) (trim($data['beids']));
		/*if($info_list['pre_csv_url']){
			$info_list['need_limit']=1;
		}
		//编辑的时候增加的
		if(!$info_list['pre_csv_url']&&!trim($data['have_pre_dl'])){
			$info_list['need_limit']=0;
		}*/
		
		if($data['beids']){
			if(!preg_match('/^\d+$/',$data['beids'])){
				return "行为id格式错误";
			}
		}

		//闹钟开始时间
		if($data['clock_start_tm']){
			$info_list['daily_start_tm'] = strtotime($data['clock_start_tm']);
		}else{
			return "请选择闹钟开始时间";
		}

		//推送开始时间结束时间
		if($data['fromdate'] && $data['todate']) {
			$info_list['start_tm'] = strtotime($data['fromdate']);
			$info_list['end_tm'] = strtotime($data['todate']);
			if($info_list['start_tm'] > $info_list['end_tm']){
				return "推送开始时间不能大于结束时间";
			}
			if($info_list['daily_start_tm'] > $info_list['end_tm']){
				return "闹钟开始时间不能大于结束时间";
			}
		}else{
			return "请选择推送开始时间和结束时间";
		}
			
		//判断同一时间段是否重复
		if($id){
			$have_where['_string'] = "start_tm <= {$info_list['end_tm']} and end_tm >= {$info_list['start_tm']} and status = 1 and push_type = 6 and id != {$id}";
		}else{
			$have_where['_string'] = "start_tm <= {$info_list['end_tm']} and end_tm >= {$info_list['start_tm']} and status = 1 and push_type = 6";
		}
		$have_result = $this -> table('sj_market_push') -> where($have_where) -> select();
		if($have_result){
			//return "该时间段已有预约闹钟";
		}
		//页面类型 赋值给info_list数组方便以后判断类型的时候用
		//cpm_page_name1
		//cpm_page_name4
		//cpm_market_page_type
		/*$info_list['market_page_type']=$data['cpm_market_page_type'];
		$info_list['page_name4']=$data['cpm_page_name4'];
		$info_list['page_name1']=$data['cpm_page_name1'];*/
		if($info_list['notice_type']==5){ //页面的情况
			$cpm = "cpm";
			$map = array();
			$rcontent = ContentTypeModel::saveRecommendContent_new($data, '', $map,$cpm);
			if($rcontent!==true){
				return $rcontent;
			}else {
				$info_list['market_page_name'] = $map['page_type'];
				$info_list['content_type'] = $map['content_type'];
				$info_list['activity_id'] = $map['activity_id'];
				if($map['parameter_field']){
					$info_list['parameter_field'] = $map['parameter_field'];
				}
			}
		}elseif($info_list['notice_type']==4){
			if($info_list['open_type']==1){
				$is_sync_accout = $data['is_sync_accout2'];
				$info_list['parameter_field'] = json_encode(array('website_is_sync_accout'=>$is_sync_accout,'website_is_actionbar'=>$data['is_actionbar2'],'website_screen_show'=>$data['screen_show2'],'website_is_h5'=>$data['is_h5_2']));
			}else {
				$info_list['parameter_field'] = '';
			}
		}else{
			$info_list['parameter_field'] = '';
		}

		return true;
	}
	
	//预下载内容处理
	function save_pre_content($data,&$info_list)
	{
		$info_list['need_limit'] = $data['need_limit'];
		if($data['need_limit']==0)
		{
			$info_list['pre_dl_count'] = "";
			$info_list['pre_csv_url'] = "";
		} 
		//合作形式
		if(isset($data['co_type_pre'])){
			$info_list['co_type'] = $data['co_type_pre'];
		}else{
			$info_list['co_type'] = 0;
		}
		if($data['pre_beid'])
		{
			if(!preg_match('/^\d+$/',$data['pre_beid'])){
				return "行为id格式错误";
			}
		}
		$info_list['beid']=(int) (trim($data['pre_beid']));
		
		if($data['pre_uninstalled_silent'])
		{
			$info_list['uninstalled_silent'] = trim($data['pre_uninstalled_silent']);
		}
		else
		{
			$info_list['uninstalled_silent'] =0;
		}
		if($data['pre_lower_version_silent'])
		{
			$info_list['lower_version_silent'] = trim($data['pre_lower_version_silent']);
		}
		else
		{
			$info_list['lower_version_silent'] =0;
		}
		//V6.4新增下载完成后  0:无操作,1:多个包名逗号隔开,2:推荐内容  活动和页面,3:指定时间
		$matches = "/^[1-9][0-9]*$/";
		if($data['download_finished_select'])
		{
			$info_list['after_finished_do'] = $data['download_finished_select'];
		}
		else
		{
			$info_list['after_finished_do'] =0;
			$info_list['after_finished_param'] = '';
			$info_list['market_page_name'] = '';
			$info_list['content_type'] = 0;
			$info_list['parameter_field'] = '';
			$info_list['activity_id'] = '';
		}
		if($info_list['after_finished_do']==1)//多个包名
		{
			$package_arr = explode(',',$data['more_package']);
			foreach($package_arr as $pk_val)
			{
				$package_result = $this -> table('sj_soft') -> where(array('package' => $pk_val,'hide' => 1,'status' => 1)) -> select();
				if(!$package_result)
				{
					return $pk_val."包名软件，不处于已上架状态！";	
				}
			}
			$info_list['market_page_name'] = '';
			$info_list['content_type'] = 0;
			$info_list['parameter_field'] = '';
			$info_list['activity_id'] = '';
			
			$info_list['after_finished_param'] = trim($data['more_package']);
		}
		else if($info_list['after_finished_do']==3)//指定时间
		{
			if (!preg_match($matches,trim($data['set_time_do']))) 
			{
				return "指定时间应为正整数";
			}
			else
			{
				$info_list['after_finished_param'] = trim($data['set_time_do']);
			}
			$info_list['market_page_name'] = '';
			$info_list['content_type'] = 0;
			$info_list['parameter_field'] = '';
			$info_list['activity_id'] = '';
		}
		else if($info_list['after_finished_do']==2)//推荐内容  活动和页面
		{
			if ($data['content_type']) {
				$map=array();
				$pre="pre";
				$rcontent=ContentTypeModel::saveRecommendContent_new($data,'',$map,$pre);
				if($rcontent!==true)
				{
					return $rcontent;
				}
				else
				{
					if($map['page_type'])//选择的是页面
					{
						$info_list['market_page_name'] = trim($map['page_type']);
					}
					if($map['activity_id'])//新增加活动id
					{
						$info_list['activity_id'] = trim($map['activity_id']);
					}
					if($map['feature_id'])//新增加专题id
					{
						$info_list['feature_id'] = trim($map['feature_id']);
					}
					$info_list['content_type'] = trim($map['content_type']);
					if($map['parameter_field'])
					{
						$info_list['parameter_field'] = trim($map['parameter_field']);
					}
					$info_list['after_finished_param'] = '';
				}
			}	
		}
		//6.4相同软件间隔时间和弹窗次数
		if($data['interval_time'])
		{
			if (!preg_match($matches,trim($data['interval_time']))) 
			{
				return "间隔时间应为正整数";
			}
			else
			{
				$info_list['interval_time'] = $data['interval_time'];
			}
		}
		else
		{
			return "相同软件相邻两次安装间隔时间不能为空！";
		}
		if($data['max_nums_pop'])
		{
			if (!preg_match($matches,trim($data['max_nums_pop']))) 
			{
				return "最多弹安装界面次数应为正整数";
			}else {
				$info_list['max_nums_pop'] = $data['max_nums_pop'];
			}
		}else {
			return "该软件累计最多弹安装界面次数不能为空！";	
		}
		//分版本弹窗
		$info_list['choose_version_code'] = ','. implode(',', $data['choose_version_code']). ',';
		//编辑的时候增加
		if($data['cover_num'] == '全部') {
			$info_list['cover_num'] = 0;
		}else {
			$info_list['cover_num'] = $data['cover_num'];
		}
		//$info_list['cover_num'] = $data['cover_num'];
		if($info_list['cover_num'])
		{
			if(!preg_match('/^\d+$/',$info_list['cover_num']) && $info_list['cover_num'] != "全部")
			{
				return "覆盖人数格式错误";
			}
		}
		$info_list['info_type']=9;
		
		/*if($info_list['pre_csv_url'])
		{
			$info_list['need_limit']=1;
		}
		//编辑增加的
		if(!$info_list['pre_csv_url']&&!trim($data['have_pre_dl']))
		{
			$info_list['need_limit']=0;
		}*/
			
		$info_list['soft_package'] = trim($data['pre_soft_package']);
		$info_list['soft_name'] = trim($data['pre_soft_name']);
		$package_result = $this -> table('sj_soft') -> where(array('package' => $info_list['soft_package'],'hide' => 1,'status' => 1)) -> select();
		if(!$package_result){
			return "包名不存在";
		}
		if(!$info_list['soft_name']){
			return "请填写预下载包名";
		} 
		//时间
		if($data['pre_start_tm'] && $data['pre_end_tm'])
		{
			$info_list['start_tm']=strtotime($data['pre_start_tm']);
			$info_list['end_tm']=strtotime($data['pre_end_tm']);
			if($info_list['start_tm'] > $info_list['end_tm']){
				return "开始时间不能大于结束时间";
			}
		}else{
			return "请选择开始时间和结束时间";
		}
		//每日推送时间段
		if (empty($data['pre_daily_fromtime']) ^ empty($data['pre_daily_totime'])) 
		{
			return "请填写完整的推送时间段";
		}
		if (!empty($data['pre_daily_fromtime']) && !empty($data['pre_daily_totime'])) 
		{
			if (!preg_match('/^[0-9]{2}:[0-9]{2}:[0-9]{2}$/', $data['pre_daily_fromtime'])
				|| !preg_match('/^[0-9]{2}:[0-9]{2}:[0-9]{2}$/', $data['pre_daily_totime'])
				|| !strtotime($data['pre_daily_fromtime']) || !strtotime($data['pre_daily_totime'])
				) {
				return "推送时间段时间格式错误";
			}
			if ($data['pre_daily_totime'] <= $data['pre_daily_fromtime']) {
				return "推送时间段结束时间应大于开始时间";
			}
		}

		if($data['pre_daily_fromtime'])
		{
			$info_list['daily_start_tm'] = $data['pre_daily_fromtime'];
		}
		if($data['pre_daily_totime'])
		{
			$info_list['daily_end_tm'] = $data['pre_daily_totime'];
		}
		return true;
	}
	
	//V6.5桌面红包内容
	function save_desk_red_content($files,$data,$id,&$info_list)
	{
		$info_list['cpm_name'] = trim($data['cpm_name']);
		$info_list['info_type'] = 12;//桌面红包接口需要
		if($id){
			//编辑判断
			$cpm_where['_string'] = "id != {$id} and cpm_name = '{$info_list['cpm_name']}' and status = 1 and push_type = 5";
			$cpm_result = $this -> table('sj_market_push') -> where($cpm_where) -> select();
			if($info_list['cpm_name']){
				if($cpm_result){
					return "该桌面红包名称已存在";
				}
			}else{
				return "请填写桌面红包名称";
			}
		}else {
			if(!$info_list['cpm_name']) {
				return "请填写桌面红包名称";
			}else {
				$cpm_result = $this -> table('sj_market_push')->where(array('cpm_name' => $info_list['cpm_name'],'status' => 1,'push_type' => 5)) -> select();
				if($cpm_result){
					return "此桌面红包名称已存在";
				}
			}
		}
		//覆盖人数
		if($data['red_cover_num'] == '全部'){
			$info_list['cover_num'] = 0;
		}else{
			$info_list['cover_num'] = trim($data['red_cover_num']);
			$info_list['cover_num'] = str_replace(",", "", $info_list['cover_num']);
		}
		if($info_list['cover_num']){
			if(!preg_match('/^\d+$/',$info_list['cover_num']) && $info_list['cover_num'] != "全部"){
				return "覆盖人数格式错误";
			}
		}
		//合作形式
		if(isset($data['co_type_desk'])){
			$info_list['co_type'] = $data['co_type_desk'];
		}else{
			$info_list['co_type'] = 0;
		}
		if($data['push_way'])
		{
			$info_list['push_way'] = $data['push_way'];
			if($info_list['push_type'] ==3)
			{
				$info_list['push_way'] = 2;
			}
		}
		//桌面红包内容
		$size_arr['pop_width']=80;
		$size_arr['pop_height']=80;
		$size_arr['high_width']=106;
		$size_arr['high_height']=106;
		$size_arr['low_width']=60;
		$size_arr['low_height']=60;
		$size_arr['title_limit']=20;  //20个汉字 40个字符
		$size_arr['subtitle_limit']=14;
		$size_arr['des_limit']=20;
		$table="sj_market_push";
		//推送开始时间结束时间
		if($data['fromdate'] && $data['todate']) {
			$info_list['start_tm']=strtotime($data['fromdate']);
			$info_list['end_tm']=strtotime($data['todate']);
			if($info_list['start_tm'] > $info_list['end_tm']){
				return "开始时间不能大于结束时间";
			}
		}else{
			return "请选择开始时间和结束时间";
		}
		//桌面红包判断同一时间段是否重复
		if($id) {
			$have_where['_string'] = "start_tm <= {$info_list['end_tm']} and end_tm >= {$info_list['start_tm']} and status = 1 and push_type = 5 and id != {$id}";
		}else {
			$have_where['_string'] = "start_tm <= {$info_list['end_tm']} and end_tm >= {$info_list['start_tm']} and status = 1 and push_type = 5";
		}
		$have_result = $this -> table('sj_market_push') -> where($have_where) -> select();
		if($have_result) {
			return "对不起，该时间段已有桌面红包";
		}
		$desk_red_result = $this->desk_red_saved($files,$data,$id,$info_list,$size_arr,$table);
		if($desk_red_result!==true) {
			return $desk_red_result;
		}
		return true;
	}
	function desk_red_saved($files,$data,$id,&$info_list,$size_arr,$table)
	{
		$desk_red_high_width=$size_arr['high_width'];
		$desk_red_high_height=$size_arr['high_height'];
		$desk_red_low_width=$size_arr['low_width'];
		$desk_red_low_height=$size_arr['low_height'];
		$desk_red_pop_width=$size_arr['pop_width'];
		$desk_red_pop_height=$size_arr['pop_height'];
		$desk_red_des_title_limit=$size_arr['title_limit'];//20个汉字 40个字符
		$desk_red_des_subtitle_limit=$size_arr['subtitle_limit'];
		$red_result_pop_des_limit=$size_arr['des_limit'];
		
		$red_map = $red_info = array();
		$bind_ext_data	=	array('name'=>'桌面红包');
		$model = new model();
		$red_have_content = $this ->table($table)->where(array('id' => $id))->find();
		if($red_have_content['desk_red_text'])
		{
			$red_info = json_decode($red_have_content['desk_red_text'],true);
			$red_map = $red_info;
		}
		//桌面红包描述标题、桌面红包描述副标题、红包结果弹窗描述标题、是否显示安智icon、弹窗展示时间
		$red_map['desk_red_des_title'] = trim($data['desk_red_des_title']);
		if(!$red_map['desk_red_des_title'] || strlen($red_map['desk_red_des_title']) > $desk_red_des_title_limit*3)
		{
			return "请填写20个汉字以内的桌面红包描述标题";
		}
		$red_map['desk_red_des_subtitle'] = trim($data['desk_red_des_subtitle']);
		if($red_map['desk_red_des_subtitle'] && strlen($red_map['desk_red_des_subtitle']) > $desk_red_des_subtitle_limit*3)
		{
			return "请填写14个汉字以内的桌面红包描述副标题";
		}
		$red_map['red_result_pop_des'] = trim($data['red_result_pop_des']);
		if(!$red_map['red_result_pop_des'] || strlen($red_map['red_result_pop_des']) > $red_result_pop_des_limit*3)
		{
			return "请填写20个汉字以内的红包结果弹窗描述标题";
		}
		
		$red_map['is_show_anzhi_icon'] = trim($data['is_show_anzhi_icon']);
		$red_map['red_pop_show_time'] = trim($data['red_pop_show_time']);
		$red_map['red_pop_show_times'] = trim($data['red_pop_show_times']);
		if($red_map['red_pop_show_time'] == 1 ) {
			$red_map['red_pop_show_times'] = '';
		}else if($red_map['red_pop_show_time'] == 2) {
			if(!preg_match('/^\+?[1-9]\d*$/', $red_map['red_pop_show_times']) || $red_map['red_pop_show_times'] < 1 || $red_map['red_pop_show_times'] >30 ){
				return "倒计时时间只能输入1~30的整数";
			}
		}
		
		$red_map['red_type'] = $data['red_type'];
		//若获取红包的条件是无 有选择页面类型功能
		if($data['red_type']==1)
		{
			/*//push是notice_type  1.软件2.专题3.活动4.网页5.页面  其余是content_type 1.软件2.活动3.专题4.页面5.网页6.礼包7.攻略
			*/
			if($table=="sj_market_push") //PUSH推送和其他不一样
			{
				//编辑
				if($id){
					if(trim($data['notice_types'])) {
						$info_list['notice_type'] = trim($data['notice_types']);
					}else {
						$info_list['notice_type'] = $red_have_content["notice_type"];
					}
				}else {
					$info_list['notice_type'] = trim($data['notice_types']);
				}
				
				$info_list['uninstalled_download'] = trim($data['uninstalled_downloads']);
				if($data['uninstalled_silents']) {
					$info_list['uninstalled_silent'] = trim($data['uninstalled_silents']);
				}else {
					$info_list['uninstalled_silent'] = 0;
				}
				$info_list['installed_select'] = trim($data['installed_selects']);
				$info_list['detail_page'] = trim($data['detail_pages']);
				$info_list['lower_version_select'] = trim($data['lower_version_selects']);
				if($data['lower_version_silents'])
				{
					$info_list['lower_version_silent'] = trim($data['lower_version_silents']);
				}else {
					$info_list['lower_version_silent'] = 0;
				}
				$info_list['page_name'] = trim($data['page_names']);
				$info_list['page_link'] = trim($data['page_links']);
				$info_list['open_type'] = trim($data['open_types']);
				$info_list['soft_package'] = trim($data['soft_packages']);
				$info_list['feature_id'] = (int)(trim($data['feature_ids']));
				$info_list['activity_id'] = (int)(trim($data['activity_ids']));
				$info_list['beid'] = (int)(trim($data['beids']));
				
				if( $data['beids'] ) {
					if(!preg_match('/^\d+$/',$data['beids'])) {
						return "行为id格式错误";
					}
				}
				if($info_list['notice_type']==5)
				{
					if ($data['content_type']) {
						$map=array();
						$cpm="cpm";
						$rcontent=ContentTypeModel::saveRecommendContent_new($data,'',$map,$cpm);
						if($rcontent!==true) {
							return $rcontent;
						}else {
							$info_list['market_page_name'] = $map['page_type'];
							$info_list['content_type'] = $map['content_type'];
							if($map['parameter_field']) {
								$info_list['parameter_field'] = $map['parameter_field'];
							}
						}
					}	
				}
				if($info_list['notice_type']==4) {
					if(!$info_list['page_name']) {
						return "请填写页面标题";
					}
					if(!$info_list['page_link']) {
						return "请填写页面链接";
					}
					$url_reg = "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]\.[a-z]{2,6}/i";
					if ( !preg_match($url_reg, $info_list['page_link']) ) {
						return "请输入有效的链接地址";
					}
					if($info_list['open_type']==1) {
						$is_sync_accout = $data['is_sync_accout2'];
						$info_list['parameter_field'] = json_encode(array('website_is_sync_accout'=>$is_sync_accout,'website_is_actionbar'=>$data['is_actionbar2'],'website_screen_show'=>$data['screen_show2'],'website_is_h5'=>$data['is_h5_2']));
					}else {
						$info_list['parameter_field'] = '';
					}

				}
			}else {
				$map = array();
				$rcontent=ContentTypeModel::saveRecommendContent($data,'',$map);
				if($rcontent!==true) {
					return $rcontent;
				}else {
					$info_list = $map;
				}
			}
			//获取红包条件为无的时候  红包id，红包总数，红包数量，红包真实随机范围，红包发放类型
		}elseif( $data['red_type'] == 2 ){
			$red_map['red_task_content1'] = trim($data['red_task_content1']);
			if( strlen($red_map['red_task_content1']) > 14*3)
			{
				return "任务文案1，最多可输入14个汉字";
			}
			$red_map['red_task_content2'] = trim($data['red_task_content2']);
			if( strlen($red_map['red_task_content2']) > 14*3)
			{
				return "任务文案2，最多可输入14个汉字";
			}
		}elseif( $table == 'sj_market_push' && $data['red_type']==2 ) {
			$info_list['notice_type'] = '';
		}
		$bind_data = array();
		if( $red_map['red_type'] == 1 ) {
			if( !$data['red_id'] ) {
				return "无任务信息";
			}
			if( $data['red_id'] && $red_info['red_id'] != $data['red_id'] ) {
				$red_package_info = D('Sj.RedActivity')->get_red_package_info($data['red_id']);
				if( empty($red_package_info) ) {
					return "未获到红包信息";
				}
				if( $red_package_info[0]['givetype'] != 1 ) {
					return "红包发放类型必须为一次性发放";
				}
				
				if( $red_have_content['id'] ) {
					$bind_data = array(
						'bind_red_type'	=>	1,
						'push_id'		=>	$red_have_content['id'],
						'red_id'		=>	$red_info['red_id'],
						'new_red_id'	=>	$data['red_id'],
						'task_id'		=>	'',
						'note'			=>	$bind_ext_data['name'],
						'table'			=>	$table,
					);
				}
				$red_map['red_id']				=	$data['red_id'];
				$red_map['red_num']				=	$red_package_info[0]['totalnum'];
				$red_map['totalmon']			=	$red_package_info[0]['totalmon'];
				$red_map['task_id']				=	'';
				$red_map['red_soft_pkg']		=	'';
				$red_map['red_soft_name']		=	'';
				$red_map['red_task_content1']	=	'';
				$red_map['red_task_content2']	=	'';
			}
		}elseif($red_map['red_type'] == 2) {
			if( !$data['task_id'] ) {
				return "无任务信息";
			}
			if( $data['task_id'] && $red_info['task_id'] != $data['task_id'] ) {
				$task_info = D('Sj.RedActivity')->get_red_soft_list(1, trim($data['red_soft_pkg']), $data['task_id']);
				$red_package_info = D('Sj.RedActivity')->get_red_package_info($task_info['red_id']);
				if( empty($red_package_info) ) {
					return "未获取到任务关联的红包信息";
				}
				if( $red_package_info[0]['givetype'] !=1 ) {
					return "任务关联的红包发放类型必须为一次性发放";
				}
				//判断时间
				$task_info['start_tm']	=	strtotime($task_info['start_tm']);
				$task_info['end_tm']	=	strtotime($task_info['end_tm']);
				$fromdate	=	strtotime($data['fromdate']);
				$todate		=	(strtotime($data['todate']) - 3600);
				if( $fromdate < $task_info['start_tm'] || $fromdate > $task_info['end_tm'] || $todate < $task_info['start_tm'] || $todate > $task_info['end_tm'] ) {
					return "桌面红包时间不在任务时间内";
				}
				if( !in_array($task_info['task_type'], array('T51','T52','T53'))) {
					return "红包任务类型有误";
				}
				if( $red_have_content['id'] ) {
					$bind_data = array(
							'bind_red_type'	=>	2,
							'push_id'		=>	$red_have_content['id'],
							'red_id'		=>	$red_info['red_id'],
							'new_red_id'	=>	$task_info['red_id'],
							'task_id'		=>	$data['task_id'],
							'note'			=>	$bind_ext_data['name'],
							'table'			=>	$table,
					);
				}
				$red_map['task_id']			=	$data['task_id'];
				$red_map['red_id']			=	$task_info['red_id'];
				$red_map['red_num']			=	$red_package_info[0]['totalnum'];
				$red_map['totalmon']		=	$red_package_info[0]['totalmon'];
				$red_map['red_soft_pkg']	=	trim($data['red_soft_pkg']);
				$red_map['red_soft_name']	=	$task_info['softname'];
			}
		}
		
		if($files['desk_red_pop']['size']||$files['desk_red_high']['size']||$files['desk_red_low']['size'])
		{
			//检查图片尺寸
			$img_info_arr = getimagesize($files['desk_red_pop']['tmp_name']);
			$img_info_arr_high = getimagesize($files['desk_red_high']['tmp_name']);
			$img_info_arr_low = getimagesize($files['desk_red_low']['tmp_name']);
			if (!$img_info_arr&&!$img_info_arr_high&&!$img_info_arr_low) 
			{
				return "上传图片出错";
			}
			if($files['desk_red_pop']['size'])
			{
				$width = $img_info_arr[0];
				$height = $img_info_arr[1];
				if ($width != $desk_red_pop_width || $height != $desk_red_pop_height) 
				{
					return "请添加尺寸为{$desk_red_pop_width}*{$desk_red_pop_height}的弹窗图片";
				}
				//检查图片格式
				if ($files['desk_red_pop']['type'] != 'image/png' && $files['desk_red_pop']['type'] != 'image/jpg'&&$files['desk_red_pop']['type'] != 'image/jpeg') 
				{
					return "请添加图片格式为：jpg，png的弹窗图片";
				}
			}else {
				if(!$id)
				{
					return "请上传弹窗图片";
				}
			}
			if($files['desk_red_high']['size'])
			{
				$width_high = $img_info_arr_high[0];
				$height_high = $img_info_arr_high[1];
				if ($width_high != $desk_red_high_width || $height_high != $desk_red_high_height) 
				{
					return "请添加尺寸为{$desk_red_high_width}*{$desk_red_high_height}的高分图片";
				}
				if ($files['desk_red_high']['type'] != 'image/png' && $files['desk_red_high']['type'] != 'image/jpg'&&$files['desk_red_high']['type'] != 'image/jpeg') 
				{
					return "请添加图片格式为：jpg，png的高分图片";
				}
			}else {
				if(!$id)
				{
					return "请上传高分图片";
				}
			}
			if($files['desk_red_low']['size'])
			{
				$width_low = $img_info_arr_low[0];
				$height_low = $img_info_arr_low[1];
				
				if ($width_low != $desk_red_low_width || $height_low != $desk_red_low_height) 
				{
					return "请添加尺寸为{$desk_red_low_width}*{$desk_red_low_height}的低分图片";
				}
				if ($files['desk_red_low']['type'] != 'image/png' && $files['desk_red_low']['type'] != 'image/jpg'&&$files['desk_red_low']['type'] != 'image/jpeg') 
				{
					return "请添加图片格式为：jpg，png的低分图片";
				}
			}else {
				if(!$id) {
					return "请上传低分图片";
				}
			}
		}else {
			if(!$id) {
				return "请上传全部图片";
			}
		}
		
		include_once SERVER_ROOT. '/tools/functions.php';
		//上传图片
		// 将图片存储起来
		$suffix = preg_match("/\.(jpg|png)$/", $files['desk_red_pop']['name'],$matches);
		$suffix_high = preg_match("/\.(jpg|png)$/", $files['desk_red_high']['name'],$matches_high);
		$suffix_low = preg_match("/\.(jpg|png)$/", $files['desk_red_low']['name'],$matches_low);
		if($matches)
		{
			$suffix = $matches[0];
		}
		if ($matches_high) {
			$suffix_high = $matches_high[0];
		} 
		if ($matches_low) {
			$suffix_low = $matches_low[0];
		} 
		$folder = "/img/" . date("Ym/d/");
		$this->mkDirs(UPLOAD_PATH . $folder);
		$relative_path = $folder . time() . '_' . rand(1000,9999) . $suffix;
		$relative_path_high = $folder . time() .'high'. '_' . rand(1000,9999) . $suffix_high;
		$relative_path_low = $folder . time() .'low'. '_' . rand(1000,9999) . $suffix_low;
		$img_path = UPLOAD_PATH . $relative_path;
		$img_path_high = UPLOAD_PATH . $relative_path_high;
		$img_path_low = UPLOAD_PATH . $relative_path_low;
		if($id)
		{
			$have_been = $model -> table($table) -> where(array('id' => $id)) -> find();
			$have_red_arr = json_decode($have_been['desk_red_text'],true);
		}
		if($files['desk_red_pop']['tmp_name'])
		{
			$ret = move_uploaded_file($files['desk_red_pop']['tmp_name'], $img_path);
			$red_map['desk_red_pop'] = $relative_path;
		}else {
			if($id)
			{
				$red_map['desk_red_pop'] = $have_red_arr['desk_red_pop'];
			}
		}
		if($files['desk_red_high']['tmp_name'])
		{
			$ret = move_uploaded_file($files['desk_red_high']['tmp_name'], $img_path_high);
			$red_map['desk_red_high'] = $relative_path_high;
		}else {
			if($id)
			{
				$red_map['desk_red_high'] = $have_red_arr['desk_red_high'];
			}
		}
		if($files['desk_red_low']['tmp_name'])
		{
			$ret = move_uploaded_file($files['desk_red_low']['tmp_name'], $img_path_low);
			$red_map['desk_red_low'] = $relative_path_low;
		}else {
			if($id)
			{
				$red_map['desk_red_low'] = $have_red_arr['desk_red_low'];
			}
		}
	
		$info_list['desk_red_text']	=	json_encode($red_map);
		$info_list['bind_data']		=	$bind_data;
		return true;
	}
	//类型：软件、专题、活动、网页、页面、、各个类型的内容判断函数
	function content_type_deal($case,&$info_list)
	{
		switch($case)
		{
			case 1://软件包名
				if(!$info_list['soft_package']) {
					return "请填写包名";
				}else {
					$package_result = $this -> table('sj_soft') -> where(array('package' => $info_list['soft_package'],'hide' => 1,'status' => 1)) -> select();
					if(!$package_result) {
						return "包名不存在";
					}else {
						if($info_list['beid']){
							$my_beid_where['_string'] = "beid = {$info_list['beid']} and package = '{$info_list['soft_package']}' and start < {$info_list['end_tm']} and end > {$info_list['start_tm']}";
							$my_beid_result = $this -> table('sj_push_be_detail') -> where($my_beid_where) -> select();
							if(!$my_beid_result){
								return "行为id与包名不匹配";
							}
						}
					}
				}
				break;
			case 2: //专题
				if(!$info_list['feature_id']){
                    return "请填写专题id";
                }
                if(!preg_match('/^\d+$/',$info_list['feature_id'])){
                    return "专题id格式错误";
                }
                if($info_list['beid']){
                    if(!$this -> check_beid($info_list['beid'],$info_list['feature_id'],$info_list['start_tm'],$info_list['end_tm'])){
                        return "行为id与专题id不对应";
                    }
                }
				break;
			case 3: //活动
				if(!$info_list['activity_id']){
                    return "请填写活动id";
                }
                if(!preg_match('/^\d+$/',$info_list['activity_id'])){
                    return "活动id格式错误";
                }
				break;
			case 4: //网页
				if(!$info_list['page_name']){
                    return "请填写页面标题";
                }
                if(!$info_list['page_link']){
                    return "请填写页面链接";
                }
                $url_reg = "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i";
                if (!preg_match($url_reg, $info_list['page_link'])) {
                	return "请输入有效的链接地址";
                }
				break;
			case 5: //页面
				$push_market_page_type=$info_list['market_page_type'];
				if($push_market_page_type==4)
				{
					$page_name=trim($info_list['page_name4']);
				}elseif($push_market_page_type==1) {
					$page_name=trim($info_list['page_name1']);
				}
				if(!$page_name)
				{
					return "页面不能为空";
				}else {
					$page_type = ContentTypeModel::convertPageName2PageType($page_name, $push_market_page_type);
					if (!$page_type) 
					{
						return "页面不存在";
					}else {
						$info_list['market_page_name']=$page_type;
						$info_list['market_page_type']=$push_market_page_type;
					}
				}
				break;
		}
		return true;
	}
	
	//编辑的时候csv文件保存
	function save_edit_file_csv($data,$files,&$info_list)
	{
		if($files['upload_file']['size'])
		{
			$filename=$files['upload_file']['name'];
			if(!$filename&&!trim($data['csv_count'])&&trim($data['have_pre_dl']))
			{
				$info_list['pre_dl_count'] = trim($data['pre_dl_count']);
				$info_list['pre_csv_url'] = trim($data['have_pre_dl']);
				$info_list['is_upload_csv'] = 1;
			}
			if(!$filename&&!$data['csv_url']&&!trim($data['have_pre_dl']))
			{
				$info_list['pre_dl_count'] = 0;
				$info_list['pre_csv_url'] = "";
			}
			if($filename&&!$data['csv_count'])
			{
				return "选择好的文件请点击上传才有效";
			}
			if(trim($data['csv_url'])&&trim($data['csv_count']))
			{
				$info_list['pre_dl_count'] = trim($data['csv_count']);
				$info_list['pre_csv_url'] = trim($data['csv_url']);
				$info_list['is_upload_csv'] = 1;
			}
		}elseif($files['paichu_upload_file']['size']) {
			$paichu_filename=$files['paichu_upload_file']['name'];
			if(!$paichu_filename&&!trim($data['paichu_csv_count'])&&trim($data['have_paichu']))
			{
				$info_list['pre_dl_count'] = trim($data['paichu_count']);
				$info_list['pre_csv_url'] = trim($data['have_paichu']);
				$info_list['is_upload_csv'] = 1;
			}
			if(!$paichu_filename&&!$data['paichu_csv_url']&&!trim($data['have_paichu']))
			{
				$info_list['pre_dl_count'] = 0;
				$info_list['pre_csv_url'] = "";
			}
			if($paichu_filename&&!$data['paichu_csv_count'])
			{
				return "选择好的文件请点击上传才有效";
			}
			if(trim($data['paichu_csv_url'])&&trim($data['paichu_csv_count']))
			{
				$info_list['pre_dl_count'] = trim($data['paichu_csv_count']);
				$info_list['pre_csv_url'] = trim($data['paichu_csv_url']);
				$info_list['is_upload_csv'] = 1;
			}
		}else {
			if($data['need_limit']==1)
			{
				$info_list['pre_dl_count'] = trim($data['csv_count']);
				$info_list['pre_csv_url'] = trim($data['csv_url']);
			}elseif($data['need_limit']==2) {
				$info_list['pre_dl_count'] = trim($data['paichu_csv_count']);
				$info_list['pre_csv_url'] = trim($data['paichu_csv_url']);
			}
		}
		return true;
	}
	//push中复制来的检查行为id的函数  有所改动
	function check_beid($beid,$feature_id,$start_tm,$end_tm){
        //$model = new Model();
        $beid_where['_string'] = "beid = {$beid} and status = 1 and start  <= {$end_tm} and end >= {$start_tm}";
        $beid_result = $this -> table('sj_push_be_detail') -> where($beid_where) -> select();
        $feature_result = $this -> table('sj_feature_soft') -> where(array('feature_id' => $feature_id,'status' => 1)) -> select();
        foreach($feature_result as $key => $val){
            $feature_soft_arr[] = $val['package'];
        }

        foreach($beid_result as $key => $val){
            if(!in_array($val['package'],$feature_soft_arr)){
                $i = 0;
            }else{
                $i = 1;
            }
            $check_arr[] = $i;
        }

        $count = array_sum($check_arr);
        if($count > 0){
            return true;
        }else{
            return false;
        }
    }
	public function check_url($url) {
        $reg = "/^((http:\/\/)|(https:\/\/))([\w\d-]+\.)+[\w-]+(\/[\x{4e00}-\x{9fa5}\d\w-.\/?%&=]*)?$/iu";
        if (!preg_match($reg, $url)) {
            return false;
        }
        return true;
    }
	 //创建目录
	public  function mkDirs($path){
            $adir = explode('/',$path);
            $dirlist = '';
            $rootdir = array_shift($adir);
            if(($rootdir!='.'||$rootdir!='..')&&!file_exists($rootdir)){
                @mkdir($rootdir);
            }
            foreach($adir as $key=>$val){
                $dirlist .= "/".$val;
                $dirpath = $rootdir.$dirlist;
                if(!file_exists($dirpath)){
                @mkdir($dirpath);
                @chmod($dirpath,0777);
                }
            }
    }
}
<?php

class MachinemessageAction extends CommonAction{

	function machine_list(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$extend_id = $_GET['extend_id'];
		$machine_name = $_GET['machine_name'];
		if($extend_id){
			$where_go .= " and extend_id = {$extend_id}";
		}
		if($machine_name){
			$where_go .= " and machine_name like '%{$machine_name}%'";
		}
		$where['_string'] = "status = 1".$where_go;
		$count = $bbs_model -> table('bbs_machine_message') -> where($where) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 20, $param);
		$result = $bbs_model -> table('bbs_machine_message') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('rank,create_tm DESC') -> select();
		$brand_results = $bbs_model -> table('bbs_machine_brand') -> where(array('status' => 1)) -> select();
		foreach($result as $key => $val){
			$entrance_result = $bbs_model -> table('bbs_machine_entrance') -> where(array('machine_id' => $val['id'],'status' => 1)) -> select();
			$val['entrance_result'] = $entrance_result;
			$brand_result = $bbs_model -> table('bbs_machine_brand') -> where(array('id' => $val['extend_id'])) -> find();
			$val['brand_name'] = $brand_result['brand_name'];
			$result[$key] = $val;
		}
		if($_GET['lr']){
			$lr = $_GET['lr'];
		}else{
			$lr = 20;
		}
		if($_GET['p']){
			$p = $_GET['p'];
		}else{
			$p = 1;
		}
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
        $show = $Page->show();
		$this -> assign('lr',$lr);
		$this -> assign('p',$p);
        $this -> assign('page', $show);
		$this -> assign('brand_result',$brand_results);
		$this -> assign('extend_id',$extend_id);
		$this -> assign('machine_name',$machine_name);
		$this -> assign('result',$result);
		$this -> display();
	}

	function add_machine_show(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$brand_result = $bbs_model -> table('bbs_machine_brand') -> where(array('status' => 1)) -> select();
		$this -> assign('brand_result',$brand_result);
		$this -> display();
	}
	
	function add_machine_do(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$extend_id = $_POST['extend_id'];
		$machine_name = $_POST['machine_name'];
		$have_name = $bbs_model -> table('bbs_machine_message') -> where(array('machine_name' => $machine_name,'status' => 1)) -> select();
		if($have_name){
			$this -> error("已存在该机型名称");
		}
		$rank = $_POST['rank'];
		$machine_pic = $_FILES['machine_pic'];
		$machine_link = $_POST['machine_link'];
		$describe = $_POST['describe'];
		$describe2 = $_POST['describe2'];
		
		if($rank <= 0){
			$this -> error("排序值错误");
		}
		if(mb_strlen($machine_name,'utf8') < 1 || mb_strlen($machine_name,'utf8') > 10){
			$this -> error("请输入1-10个字以内的机型名称");
		}
		if(mb_strlen($machine_link,'utf8') < 1 || mb_strlen($machine_link,'utf8') > 255){
			$this -> error("请输入1-255个字以内的机型地址");
		}
		if(mb_strlen($describe,'utf8') < 1 || mb_strlen($describe,'utf8') > 10){
			$this -> error("请输入1-10个字以内的机型描述");
		}
		if(mb_strlen($describe2,'utf8') < 1 || mb_strlen($describe2,'utf8') > 10){
			$this -> error("请输入1-10个字以内的机型描述");
		}
		$describe = str_replace(chr(32),'&nbsp;',addslashes(htmlspecialchars($describe)));
		$describe2 = str_replace(chr(32),'&nbsp;',addslashes(htmlspecialchars($describe2)));
		if($machine_pic['size']){
			$halve_wd = getimagesize($machine_pic['tmp_name']);
            $widhig_ha = $halve_wd[3];
            $wh_ha = explode(' ', $widhig_ha);
            $wh1_ha = $wh_ha[0];
            $widths_ha = explode('=', $wh1_ha);
            $width1_ha = substr($widths_ha[1], 0, -1);
            $width_go_ha = substr($width1_ha, 1);
            $hi1_ha = $wh_ha[1];
            $heights_ha = explode('=', $hi1_ha);
            $height1_ha = substr($heights_ha[1], 0, -1);
            $height_go_ha = substr($height1_ha, 1);
            if ($width_go_ha != 72 || $height_go_ha != 110) {
                $this->error("图标大小不符合条件");
            }

            if ($machine_pic['type'] != 'image/png' && $machine_pic['type'] != 'image/jpg' && $machine_pic['type'] != 'image/jpeg') {
                $this->error("图标格式错误");
            }
			$config['multi_config']['machine_pic'] = array(
				'savepath' => UPLOAD_PATH . '/img/' . $path,
				'saveRule' => 'getmsec',
			);
			$list = $this->_uploadapk(0, $config);
			$bbs_pic_url = $list['image'][0]['url'];
			$data['machine_pic'] = $bbs_pic_url;
		}else{
			$this -> error("请上传机型图片");
		}
		
		$entrance_name = $_POST['entrance_name'];
		$entrance_link = $_POST['entrance_link'];
		for($i=0;$i<count($entrance_name);$i++){
			if(mb_strlen($entrance_name[$i],'utf8') > 5){
				$this -> error("请输入1-5个字以内的入口名称");
			}
			if(mb_strlen($entrance_link[$i],'utf8') > 255){
				$this -> error("请输入1-5个字以内的入口地址");
			}
			$entrance_message[$i]['entrance_name'] = $entrance_name[$i];
			$entrance_message[$i]['entrance_link'] = $entrance_link[$i];
		}
		$data['extend_id'] = $extend_id;
		$data['machine_name'] = $machine_name;
		$data['machine_link'] = $machine_link;
		$data['rank'] = $rank;
		$data['describe'] = $describe;
		$data['describe2'] = $describe2;
		$data['create_tm'] = time();
		$data['update_tm'] = time();
		$data['status'] = 1;
		$result = $bbs_model -> table('bbs_machine_message') -> add($data);
		if($result){
			foreach($entrance_message as $key => $val){
				if($val['entrance_name'] && $val['entrance_link']){
					$entrance_data = array(
						'entrance_name' => $val['entrance_name'],
						'entrance_link' => $val['entrance_link'],
						'machine_id' => $result,
						'create_tm' => time(),
						'update_tm' => time(),
						'status' => 1
					);
					$entrance_result = $bbs_model -> table('bbs_machine_entrance') -> add($entrance_data);
				}
			}
			$this -> writelog("已添加id为{$result}的机型信息","bbs_machine_message",$result,__ACTION__ ,"","add");
			$this -> assign('jumpUrl','/index.php/Bbs_manage/Machinemessage/machine_list');
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}
	
	function edit_machine_show(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$result = $bbs_model -> table('bbs_machine_message') -> where(array('id' => $id)) -> find();
		$entrance_result = $bbs_model -> table('bbs_machine_entrance') -> where(array('machine_id' => $result['id'],'status' => 1)) -> select();
		$all_entrance = $bbs_model -> table('bbs_machine_entrance') -> order('id DESC') -> select();
		$extend_id = $_GET['exntend_id'];
		$machine_name = $_GET['machine_name'];
		$brand_result = $bbs_model -> table('bbs_machine_brand') -> where(array('id' => $result['extend_id'])) -> find();
		$extend_id = $_GET['extend_id'];
		$machine_name = $_GET['machine_name'];
		$lr = $_GET['lr'];
		$p = $_GET['p'];
		$this -> assign('brand_result',$brand_result);
		$this -> assign('extend_id',$extend_id);
		$this -> assign('lr',$lr);
		$this -> assign('p',$p);
		$this -> assign("all_entrance",$all_entrance[0]['id'] + 1);
		$this -> assign("entrance_count",count($entrance_result) - 1);
		$this -> assign('entrance_result',$entrance_result);
		$this -> assign('machine_name',$machine_name);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function edit_machine_do(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$extend_id = $_POST['extend_id'];
		$machine_names = $_POST['machine_names'];
		$lr = $_POST['lr'];
		$p = $_POST['p'];
		if($extend_id){
			$where_go .= "/extend_id/{$extend_id}";
		}
		if($machine_names){
			$where_go .= "/machine_name/{$machine_names}";
		}
		if($lr){
			$where_go .= "/lr/{$lr}";
		}
		if($p){
			$where_go .= "/p/{$p}";
		}
		
		$id = $_POST['id'];
		$machine_name = $_POST['machine_name'];
		$have_where['_string'] = "machine_name = {$machine_name} and id != {$id} and status = 1";
		$have_name = $bbs_model -> table('bbs_machine_message') -> where($have_where) -> select();
		if($have_name){
			$this -> error("已存在该机型名称");
		}
		$rank = $_POST['rank'];
		$machine_pic = $_FILES['machine_pic'];
		$machine_link = $_POST['machine_link'];
		$describe = $_POST['describe'];
		$describe2 = $_POST['describe2'];
		
		if($rank <= 0){
			$this -> error("排序值错误");
		}
		if(mb_strlen($machine_name,'utf8') < 1 || mb_strlen($machine_name,'utf8') > 10){
			$this -> error("请输入1-10个字以内的机型名称");
		}
		if(mb_strlen($machine_link,'utf8') < 1 || mb_strlen($machine_link,'utf8') > 255){
			$this -> error("请输入1-255个字以内的机型地址");
		}
		
		if(mb_strlen($describe,'utf8') < 1 || mb_strlen($describe,'utf8') > 10){
			$this -> error("请输入1-10个字以内的机型描述");
		}
		if(mb_strlen($describe2,'utf8') < 1 || mb_strlen($describe2,'utf8') > 10){
			$this -> error("请输入1-10个字以内的机型描述");
		}
		$describe = str_replace(chr(32),'&nbsp;',addslashes(htmlspecialchars($describe)));
		$describe2 = str_replace(chr(32),'&nbsp;',addslashes(htmlspecialchars($describe2)));
		if($machine_pic['size']){
			$halve_wd = getimagesize($machine_pic['tmp_name']);
            $widhig_ha = $halve_wd[3];
            $wh_ha = explode(' ', $widhig_ha);
            $wh1_ha = $wh_ha[0];
            $widths_ha = explode('=', $wh1_ha);
            $width1_ha = substr($widths_ha[1], 0, -1);
            $width_go_ha = substr($width1_ha, 1);
            $hi1_ha = $wh_ha[1];
            $heights_ha = explode('=', $hi1_ha);
            $height1_ha = substr($heights_ha[1], 0, -1);
            $height_go_ha = substr($height1_ha, 1);
            if ($width_go_ha != 72 || $height_go_ha != 110) {
                $this->error("图标大小不符合条件");
            }

            if ($machine_pic['type'] != 'image/png' && $machine_pic['type'] != 'image/jpg' && $machine_pic['type'] != 'image/jpeg') {
                $this->error("图标格式错误");
            }
			$config['multi_config']['machine_pic'] = array(
				'savepath' => UPLOAD_PATH . '/img/' . $path,
				'saveRule' => 'getmsec',
			);
			$list = $this->_uploadapk(0, $config);
			$bbs_pic_url = $list['image'][0]['url'];
			$data['machine_pic'] = $bbs_pic_url;
		}
		$entrance_name = $_POST['entrance_name'];
		$entrance_link = $_POST['entrance_link'];
		for($i=0;$i<count($entrance_name);$i++){
			if(mb_strlen($entrance_name[$i],'utf8') > 5){
				$this -> error("请输入1-5个字以内的入口名称");
			}
			if(mb_strlen($entrance_link[$i],'utf8') > 255){
				$this -> error("请输入1-5个字以内的入口地址");
			}
			$entrance_message[$i]['entrance_name'] = $entrance_name[$i];
			$entrance_message[$i]['entrance_link'] = $entrance_link[$i];
		}
		$entrance_id = $_POST['entrance_id'];
		$data['machine_name'] = $machine_name;
		$data['rank'] = $rank;
		$data['machine_link'] = $machine_link;
		$data['describe'] = $describe;
		$data['describe2'] = $describe2;
		$data['update_tm'] = time();
		$log_result = $this -> logcheck(array('id' => $id),'bbs_machine_message',$data,$bbs_model);
		$result = $bbs_model -> table('bbs_machine_message') -> where(array('id' => $id)) -> save($data);
		if($result){
			for($i=0;$i<count($entrance_id);$i++){
				$result_have = $bbs_model -> table('bbs_machine_entrance') -> where(array('id' => $entrance_id[$i])) -> select();
				if($result_have){
					if($entrance_name[$i] && $entrance_link[$i]){
						$data_about = array(
							'entrance_name' => $entrance_name[$i],
							'entrance_link' => $entrance_link[$i],
							'update_tm' => time()
						);
						$about_result = $bbs_model -> table('bbs_machine_entrance') -> where(array('id' => $entrance_id[$i],'status' => 1)) -> save($data_about);
					}
				}else{
					if($entrance_name[$i] && $entrance_link[$i]){
						$data_about = array(
							'machine_id' => $id,
							'entrance_name' => $entrance_name[$i],
							'entrance_link' => $entrance_link[$i],
							'update_tm' => time(),
							'create_tm' => time(),
							'status' => 1
						);
						$about_result = $bbs_model -> table('bbs_machine_entrance') -> add($data_about);
					}
				}
			}
		}
	
		if($result){
			$this -> writelog("已编辑id为{$id}的机型信息".$log_result,"bbs_machine_message",$result,__ACTION__ ,"","edit");
			$this -> assign("jumpUrl","/index.php/Bbs_manage/Machinemessage/machine_list".$where_go);
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	function del_machine(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$extend_id = $_GET['extend_id'];
		$machine_name = $_GET['machine_name'];
		if($extend_id){
			$where_go .= "/extend_id/{$extend_id}";
		}
		if($machine_name){
			$where_go .= "/machine_name/{$machine_name}";
		}
		$result = $bbs_model -> table('bbs_machine_message') -> where(array('id' => $id)) -> save(array('status' => 0));
		
		if($result){
			$this -> writelog("已删除id为{$id}的机型信息","bbs_machine_message",$id,__ACTION__ ,"","del");
			$this -> assign('jumpUrl','/index.php/Bbs_manage/Machinemessage/machine_list'.$where_go);
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	
	function del_entrance_true(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$result = $bbs_model -> table('bbs_machine_entrance') -> where(array('id' => $id)) -> save(array('status' => 0));
		if($result){
			echo 1;
			return 1;
		}else{
			echo 2;
			return 2;
		}
	}
	
	function change_rank(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$rank = $_GET['rank'];
		$data['rank'] = $rank;
		$data['update_tm'] = time();
		$log_result = $this -> logcheck(array('id' => $id),'bbs_machine_message',$data,$bbs_model);
		$result = $bbs_model -> table('bbs_machine_message') -> where(array('id' => $id)) -> save($data);
		if($result){
			$this -> writelog("已编辑id为{$id}的机型信息".$log_result,"bbs_machine_message",$id,__ACTION__ ,"","edit");
			echo 1;
			return 1;
		}else{
			echo 2;
			return 2;
		}
	}
	
	function brand_list(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$count = $bbs_model -> table('bbs_machine_brand') -> where(array('status' => 1)) -> order('rank,create_tm DESC') -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 20, $param);
		$result = $bbs_model -> table('bbs_machine_brand') -> where(array('status' => 1)) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('rank,create_tm DESC') -> select();
	
		if($_GET['lr']){
			$lr = $_GET['lr'];
		}else{
			$lr = 20;
		}
		if($_GET['p']){
			$p = $_GET['p'];
		}else{
			$p = 1;
		}
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
        $show = $Page->show();
		$this -> assign('lr',$lr);
		$this -> assign('p',$p);
        $this -> assign('page', $show);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function add_brand_show(){
		$this -> display();
	}
	
	function add_brand_do(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$brand_name = $_POST['brand_name'];
		$have_result = $bbs_model -> table('bbs_machine_brand') -> where(array('brand_name' => $brand_name,'status' => 1)) -> select();
		if($have_result){
			$this -> error("已存在该机型信息");
		}
		$brand_link = $_POST['brand_link'];
		$rank = $_POST['rank'];
		if($rank<=0)
		{
		 $this->error("排序值错误");
		}
		$data = array(
			'brand_name' => $brand_name,
			'brand_link' => $brand_link,
			'rank' => $rank,
			'create_tm' => time(),
			'update_tm' => time(),
			'status' => 1
		);
		$result = $bbs_model -> table('bbs_machine_brand') -> add($data);
		if($result){
			$this -> writelog("已添加id为{$result}的机型品牌","bbs_machine_brand",$result,__ACTION__ ,"","add");
			$this -> assign('jumpUrl','/index.php/Bbs_manage/Machinemessage/brand_list');
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}
	
	function edit_brand_show(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$lr = $_GET['lr'];
		$p = $_GET['p'];
		$result = $bbs_model -> table('bbs_machine_brand') -> where(array('id' => $id)) -> find();
		$this -> assign('lr',$lr);
		$this -> assign('p',$p);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function edit_brand_do(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_POST['id'];
		$lr = $_POST['lr'];
		$r = $_POST['r'];
		if($lr){
			$where_go .= "/lr/{$lr}";
		}
		if($p){
			$where_go .= "/p/p";
		}
		$brand_name = $_POST['brand_name'];
		$where_have['_string'] = "brand_name = {$brand_name} and status = 1 and id != {$id}";
		$have_result = $bbs_model -> table('bbs_machine_brand') -> where($where_have) -> select();
		if($have_result){
			$this -> error("已存在该机型信息");
		}
		$brand_link = $_POST['brand_link'];
		$rank = $_POST['rank'];
		if($rank<=0)
		{
		 $this->error("排序值错误");
		}
		$data = array(
			'brand_name' => $brand_name,
			'brand_link' => $brand_link,
			'rank' => $rank,
			'update_tm' => time()
		);
		$log_result = $this -> logcheck(array('id' => $id),'bbs_machine_brand',$data,$bbs_model);
		$result = $bbs_model -> table('bbs_machine_brand') -> where(array('id' => $id)) -> save($data);
		if($result){
			$this -> writelog("已编辑id为{$id}的机型品牌".$log_result,"bbs_machine_brand",$id,__ACTION__ ,"","edit");
			$this -> assign("jumpUrl","/index.php/Bbs_manage/Machinemessage/brand_list".$where_go);
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	function del_brand(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$result = $bbs_model -> table('bbs_machine_brand') -> where(array('id' => $id)) -> save(array('status' => 0));
		if($result){
			$this -> writelog("已删除id为{$id}的机型品牌","bbs_machine_brand",$id,__ACTION__ ,"","del");
			$this -> assign('jumpUrl','/index.php/Bbs_manage/Machinemessage/brand_list');
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	
	function change_rank_brand(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$rank = $_GET['rank'];
		$data['rank'] = $rank;
		$data['update_tm'] = time();
		$log_result = $this -> logcheck(array('id' => $id),'bbs_machine_brand',$data,$bbs_model);
		$result = $bbs_model -> table('bbs_machine_brand') -> where(array('id' => $id)) -> save($data);
		if($result){
			$this -> writelog("已编辑id为{$id}的机型品牌".$log_result,"bbs_machine_brand",$id,__ACTION__ ,"","edit");
			echo 1;
			return 1;
		}else{
			echo 2;
			return 2;
		}
	}
	
}
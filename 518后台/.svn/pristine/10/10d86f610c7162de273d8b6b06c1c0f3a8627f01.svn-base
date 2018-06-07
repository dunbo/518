<?php

class MobileAction extends CommonAction{

    private $lists;             //列表
    private $conf_db;           //配置表
    private $config_list;       //配置列表
    private $hashs;             //默认hashs
    private $map;               //条件
    private $resolution_db;     //分辨率表dsd
    private $resolution_list;   //分辨率列表
    private $category_db;       //类别表
    private $category_list_parent;     //类别列表
    private $category_list_child;     //类别列表
    private $soft_db;           //软件表
    private $soft_list;         //软件列表
    private $soft_note_db;      //软件附属表
    private $soft_note_list;    //软件附属列表
    private $soft_thumb_db;      //软件图片表
    private $soft_thumb_list;    //软件图片列表
    private $soft_file_db;      //软件附属表
    private $soft_file_list;    //软件附属列表
    private $operating_db;      //运营商表
    private $operating_list;    //运营商列表
    private $scan_result_db;    //软件扫描结果表
    private $scan_result_list;  //软件扫描结果列表
    private $softid;            //软件id
    private $sid;               //临时ID
    private $apklist;           //APK列表
    private $imagelist;         //APK列表
    private $order;             //排序
    private $oldsoftlist;       //原软件信息
    private $denyid;            //驳回ID
    private $denymsg;           //驳回信息
    private $returnurl;         //返回地址
	private $pid;
 
    public $permit_arr = array("new" => "add", "update" => "update", "edit" => "update","reject" => "add", "below" => "add");
    public $admin_data_log_sql = "SELECT  ss.softid appid,ss.softname title,ss.dev_name developer,ss.package package,ss.category_id category,ss.version version,ss.version_code versioncode,ssf.min_firmware minsdkversion,ss.upload_tm releasedate,ss.intro description,ss.score hotlevel,ss.tags keyword,ssf.url apkurl,ss.total_downloaded downloadnumber,ssf.filesize packagesize,ssf.iconurl smallmaplink FROM sj_soft AS ss LEFT JOIN sj_soft_file AS ssf on ss.softid = ssf.softid  where ss.softid = ";

    public function index() {
        exit;
    }
	
	//数据包软件列表
	public function mobilesoftlist(){
		if ($_GET['type'] == 'below'){
			$where = 'AND hide=3';
			$this->assign('type', 'below');
		} else {
			$where = 'AND hide=1';
		}
		if (isset($_POST['flag']) && !empty($_POST['flag'])){
			$softname = trim($_POST['softname']);
			$packagename = trim($_POST['packagename']);
			$archivename = trim($_POST['archivename']);
			$devname = trim($_POST['devname']);
			$descname = trim($_POST['descname']);
			if ($softname)	$where .= " AND ss.softname='$softname'";
			if ($packagename)	$where .= " AND ss.package='$packagename'";
			if ($archivename)	$where .= " AND '$archivename'";
			if ($devname)	$where .= " AND ss.dev_name='$devname' OR ss.dev_enname='$devname'";
			if ($descname)	$where .= " AND ss.intro like '%$descname%'";
		}
//		$soft = M('soft');
//		$soft_file = M('soft_file');
		$archive_file = M('archives_file');
		import("@.ORG.Page");
		$count = $archive_file->join('INNER JOIN sj_soft ss ON sj_archives_file.softid = ss.softid')->join('sj_soft_file ssf ON ss.softid=ssf.softid')->where("sj_archives_file.`arc_status`=1 $where")->count();
		$Page = new Page($count, 10);
		$show = $Page->show();
		$str = 'sj_archives_file.id, sj_archives_file.arc_name, sj_archives_file.arc_filesize, ss.*, ssf.iconurl, sc.name';
		$archivefile = $archive_file->join('INNER JOIN sj_soft ss ON sj_archives_file.softid = ss.softid')->join('sj_soft_file ssf ON ss.softid=ssf.softid')->join('sj_category sc ON sc.category_id = TRIM(BOTH "," FROM ss.category_id)')->where("sj_archives_file.`arc_status`=1 $where")->field($str)->order('sj_archives_file.arc_last_refresh desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		if ($archivefile){
			//start/////////////////////////////////////////////////////////////////
			$known_abis = array(
	            'armeabi' => ABI_ARMEABI,
	            'armeabi-v7a' => ABI_ARMEABI_V7A,
	            'x86' => ABI_X86,
	            'mips' => ABI_MIPS,
	        );
			//安全扫描结果结束
	        $operating_db = D('Sj.Operating');
	        $operating_list = $operating_db->where('only_auth=0')->field('oid,mname')->select();
	        $lists = '';
	        for ($i = 0; $i < count($operating_list); $i++) {
	            $lists[$operating_list[$i]['oid']] = $operating_list[$i]['mname'];
	        }
	        $lists[0] = '不隐藏';
		      
	        $n = count($archivefile);
			for ($i=0; $i<$n; $i++){
				foreach ($known_abis as $abi_key => $abi_value) {
                    if ($abi_value & $archivefile[$i]['abi'] || $archivefile[$i]['abi'] == 0)
                        $archivefile[$i]['abis'][] = $abi_key;
                }
                $archivefile[$i]['operatorhide'] = $lists[$archivefile[$i]['operatorhide']];
			}
			//end/////////////////////////////////////////////////////////////////
		}
		
		/*echo '<pre>';
		print_r($archivefile);
		echo '</pre>';*/
		$this->assign('page', $show);
		$this->assign('softlist', $archivefile);
		$this->display();
	}
	
	//添加数据包软件 添加/更新apk 显示
	public function apk_upload(){
		$this->display();
	}
	
	//添加数据包软件 添加/更新apk
	public function apk_upload_do(){
		$model = new Model();
		$path = date("Ym/d");
		if($_FILES){
			if (!empty($_FILES['apkname']['size'])) {
				//APK上传
				//print_r($_FILES);exit;
				$path = date("Ym/d/");
				$config = array(
					'multi_config' => array(
						'apkname' => array(
							'savepath' => UPLOAD_PATH . '/apk/' . $path,
							'saveRule' => 'packagename'
						)
					)
				);
				$apk = $this->_uploadapk(true, $config);		
			}

			$where['package'] = $apk['apk'][0]['packagename'];
			$list = $apk['apk'][0];
			$where['status'] = 1;
			$data['package'] = $list['packagename'];
			$data['abi'] = $list['abi'];
			$data['status'] = 0;
			$data['hide'] = 1;
			$data['version'] = $list['versionName'];
			$data['version_code'] = $list['versionCode'];
			
			$data_f['package_status'] = 0;
			$data_f['apk_name'] = $list['packagename'];
			$data_f['url'] = $list['apkurl_db'];
			$data_f['filesize'] = $list['size'];
			$data_f['iconurl'] = $list['iconurl_db'];
			$data_f['abi'] = $list['abi'];
			$data_f['apk_icon'] = $list['apk_icon_db'];
			
			$result = $model -> table('sj_soft') -> where($where) -> select();
			$tem_result = $model -> table('sj_soft') -> add($data);
			$data_f['softid'] = $tem_result;
			$tem_arch_result = $model -> table('sj_soft_file') -> add($data);
			
			if($tem_result && $tem_arch_result){
				if(!$result){
					$from = 0;
					sleep(3);
					$this -> assign('jumpurl','/index.php/'.GROUP_NAME.'/Mobile/archives_upload');
				}else if($result[0]['version_code'] > $apk['apk'][0]['version_code']){
					$from = 1;
					sleep(3);
					$this -> assign('jumpurl','/index.php/'.GROUP_NAME.'/Mobile/apk_upload');
				}else if($result[0]['version_code'] == $apk['apk'][0]['version_code']){
					$from = 2;
				}else if($result[0]['varsion_code'] < $apk['apk'][0]['version_code']){
					$from = 3;
				}
			}else{
				$this -> error("对不起,apk上传失败");
				$this -> assign('jumpurl','/index.php/'.GROUP_NAME.'/Mobile/apk_upload');
			}
		}
		$this -> assign('from',$from);
		$this -> assign('softid',$tem_result);
		$this -> display("apk_upload");
	}
	
	//添加数据包软件 添加/更新数据包
	public function packet_upload(){
	
	}

	//添加数据包软件 填写软件详细信息 
	public function add_soft(){
	
	}

	//编辑数据包软件 编辑软件详细信息
	public function update_soft(){
	
	
	}
	
	//下架/恢复数据包软件
	public function del_soft(){
	
	
	}
	
	//数据包软件下架
	public function soft_undercarriage(){
        $this->softid = $_GET['softid'];
        if (empty($this->softid)) {
            $this->ajaxReturn(0,'非法操作失败,如频繁出现，请联系管理员！',0);
        }
        $this->soft_db = M('soft');

        $map = '';
        $map['status'] = '1';
        $map['hide'] = '3';
        $map['last_refresh'] = time();
        import("@.ORG.Http");
        $this->map = '';
        if (strstr($this->softid, ",")) {
            $this->map = " `softid` in (" . substr($this->softid, 1) . ") ";
        } else {
            $this->map['softid'] = $this->softid;
        }
        if (false !== $this->soft_db->where($this->map)->save($map)) {
            $soft_info = $this->soft_db->where($this->map)->select();
            $date = $map['last_refresh'];
            $modify_type = "delete";
            if (strstr($this->softid, ",")) {
                $softidarr = explode(',', substr($this->softid, 1));
                foreach ($softidarr as $k => $v) {
                    $this->update_data_log($this->softid, $modify_type, $date);
					$where_log['softid'] = $v;
					$where_log['status'] = 1;
					$log_result = $this -> soft_db -> where($where_log) -> field('package') -> select();
                    $this->writelog('下架了软件ID为' . $v . ',软件包名为'.$log_result[0]['package'].'的软件,原因为' . $map['deny_msg']);
                }
                $this->success("下架成功！");
            } else {
                $this->update_data_log($this->softid, $modify_type, $date);
				$where_log['softid'] = $this -> softid;
				$where_log['status'] = 1;
				$log_result = $this -> soft_db -> where($where_log) -> field('package') -> select();
                $this->writelog('下架了软件ID为' . $this->softid . ',包名为'.$log_result[0]['package'].'的软件,下架原因为' . $map['deny_msg']);
                $this->ajaxReturn(1,'软件ID:'.$this->softid.'下架成功！',1);
            }
        } else {
            $this->ajaxReturn(0,'下架失败！',0);
        }
	}
	
	//下架数据包软件编辑显示
	function soft_edit(){
		if($_GET['fromjs'] == 2){
			$model = new Model();
			$softid = $_GET['softid'];
			$where['softid'] = $softid;
			$where['hide'] = 3;
			$where['status'] = 1;
			$soft_message = $model -> table('sj_soft') -> where($where) -> select();
			$soft_archives = $model -> table('sj_sj_archives_file') -> where($where) -> select();
		
		}
	
	
	
	
	}
	
	//下架数据包软件恢复
	function soft_recover(){
	    $this->softid = $_GET['softid'];
        if (empty($this->softid)) {
            $this->ajax(0, "非法操作失败,如频繁出现，请联系管理员！", 0);
        }
        $this->soft_db = M('soft');
        $packages = '';
        $this->map['softid'] = $this->softid;
        $packages = $this->soft_db->where($this->map)->getField('package');
        $this->map = '';
        $this->map['package'] = $packages;
        $this->soft_list = $this->soft_db->where($this->map)->count('softid');

        if ($this->soft_list > 1) {
            $this->ajaxReturn(0,'对不起,由于系统中存在该软件的其他信息，因此无法撤销，如有特殊需要，请联系技术人员！',0);
        } else {
            $map = '';
            $map['status'] = '1';
            $map['hide'] = '2';
            $this->map = '';
            $this->map['softid'] = $this->softid;
            $this->soft_db->where($this->map)->save($map);
			$where_log['softid'] = $this -> softid;
			$where_log['status'] = 1;
			$log_result = $this -> soft_db -> where($where_log) -> field('package') -> select();
            $this->writelog('撤销了软件ID为' . $this->softid . '的软件,软件包名为:'.$log_result[0]['package']);
            $this->ajaxReturn(1,'软件ID:'.$this->softid.'撤销成功！',1);
        }
	}

    function update_data_log($softid, $type, $date) {
        $ymd = date("Y-m-d", $date);
		$H = date("H",$date);
        $soft_db = M('soft');
        $dir = P_LOG_DIR . "/admin.goapk.com/" . $ymd;
        if (!is_dir($dir))
            mkdir($dir, 0755);
        $sql = $this->admin_data_log_sql . $softid;
        $result = $soft_db->query($sql);
        $soft_modify = $result[0];
		if($soft_modify){
	        $soft_modify['type'] = $type;
	        $soft_modify['date'] = $date;
	        $h = date("H", $date);
			permanentlog("data_modify_{$H}.log",json_encode($soft_modify));
		}
        //file_put_contents("{$dir}/data_modify.log", json_encode($soft_modify) . "\n", FILE_APPEND);
    }
    
    function request_data_log($method, $request_url, $softid, $type, $date) {
        $dir = P_LOG_DIR . "/admin.goapk.com/" . date("Y-m-d", $date);
        if (!is_dir($dir))
            mkdir($dir, 0755);
        file_put_contents($dir . "/curl.log", date("Y-m-d H:i:s", $date) . '=> Took  method ' . $method . ' to send a request to ' . $request_url . "in to softid " . $softid . " " . $type . " \n", FILE_APPEND);
    }
}




?>
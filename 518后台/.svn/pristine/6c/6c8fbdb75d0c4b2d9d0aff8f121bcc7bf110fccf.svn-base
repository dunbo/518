<?php

/**
 * 安智网产品管理平台 软件管理之控制器
 * ============================================================================
 * 版权所有 2009-2011 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * by:金山 2011.4.25
 * ----------------------------------------------------------------------------
  hide  //0历史 1正常 2新软件 3下架 4编辑软件 5更新软件 6驳回
 */
define('ABI_ARMEABI', 1);
define('ABI_ARMEABI_V7A', 2);
define('ABI_X86', 4);
define('ABI_MIPS', 8);

class SoftAction extends CommonAction {

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

    //软件管理__软件添加_显示
    public function softadd() {
        //分辨率
        $this->resolution_db = M('resolution');
        $this->resolution_list = $this->resolution_db->field('resolutionid,note')->where('status=1')->select();
        $this->assign('resolutionlist', $this->resolution_list);

        //分类
       /*  $this->category_db = M('category');
        $this->category_list_parent = $this->category_db->field('category_id,name')->where('status=1 and parentid=0')->order('orderid')->select();

        $this->category_list_child = $this->category_db->field('category_id,name,parentid')->where('status=1 and parentid!=0')->order('orderid')->select();

        for ($i = 0; $i < count($this->category_list_parent); $i++) {

            for ($j = 0; $j < count($this->category_list_child); $j++) {
                if ($this->category_list_child[$j]['parentid'] == $this->category_list_parent[$i]['category_id']) {
                    $this->category_list_parent[$i]['child'][] = $this->category_list_child[$j];
                }
            }
        }
        $this->assign('categorylist', $this->category_list_parent); */
		$category = D('Sj.Category');
		$array_config=array(
			"categoryid"=>"categoryid[]",
			"selected"=>$_GET['categoryid']
		);
		$conf_list = $category->getCategory($array_config);
        $this->assign('conflist',$conf_list);

        //固件版本
        $this->conf_db = D('Sj.Config');
        $this->config_list = $this->conf_db->field('configname,configcontent')->where('config_type="firmware"')->select();
        $this->assign('configlist', $this->config_list);

        //运营商
        $this->operating_db = D('Sj.Operating');
        $this->operating_list = $this->operating_db->where('only_auth=0')->field('oid,mname')->select();
        $this->assign('operatinglist', $this->operating_list);

        $this->display();
    }

    public function soft_add_channel() {
        $channel_model = M('channel');
        $channels = $channel_model->field("`cid`,`chname`")->where(array('status' => 1))->select();
        $this->assign('channel_list', $channels);
        $cook = substr($_COOKIE['cids'], 2, -1);
        $cookstr = strtr($cook, array('_' => ','));
        $chl = $channel_model->field("`cid`,`chname`")->where(' `cid` in (' . $cookstr . ')')->select();
        $this->assign('chl_list', $chl);
        $time[0] = date("Y-m-d", time());
        $time[1] = date("Y-m-d", (time() + (7 * 86400)));
        $this->assign('time', $time);
        $this->softadd();
    }

    //软件管理__软件添加_上传
    public function upload() {
        $cids = array();
        if (isset($_POST['channel'])) {
            $cids = array();
            foreach ($_POST['channel'] as $cid) {
                if ($cid >= 0)
                    $cids[] = $cid;
            }
            $cids = array_unique($cids);
            if (count($cids) > 0) {
                $s = implode(',', $cids);
                $s = ",{$s},";
                $this->soft_list['channel_id'] = $s;
            }
        }
        $this->soft_list['softname'] = trim($_POST['softname']);
		$this->soft_list['type'] = trim($_POST['type']);
        $this->soft_list['ename'] = trim($_POST['ename']);
        $this->soft_list['dev_name'] = trim($_POST['dev_name']);
        $this->soft_list['dev_enname'] = trim($_POST['dev_enname']);
        $this->soft_list['category_id'] = implode(',', $_POST['categoryid']);
        $this->soft_list['category_id'] = ',' . $this->soft_list['category_id'] . ',';
        $this->soft_list['update_type'] = 1;
        $thumbcid = '';
        $thumbcid = $_POST['categoryid'];
        if ($thumbcid[0] == 0) {
            $this->error('请选择软件类别');
        }
        if ($thumbcid[0] == $thumbcid[1] && $thumbcid[0] != 0) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error('对不起,软件类别，不可重复');
        }
        if ($thumbcid[1] == $thumbcid[2] && $thumbcid[1] != 0) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error('对不起,软件类别，不可重复');
        }
        if ($thumbcid[0] == $thumbcid[2] && $thumbcid[1] != 0) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error('对不起,软件类别，不可重复');
        }

        if (!eregi("[\u4e00-\u9fa5]", $this->soft_list['ename']) && $this->soft_list['ename'] != '') {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error('对不起,软件英文名暂不支持中文');
            exit;
        }
        if (empty($this->soft_list['softname'])) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error('软件名为必填项');
        }
        if (empty($this->soft_list['dev_name'])) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error('开发者姓名为必填项');
        }
        if (mb_strlen($_POST['intro'], 'utf-8') > 1500) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error('对不起，内容不得超过1500字');
        }
        if (mb_strlen($_POST['note'], 'utf-8') > 1500) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error('对不起，备注不得超过1500字');
        }

        if (empty($_POST['intro'])) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . $this->returnurl . '');
            $this->error('软件描述为必填项');
        }
		
		$util = D('Sj.Util');
		$param = array(
			'softname' => $this->soft_list['softname'],
			'dev_name' => $this->soft_list['dev_name'],
			'intro' => $this->soft_list['intro'],
		);
		
		$result = $util->filter_word($param);
	    if ($result['softname'][0] == false) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error("软件名含有非法字符 {$result['softname'][1]}，请重新编辑后提交！");
        }
		
		if ($result['dev_name'][0] == false) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error("开发者含有非法字符 {$result['dev_name'][1]}，请重新编辑后提交！");
        }
		if ($result['intro'][0] == false) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error("软件描含有非法字符 {$result['intro'][1]}，请重新编辑后提交！");
        }


        $num = count($_FILES["image"]["name"]);
        for ($i = 0; $i < $num; $i++) {
            if (empty($_FILES["image"]["name"][$i])) {
                unset($_FILES["image"]["name"][$i]);
                unset($_FILES["image"]["type"][$i]);
                unset($_FILES["image"]["tmp_name"][$i]);
                unset($_FILES["image"]["error"][$i]);
                unset($_FILES["image"]["size"][$i]);
            }
        }
        $num = count($_FILES["apk"]["name"]);
        for ($i = 0; $i < $num; $i++) {
            if (empty($_FILES["apk"]["name"][$i])) {
                unset($_FILES["apk"]["name"][$i]);
                unset($_FILES["apk"]["type"][$i]);
                unset($_FILES["apk"]["tmp_name"][$i]);
                unset($_FILES["apk"]["error"][$i]);
                unset($_FILES["apk"]["size"][$i]);
            }
        }

        if (empty($_FILES['image']['size'])) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error('无图无真相');
        }
        if (!empty($_FILES['apk']['size'])) {
            //APK上传
            $path = date("Ym/d/");
            $config = array(
                'multi_config' => array(
                    'apk' => array(
                        'savepath' => UPLOAD_PATH . '/apk/' . $path,
                        'saveRule' => 'packagename'
                    ),
                    'image' => array(
                        'savepath' => UPLOAD_PATH . '/thumb/' . $path,
                        'saveRule' => 'thumbname',
                    )
                ),
                'flip' => true,
				'img_p_size' => 1024*30,  //图片常规压缩大小
				'img_p_width'=> 320, //图片常规压缩宽度
				'img_p_height'=> 534, //图片常规压缩宽度
				'img_s_size' => 1024*10, //图片缩略图大小
				'img_s_width' => 150, //缩略图宽
				'img_ext' => '.jpg', //截图文件扩展名
            );
            $this->lists = $this->_uploadapk(true, $config);
        } else {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error('对不起，APK必须上传');
        }
        $this->apklist = $this->lists['apk'];
        $this->imagelist = $this->lists['image'];

        //sort($this->apklist);
        //sort($this->imagelist);
        //dump($this->apklist);
        //dump($this->imagelist);

        $package = $this->apklist[0]['packagename'];
        $nVersionCode = $this->apklist[0]['versionCode'];
        $nVersionName = $this->apklist[0]['versionName'];
        $nAbi = $this->apklist[0]['abi'];
        $abiType = $versionCodeType = $versionNameType = $apktype = 'true';
        for ($i = 0; $i < count($this->apklist); $i++) {
            $abi = $this->apklist[$i]['abi'];
            if ($this->apklist[$i]['packagename'] != $package) {
                $apktype = false;
            }

            if ($this->apklist[$i]['versionCode'] != $nVersionCode) {
                $versionCodeType = false;
            }

            if ($this->apklist[$i]['versionName'] != $nVersionName) {
                $versionNameType = false;
            }

            if ($this->apklist[$i]['abi'] != $nAbi) {
                $abiType = false;
            }
        }

        if ($apktype == false) {

            for ($i = 0; $i < count($this->apklist); $i++) {
                unlink($this->apklist[$i]['savepath']);
                unlink($this->apklist[$i]['icon']);
            }
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error('添加失败！包名不统一');
        }


        if ($versionCodeType == false) {

            for ($i = 0; $i < count($this->apklist); $i++) {
                unlink($this->apklist[$i]['savepath']);
                unlink($this->apklist[$i]['icon']);
            }
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . $this->returnurl);
            $this->error('编辑失败！version code不统一');
        }

        if ($versionNameType == false) {

            for ($i = 0; $i < count($this->apklist); $i++) {
                unlink($this->apklist[$i]['savepath']);
                unlink($this->apklist[$i]['icon']);
            }
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . $this->returnurl);
            $this->error('编辑失败！version name不统一');
        }

        if ($abiType == false) {
            for ($i = 0; $i < count($this->apklist); $i++) {
                unlink($this->apklist[$i]['savepath']);
                unlink($this->apklist[$i]['icon']);
            }
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . $this->returnurl);
            $this->error('编辑失败！abi不统一');
        }



        $this->soft_list['package'] = $this->apklist[0]['packagename'];
        $this->soft_list['dev_name'] = trim($_POST['dev_name']);
        $this->soft_list['dever_email'] = $_POST['dever_email'];
        $this->soft_list['dever_page'] = $_POST['dever_page'];
        $this->soft_list['costs'] = 0;
        $this->soft_list['version'] = $this->apklist[0]['versionName'];
        $this->soft_list['version_code'] = $this->apklist[0]['versionCode'];
        $this->soft_list['intro'] = $_POST['intro'];
        $this->soft_list['abi'] = $this->apklist[0]['abi'];

        $this->soft_db = M('soft');
        $this->soft_db->ping();
        $this->soft_db->flush();

        $thumbmap = array();

        $thumbmap['package'] = $this->soft_list['package'];
        $thumbmap['status'] = 1;
        $thumbmap['_string'] = "hide <> 0";
        $thumbmap['abi'] = $this->soft_list['abi'];
        //$thumbmap['version'] = $this->soft_list['version'];
        //$thumbmap['version_code'] = $this->soft_list['version_code'];

        $softcount = $this->soft_db->where($thumbmap)->count('softid');
        if (!empty($softcount) && empty($cids)) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error('添加失败！该软件已收录');
        }

        //总下载量
        $this->soft_db->query("select now()");
        $map = array(
            'package' => $this->soft_list['package'],
            'status' => 1,
            'hide' => 1,
        );

        $oldsoft = $this->soft_db->where($map)->field('total_downloaded,total_downloaded_detain,total_downloaded_add, score')->find();

        if ($oldsoft) {
            $this->soft_list['total_downloaded'] = $oldsoft['total_downloaded'];
            $this->soft_list['total_downloaded_detain'] = $oldsoft['total_downloaded_detain'];
            $this->soft_list['total_downloaded_add'] = $oldsoft['total_downloaded_add'];
            $this->soft_list['score'] = $oldsoft['score'];
        } else {
            $this->soft_list['score'] = 0;
            $this->soft_list['total_downloaded'] = 0;
            $this->soft_list['total_downloaded_detain'] = 0;
            $this->soft_list['total_downloaded_add'] = 0;
        }

        $this->soft_list['claim_status'] = 0;


        $this->soft_list['tags'] = $_POST['tags'];
        $this->soft_list['upload_tm'] = time();
        $this->soft_list['last_refresh'] = time();


        if (empty($this->soft_list['tags'])) {
            $tihis->soft_list['tags'] = $this->soft_list['softname'] . $this->soft_list['package'];
        }
        $tags = '';
        $tags = explode(',', $this->soft_list['tags']);
        if (count($tags) > 5) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . (empty($cids) ? 'softadd' : 'soft_add_channel'));
            $this->error("最多5个关键字，谢谢");
        }

        //默认为认证中
        $this->soft_list['safe'] = 0;

        $this->soft_db->query("select now()");
        $this->soft_list['deny_msg'] = '首次后台上传';

        //运营商是否隐藏
        $operating = $_POST['operating'];


        if (!empty($operating)) {

            $this->soft_list['operatorhide'] = implode(',', $_POST['operating']);

            //插入运营商隐藏列表
        } else {
            $this->soft_list['operatorhide'] = 0;
        }
        $this->soft_list['hide'] = empty($cids) ? 1 : 1024;
        $this->soft_list['status'] = 1;

        //软件表
        $add_soft_list = $this->soft_list;
        $this->soft_db->ping();
        $this->softid = $this->soft_db->data($add_soft_list)->add();
        if (false == $this->softid) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . (empty($cids) ? 'softadd' : 'soft_add_channel'));
            $this->error("添加软件失败,数据插入发生错误！1");
        }


        $task_client = get_task_client();
        $task_client->doHighBackground("sync_filter_cache", json_encode(array("softid" => $this->softid, 'line' => __LINE__)));
        $task_client->doBackground("refresh_lack", json_encode(array("softid" => $this->softid)));

        $this->soft_note_list['auth'] = $_POST['auth'] ? $_POST['auth'] : 0;
        $this->soft_note_list['note'] = $_POST['note'];
        $this->soft_note_list['package'] = $this->apklist[0]['packagename'];


        $this->soft_note_db = M('soft_note');           //软件表
        if (false == $this->soft_note_db->add($this->soft_note_list)) {

            $thumbmap = '';
            $thumbmap['package'] = $this->soft_note_list['package'];
            $softnotecount = $this->soft_note_db->where($thumbmap)->count();
            if (empty($softnotecount)) {
                $this->soft_db->where('softid=' . $this->softid)->delete();
                $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/User/useradd');
                $this->error("添加软件失败,数据插入发生错误！2");
            }
        }


        $this->soft_thumb_db = M('soft_thumb');
        for ($i = 0; $i < count($this->imagelist); $i++) {
            $this->soft_thumb_list['softid'] = $this->softid;
            $this->soft_thumb_list['url'] = $this->imagelist[$i]['url'];
            $this->soft_thumb_list['image_raw'] = $this->imagelist[$i]['url_original'];
            $this->soft_thumb_list['image_thumb'] = $this->imagelist[$i]['url_resize'];
            $this->soft_thumb_list['status'] = 1;
            $this->soft_thumb_list['rank'] = $this->imagelist[$i]['key'];
            $this->soft_thumb_list['upload_time'] = time();
            $this->soft_thumb_list['last_refresh'] = time();
            if (false == $this->soft_thumb_db->add($this->soft_thumb_list)) {

                $this->soft_db->where('softid=' . $this->softid)->delete();
                $this->soft_note_db->where('package=' . $this->soft_note_list['package'])->delete();
                $this->soft_thumb_db->where('softid=' . $this->softid)->delete();

                $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . (empty($cids) ? 'softadd' : 'soft_add_channel'));
                $this->error("添加软件失败,数据插入发生错误！3");
            }
        }


        $this->soft_file_db = M('soft_file');

        $this->resolution_list = $_POST['resolution'];
        $minfirame = '';
        $maxfirame = '';

        $minfirame = $_POST['minfirame'];
        $maxfirame = $_POST['maxfirame'];

        $sql = "select * from sj_soft_permission_details";
        $allperms = $this->soft_db->query($sql);
        $allperms_mp = array();
        foreach ($allperms as $val) {
            $allperms_mp[$val['name']] = $val['id'];
        }
        $sql = "select * from sj_soft_uses_library_details";
        $alllibs = $this->soft_db->query($sql);
        $alllibs_mp = array();
        foreach ($alllibs as $val) {
            $alllibs_mp[$val['name']] = $val['id'];
        }

        $soft_file_safe_db = M('soft_scan_result');
        $soft_file_insert = array();
        for ($i = 0; $i < count($this->apklist); $i++) {
            $key = $this->apklist[$i]['key'];
            $maxfirame[$key] = empty($maxfirame[$key]) ? 0 : $maxfirame[$key];
            $check_key = "{$this->apklist[$i]['supports-screens']}_{$this->apklist[$i]['min_sdk_ver']}_{$this->apklist[$i]['abi']}";
            if (isset($soft_file_checked[$check_key])) {
                for ($j = 0; $j < count($this->apklist); $j++) {
                    unlink($this->apklist[$j]['savepath']);
                }

                foreach ($soft_file_insert as $soft_file_id) {
                    if (empty($soft_file_id))
                        continue;
                    $this->soft_file_db->where('id=' . $soft_file_id)->delete();
                }
                //$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Soft/'.$this->returnurl.'');
                $this->error("不允许上传分辨率、最小固件、abi类型相同的包名");
                break;
            }


            $this->soft_file_list['softid'] = $this->softid;
            $this->soft_file_list['package_status'] = 1;
            $this->soft_file_list['apk_name'] = $this->apklist[$i]['packagename'];
            $this->soft_file_list['url'] = $this->apklist[$i]['apkurl_db'];
            $this->soft_file_list['filesize'] = $this->apklist[$i]['size'];
            $this->soft_file_list['screen'] = $this->apklist[$i]['supports-screens'];
            $this->soft_file_list['resolutionid'] = $this->resolution_list[$key];

            $this->soft_file_list['min_firmware'] = $this->apklist[$i]['min_sdk_ver'];
            $this->soft_file_list['max_firmware'] = $maxfirame[$key];

            $this->soft_file_list['iconurl'] = $this->apklist[$i]['iconurl_db'];
            $this->soft_file_list['upload_time'] = time();
            $this->soft_file_list['last_refresh'] = time();
            $this->soft_file_list['safe'] = 0;
            $this->soft_file_list['abi'] = $this->apklist[$i]['abi'];
            $this->soft_file_list['apk_icon'] = $this->apklist[$i]['apk_icon_db'];
            /* md5,sha-1加密 */
            $this->soft_file_list['md5_file'] = md5_file(UPLOAD_PATH . $this->apklist[$i]['apkurl_db']);
            $this->soft_file_list['sha1_file'] = sha1_file(UPLOAD_PATH . $this->apklist[$i]['apkurl_db']);

            include_once(SERVER_ROOT . '/tools/functions.php');
            $this->soft_file_list['advertisement'] = test_apk_for_3rdparty(UPLOAD_PATH . $this->apklist[$i]['apkurl_db']);

            $type = $this->soft_file_db->add($this->soft_file_list);
            if (false == $type) {

                $this->soft_db->where('softid=' . $this->softid)->delete();
                $this->soft_note_db->where('package=' . $this->soft_note_list['package'])->delete();
                $this->soft_thumb_db->where('softid=' . $this->softid)->delete();

                $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . (empty($cids) ? 'softadd' : 'soft_add_channel'));
                $this->error("添加软件失败,数据插入发生错误！4");
            }
			//张辉时间添加语句
			if($_POST['date0']&&$_POST['date1']){
				$zh_soft_time=M("soft_time");
				$zh_soft_time->add(array("softid" => $this->softid, "start_time" => strtotime($_POST['date0']), "end_time" => strtotime($_POST['date1'])));
			}
			//张辉时间添加语句结束
            $soft_file_insert[$check_key] = $type;
            //UPLOAD_PATH. '/apk/'. $path  get_apkinfo_app2sd($file)
            //sj_soft_app_info id, info_desc, name
            //sj_soft_app2sd id fileid app2info_id
            $sai_db = M("soft_app_info");
            $sa2sd_db = M("soft_app2sd");
            $app2sd_arr = array("android:installLocation=auto", "android:installLocation=preferExternal", "android:installLocation=internalOnly");
            $app2sd = get_apkinfo_app2sd(UPLOAD_PATH . $this->apklist[$i]['apkurl_db']);
            if (in_array($app2sd, $app2sd_arr)) {
                $sai_list = $sai_db->getField("info_desc,id");
                $sai_id = $sai_list[$app2sd];
                $data['fileid'] = $this->softid;
                $data['app2info_id'] = $sai_id;
                $sa2sd_db->add($data);
            }
            //end get_apkinfo_app2sd
            //权限
            foreach ($this->apklist[$i]['permission'] as $p) {
                if (!isset($allperms_mp[$p]))
                    continue;
                $sql = "insert into sj_soft_permission (`fileid`, `permissionid`) values ({$type}, {$allperms_mp[$p]})";
                $this->soft_file_db->query($sql);
            }
            foreach ($this->apklist[$i]['library'] as $l) {
                if (!isset($alllibs_mp[$l])) {
                    $sql = "insert into sj_soft_uses_library_details (`name`) values ('{$l}')";
                    $this->soft_file_db->query($sql);
                }
            }
            foreach ($this->apklist[$i]['library-optional'] as $l) {
                if (!isset($alllibs_mp[$l])) {
                    $sql = "insert into sj_soft_uses_library_details (`name`) values ('{$l}')";
                    $this->soft_file_db->query($sql);
                }
            }
            $sql = "select * from sj_soft_uses_library_details";
            $alllibs = $this->soft_db->query($sql);
            $alllibs_mp = array();
            foreach ($alllibs as $val) {
                $alllibs_mp[$val['name']] = $val['id'];
            }
            foreach ($this->apklist[$i]['library'] as $l) {
                $sql = "insert into sj_soft_uses_library (`fileid`,`libraryid`,`type`) values ({$type},{$alllibs_mp[$l]},1)";
                $this->soft_file_db->query($sql);
            }
            foreach ($this->apklist[$i]['library-optional'] as $l) {
                $sql = "insert into sj_soft_uses_library (`fileid`,`libraryid`,`type`) values ({$type},{$alllibs_mp[$l]},0)";
                $this->soft_file_db->query($sql);
            }
            //安全认证发送
            include_once(SITE_PATH . "../model/scanSoft.php");

            $soft_hash = scanSoft::getSoftHash(UPLOAD_PATH . $this->soft_file_list['url']);

            $safelist = array(
                'sfid' => $type,
                'hash' => $soft_hash,
                'provider' => 1,
                'time_req' => time(),
            );
            $soft_file_safe_db->add($safelist);
            $params = array(
                "download_url" => ATTACHMENT_HOST . $this->soft_file_list['url'],
                "soft_hash" => $soft_hash,
            );
            $re = scanSoft::requestPostQQ($params);

            $safelist = array(
                'sfid' => $type,
                'hash' => $soft_hash,
                'provider' => 2,
                'time_req' => time(),
            );
            $soft_file_safe_db->add($safelist);
            $re = scanSoft::requestPostAQGJ($params);
			
				//网秦安全认证
	
			$scan_result = array(
				'sfid' => $type,
				'hash' => $soft_hash,
				'provider' => 3,
				'time_req' => time(),
			);
			$soft_file_safe_db -> add($scan_result);
			$re = scanSoft::requestGetWQ($params);
        }
        $modify_type = "add";
        $date = time();
        $this->update_data_log($this->softid, $modify_type, $date);  //软件添加记录日志
        $this->writelog('上传了ID为[' . $this->softid . ']软件');
        $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_list');
        $this->success("添加成功！");
    }
    
	
	//软件管理__软件全局搜索
	public function soft_global_search (){
	    
        $this->map = 'status = 1 AND hide in(1,2,3,4,5,7)';
        
        $this->order = 'last_refresh desc';
        
		$param = '';
        import("@.ORG.Input");
		$Input = Input::getInstance();

		if(!empty($_GET['softid'])){
		   $softid = $Input->REQUEST('softid');
		   $this->assign("softid", $softid);
		   $this->map.=' and softid = "'.trim($softid).'"';
		   $param .= "softid/$softid/";
		}
		
		if(!empty($_GET['softname'])){
		   $softname = $Input->REQUEST('softname'); ;
		   $this->assign("softname", $softname);
		   $this->assign("softnameurl", urlencode($softname));
		   $this->map.=' and softname like "%'.trim($softname).'%"';
		   $param .= "softname/".urlencode($softname)."/";
		}
		
		if(!empty($_GET['package'])){
		   $package = $Input->REQUEST('package');
		   $this->assign("package", $package);
		   $this->map.=' and package like "%'.trim($package).'%"';
		   $param .= "package/$package/";
        }
		
		if(!empty($_GET['dev_name'])){
		   $dev_name = $Input->REQUEST('dev_name');
		   $this->assign("dev_name", $dev_name);
		   $this->assign("dev_nameurl", urlencode($dev_name));
		   $this->map.=' and dev_name like "%' . trim($dev_name) . '%"';
		   $param .= "dev_name/".urlencode($dev_name)."/";
		}
		if(!empty($_GET['categoryid'])){
		   $categoryid = $Input->REQUEST('categoryid');
		   $this->assign("categoryid", $categoryid);
		   $this->map.=' and (SELECT find_in_set  (' . trim($categoryid) . ',`category_id`)>0)';
		   $param .= "categoryid/$categoryid/";
		}
		if (!empty($_GET['hide'])) {
		    //0历史 1正常 2新软件 3下架 4编辑软件 5更新软件 6驳回 7 驳回审核
			//safe 2 不安全  1 0 安全
            $hide = $_GET['hide'];
			switch($hide){
			   case 1: $url = "Soft/soft_list/$param";break;
			   case 2: $url = "Soft/soft_new_list/$param";break;
			   case 3: $url = "Soft/soft_new_list/type/below/$param";break;
			   case 4: $url = "Soft/soft_new_list/type/edit/$param";break;
			   case 5: $url = "Soft/soft_new_list/type/update/$param";break;
			   case 7: $url = "Soft/soft_new_list/type/reject/$param";break;
			   default : $url = 'Soft/soft_list/softid/$softid/';
			}
			$this->redirect($url); 
        }

        $this->soft_db = M('soft');
        import("@.ORG.Page");
        $count = $this->soft_db->where($this->map)->count();
        $param = http_build_query($_GET);
        $Page = new Page($count, 20, $param);
        $this->soft_list = $this->soft_db->where($this->map)->field('softid,package,softname,category_id,dev_name,dever_email,hide,safe')->order($this->order)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        //echo $this -> soft_db -> getLastSql();
        $this->category_db = M('category');
		$category = D('Sj.Category');
		$array_config=array(
			"categoryid"=>"categoryid",
			"selected"=>$_GET['categoryid']
		);
		$conf_list = $category->getCategory($array_config);
        $this->assign('conflist',$conf_list);
        $this->category_db = M('category');
	$soft_file = M('soft_file');
		for ($i = 0; $i < count($this->soft_list); $i++) {
			$cateid = substr($this->soft_list[$i]['category_id'],1,-1);
			$categorynames = $this->category_db->field('name')->where('status=1 and category_id = '.$cateid)->select();
			$fileinfo = $soft_file -> where('softid = '.$this -> soft_list[$i]['softid'].' and package_status = 1') -> limit(1)-> select();
			$this -> soft_list[$i]['iconurl'] = $fileinfo[0]['iconurl'];
			foreach ($categorynames as $categoryname) {
				$this->soft_list[$i]['category'] = $categoryname['name'];
			}
	    }
        
        if ($_GET['p'])
            $this->assign('p', $_GET['p']);
		else
            $this->assign('p', '1');
		
        $this->assign('softlist', $this->soft_list);
       
        $Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);
        $this->display();
	
	}
	
	
    //软件管理__软件列表
    public function soft_list() {
        $known_abis = array(
            'armeabi' => ABI_ARMEABI,
            'armeabi-v7a' => ABI_ARMEABI_V7A,
            'x86' => ABI_X86,
            'mips' => ABI_MIPS,
        );

        $this->map = 'status=1 and hide=1';

        if (isset($_GET['field']) && isset($_GET['order'])) {
            $this->order = trim($_GET['field']) . ' ' . trim($_GET['order']);
        } else {
            $this->order = 'last_refresh desc';
        }

        if (!empty($_GET['softid'])) {
            $this->assign("softid", $_GET['softid']);
            $this->map.=' and softid="' . trim($_GET['softid']) . '"';
        }
        if (!empty($_GET['softname'])) {
            $this->assign("softname", $_GET['softname']);
            $this->map.=' and softname like "%' . trim($_GET['softname']) . '%"';
        }
        if (!empty($_GET['package'])) {
            $this->assign("package", $_GET['package']);
            $this->map.=' and package like "%' . trim($_GET['package']) . '%"';
        }
        if (!empty($_GET['dev_name'])) {
            $this->assign("dev_name", $_GET['dev_name']);
            $this->map.=' and dev_name like "%' . trim($_GET['dev_name']) . '%"';
        }
        if (!empty($_GET['softinfo'])) {
            $this->assign("softinfo", $_GET['softinfo']);
            $this->map.=' and intro like "%' . trim($_GET['softinfo']) . '%"';
        }
        if (!empty($_GET['dev_id'])) {
            $this->assign("dev_id", $_GET['dev_id']);
            $this->map.=' and dev_id="' . trim($_GET['dev_id']) . '"';
        }
        if (isset($_GET['only_search'])) {
            $this->assign("only_search", $_GET['only_search']);
            $s = trim($_GET['only_search']) == 'y' ? 1 : 0;
            $this->map.=' and only_search="' . $s . '"';
        }
        if (!empty($_GET['categoryid'])) {
            $this->assign("categoryid", $_GET['categoryid']);
            $this->map.=' and (SELECT find_in_set  (' . trim($_GET['categoryid']) . ',`category_id`)>0)';
            //$this->map.=' and category_id like "%,'.preg_replace($_POST['categoryid']).',%"';
        }

        //广告
        if (!empty($_GET['advertisement'])) {
            $this->assign("advertisement", $_GET['advertisement']);
            $model = new Model();
            $slist = $model->table('sj_soft_file')->where('package_status = 1 and advertisement & ' . $_GET['advertisement'] . ' <>0')->field('softid')->select();
            foreach ($slist as $info) {
                if (empty($info['softid'])) {
                    continue;
                }
                $sid_arr[] = $info['softid'];
            }
            $where = "(" . implode(",", $sid_arr) . ")";
            $this->map .= ' and softid  in ' . $where;
        }
        $this->assign("operatorhide", '999');
        if ($_GET['operatorhide'] != '999' && $_GET['operatorhide'] != '') {
            $this->assign("operatorhide", $_GET['operatorhide']);
            $this->map.=' and (SELECT find_in_set  (' . trim($_GET['operatorhide']) . ',`operatorhide`)>0)';
            //$this->map.=' and category_id like "%,'.preg_replace($_POST['categoryid']).',%"';
        }

        $_SESSION['admin']['soft_list']['where'] = $this->map;
        $_SESSION['admin']['soft_list']['order'] = $this->order;

        $this->soft_db = M('soft');
        import("@.ORG.Page");
        $count = $this->soft_db->where($this->map)->count();
        $param = http_build_query($_GET);
        $Page = new Page($count, 10, $param);
        $this->soft_list = $this->soft_db->where($this->map)->field('softid,package,softname,version,category_id,dev_name,tags,dever_email,total_downloaded,total_downloaded_detain,total_downloaded_add,(total_downloaded-total_downloaded_detain+total_downloaded_add) as detain,operatorhide,dev_id,score,left(intro,60) intro,last_refresh,only_search,abi,update_type,type')->order($this->order)->limit($Page->firstRow . ',' . $Page->listRows)->select();
       
		$this->category_db = M('category');
        /* $conf_list=$this->category_db->where("status=1 and parentid=0")->field('category_id,name,orderid')->order('orderid')->select();
		foreach($conf_list as $m=>$n){
			
			$lists=$this->category_db->where('status=1 and parentid='.$n['category_id'])->order('orderid')->select();
			$conf_list[$m][$n['category_id']]=$lists;
			 foreach($lists as $key=>$val){
				$lists[$key]['category_id']=$val['category_id'];
				$list_three=$this->category_db->where('status=1 and parentid='.$lists[$key]['category_id'])->order('orderid')->select();
				$conf_list[$m][$n['category_id']][$val['category_id']]=$list_three;
			} 
		} */
		$category = D('Sj.Category');
		$array_config=array(
			"categoryid"=>"categoryid",
			"selected"=>$_GET['categoryid']
		);
		$conf_list = $category->getCategory($array_config);
        $this->assign('conflist',$conf_list);
		//print_r($conf_list);exit;
		
        for ($i = 0; $i < count($this->soft_list); $i++) {
            $softid.=",'" . $this->soft_list[$i]['softid'] . "'";
            $package.=',"' . $this->soft_list[$i]['package'] . '"';
        }

        if ($softid[0] == ',') {
            $softid = substr($softid, 1);
        }
        
        
        $perms_db = M("soft_permission");
        $sql = "select ssp.fileid softid,ssp.permissionid permissionid,sspd.des des from sj_soft_permission as ssp left join
         sj_soft_permission_des as sspd  on ssp.permissionid = sspd.permissionid
             where ssp.fileid in ({$softid})
        ";
        $perms_array = $perms_db->query($sql);
        $perms_infos = array();
        foreach ($perms_array as $info) {
            $perms_infos[$info['softid']][$info['permissionid']] = $info['des'];
        }
        if ($package[0] == ',') {
            $package = substr($package, 1);
        }
        $this->soft_note_db = M('soft_note');
        $map = '';
        $map['package'] = array('in', $package);
        $this->soft_note_list = $this->soft_note_db->field('package,left(note,60) note,auth')->where($map)->select();
        //show soft permission
        $perms_db = M("soft_permission");
        $sql = "select ssp.fileid softid,ssp.permissionid permissionid,sspd.des des from sj_soft_permission as ssp left join
         sj_soft_permission_des as sspd  on ssp.permissionid = sspd.permissionid
             where ssp.fileid in ({$softid})
        ";
        $perms_array = $perms_db->query($sql);
        $perms_infos = array();
        foreach ($perms_array as $info) {
            $perms_infos[$info['softid']][$info['permissionid']] = $info['des'];
        }
        //end show soft permission
        $this->soft_file_db = M('soft_file');
        $map = array();
        $map['softid'] = array('in', $softid);
        $map['package_status'] = 1;
        $this->soft_file_list = $this->soft_file_db->field('id,softid,iconurl,url,advertisement')->where($map)->select();
        
        
        //安全扫描结果开始
        for ($i = 0; $i < count($this->soft_file_list); $i++) {
            $sfid.=",'" . $this->soft_file_list[$i]['id'] . "'";
        }
        if ($sfid[0] == ',') {
            $sfid = substr($sfid, 1);
        }
        $this->scan_result_db = D('soft_scan_result');
        $sql = " SELECT ssr.id,ssr.sfid,ssr.provider,ssr.time_req,ssr.time_rep,ssr.safe,ssf.softid FROM  sj_soft_scan_result AS ssr".
               " LEFT JOIN  sj_soft_file AS ssf  ON ssr.sfid = ssf.id WHERE ssr.sfid IN (".$sfid.")";
        $this->scan_result_list = $this->scan_result_db->query($sql);
        //echo $this -> scan_result_db -> getLastSql();
		for($i=0;$i<count($this->soft_list);$i++){
		     for($j=0;$j<count($this->scan_result_list);$j++){
			      if($this->scan_result_list[$j]['softid'] == $this->soft_list[$i]['softid']){
				       $this->soft_list[$i]['scan_result_list'][] = $this->scan_result_list[$j];
				  }
			 }
		}
        //安全扫描结果结束
    
        $this->operating_db = D('Sj.Operating');
        $this->operating_list = $this->operating_db->where('only_auth=0')->field('oid,mname')->select();
        $this->lists = '';

        for ($i = 0; $i < count($this->operating_list); $i++) {
            $this->lists[$this->operating_list[$i]['oid']] = $this->operating_list[$i]['mname'];
        }
        //dump($this->lists);
        $this->lists[0] = '不隐藏';
        //dump($this->lists);


        $this->category_list_child = $this->category_db->getField('category_id,name');
		//print_r($this->category_list_child);
        //广告信息
        $adlist = $this->ad_select();
        include_once SERVER_ROOT . '/tools/functions.php';

        if ($this->soft_list && $this->soft_file_list) { //known_abis是被写死的 所以对soft_list 和 soft_file_list进行判断以免出现BUG
            for ($i = 0; $i < count($this->soft_list); $i++) {
                for ($j = 0; $j < count($this->soft_note_list); $j++) {
                    if ($this->soft_note_list[$j]['package'] == $this->soft_list[$i]['package']) {
                        $this->soft_list[$i]['note'] = $this->soft_note_list[$j]['note'];
                        $this->soft_list[$i]['auth'] = $this->soft_note_list[$j]['auth'];
                    }
                }
                for ($j = 0; $j < count($this->soft_file_list); $j++) {
                    if ($this->soft_file_list[$j]['softid'] == $this->soft_list[$i]['softid']) {
                        $this->soft_list[$i]['iconurl'] = $this->soft_file_list[$j]['iconurl'];
                        $adv_key = $this->soft_file_list[$j]['advertisement'];
                        $this->soft_list[$i]['advertisement'] = $this->ad($adv_key);
                        //$this->soft_list[$i]['detected_addon'] = test_apk_for_addon('/data/att/m.goapk.com'. $this->soft_file_list[$j]['url']);
                    }
                }
                $cid = explode(',', $this->soft_list[$i]['category_id']);
                for ($k = 0; $k < count($cid); $k++) {
                    $this->soft_list[$i]['category'].=$this->category_list_child[$cid[$k]];
                }
                $oid = explode(',', $this->soft_list[$i]['operatorhide']);
                for ($k = 0; $k < count($oid); $k++) {
                    $this->soft_list[$i]['operatorhides'].=$this->lists[$oid[$k]];
                }

                foreach ($known_abis as $abi_key => $abi_value) {
                    if ($abi_value & $this->soft_list[$i]['abi'] || $this->soft_list[$i]['abi'] == 0)
                        $this->soft_list[$i]['abis'][] = $abi_key;
                }
                $permiss = array('5', '6', '50', '51');
                $permiss_str = '';
                foreach ($perms_infos[$this->soft_list[$i]['softid']] as $key => $info) {
                    if (in_array($key, $permiss) && !empty($info)) {
                        $permiss_str .= $info . "<br/>";
                    }
                }

                $this->soft_list[$i]['permission_desc'] = $permiss_str;
            }
        }


        if ($_GET['p'])
            $this->assign('p', '?p=' . $_GET['p']);
		else
            $this->assign('p', '');

		if (isset($_GET['field'])&&isset($_GET['order']))
            $this->assign('field', trim($_GET['field']));
		    $this->assign('order', trim($_GET['order']));

        $this->assign('softlist', $this->soft_list);
        
        $this->assign('adlist', $adlist);
        $Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);
        $this->display();
    }

    //软件管理__软件编辑_显示
    public function soft_history_view() {
        $known_abis = array(
            'armeabi' => ABI_ARMEABI,
            'armeabi-v7a' => ABI_ARMEABI_V7A,
            'x86' => ABI_X86,
            'mips' => ABI_MIPS,
        );
		
		if(isset($_GET['p'])){
		    $this->assign("returnurl", '/index.php/Sj/Soft/soft_list/p/'.$_GET['p']);
		}else{
		    $this->assign("returnurl", '/index.php/Sj/Soft/soft_list/p/1');
		}
		
        $this->map = 'status=1 and hide=0';

        $this->order = 'last_refresh desc';

        if (!empty($_GET['package'])) {
            $this->assign("package", $_GET['package']);
            $this->map.=" and package='" . trim($_GET['package']) . "'";
        }

        //广告
        if (!empty($_GET['advertisement'])) {
            $this->assign("advertisement", $_GET['advertisement']);
            $model = new Model();
            $slist = $model->table('sj_soft_file')->where('package_status = 1 and advertisement & ' . $_GET['advertisement'] . ' <>0')->field('softid')->select();
            foreach ($slist as $info) {
                if (empty($info['softid'])) {
                    continue;
                }
                $sid_arr[] = $info['softid'];
            }
            $where = "(" . implode(",", $sid_arr) . ")";
            $this->map .= ' and softid  in ' . $where;
        }
        $this->assign("operatorhide", '999');
        if ($_GET['operatorhide'] != '999' && $_GET['operatorhide'] != '') {
            $this->assign("operatorhide", $_GET['operatorhide']);
            $this->map.=' and (SELECT find_in_set  (' . trim($_GET['operatorhide']) . ',`operatorhide`)>0)';
        }

        $_SESSION['admin']['soft_list']['where'] = $this->map;
        $_SESSION['admin']['soft_list']['order'] = $this->order;

        $this->soft_db = M('soft');
        import("@.ORG.Page");
        //echo $this->map;
        $count = $this->soft_db->where($this->map)->count();
        $param = http_build_query($_GET);
        $Page = new Page($count, 15, $param);
        $this->soft_list = $this->soft_db->where($this->map)->field('softid,package,softname,version,category_id,dev_name,dever_email,total_downloaded,total_downloaded_detain,total_downloaded_add,(total_downloaded-total_downloaded_detain+total_downloaded_add) as detain,operatorhide,dev_id,score,left(intro,60) intro,last_refresh,only_search,abi,update_type')->order($this->order)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        //echo $this -> soft_db -> getLastSql();
        //exit;
        $this->category_db = M('category');
        $this->category_list_parent = $this->category_db->field('category_id,name')->where('status=1 and parentid=0')->order('orderid')->select();

        $this->category_list_child = $this->category_db->field('category_id,name,parentid')->where('status=1 and parentid!=0')->order('orderid')->select();

        for ($i = 0; $i < count($this->category_list_parent); $i++) {

            for ($j = 0; $j < count($this->category_list_child); $j++) {
                if ($this->category_list_child[$j]['parentid'] == $this->category_list_parent[$i]['category_id']) {
                    $this->category_list_parent[$i]['child'][] = $this->category_list_child[$j];
                }
            }
        }
        $this->assign('categorylist', $this->category_list_parent);

        for ($i = 0; $i < count($this->soft_list); $i++) {
            $softid.=',"' . $this->soft_list[$i]['softid'] . '"';
            $package.=',"' . $this->soft_list[$i]['package'] . '"';
        }

        if ($softid[0] == ',') {
            $softid = substr($softid, 1);
        }

        $perms_db = M("soft_permission");
        $sql = "select ssp.fileid softid,ssp.permissionid permissionid,sspd.des des from sj_soft_permission as ssp left join
         sj_soft_permission_des as sspd  on ssp.permissionid = sspd.permissionid
             where ssp.fileid in ({$softid})
        ";
        $perms_array = $perms_db->query($sql);
        $perms_infos = array();
        foreach ($perms_array as $info) {
            $perms_infos[$info['softid']][$info['permissionid']] = $info['des'];
        }
        if ($package[0] == ',') {
            $package = substr($package, 1);
        }
        $this->soft_note_db = M('soft_note');
        $map = '';
        $map['package'] = array('in', $package);
        $this->soft_note_list = $this->soft_note_db->field('package,left(note,60) note,auth')->where($map)->select();

        //show soft permission
        $perms_db = M("soft_permission");
        $sql = "select ssp.fileid softid,ssp.permissionid permissionid,sspd.des des from sj_soft_permission as ssp left join
         sj_soft_permission_des as sspd  on ssp.permissionid = sspd.permissionid
             where ssp.fileid in ({$softid}) ";
        $perms_array = $perms_db->query($sql);
        $perms_infos = array();
        foreach ($perms_array as $info) {
            $perms_infos[$info['softid']][$info['permissionid']] = $info['des'];
        }
        //end show soft permission
        $this->soft_file_db = M('soft_file');
        $map = array();
        $map['softid'] = array('in', $softid);
        $map['package_status'] = 1;
        $this->soft_file_list = $this->soft_file_db->field('softid,iconurl,url,advertisement')->where($map)->select();
        
        //$sql = "SELECT id,softid,url FROM sj_soft_file WHERE softid in( ".$softid.")";
        //echo $this -> soft_file_db -> getLastSql();
        
        for($i = 0; $i < count($this->soft_list); $i++){
            for ($j = 0; $j < count($this->soft_file_list); $j++) {
                    if ( $this->soft_list[$i]['softid'] == $this->soft_file_list[$j]['softid']) {
                        $this->soft_list[$i]['apkurl']  = $this->soft_file_list[$j]['url'];
                    }
           }
        }   
        
        $this->operating_db = D('Sj.Operating');
        $this->operating_list = $this->operating_db->where('only_auth=0')->field('oid,mname')->select();
        $this->lists = '';

        for ($i = 0; $i < count($this->operating_list); $i++) {
            $this->lists[$this->operating_list[$i]['oid']] = $this->operating_list[$i]['mname'];
        }
        //dump($this->lists);
        $this->lists[0] = '不隐藏';
        //dump($this->lists);

        $this->category_list_child = $this->category_db->getField('category_id,name');
        //广告信息
        $adlist = $this->ad_select();

        include_once SERVER_ROOT . '/tools/functions.php';
        // if($this -> soft_list && $this -> soft_file_list){ //known_abis是被写死的 所以对soft_list 和 soft_file_list进行判断以免出现BUG
        for ($i = 0; $i < count($this->soft_list); $i++) {
            for ($j = 0; $j < count($this->soft_note_list); $j++) {
                if ($this->soft_note_list[$j]['package'] == $this->soft_list[$i]['package']) {
                    $this->soft_list[$i]['note'] = $this->soft_note_list[$j]['note'];
                    $this->soft_list[$i]['auth'] = $this->soft_note_list[$j]['auth'];
                }
            }
            for ($j = 0; $j < count($this->soft_file_list); $j++) {
                if ($this->soft_file_list[$j]['softid'] == $this->soft_list[$i]['softid']) {
                    $this->soft_list[$i]['iconurl'] = $this->soft_file_list[$j]['iconurl'];
                    $this->soft_list[$i]['url'] = $this->soft_file_list[$j]['url'];
                    $adv_key = $this->soft_file_list[$j]['advertisement'];
                    $this->soft_list[$i]['advertisement'] = $this->ad($adv_key);
                }
            }
           
        
            $cid = explode(',', $this->soft_list[$i]['category_id']);
            for ($k = 0; $k < count($cid); $k++) {
                $this->soft_list[$i]['category'].=$this->category_list_child[$cid[$k]];
            }
            $oid = explode(',', $this->soft_list[$i]['operatorhide']);
            for ($k = 0; $k < count($oid); $k++) {
                $this->soft_list[$i]['operatorhides'].=$this->lists[$oid[$k]];
            }

            foreach ($known_abis as $abi_key => $abi_value) {
                if ($abi_value & $this->soft_list[$i]['abi'] || $this->soft_list[$i]['abi'] == 0)
                    $this->soft_list[$i]['abis'][] = $abi_key;
            }
            $permiss = array('5', '6', '50', '51');
            $permiss_str = '';
            foreach ($perms_infos[$this->soft_list[$i]['softid']] as $key => $info) {
                if (in_array($key, $permiss) && !empty($info)) {
                    $permiss_str .= $info . "<br/>";
                }
            }

            $this->soft_list[$i]['permission_desc'] = $permiss_str;
        }
        
        

        
        if ($_GET['p'])
            $this->assign('p', '?p=' . $_GET['p']); else
            $this->assign('p', '');
        $this->assign('softlist', $this->soft_list);
        $this->assign('adlist', $adlist);
        $Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);
        $this->display();
    }

    //软件管理__软件编辑_显示
    public function soft_edit() {

        $known_abis = array(
            'armeabi' => ABI_ARMEABI,
            'armeabi-v7a' => ABI_ARMEABI_V7A,
            'x86' => ABI_X86,
            'mips' => ABI_MIPS,
        );

        $log_mesage = '';

        $this->softid = $_GET['softid'];

        if (empty($this->softid)) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_list');
            $this->error("非法操作失败,如频繁出现，请联系管理员！");
        }
        //分辨率
        $this->resolution_db = M('resolution');
        $this->resolution_list = $this->resolution_db->field('resolutionid,note')->where('status=1')->select();
        $this->assign('resolutionlist', $this->resolution_list);

        //分类
       /*  $this->category_db = M('category');
        $this->category_list_parent = $this->category_db->field('category_id,name')->where('status=1 and parentid=0')->order('orderid')->select();

        $this->category_list_child = $this->category_db->field('category_id,name,parentid')->where('status=1 and parentid!=0')->order('orderid')->select();

        for ($i = 0; $i < count($this->category_list_parent); $i++) {

            for ($j = 0; $j < count($this->category_list_child); $j++) {
                if ($this->category_list_child[$j]['parentid'] == $this->category_list_parent[$i]['category_id']) {
                    $this->category_list_parent[$i]['child'][] = $this->category_list_child[$j];
                }
            }
        }
        $this->assign('categorylist', $this->category_list_parent); */
		

        //固件版本
        $this->conf_db = D('Sj.Config');
        $this->config_list = $this->conf_db->field('configname,configcontent')->where('config_type="firmware"')->select();
        $this->assign('configlist', $this->config_list);

        //运营商
        $this->operating_db = D('Sj.Operating');
        $this->operating_list = $this->operating_db->where('only_auth=0')->field('oid,mname')->select();
        $this->assign('operatinglist', $this->operating_list);

        $this->soft_db = M('soft');
        $this->map = 'status=1 and softid=' . $this->softid . '';

        $this->soft_list = $this->soft_db->where($this->map)->select();


        $this->soft_note_db = M('soft_note');
        $map = '';
        $map['package'] = $this->soft_list[0]['package'];
        $this->soft_note_list = $this->soft_note_db->where($map)->field('note,auth')->select();


        $this->soft_file_db = M('soft_file');
        $map = '';
        $map['softid'] = $this->softid;
        $map['package_status'] = 1;
        $this->soft_file_list = $this->soft_file_db->where($map)->field('id,url,iconurl,min_firmware,max_firmware,resolutionid,abi')->select();
        foreach ($this->soft_file_list as $k => $v) {
            $this->soft_file_list[$k]['abis'] = array();
            if ($this->soft_file_list[$k]['min_firmware'] < 3)
                $this->soft_file_list[$k]['min_firmware'] = 3;
            foreach ($known_abis as $abi_key => $abi_value) {
                if ($abi_value & $v['abi'])
                    $this->soft_file_list[$k]['abis'][] = $abi_key;
            }
        }

        $this->soft_thumb_db = M('soft_thumb');
        $map = '';
        $map['softid'] = $this->softid;
        $map['status'] = 1;
        $this->soft_thumb_list = $this->soft_thumb_db->where($map)->order('rank')->field('id,url,rank')->select();

        $thumblist = array();

        for ($i = 0; $i < count($this->soft_thumb_list); $i++) {
            $thumblist[$this->soft_thumb_list[$i]['rank']]['id'] = $this->soft_thumb_list[$i]['id'];
            $thumblist[$this->soft_thumb_list[$i]['rank']]['url'] = $this->soft_thumb_list[$i]['url'];
            $thumblist[$this->soft_thumb_list[$i]['rank']]['rank'] = $this->soft_thumb_list[$i]['rank'];
        }
        $this->soft_thumb_list = $thumblist;
        //dump($this->soft_thumb_list);
        //$map['category_id']=array('in',$softid);
        if ($this->soft_list[0]['category_id'][0] == ',') {
            $this->soft_list[0]['category_id'] = substr($this->soft_list[0]['category_id'], 1);
        }

        $tnum = strlen($this->soft_list[0]['category_id']);
        $tnum--;
        if ($this->soft_list[0]['category_id'][$tnum] == ',') {
            $this->soft_list[0]['category_id'] = substr($this->soft_list[0]['category_id'], 0, -1);
        }

        $cid = explode(',', $this->soft_list[0]['category_id']);

        //运营商隐藏
        $operatorhide = explode(',', $this->soft_list[0]['operatorhide']);
        //dump($operatorhide);


        $this->soft_list[0]['note'] = $this->soft_note_list[0]['note'];
        $this->soft_list[0]['auth'] = $this->soft_note_list[0]['auth'];

        $this->returnurl = $_GET['returnurl'];

        //$this->soft_list[0]['iconurl']=$this->soft_file_list;
        //dump($this->soft_list);

        $this->assign('softlist', $this->soft_list[0]);
        $this->assign('thumblist', $this->soft_thumb_list);

        //dump($this->soft_thumb_list);
        $this->assign('softfilecount', count($this->soft_file_list));
        $this->assign('filelist', $this->soft_file_list);


        //$this->assign('cid', $cid);
		$category = D('Sj.Category');
		$array_config=array(
			"categoryid"=>"categoryid[]",
			"selected"=>$cid[0]
		);
		$conf_list = $category->getCategory($array_config);
        $this->assign('conflist',$conf_list);
        $this->assign('operatorhide', $operatorhide);
        if (isset($_GET['type']))
            $this->returnurl .='/type/' . $_GET['type'];
        $this->assign('returnurl', $this->returnurl);

        $this->display();
    }

    //软件管理__软件编辑_提交
    public function soft_edit_upload() {
		//print_r($_POST['categoryid']);exit;
        $this->softid = trim($_POST['softid']);
        $this->returnurl = $_POST['returnurl'];
        if (empty($this->returnurl)) {
            $this->returnurl = 'soft_edit?softid=' . $this->softid;
        }else{
			$this->returnurl = $_POST['returnurl'].'?softid=' . $this->softid;
        }


        if (empty($this->softid)) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . $this->returnurl . '');
            $this->error('出现了一些小错误，请返回列表页');
        }

        $this->soft_list['softname'] = trim($_POST['softname']);
		$this->soft_list['type'] = trim($_POST['type']);
        $this->soft_list['ename'] = trim($_POST['ename']);
		$this->soft_list['category_id'] = implode(',', $_POST['categoryid']);
        $this->soft_list['category_id'] = ',' . $this->soft_list['category_id'] . ',';
	
        $thumbcid = '';
        $thumbcid = $_POST['categoryid'];
        if ($thumbcid[0] == 0) {
            $this->error('请选择软件类别');
        }
        if ($thumbcid[0] == $thumbcid[1] && $thumbcid[0] != 0) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error('对不起,软件类别，不可重复');
        }
        if ($thumbcid[1] == $thumbcid[2] && $thumbcid[1] != 0) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error('对不起,软件类别，不可重复');
        }
        if ($thumbcid[0] == $thumbcid[2] && $thumbcid[1] != 0) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error('对不起,软件类别，不可重复');
        }

        if (!eregi("[\u4e00-\u9fa5]", $this->soft_list['ename']) && $this->soft_list['ename'] != '') {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . $this->returnurl . '');
            $this->error('对不起,软件英文名暂不支持中文');
            exit;
        }
        if (mb_strlen($_POST['intro'], 'utf-8') > 1500) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . $this->returnurl . '');
            $this->error('对不起，软件描述不得超过1500字');
        }

        if (empty($_POST['softname'])) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . $this->returnurl . '');
            $this->error('软件名为必填项');
        }

        if (empty($_POST['intro'])) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . $this->returnurl . '');
            $this->error('软件描述为必填项');
        }
        if (mb_strlen($_POST['note'], 'utf-8') > 1500) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error('对不起，软件备注不得超过1500字');
        }
		
		$util = D('Sj.Util');
		$param = array(
			'softname' => $this->soft_list['softname'],
			'dev_name' => $this->soft_list['dev_name'],
			'intro' => $this->soft_list['intro'],
		);
		
		$result = $util->filter_word($param);
	    if ($result['softname'][0] == false) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error("软件名含有非法字符 {$result['softname'][1]}，请重新编辑后提交！");
        }
		
		if ($result['dev_name'][0] == false) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error("开发者含有非法字符 {$result['dev_name'][1]}，请重新编辑后提交！");
        }
		if ($result['intro'][0] == false) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error("软件描含有非法字符 {$result['intro'][1]}，请重新编辑后提交！");
        }
		
		
        $num = count($_FILES["image"]["name"]);
        for ($i = 0; $i < $num; $i++) {
            if (empty($_FILES["image"]["name"][$i])) {
                unset($_FILES["image"]["name"][$i]);
                unset($_FILES["image"]["type"][$i]);
                unset($_FILES["image"]["tmp_name"][$i]);
                unset($_FILES["image"]["error"][$i]);
                unset($_FILES["image"]["size"][$i]);
            } else {
                $imagerank[] = $i;
            }
        }
        $num = count($_FILES["apk"]["name"]);
        for ($i = 0; $i < $num; $i++) {
            if (empty($_FILES["apk"]["name"][$i])) {
                unset($_FILES["apk"]["name"][$i]);
                unset($_FILES["apk"]["type"][$i]);
                unset($_FILES["apk"]["tmp_name"][$i]);
                unset($_FILES["apk"]["error"][$i]);
                unset($_FILES["apk"]["size"][$i]);
            }
        }

        $this->soft_db = M('soft');           //软件表
		$zh_soft_time = M('soft_time');
        $this->map = '';
        $this->map['softid'] = $this->softid;

        $packages = '';
        $result = $this->soft_db->where($this->map)->field('package,version,version_code,softid')->find();
        $packages = $result['package'];
        $GLOBALS['upload_package_name'] = $packages;
        $versionCode = $result['version_code'];
        $versionName = $result['version'];

        $this->soft_list['tags'] = $_POST['tags'];

        $this->soft_list['last_refresh'] = time();

        if (empty($this->soft_list['tags'])) {
            $tihis->soft_list['tags'] = $this->soft_list['softname'] . $this->soft_list['package'];
        }

        $tags = '';
        $tags = explode(',', $this->soft_list['tags']);
        if (count($tags) > 5) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . $this->returnurl . '');
            $this->error("最多5个关键字，谢谢");
        }

        $this->soft_thumb_db = M('soft_thumb');

        $this->resolution_list = $_POST['resolution'];
        $fileid = $_POST['fileid'];

        $minfirame = $_POST['minfirame'];
        $maxfirame = $_POST['maxfirame'];

        //附件上传
        if (!empty($_FILES['apk']['size']) || !empty($_FILES['image']['size'])) {
            if (!empty($_FILES['apk']['size'])) {
                $this->soft_list['update_type'] = 1;
            }
            $path = date("Ym/d/");
            $config = array(
                'multi_config' => array(
                    'apk' => array(
                        'savepath' => UPLOAD_PATH . '/apk/' . $path,
                        'saveRule' => 'packagename'
                    ),
                    'image' => array(
                        'savepath' => UPLOAD_PATH . '/thumb/' . $path,
                        'saveRule' => 'thumbname',
                    )
                ),
                'flip' => true,
				'img_p_size' => 1024*30,  //图片常规压缩大小
				'img_p_width'=> 320, //图片常规压缩宽度
				'img_p_height'=> 534, //图片常规压缩宽度
				'img_s_size' => 1024*10, //图片缩略图大小
				'img_s_width' => 150, //缩略图宽
				'img_ext' => '.jpg', //截图文件扩展名
            );
            $this->lists = $this->_uploadapk(true, $config);


            $this->apklist = $this->lists['apk'];
            $this->imagelist = $this->lists['image'];

            //sort($this->apklist);
            //sort($this->imagelist);
            //dump($this->apklist);
            //dump($this->imagelist);

            $abiType = $versionNameType = $versionCodeType = $apktype = true;
            $package = $this->apklist[0]['packagename'];
            $nVersionCode = $this->apklist[0]['versionCode'];
            $nVersionName = $this->apklist[0]['versionName'];
            $nAbi = $this->apklist[0]['abi'];
            for ($i = 0; $i < count($this->apklist); $i++) {
                $package = $this->apklist[0]['packagename'];
                $nVersionCode = $this->apklist[0]['versionCode'];
                $nVersionName = $this->apklist[0]['versionName'];
                if ($this->apklist[$i]['packagename'] != $package || $this->apklist[$i]['packagename'] != $packages) {
                    $apktype = false;
                }

                if ($this->apklist[$i]['versionCode'] != $nVersionCode || $this->apklist[$i]['versionCode'] != $versionCode) {
                    $versionCodeType = false;
                }

                if ($this->apklist[$i]['versionName'] != $nVersionName || $this->apklist[$i]['versionName'] != $versionName) {
                    $versionNameType = false;
                }
                if ($this->apklist[$i]['abi'] != $nAbi || $this->apklist[$i]['abi'] != $abi) {
                    //$abiType=false;
                }
            }

            if ($apktype == false) {

                for ($i = 0; $i < count($this->apklist); $i++) {
                    unlink($this->apklist[$i]['savepath']);
                    unlink($this->apklist[$i]['icon']);
                }
                $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . $this->returnurl . '');
                $this->error('编辑失败！包名不统一');
            }

            if ($versionCodeType == false) {

                for ($i = 0; $i < count($this->apklist); $i++) {
                    unlink($this->apklist[$i]['savepath']);
                    unlink($this->apklist[$i]['icon']);
                }
                $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . $this->returnurl . '');
                $this->error('编辑失败！version code不统一');
            }

            if ($versionNameType == false) {

                for ($i = 0; $i < count($this->apklist); $i++) {
                    unlink($this->apklist[$i]['savepath']);
                    unlink($this->apklist[$i]['icon']);
                }
                $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . $this->returnurl . '');
                $this->error('编辑失败！version name不统一');
            }

            if ($abiType == false) {
                for ($i = 0; $i < count($this->apklist); $i++) {
                    unlink($this->apklist[$i]['savepath']);
                    unlink($this->apklist[$i]['icon']);
                }
                $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . $this->returnurl . '');
                $this->error('编辑失败！abi不统一');
            }
            $thumb_log = '';
            for ($i = 0; $i < count($this->imagelist); $i++) {
                $map = '';
                $map['softid'] = $this->softid;
                $map['status'] = 1;
                $map['rank'] = $imagerank[$i];
                $thumbmap = '';
                $thumbmap['status'] = 0;
                $this->soft_thumb_db->where($map)->save($thumbmap);
                $this->soft_thumb_list['softid'] = $this->softid;
                $this->soft_thumb_list['url'] = $this->imagelist[$i]['url'];
                $this->soft_thumb_list['image_raw'] = $this->imagelist[$i]['url_original'];
                $this->soft_thumb_list['image_thumb'] = $this->imagelist[$i]['url_resize'];
                $this->soft_thumb_list['status'] = 1;
                $this->soft_thumb_list['rank'] = $this->imagelist[$i]['key'];
                $this->soft_thumb_list['upload_time'] = time();
                $this->soft_thumb_list['last_refresh'] = time();
                if (false == $this->soft_thumb_db->add($this->soft_thumb_list)) {
                    //$this->soft_db->where('softid='.$this->softid)->delete();
                    //$this->soft_note_db->where('package='.$this->soft_note_list['package'])->delete();
                    //$this->soft_thumb_db->where('softid='.$this->softid)->delete();

                    $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . $this->returnurl . '');
                    $this->error("编辑软件截图失败,数据插入发生错误！");
                } else {
                    $thumb_log = "更新软件截图\n";
                }
            }


            $this->soft_file_db = M('soft_file');


            $soft_file_safe_db = M('soft_scan_result');
            $apk_log = '';
            $soft_file_insert = array();
            $soft_file_update = array();

            $map = array();
            $map['softid'] = $this->softid;
            $map['package_status'] = 1;
            $soft_file_list = $this->soft_file_db->where($map)->field('id,min_firmware,screen,abi')->select();
            $soft_file_checked = array();
            foreach ($soft_file_list as $soft_file) {
                $check_key = "{$soft_file['screen']}_{$soft_file['min_firmware']}_{$soft_file['abi']}";
                $soft_file_checked[$check_key] = $soft_file['id'];
            }


            $sql = "select * from sj_soft_permission_details";
            $allperms = $this->soft_db->query($sql);
            $allperms_mp = array();
            foreach ($allperms as $val) {
                $allperms_mp[$val['name']] = $val['id'];
            }
            $sql = "select * from sj_soft_uses_library_details";
            $alllibs = $this->soft_db->query($sql);
            $alllibs_mp = array();
            foreach ($alllibs as $val) {
                $alllibs_mp[$val['name']] = $val['id'];
            }

            for ($i = 0; $i < count($this->apklist); $i++) {
                $key = $this->apklist[$i]['key'];
                $maxfirame[$key] = empty($maxfirame[$key]) ? 0 : $maxfirame[$key];
                //$check_key = "{$this->apklist[$i]['supports-screens']}_{$this->apklist[$i]['min_sdk_ver']}_{$this->apklist[$i]['abi']}_{$maxfirame[$key]}";
                $check_key = "{$this->apklist[$i]['supports-screens']}_{$this->apklist[$i]['min_sdk_ver']}_{$this->apklist[$i]['abi']}";
                if (isset($soft_file_checked[$check_key]) && $fileid[$key] != $soft_file_checked[$check_key]) {
                    for ($j = 0; $j < count($this->apklist); $j++) {
                        unlink($this->apklist[$j]['savepath']);
                    }

                    foreach ($soft_file_insert as $soft_file_id) {
                        if (empty($soft_file_id))
                            continue;
                        $this->soft_file_db->where('id=' . $soft_file_id)->delete();
                    }
                    foreach ($soft_file_update as $soft_file_id) {
                        if (empty($soft_file_id))
                            continue;
                        $map = array(
                            'package_status' => 1
                        );
                        $where = array(
                            'id' => $soft_file_id
                        );
                        $this->soft_file_db->where($where)->save($map);
                    }

                    $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . $this->returnurl . '');
                    $this->error("不允许上传分辨率、最小固件、abi类型相同的包名");
                    break;
                }
                $this->soft_file_list['softid'] = $this->softid;
                $this->soft_file_list['package_status'] = 1;
                $this->soft_file_list['apk_name'] = $this->apklist[$i]['packagename'];
                $this->soft_file_list['url'] = $this->apklist[$i]['apkurl_db'];
                $this->soft_file_list['filesize'] = $this->apklist[$i]['size'];
                $this->soft_file_list['screen'] = $this->apklist[$i]['supports-screens'];
                $this->soft_file_list['resolutionid'] = $this->resolution_list[$key];

                $this->soft_file_list['min_firmware'] = $this->apklist[$i]['min_sdk_ver'];
                $this->soft_file_list['max_firmware'] = $maxfirame[$key];

                $this->soft_file_list['iconurl'] = $this->apklist[$i]['iconurl_db'];
                $this->soft_file_list['abi'] = $this->apklist[$i]['abi'];
                $this->soft_file_list['upload_time'] = time();
                $this->soft_file_list['last_refresh'] = time();
                $this->soft_file_list['safe'] = 0;
                $this->soft_file_list['apk_icon'] = $this->apklist[$i]['apk_icon_db'];
                /* 文件MD5,sha-1加密 */
                $this->soft_file_list['md5_file'] = md5_file(UPLOAD_PATH . $this->soft_file_list['url']);
                $this->soft_file_list['sha1_file'] = sha1_file(UPLOAD_PATH . $this->soft_file_list['url']);
                include_once(SERVER_ROOT . '/tools/functions.php');
                $this->soft_file_list['advertisement'] = test_apk_for_3rdparty(UPLOAD_PATH . $this->apklist[$i]['apkurl_db']);
                $type = $this->soft_file_db->add($this->soft_file_list);
                if (false == $type) {

                    //$this->soft_db->where('softid='.$this->softid)->delete();
                    //$this->soft_note_db->where('package='.$this->soft_note_list['package'])->delete();
                    //$this->soft_thumb_db->where('softid='.$this->softid)->delete();
                    $apk_log = '';

                    $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . $this->returnurl . '');
                    $this->error("编辑软件apk失败,数据插入发生错误！");
                } else {
                    if (!empty($fileid[$key])) {
                        $where = array();
                        $where['id'] = $fileid[$key];
                        $map = array();
                        $map['package_status'] = '0';
                        $status = $this->soft_file_db->where($where)->save($map);
                        $soft_file_update[$check_key] = $fileid[$key];
                        if ($status)
                            $apk_log .= "对softid为{$this->softid} 软件的包进行替换，原包fileid {$fileid[$key]},新包fileid {$type};\n";
                        unset($fileid[$key]);
                    } else {
                        $apk_log .= "对softid为{$this->softid} 软件的包进行新增，新包fileid {$type};\n";
                    }
                    unset($_POST['fileid'][$i]);
                }
                $soft_file_checked[$check_key] = $soft_file_insert[$check_key] = $type;

                foreach ($this->apklist[$i]['permission'] as $p) {
                    if (!isset($allperms_mp[$p]))
                        continue;
                    $sql = "insert into sj_soft_permission (`fileid`, `permissionid`) values ({$type}, {$allperms_mp[$p]})";
                    $this->soft_file_db->query($sql);
                }
                foreach ($this->apklist[$i]['library'] as $l) {
                    if (!isset($alllibs_mp[$l])) {
                        $sql = "insert into sj_soft_uses_library_details (`name`) values ('{$l}')";
                        $this->soft_file_db->query($sql);
                    }
                }
                foreach ($this->apklist[$i]['library-optional'] as $l) {
                    if (!isset($alllibs_mp[$l])) {
                        $sql = "insert into sj_soft_uses_library_details (`name`) values ('{$l}')";
                        $this->soft_file_db->query($sql);
                    }
                }
                $sql = "select * from sj_soft_uses_library_details";
                $alllibs = $this->soft_db->query($sql);
                $alllibs_mp = array();
                foreach ($alllibs as $val) {
                    $alllibs_mp[$val['name']] = $val['id'];
                }
                foreach ($this->apklist[$i]['library'] as $l) {
                    $sql = "insert into sj_soft_uses_library (`fileid`,`libraryid`,`type`) values ({$type},{$alllibs_mp[$l]},1)";
                    $this->soft_file_db->query($sql);
                }
                foreach ($this->apklist[$i]['library-optional'] as $l) {
                    $sql = "insert into sj_soft_uses_library (`fileid`,`libraryid`,`type`) values ({$type},{$alllibs_mp[$l]},0)";
                    $this->soft_file_db->query($sql);
                }

                include_once(SITE_PATH . "../model/scanSoft.php");

                $soft_hash = scanSoft::getSoftHash(UPLOAD_PATH . $this->soft_file_list['url']);

                $safelist = array(
                    'sfid' => $type,
                    'hash' => $soft_hash,
                    'provider' => 1,
                    'time_req' => time(),
                );
                $soft_file_safe_db->add($safelist);
                $params = array(
                    "download_url" => ATTACHMENT_HOST . $this->soft_file_list['url'],
                    "soft_hash" => $soft_hash,
                );
                $re = scanSoft::requestPostQQ($params);
                $safelist = array(
                    'sfid' => $type,
                    'hash' => $soft_hash,
                    'provider' => 2,
                    'time_req' => time(),
                );
                $soft_file_safe_db->add($safelist);
                $re = scanSoft::requestPostAQGJ($params);
				//网秦安全认证
	
				$scan_result = array(
					'sfid' => $type,
					'hash' => $soft_hash,
					'provider' => 3,
					'time_req' => time(),
				);
				$soft_file_safe_db -> add($scan_result);
				$re = scanSoft::requestGetWQ($params);				
            }
            if (!empty($this->apklist)) {
                $this->soft_list['version'] = $this->apklist[0]['versionName'];
                $this->soft_list['version_code'] = $this->apklist[0]['versionCode'];
                if (empty($this->apklist[0]['versionCode']) || empty($this->apklist[0]['versionCode'])) {
                    file_put_contents('/tmp/version_bug.log', var_export($this->apklist, true), FILE_APPEND);
                    $this->soft_list['version'] = 'new';
                    $this->soft_list['version_code'] = 0;
                }
            }
        }
        $log_mesage .= $thumb_log . $apk_log;


        $this->soft_file_db = M('soft_file');

        $firmware_log = '';

        for ($i = 0; $i < count($fileid); $i++) {
            if (!empty($fileid[$i])) {
                $thumbmap['id'] = $fileid[$i];
                $map = '';
                $map['max_firmware'] = $maxfirame[$i];
                $status = $this->soft_file_db->where($thumbmap)->save($map);
                if (!empty($maxfirame[$i]) && $status)
                    $firmware_log = "更新apk固件版本限制\n";
            }
        }
        $log_mesage .= $firmware_log;

        //删除指定图片
        $dellist = '';
        $dellist = $_POST['delimg'];

        if (!empty($dellist)) {

            for ($i = 0; $i < count($dellist); $i++) {
                $map = '';
                $map['status'] = '0';
                $this->soft_thumb_db->where('id=' . $dellist[$i])->save($map);
            }
            $log_mesage .= "删除软件截图\n";
        }
        //删除指定apk
        $delapk = '';
        $delapk = $_POST['delapk'];
        if (!empty($delapk)) {
            for ($i = 0; $i < count($delapk); $i++) {
                $map = '';
                $where['id'] = (int) $delapk[$i];
                $apkmap['package_status'] = 0;
                $affect = $this->soft_file_db->where($where)->save($apkmap);
            }
            $log_mesage .= "删除指定apk文件\n";
        }
        //APK权限
        //if($this->apklist) {
        //}
        //$this->soft_list['dev_name']    =   trim($_POST['dev_name']);
        $this->soft_list['dev_name'] = $_POST['dev_name'];
        $this->soft_list['dev_enname'] = $_POST['dev_enname'];
        $this->soft_list['dever_email'] = $_POST['dever_email'];
        $this->soft_list['dever_page'] = $_POST['dever_page'];
        $this->soft_list['intro'] = $_POST['intro'];

        $this->soft_list['deny_msg'] = '后台编辑该软件';
        //dump($this->soft_list);exit;
        //运营商是否隐藏
        $operating = $_POST['operating'];


        if (!empty($operating)) {

            $this->soft_list['operatorhide'] = implode(',', $_POST['operating']);
            //插入运营商隐藏列表
        } else {
            $this->soft_list['operatorhide'] = 0;
        }
        if (false == $this->soft_db->where($this->map)->save($this->soft_list)) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . $this->returnurl . '');
            $this->error("编辑软件失败,数据插入发生错误！");
        }
        //张辉/时间修改开始语句
        if (false == $zh_soft_time->where('softid=' . $this->softid)->field('softid')->find()) {
            $zh_soft_time->add(array("softid" => $this->softid, "start_time" => strtotime($_POST['date0']), "end_time" => strtotime($_POST['date1'])));
        } else {
            $data['start_time'] = strtotime($_POST['date0']);
            $data['end_time'] = strtotime($_POST['date1']);
            $zh_soft_time->where('softid='.$this->softid)->data($data)->save();
			//echo $zh_soft_time->getlastsql();exit;
        }
        //张辉/时间修结束始语句
        $task_client = get_task_client();
        $task_client->doHighBackground("sync_filter_cache", json_encode(array("softid" => $this->softid, 'line' => __LINE__)));
        $task_client->doBackground("refresh_lack", json_encode(array("softid" => $this->softid)));

        $this->soft_note_list['auth'] = $_POST['auth'] ? $_POST['auth'] : 0;
        $this->soft_note_list['note'] = $_POST['note'];


        $this->soft_note_db = M('soft_note');           //软件表


        $this->map = '';
        $this->map['package'] = $packages;

        if (false === $this->soft_note_db->where($this->map)->save($this->soft_note_list)) {

            //$this->soft_db->where('softid='.$this->softid)->delete();
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . $this->returnurl . '');
            $this->error("编辑软件失败,数据附件插入发生错误！");
        } else {
            //$this->writelog('编辑了软件ID为'.$this->softid.'的软件');
            $date = time();
            $modify_type = "update";
            $this->update_data_log($this->softid, $modify_type, $date); //记录软件更新日志
            $this->writelog("对软件ID为{$this->softid}的软件进行了更新:\n" . $log_mesage);
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . $this->returnurl . '');
            $this->success("编辑成功！");
        }
    }

    //软件管理__软件更新_显示
    public function soft_update() {

        $this->softid = $_GET['softid'];

        if (empty($this->softid)) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_list');
            $this->error("非法操作失败,如频繁出现，请联系管理员！");
        }
        //分辨率
        $this->resolution_db = M('resolution');
        $this->resolution_list = $this->resolution_db->field('resolutionid,note')->where('status=1')->select();
        $this->assign('resolutionlist', $this->resolution_list);
        //固件版本
        $this->conf_db = D('Sj.Config');
        $this->config_list = $this->conf_db->field('configname,configcontent')->where('config_type="firmware"')->select();
        $this->assign('configlist', $this->config_list);

        //运营商信息
        $this->operating_db = D('Sj.Operating');
        $this->operating_list = $this->operating_db->where('only_auth=0')->field('oid,mname')->select();
        $this->assign('operatinglist', $this->operating_list);

        $this->soft_db = M('soft');
        $this->map = 'status=1 and softid=' . $this->softid . '';

        $this->soft_list = $this->soft_db->where($this->map)->select();



        $this->soft_note_db = M('soft_note');
        $map = '';
        $map['package'] = $this->soft_list[0]['package'];
        $this->soft_note_list = $this->soft_note_db->where($map)->field('note,auth')->select();


        $this->soft_file_db = M('soft_file');
        $map = '';
        $map['package_status'] = 1;
        $map['softid'] = $this->softid;
        $this->soft_file_list = $this->soft_file_db->where($map)->field('id,url,iconurl,min_firmware,max_firmware,resolutionid')->select();


        $this->soft_thumb_db = M('soft_thumb');
        $map = '';
        $map['softid'] = $this->softid;
        $map['status'] = 1;
        $this->soft_thumb_list = $this->soft_thumb_db->where($map)->order('rank')->field('url,rank')->select();
        //dump($this->soft_thumb_list);
        //$map['category_id']=array('in',$softid);
        if ($this->soft_list[0]['category_id'][0] == ',') {
            $this->soft_list[0]['category_id'] = substr($this->soft_list[0]['category_id'], 1);
        }

        $tnum = strlen($this->soft_list[0]['category_id']);
        $tnum--;
        if ($this->soft_list[0]['category_id'][$tnum] == ',') {
            $this->soft_list[0]['category_id'] = substr($this->soft_list[0]['category_id'], 0, -1);
        }

        $cid = explode(',', $this->soft_list[0]['category_id']);
        //dump($cid);
        //运营商隐藏
        $operatorhide = explode(',', $this->soft_list[0]['operatorhide']);
        //dump($operatorhide);


        $this->soft_list[0]['note'] = $this->soft_note_list[0]['note'];
        $this->soft_list[0]['auth'] = $this->soft_note_list[0]['auth'];

        //$this->soft_list[0]['iconurl']=$this->soft_file_list;
        //dump($this->soft_list);

        $this->assign('softlist', $this->soft_list[0]);
        $this->assign('thumblist', $this->soft_thumb_list);

        //dump($this->soft_thumb_list);
        //dump($this->soft_file_list);
        $this->assign('filelist', $this->soft_file_list);
		 //分类
        $this->category_db = M('category');
        $category = D('Sj.Category');
		$array_config=array(
			"categoryid"=>"categoryid",
			"selected"=>$cid[0]
		);
		$conf_list = $category->getCategory($array_config);
        $this->assign('conflist',$conf_list);

        $this->assign('cid', $cid);
        $this->assign('operatorhide', $operatorhide);

        $this->display();
    }

    //软件更新
    public function soft_update_upload() {


        $this->sid = $_POST['softid'];
        $this->returnurl = 'soft_update?softid=' . $this->sid;
        if (empty($this->sid)) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_list');
            $this->error("非法操作失败,如频繁出现，请联系管理员！");
        }
        if (mb_strlen($_POST['intro'], 'utf-8') > 1500) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_list');
            $this->error('对不起，内容不得超过1500字');
        }

        $this->soft_list['softname'] = trim($_POST['softname']);
        $this->soft_list['ename'] = trim($_POST['ename']);

        $this->soft_list['category_id'] = implode(',', $_POST['categoryid']);
        $this->soft_list['category_id'] = ',' . $this->soft_list['category_id'] . ',';

        $thumbcid = '';
        $thumbcid = $_POST['categoryid'];
        if ($thumbcid == 0) {
            $this->error('请选择软件类别');
        }
/*         if ($thumbcid[0] == $thumbcid[1] && $thumbcid[0] != 0) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . $this->returnurl);
            $this->error('对不起,软件类别，不可重复');
        }
        if ($thumbcid[1] == $thumbcid[2] && $thumbcid[1] != 0) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . $this->returnurl);
            $this->error('对不起,软件类别，不可重复');
        }
        if ($thumbcid[0] == $thumbcid[2] && $thumbcid[1] != 0) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . $this->returnurl);
            $this->error('对不起,软件类别，不可重复');
        } */

        if (!eregi("[\u4e00-\u9fa5]", $this->soft_list['ename']) && $this->soft_list['ename'] != '') {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . $this->returnurl);
            $this->error('对不起,软件英文名暂不支持中文');
            exit;
        }
        if (empty($this->soft_list['softname'])) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . $this->returnurl);
            $this->error('软件名为必填项');
        }
		
        if (mb_strlen($_POST['intro'], 'utf-8') > 1500) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . $this->returnurl);
            $this->error('对不起，内容不得超过1500字');
        }
		
        if (mb_strlen($_POST['note'], 'utf-8') > 1500) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . $this->returnurl);
            $this->error('对不起，备注不得超过1500字');
        }
		$util = D('Sj.Util');
		$param = array(
			'softname' => $this->soft_list['softname'],
			'dev_name' => $this->soft_list['dev_name'],
			'intro' => $this->soft_list['intro'],
		);
		
		$result = $util->filter_word($param);
	    if ($result['softname'][0] == false) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error("软件名含有非法字符 {$result['softname'][1]}，请重新编辑后提交！");
        }
		
		if ($result['dev_name'][0] == false) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error("开发者含有非法字符 {$result['dev_name'][1]}，请重新编辑后提交！");
        }
		if ($result['intro'][0] == false) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error("软件描含有非法字符 {$result['intro'][1]}，请重新编辑后提交！");
        }
		
        $num = count($_FILES["image"]["name"]);
        for ($i = 0; $i < $num; $i++) {
            if (empty($_FILES["image"]["name"][$i])) {
                unset($_FILES["image"]["name"][$i]);
                unset($_FILES["image"]["type"][$i]);
                unset($_FILES["image"]["tmp_name"][$i]);
                unset($_FILES["image"]["error"][$i]);
                unset($_FILES["image"]["size"][$i]);
            } else {
                $imagerank[] = $i;
            }
        }
		$action = $_POST['action'];//是否采集信息提交；
		
		
		//图片上传
		$path = date("Ym/d/");
		$config = array(
			'multi_config' => array()
		);
		if (!empty($_FILES['image']['size'])) {
			$config['multi_config']['image'] = array(
				'savepath' => UPLOAD_PATH . '/thumb/' . $path,
				'saveRule' => 'thumbname',
			);
			$config['flip'] = true;
			$config['img_p_size'] = 1024*30;  //图片常规压缩大小
			$config['img_p_width'] = 320; //图片常规压缩宽度
			$config['img_p_height'] = 534; //图片常规压缩宽度
			$config['img_s_size'] = 1024*10; //图片缩略图大小
			$config['img_s_width'] = 150; //缩略图宽
			$config['img_ext'] = '.jpg'; //截图文件扩展名
		}
		if ($action =='caiji' ){
			$apkfile = dirname(realpath(__FILE__)).'/../../../'.$_POST['apkfile'];
			//$apkfile = UPLOAD_PATH. '/'. $_POST['apkfile'];
			$packagename = $_POST['package'];
			
			//TODO 处理本地文件
			if (file_exists($apkfile)) {
			   list($msec, $sec) = explode(' ', microtime());
               $msec = substr($msec, 2);
			   
			   $savefilepath = UPLOAD_PATH . '/apk/'.$path;
			   $filekey= 0;
			   $savefilename = $packagename.'_'.$msec.'_'.$filekey.'.apk';
			   if(!is_dir($savefilepath)){
				  mkdir($savefilepath);	
			   }
			   if(file_put_contents($savefilepath.$savefilename, file_get_contents($apkfile))<=0)
			   {
				    echo 'apk包上传失败···请您重试';
            		exit;
			   }
			   $apksize = filesize($savefilepath.$savefilename);
			  	   
			   $tmp = get_apk_info($savefilepath.$savefilename);
			 
            	if($tmp == false) {
            		echo '出现了个小错误···请您重试';
            		exit;
            	}
            	$tmp['abi'] = get_apk_abi($savefilepath.$savefilename);
            	
            	$package_name = $tmp['packagename'];
            	$iconname = $package_name . '_'. $msec. '_'. $filekey. substr($tmp['icon'], strrpos($tmp['icon'], '.'));
            	$iconpath = UPLOAD_PATH. '/icon/'. $path;
            	//$upload->__mkdir($iconpath);
				mkdir($iconpath);
            	
            	$iconurl = $iconpath. $iconname;
            	$apk_icon_url = tempnam($iconpath, $package_name. "_");
            	$apk_icon_url .= substr($tmp['icon'], strrpos($tmp['icon'], '.'));
            	$apk_icon_url_db = str_replace(UPLOAD_PATH, '', $apk_icon_url);
            	$iconurl_db = str_replace(UPLOAD_PATH, '', $iconurl);
            	$apkurl_db = str_replace(UPLOAD_PATH, '', $savefilepath). $savefilename;

              
            	copy($tmp['icon'], $iconurl);
            	go_make_links($iconurl);
            	# original icon
            	copy($tmp['icon_original'], $apk_icon_url);
            	go_make_links($apk_icon_url);
                if (!is_file($iconurl)) {
                    echo "图标(${tmp['icon']} => ${iconurl})复制失败。";
                    exit;
                }
            	unlink($tmp['icon']);
            	unlink($tmp['icon_original']);

            	$tmp['icon'] = $iconurl;
            	$tmp['size'] = $apksize;
            	# XXX: what's this
            	$tmp['savepath'] = $savefilepath. $savefilename;

            	$tmp['iconurl_db'] = $iconurl_db;
            	$tmp['apkurl_db'] = $apkurl_db;
            	$tmp['key'] = $filekey;
            	$tmp['apk_icon_db'] = $apk_icon_url_db;

            	$apkurl = $savefilepath. $savefilename;
            	$apkouturl = $savefilepath;
            	splitfile($apkurl, $apkouturl);
            	go_make_links($apkurl);
            	$apkinfo[] = $tmp;
			   
			 
			   
            } 
			
			
            
			
			 $this->apklist = $apkinfo;
			

		}else{
		  
		    $num = count($_FILES["apk"]["name"]);
			for ($i = 0; $i < $num; $i++) {
				if (empty($_FILES["apk"]["name"][$i])) {
					unset($_FILES["apk"]["name"][$i]);
					unset($_FILES["apk"]["type"][$i]);
					unset($_FILES["apk"]["tmp_name"][$i]);
					unset($_FILES["apk"]["error"][$i]);
					unset($_FILES["apk"]["size"][$i]);
				}
			}
		
			if (!empty($_FILES['apk']['size'])) {
				$config['multi_config']['apk'] = array(
					'savepath' => UPLOAD_PATH . '/apk/' . $path,
					'saveRule' => 'packagename'
				);
			} else {
				$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
				$this->error('对不起，APK必须上传');
			}
	    }
		$this->imagelist = array();
		if (!empty($config['multi_config'])) {
			$this->lists = $this->_uploadapk(true, $config);// 上传apk及图片	
			$this->imagelist = $this->lists['image'];
		}
		
		if ($action !='caiji' ){
			$this->apklist = $this->lists['apk'];
		}
   
       

        $this->soft_db = M('soft');           //软件表
        $this->map = '';
        $this->map['softid'] = $this->sid;

        $this->oldsoftlist = $this->soft_db->where($this->map)->field('claim_status,dev_id,total_downloaded,total_downloaded_detain,total_downloaded_add,package,version_code,version')->select();


        $apktype = 'true';
        for ($i = 0; $i < count($this->apklist); $i++) {
            $package = $this->apklist[0]['packagename'];
            if ($this->apklist[$i]['packagename'] != $package || $this->apklist[$i]['packagename'] != $this->oldsoftlist[0]['package']) {
                $apktype = false;
            }
        }

        if ($apktype == false) {

            for ($i = 0; $i < count($this->apklist); $i++) {
                unlink($this->apklist[$i]['savepath']);
                unlink($this->apklist[$i]['icon']);
            }
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error('添加失败！包名不统一');
        }


        //APK权限
        //if($this->apklist) {
        //}

        $this->soft_list['abi'] = $this->apklist[0]['abi'];
        $this->soft_list['package'] = $this->apklist[0]['packagename'];
        $this->soft_list['dev_name'] = trim($_POST['dev_name']);
        $this->soft_list['dev_enname'] = trim($_POST['dev_enname']);
        $this->soft_list['dever_email'] = $_POST['dever_email'];
        $this->soft_list['dever_page'] = $_POST['dever_page'];
        $this->soft_list['category_id'] = implode(',', $_POST['categoryid']);
        $this->soft_list['category_id'] = ',' . $this->soft_list['category_id'] . ',';
        $this->soft_list['costs'] = 0;
        $this->soft_list['version'] = $this->apklist[0]['versionName'];
        $this->soft_list['version_code'] = $this->apklist[0]['versionCode'];
        $this->soft_list['intro'] = $_POST['intro'];
        $this->soft_list['total_downloaded'] = $this->oldsoftlist[0]['total_downloaded'];
        $this->soft_list['total_downloaded_detain'] = $this->oldsoftlist[0]['total_downloaded_detain'];
		$this->soft_list['total_downloaded_add'] = $this -> oldsoftlist[0]['total_downloaded_add'];


        if (version_compare($this->oldsoftlist[0]['version_code'], $this->soft_list['version_code'], '>') || version_compare($this->oldsoftlist[0]['version_code'], $this->soft_list['version_code'], 'eq')) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error('添加失败！version_code需增加');
        }

        $this->soft_list['score'] = 0;
        $this->soft_list['claim_status'] = $this->oldsoftlist[0]['claim_status'];
        $this->soft_list['dev_id'] = $this->oldsoftlist[0]['dev_id'];


        $this->soft_list['tags'] = $_POST['tags'];
        $tags = '';
        $tags = explode(',', $this->soft_list['tags']);
        if (count($tags) > 5) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error("最多5个关键字，谢谢");
        }
        $this->soft_list['upload_tm'] = time();
        $this->soft_list['last_refresh'] = time();


        if (empty($this->soft_list['tags'])) {
            $tihis->soft_list['tags'] = $this->soft_list['softname'] . $this->soft_list['package'];
        }

        //默认为认证中
        $this->soft_list['safe'] = 0;


        $this->soft_list['deny_msg'] = '首次后台上传';

        //运营商是否隐藏
        $operating = $_POST['operating'];


        if (!empty($operating)) {
            $this->soft_list['operatorhide'] = implode(',', $_POST['operating']);
            //插入运营商隐藏列表
        } else {
            $this->soft_list['operatorhide'] = 0;
        }
        $this->soft_list['hide'] = 1;
        $this->soft_list['status'] = 1;

        $this->soft_db = M('soft');           //软件表
        $this->softid = $this->soft_db->add($this->soft_list);
        if (false == $this->softid) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
            $this->error("添加软件失败,数据插入发生错误！");
        }

        $task_client = get_task_client();
        $task_client->doHighBackground("sync_filter_cache", json_encode(array("softid" => $this->softid, 'line' => __LINE__)));
        $task_client->doBackground("refresh_lack", json_encode(array("softid" => $this->softid)));

        $this->soft_note_list['auth'] = $_POST['auth'] ? $_POST['auth'] : 0;
        $this->soft_note_list['note'] = $_POST['note'];

        $this->soft_note_db = M('soft_note');           //软件表
        $this->map = '';
        $this->map['package'] = $this->soft_list['package'];

        if (false === $this->soft_note_db->where($this->map)->save($this->soft_note_list)) {

            $this->soft_db->where('softid=' . $this->softid)->delete();
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/User/useradd');
            $this->error("添加软件失败,数据插入发生错误！");
        }
        //张辉时间添加语句
        $this->soft_db->table('sj_soft_time')->add(array("softid" => $this->softid, "start_time" => strtotime($_POST['date0']), "end_time" => strtotime($_POST['date1'])));
        //张辉时间添加语句结束

		
		//需要修改
        $this->soft_thumb_db = M('soft_thumb');
        for ($i = 0; $i < count($this->imagelist); $i++) {
            $this->soft_thumb_list['softid'] = $this->softid;
            $this->soft_thumb_list['url'] = $this->imagelist[$i]['url'];
            $this->soft_thumb_list['image_raw'] = $this->imagelist[$i]['url_original'];
            $this->soft_thumb_list['image_thumb'] = $this->imagelist[$i]['url_resize'];
            $this->soft_thumb_list['status'] = 1;
            $this->soft_thumb_list['rank'] = $this->imagelist[$i]['key'];
            $this->soft_thumb_list['upload_time'] = time();
            $this->soft_thumb_list['last_refresh'] = time();
            if (false == $this->soft_thumb_db->add($this->soft_thumb_list)) {

                $this->soft_db->where('softid=' . $this->softid)->delete();
                $this->soft_note_db->where('package=' . $this->soft_note_list['package'])->delete();
                $this->soft_thumb_db->where('softid=' . $this->softid)->delete();

                $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
                $this->error("添加软件失败,数据插入发生错误！");
            }
            $map = '';
            $map['softid'] = $this->sid;
            $map['status'] = 1;
            $map['rank'] = $imagerank[$i];
            $thumbmap = '';
            $thumbmap['status'] = 0;
            $this->soft_thumb_db->where($map)->save($thumbmap);
        }


        $this->soft_thumb_list = '';
        $map = '';
        $map['softid'] = $this->sid;
        $map['status'] = 1;
        $this->soft_thumb_list = $this->soft_thumb_db->where($map)->select();
        for ($i = 0; $i < count($this->soft_thumb_list); $i++) {
            $map = '';
            $map['softid'] = $this->softid;
            $map['url'] = $this->soft_thumb_list[$i]['url'];
            $map['image_raw'] = $this->soft_thumb_list[$i]['image_raw'];
            $map['image_thumb'] = $this->soft_thumb_list[$i]['image_thumb'];
            $map['status'] = 1;
            $map['rank'] = $this->soft_thumb_list[$i]['rank'];
            $map['upload_time'] = $this->soft_thumb_list[$i]['upload_time'];
            $map['last_refresh'] = time();

            if (false == $this->soft_thumb_db->add($map)) {

                $this->soft_db->where('softid=' . $this->softid)->delete();
                $this->soft_note_db->where('package=' . $this->soft_note_list['package'])->delete();
                $this->soft_thumb_db->where('softid=' . $this->softid)->delete();

                $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
                $this->error("添加软件失败,数据插入发生错误！");
            }
        }


        $this->soft_file_db = M('soft_file');

        $this->resolution_list = $_POST['resolution'];
        $minfirame = '';
        $maxfirame = '';

        $minfirame = $_POST['minfirame'];
        $maxfirame = $_POST['maxfirame'];

        //dump($minfirame);
        //dump($maxfirame);
        //dump($this->resolution_list);
        $soft_file_safe_db = M('sj_soft_scan_result');

        $soft_file_insert = array();
        $abi = 0;
        $sql = "select * from sj_soft_permission_details";
        $allperms = $this->soft_db->query($sql);
        $allperms_mp = array();
        foreach ($allperms as $val) {
            $allperms_mp[$val['name']] = $val['id'];
        }
        $sql = "select * from sj_soft_uses_library_details";
        $alllibs = $this->soft_db->query($sql);
        $alllibs_mp = array();
        $id_max = 0;
        foreach ($alllibs as $val) {
            $alllibs_mp[$val['name']] = $val['id'];
            if ($val['id'] > $id_max)
                $id_max = $val['id'];
        }

        for ($i = 0; $i < count($this->apklist); $i++) {
            $key = $this->apklist[$i]['key'];
            $maxfirame[$key] = empty($maxfirame[$key]) ? 0 : $maxfirame[$key];
            $check_key = "{$this->apklist[$i]['supports-screens']}_{$this->apklist[$i]['min_sdk_ver']}_{$this->apklist[$i]['abi']}";
            $abi = $this->apklist[$i]['abi'];
            if (isset($soft_file_insert[$check_key])) {
                for ($j = 0; $j < count($this->apklist); $j++) {
                    unlink($this->apklist[$j]['savepath']);
                }

                foreach ($soft_file_insert as $soft_file_id) {
                    if (empty($soft_file_id))
                        continue;
                    $this->soft_file_db->where('id=' . $soft_file_id)->delete();
                }
                $this->soft_db->where('softid=' . $this->softid)->delete();
                $this->soft_note_db->where('package=' . $this->soft_note_list['package'])->delete();
                $this->soft_thumb_db->where('softid=' . $this->softid)->delete();

                $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/' . $this->returnurl . '');
                $this->error("不允许上传分辨率、最小固件、abi类型相同的包名");
                break;
            }


            $this->soft_file_list['softid'] = $this->softid;
            $this->soft_file_list['package_status'] = 1;
            $this->soft_file_list['apk_name'] = $this->apklist[$i]['packagename'];
            $this->soft_file_list['url'] = $this->apklist[$i]['apkurl_db'];
            $this->soft_file_list['filesize'] = $this->apklist[$i]['size'];
            $this->soft_file_list['screen'] = $this->apklist[$i]['supports-screens'];
            $this->soft_file_list['resolutionid'] = $this->resolution_list[$key];

            $this->soft_file_list['min_firmware'] = $this->apklist[$i]['min_sdk_ver'];
            $this->soft_file_list['max_firmware'] = $maxfirame[$key];

            $this->soft_file_list['iconurl'] = $this->apklist[$i]['iconurl_db'];
            $this->soft_file_list['upload_time'] = time();
            $this->soft_file_list['last_refresh'] = time();
            $this->soft_file_list['safe'] = 0;
            $this->soft_file_list['abi'] = $this->apklist[$i]['abi'];
            $this->soft_file_list['apk_icon'] = $this->apklist[$i]['apk_icon_db'];

            include_once(SERVER_ROOT . '/tools/functions.php');
            $this->soft_file_list['advertisement'] = test_apk_for_3rdparty(UPLOAD_PATH . $this->apklist[$i]['apkurl_db']);

            $type = $this->soft_file_db->add($this->soft_file_list);

            if (false == $type) {
                $this->soft_db->where('softid=' . $this->softid)->delete();
                $this->soft_note_db->where('package=' . $this->soft_note_list['package'])->delete();
                $this->soft_thumb_db->where('softid=' . $this->softid)->delete();

                $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/softadd');
                $this->error("更新软件失败,数据插入发生错误！");
            }
            $soft_file_insert[$check_key] = $type;

            foreach ($this->apklist[$i]['permission'] as $p) {
                if (!isset($allperms_mp[$p]))
                    continue;
                $sql = "insert into sj_soft_permission (`fileid`, `permissionid`) values ({$type}, {$allperms_mp[$p]})";
                $this->soft_file_db->query($sql);
            }
            foreach ($this->apklist[$i]['library'] as $l) {
                if (!isset($alllibs_mp[$l])) {
                    $sql = "insert into sj_soft_uses_library_details (`name`) values ('{$l}')";
                    $this->soft_file_db->query($sql);
                }
            }
            foreach ($this->apklist[$i]['library-optional'] as $l) {
                if (!isset($alllibs_mp[$l])) {
                    $sql = "insert into sj_soft_uses_library_details (`name`) values ('{$l}')";
                    $this->soft_file_db->query($sql);
                }
            }
            $sql = "select * from sj_soft_uses_library_details";
            $alllibs = $this->soft_db->query($sql);
            $alllibs_mp = array();
            foreach ($alllibs as $val) {
                $alllibs_mp[$val['name']] = $val['id'];
            }
            foreach ($this->apklist[$i]['library'] as $l) {
                $sql = "insert into sj_soft_uses_library (`fileid`,`libraryid`,`type`) values ({$type},{$alllibs_mp[$l]},1)";
                $this->soft_file_db->query($sql);
            }
            foreach ($this->apklist[$i]['library-optional'] as $l) {
                $sql = "insert into sj_soft_uses_library (`fileid`,`libraryid`,`type`) values ({$type},{$alllibs_mp[$l]},0)";
                $this->soft_file_db->query($sql);
            }

            include_once(SITE_PATH . "../model/scanSoft.php");

            $soft_hash = scanSoft::getSoftHash(UPLOAD_PATH . $this->soft_file_list['url']);

            $safelist = array(
                'sfid' => $type,
                'hash' => $soft_hash,
                'time_req' => time(),
                'provider' => 1,
            );
            $soft_file_safe_db->add($safelist);
            $params = array(
                "download_url" => ATTACHMENT_HOST . $this->soft_file_list['url'],
                "soft_hash" => $soft_hash,
            );
            $re = scanSoft::requestPostQQ($params);
            $safelist = array(
                'sfid' => $type,
                'hash' => $soft_hash,
                'time_req' => time(),
                'provider' => 2,
            );
            $soft_file_safe_db->add($safelist);
            $re = scanSoft::requestPostAQGJ($params);
			//网秦安全认证	
			$scan_result = array(
				'sfid' => $type,
				'hash' => $soft_hash,
				'provider' => 3,
				'time_req' => time(),
			);
			$soft_file_safe_db -> add($scan_result);
			$re = scanSoft::requestGetWQ($params);
        }

        $this->map = '';
        $this->map['hide'] = '0';
        $this->map['abi'] = $abi;
        $this->soft_db->where('softid=' . $this->sid)->save($this->map);
        import("@.ORG.Http");
        $date = time();
        $modify_type = "delete";
        $this->update_data_log($this->sid, $modify_type, $date); //记录软件删除日志

        $modify_type = "update";
        $this->update_data_log($this->softid, $modify_type, $date); //记录软件更新日志

        $this->writelog('更新了软件ID为' . $this->sid . '的软件');
		if ($action =='caiji' ){
		    $model = new model();
		    $model->table('cj_new_sowftware')->where(array("sid" => $this->sid))->save(array("new_status" => 0));
			$this->assign('jumpUrl', '/index.php/Caiji/Newsoftwarelist/index');
		}else{
			$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_list');
			
		}
        $this->success("更新成功，添加成功！");
    }

    //软件管理__软件撤销
    public function soft_back() {


        $this->softid = $_GET['softid'];

        if (empty($this->softid)) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_list');
            $this->error("非法操作失败,如频繁出现，请联系管理员！");
        }
        $this->soft_db = M('soft');
        $packages = '';
        $this->map['softid'] = $this->softid;
        $packages = $this->soft_db->where($this->map)->getField('package');
        //dump($packages);
        $this->map = '';
        $this->map['package'] = $packages;
        $this->soft_list = $this->soft_db->where($this->map)->count('softid');
        if ($this->soft_list > 1) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_list');
            $this->error("对不起,由于系统中存在该软件的其他信息，因此无法撤销，如有特殊需要，请联系技术人员！");
        } else {
            $map = '';
            $map['status'] = '1';
            $map['hide'] = '2';

            $this->map = '';
            $this->map['softid'] = $this->softid;
            $this->soft_db->where($this->map)->save($map);
            $this->writelog('撤销了软件ID为' . $this->softid . '的软件');
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_list');
            $this->success("更新成功，添加成功！");
        }
    }

    //软件管理__软件下架
    public function soft_undercarriage() {

        $this->softid = $_GET['softid'];

        if (empty($this->softid)) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_list');
            $this->error("非法操作失败,如频繁出现，请联系管理员！");
        }
        $this->soft_db = M('soft');

        $map = '';
        $map['status'] = '1';
        $map['hide'] = '3';
        $map['last_refresh'] = time();
        if (empty($_GET['deny_msg']))
            $this->error("请输入下架原因！！");
        $map['deny_msg'] = $_GET['deny_msg'];
        import("@.ORG.Http");
        $this->map = '';
        if (strstr($this->softid, ",")) {
            $this->map = " `softid` in (" . substr($this->softid, 1) . ") ";
        } else {
            $this->map['softid'] = $this->softid;
        }
        //$this->soft_db->where($this->map)->save($map);
        //dump($this->soft_list);
        //记录删除数据log  for baidu
        if (false !== $this->soft_db->where($this->map)->save($map)) {
            /* 		echo $this -> soft_db -> getLastSql();
              exit; */
            $soft_info = $this->soft_db->where($this->map)->select();
            $date = $map['last_refresh'];
            $modify_type = "delete";
            if (strstr($this->softid, ",")) {
                $softidarr = explode(',', substr($this->softid, 1));
                foreach ($softidarr as $k => $v) {
                    $this->update_data_log($this->softid, $modify_type, $date);
                    $this->writelog('下架了软件ID为' . $v . '的软件,原因为' . $map['deny_msg']);
                }
                $this->success("下架成功！");
            } else {
                $this->update_data_log($this->softid, $modify_type, $date);
                $this->writelog('下架了软件ID为' . $this->softid . '的软件,下架原因为' . $map['deny_msg']);
                $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_list' . '/p/' . $_GET['p'] . '/');
                $this->success("下架成功！");
            }
        } else {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_list' . '/p/' . $_GET['p'] . '/');
            $this->error("下架失败！");
        }
    }

    public function getfeaturelist() {
        $model = M('feature');
        $featurelist = $model->where('status=1')->field('feature_id, name')->order('orderid')->select();
        $this->assign('featurelist', $featurelist);
    }

    //软件管理__审核、编辑、更新、下架列表
    public function soft_new_list() {
	
        $this->getfeaturelist();
        $type = '';
        $hide = '';
        $actioname = '';
        $tmpname = '';
        $type = $_REQUEST['type'];
		
        if (empty($type)) {
            $type = 'new';
        }
        if ($_REQUEST['sosotype'] == "clear") {
            unset($_SESSION['admin']['soft_new']);
            unset($_SESSION['admin']['s_n_post']);
        }
        switch ($type) {
            case 'new':
                $actionname = '新软件列表';
                if ($_SESSION['admin']['soft_new']['soft_tpye'] != 2) {
                    unset($_SESSION['admin']['soft_new']);
                    unset($_SESSION['admin']['s_n_post']);
                    $_SESSION['admin']['soft_new']['soft_tpye'] = '2';
                }
                $tmpname = 'soft_new_list';
                $hide = 2;
                break;
            case 'edit':
                $actionname = '编辑软件列表';
                $tmpname = 'soft_new_list';
                if ($_SESSION['admin']['soft_new']['soft_tpye'] != 4) {
                    unset($_SESSION['admin']['soft_new']);
                    unset($_SESSION['admin']['s_n_post']);
                    $_SESSION['admin']['soft_new']['soft_tpye'] = '4';
                }
                $hide = 4;
                break;
            case 'update':
                $actionname = '更新软件列表';
                $tmpname = 'soft_update_list';
                if ($_SESSION['admin']['soft_new']['soft_tpye'] != 5) {
                    unset($_SESSION['admin']['soft_new']);
                    unset($_SESSION['admin']['s_n_post']);
                    $_SESSION['admin']['soft_new']['soft_tpye'] = '5';
                }
                $hide = 5;
                break;
            case 'below':
                $actionname = '下架软件列表';
                if ($_SESSION['admin']['soft_new']['soft_tpye'] != 3) {
                    unset($_SESSION['admin']['soft_new']);
                    unset($_SESSION['admin']['s_n_post']);
                    $_SESSION['admin']['soft_new']['soft_tpye'] = '3';
                }
                $tmpname = 'soft_below_list';
                $hide = 3;
                break;
            case 'kyaka':
                $actionname = '驳回软件列表';
                if ($_SESSION['admin']['soft_new']['soft_tpye'] != 6) {
                    unset($_SESSION['admin']['soft_new']);
                    unset($_SESSION['admin']['s_n_post']);
                    $_SESSION['admin']['soft_new']['soft_tpye'] = '6';
                }
                $tmpname = 'soft_kyaka_list';
                $hide = 6;
                break;
            case 'reject' :
                $actionname = '驳回软件审核';
                if ($_SESSION['admin']['soft_new']['soft_tpye'] != 7) {
                    unset($_SESSION['admin']['soft_new']);
                    unset($_SESSION['admin']['s_n_post']);
                    $_SESSION['admin']['soft_new']['soft_tpye'] = '7';
                }
                $tmpname = 'soft_new_list';
                $hide = 7;
                break;
        }
		
		$pos = strpos($_SERVER['HTTP_REFERER'], 'soft_global_search');

		if ($pos === false) {
			$softname = $_REQUEST['softname'];
			$dev_name = $_REQUEST['dev_name'];
		} else {
			$softname = urldecode($_REQUEST['softname']);
			$dev_name = urldecode($_REQUEST['dev_name']);
		}

        if (empty($_REQUEST['p'])) {
            $this->map = 'status=1 and hide=' . $hide . ' ';
            if ($hide == 5) {
                $this->order = 'total_downloaded  DESC';
            } else {
                $this->order = 'softid ';
            }
            if (!empty($_REQUEST['softid'])) {
                $_SESSION['admin']['s_n_post']['softid'] = $_REQUEST['softid'];
                $this->map.=' and softid="' . trim($_REQUEST['softid']) . '"';
                $_SESSION['admin']['soft_new']['softid'] = ' and softid="' . trim($_REQUEST['softid']) . '"';
            } else {
                if (!empty($_SESSION['admin']['soft_new']['softid'])) {
                    $this->map .= $_SESSION['admin']['soft_new']['softid'];
                }
            }
            $this->assign("post_softid", $_SESSION['admin']['s_n_post']['softid']);
            if (!empty($_REQUEST['softname'])) {
                $_SESSION['admin']['s_n_post']['softname'] = $softname;
                $this->map.=' and softname like "%' . trim($softname) . '%"';
                $_SESSION['admin']['soft_new']['softname'] = ' and softname like "%' . trim($softname) . '%"';
            } else {
                if (!empty($_SESSION['admin']['soft_new']['softname'])) {
                    $this->map .= $_SESSION['admin']['soft_new']['softname'];
                }
            }
            $this->assign("post_softname", $_SESSION['admin']['s_n_post']['softname']);
            if (!empty($_REQUEST['package'])) {
                $_SESSION['admin']['s_n_post']['package'] = $_REQUEST['package'];
                $this->map.=' and package like "%' . trim($_REQUEST['package']) . '%"';
                $_SESSION['admin']['soft_new']['package'] = ' and package like "%' . trim($_REQUEST['package']) . '%"';
            } else {
                if (!empty($_SESSION['admin']['soft_new']['package'])) {
                    $this->map .= $_SESSION['admin']['soft_new']['package'];
                }
            }
            $this->assign("post_package", $_SESSION['admin']['s_n_post']['package']);

            //广告
            if (!empty($_REQUEST['advertisement'])) {
                $this->assign("advertisement", $_REQUEST['advertisement']);
                $model = new Model();
                $slist = $model->table('sj_soft_file')->where('package_status = 1 and advertisement & ' . $_REQUEST['advertisement'] . '<>0')->field('softid')->select();
                foreach ($slist as $info) {
                    $sid_arr[] = $info['softid'];
                }
                $where = "(" . implode(",", $sid_arr) . ")";
                $this->map .= ' and softid  in ' . $where;
            }

            if (!empty($_REQUEST['dev_name'])) {
                $_SESSION['admin']['s_n_post']['dev_name'] = $dev_name;
                $this->map.=' and dev_name like "%' . trim($dev_name) . '%"';
                $_SESSION['admin']['soft_new']['dev_name'] = ' and dev_name like "%' . trim($dev_name) . '%"';
            } else {
                if (!empty($_SESSION['admin']['soft_new']['dev_name'])) {
                    $this->map .= $_SESSION['admin']['soft_new']['dev_name'];
                }
            }
            $this->assign("post_dev_name", $_SESSION['admin']['s_n_post']['dev_name']);
			
            if (!empty($_REQUEST['categoryid'])) {
                $_SESSION['admin']['s_n_post']['categoryid'] = $_REQUEST['categoryid'];
                $this->map.=' and (SELECT find_in_set  (' . trim($_REQUEST['categoryid']) . ',`category_id`)>0)';
                $_SESSION['admin']['soft_new']['categoryid'] = ' and (SELECT find_in_set  (' . trim($_REQUEST['categoryid']) . ',`category_id`)>0)';
                //$this->map.=' and category_id like "%,'.preg_replace($_REQUEST['categoryid']).',%"';
            } else {
                if (!empty($_SESSION['admin']['soft_new']['categoryid'])) {
                    $this->map .= $_SESSION['admin']['soft_new']['categoryid'];
                }
            }
            $this->assign("post_categoryid", $_SESSION['admin']['s_n_post']['categoryid']);
            $_SESSION['admin']['soft_new']['where'] = $this->map;
            $_SESSION['admin']['soft_new']['order'] = $this->order;
        } else {
            $this->map = $_SESSION['admin']['soft_new']['where'];
            $this->order = $_SESSION['admin']['soft_new']['order'];
        }
        $this->soft_db = M('soft');
        $device_adapter_db = M('device_adapter');
        $device_db = D("Sj.Device");
        import("@.ORG.Page");
        $count = $this->soft_db->where($this->map)->count();

        $Page = new Page($count, 10);
        $this->soft_list = $this->soft_db->where($this->map)->field('softid,package,softname,version,category_id,dev_name,dev_id,dever_email,last_refresh,update_type,total_downloaded,deny_msg,tags')->order($this->order)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		//echo $this->soft_db->getLastSql();

        $this->category_db = M('category');
		$category = D('Sj.Category');
		$array_config=array(
			"categoryid"=>"categoryid",
			"selected"=>$_GET['categoryid']
		);
		$conf_list = $category->getCategory($array_config);
        $this->assign('conflist',$conf_list);
       /*  $this->category_list_parent = $this->category_db->field('category_id,name')->where('status=1 and parentid=0')->order('orderid')->select();

        $this->category_list_child = $this->category_db->field('category_id,name,parentid')->where('status=1 and parentid!=0')->order('orderid')->select();

        for ($i = 0; $i < count($this->category_list_parent); $i++) {

            for ($j = 0; $j < count($this->category_list_child); $j++) {
                if ($this->category_list_child[$j]['parentid'] == $this->category_list_parent[$i]['category_id']) {
                    $this->category_list_parent[$i]['child'][] = $this->category_list_child[$j];
                }
            }
        }
        $this->assign('categorylist', $this->category_list_parent); */

        for ($i = 0; $i < count($this->soft_list); $i++) {
            $softid.=',"' . $this->soft_list[$i]['softid'] . '"';
            $package.=',"' . $this->soft_list[$i]['package'] . '"';
        }

        if ($softid[0] == ',') {
            $softid = substr($softid, 1);
        }
        if ($package[0] == ',') {
            $package = substr($package, 1);
        }
        
        $this->soft_note_db = M('soft_note');
        $map = '';
        $map['package'] = array('in', $package);
        $this->soft_note_list = $this->soft_note_db->field('package,note')->where($map)->select();

        $this->soft_file_db = M('soft_file');
        $map = '';
        $map['softid'] = array('in', $softid);
        $map['package_status'] = array('gt', 0);
        $this->soft_file_list = $this->soft_file_db->field('softid,url,iconurl,advertisement')->where($map)->select();
        
        //安全扫描结果开始
        for ($i = 0; $i < count($this->soft_file_list); $i++) {
            $sfid.=",'" . $this->soft_file_list[$i]['id'] . "'";
        }

        if ($sfid[0] == ',') {
            $sfid = substr($sfid, 1);
        }
        
        $this->scan_result_db = D('soft_scan_result');
        $sql = " SELECT ssr.sfid,ssr.provider,ssr.time_req,ssr.time_rep,ssr.safe,ssf.softid FROM  sj_soft_scan_result AS ssr".
               " LEFT JOIN  sj_soft_file AS ssf  ON ssr.sfid = ssf.id WHERE ssr.sfid IN (".$sfid.")";
        $this->scan_result_list = $this->scan_result_db->query($sql);
        //echo $this -> scan_result_db -> getLastSql();
        for($i=0;$i<count($this->soft_list);$i++){
		     for($j=0;$j<count($this->scan_result_list);$j++){
			      if($this->scan_result_list[$j]['softid'] == $this->soft_list[$i]['softid']){
				       $this->soft_list[$i]['scan_result_list'][] = $this->scan_result_list[$j];
				  }
			 }
		}
        //安全扫描结果结束
        
        $this->category_list_child = $this->category_db->getField('category_id,name');
       //show soft permission
        $perms_db = M("soft_permission");
        $sql = "select ssp.fileid softid,ssp.permissionid permissionid,sspd.des des from sj_soft_permission as ssp left join
         sj_soft_permission_des as sspd  on ssp.permissionid = sspd.permissionid
             where ssp.fileid in ({$softid})
        ";
        $perms_array = $perms_db->query($sql);
        $perms_infos = array();
        foreach ($perms_array as $info) {
            $perms_infos[$info['softid']][$info['permissionid']] = $info['des'];
        }
//end show soft permission
        include_once SERVER_ROOT . '/tools/functions.php';
        # XXX: TMD谁写的？？？
        //广告信息
        $adlist = $this->ad_select();
        if ($this->soft_list && $this->soft_file_list) {
            for ($i = 0; $i < count($this->soft_list); $i++) {
                for ($j = 0; $j < count($this->soft_note_list); $j++) {
                    if ($this->soft_note_list[$j]['package'] == $this->soft_list[$i]['package']) {
                        $this->soft_list[$i]['note'] = $this->soft_note_list[$j]['note'];
                    }
                }
                for ($j = 0; $j < count($this->soft_file_list); $j++) {
                    if ($this->soft_file_list[$j]['softid'] == $this->soft_list[$i]['softid']) {
                        $this->soft_list[$i]['iconurl'] = $this->soft_file_list[$j]['iconurl'];
                        $adv_key = $this->soft_file_list[$j]['advertisement'];
                        $this->soft_list[$i]['advertisement'] = $this->ad($adv_key);
                        //$this->soft_list[$i]['detected_addon'] = test_apk_for_addon('/data/att/m.goapk.com'. $this->soft_file_list[$j]['url']);
                    }
                }
                $cid = explode(',', $this->soft_list[$i]['category_id']);
                for ($k = 0; $k < count($cid); $k++) {
                    $this->soft_list[$i]['category'].=$this->category_list_child[$cid[$k]];
                }
                $where = array();
                $where['package'] = $this->soft_list[$i]['package'];
                $where['status'] = 2;
                $d_adapter = $device_adapter_db->field('did,package')->where($where)->select();
                foreach ($d_adapter as $info) {
                    $device = $device_db->field('dname')->where(array('did' => $info['did']))->select();
                    //$this -> soft_list[$i]['device_adapter'] .= $device[0]['dname'].":<br/>".$info['note']."</br>";
                    $this->soft_list[$i]['device_adapter'] .= $device[0]['dname'];
                }

                $permiss = array('5', '6', '50', '51');
                $permiss_str = '';
                foreach ($perms_infos[$this->soft_list[$i]['softid']] as $key => $info) {
                    if (in_array($key, $permiss) && !empty($info)) {
                        $permiss_str .= $info . "<br/>";
                    }
                }
                $this->soft_list[$i]['permission_desc'] = $permiss_str;
            }
        }

        //驳回信息
        $this->conf_db = D('Sj.Config');
        $this->config_list = $this->conf_db->field('configname,configcontent')->where('config_type="denymsg"')->select();
        

        $Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);
        $this->assign('configlist', $this->config_list);
        $this->assign('actionname', $actionname);
        $this->assign('type', $type);
        $this->assign('softlist', $this->soft_list);
  
        if ($_REQUEST['p'])
            $this->assign('p', $_REQUEST['p']); 
		else
            $this->assign('p', 1);
		
        if ($_REQUEST['lr'])
            $this->assign('lr', $_REQUEST['lr']); 
		else
            $this->assign('lr', 10);
		$this->assign('returnurl', 'soft_new_list/type/' . $type . '/p/' . $_REQUEST['p'] . '/lr/'.$_REQUEST['lr'].'/');	
        $this->assign('softlist', $this->soft_list);
        $this->assign('adlist', $adlist);
        $this->display($tmpname);
    }

    # 在审核和驳回前检查是否有1条以上的审核中的记录，如果有提示报告错误

    public function soft_permit_deny_hook() {
        $this->softid = $_GET['softid'];
        if ($this->softid[0] == ',') {
            $this->softid = substr($this->softid, 1);
        }
        $this->soft_db = M('soft');
        $package = $this->soft_db->where('softid in (' . $this->softid . ')')->field('package')->select();
        if (empty($package)) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_list' . '/p/' . $_GET['p'] . '/');
            $this->error("找不到${_GET['softid']}对应的包，请报告开发人员。");
            return true;
        }
        $package = $package[0]['package'];
        # 2新软件 4编辑软件 5更新软件
        $info = $this->soft_db->where("package='${package}' AND status=1 AND hide in(2,4,5)")->select();
        if (count($info) > 1) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_list' . '/p/' . $_GET['p'] . '/');
            $this->error("软件ID（${_GET['softid']}）状态可能存在问题，请报告开发人员。");
            return true;
        }
        return false;
    }

    //软件管理__软件驳回
    public function soft_deny() {

        $_GET['denymsg'];
        if ($_GET['denymsg'] == "未通过" || $_GET['denymsg'] == "type") {
            $this->error("请填写具体驳回信息！！！");
        }
        if ($this->soft_permit_deny_hook())
            return;

        $type = $_GET['type'];


        if (empty($type)) {
            $type = 'new';
        }

        $this->softid = $_GET['softid'];
        if ($this->softid[0] == ',') {
            $this->softid = substr($this->softid, 1);
        }

        $this->denyid = $_GET['denyid'];
        if (empty($this->denyid)) {
            $this->denymsg = $_GET['denymsg'];
            if (empty($this->denymsg)) {
                $this->error("驳回信息不能为空！！！");
            }
        } else {
            $this->conf_db = D('Sj.Config');
            $this->map = '';
            $this->map['configname'] = $this->denyid;
            $this->map['config_type'] = 'denymsg';
            $this->denymsg = $this->conf_db->where($this->map)->getField('configcontent');
        }
        $this->soft_db = M('soft');
        $this->soft_file_db = M('soft_file');

        $this->map = '';
        $this->map['status'] = 1;
        $this->map['deny_msg'] = $this->denymsg;
		
		switch($type){
		   case $type == 'new';
		        $this->map['hide'] = 3;
				break;
		   case $type == 'reject';
		        $this->map['hide'] = 6;
				break;
		   default: 
		       $this->map['hide'] = 6;
				break;
		}

        $map = '';
        $map['package_status'] = '3';


        if ($type == 'update') {
            $package = '';
            $package = $this->soft_db->where('softid in (' . $this->softid . ')')->field('package')->select();

            $newthumb = "";
            $newthumb['hide'] = 6;
            $newtype = '';
            $newtype['status'] = 0;

            for ($i = 0; $i < count($package); $i++) {
                $newthumb['package'] = $package[$i]['package'];
                $this->soft_db->where($newthumb)->save($newtype);
            }
        }

        if (false !== $this->soft_db->where('softid in (' . $this->softid . ')')->save($this->map) && false !== $this->soft_file_db->where('softid in (' . $this->softid . ')')->save($map)) {
            $this->writelog('驳回了软件ID为' . $this->softid . '的软件，内容为' . $this->denymsg);
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_new_list/type/' . $type . '/p/' . $_GET['p'] . '/');
            $this->success("驳回成功！");
        } else {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_new_list/type/' . $type . '/p/' . $_GET['p'] . '/');
            $this->error("驳回失败！");
        }
    }

    protected function soft_permit_one($softid, $type, &$package = '') {
        $soft_db = M('soft');
        $info = $soft_db->where("softid=${softid}")->select();
        if (!$info)
            return false;
        $package = $info[0]['package'];
        $abi = $info[0]['abi'];
        $map = array('abi' => $abi, 'package' => $package, 'status' => 1, 'hide' => 1);
        $info = $soft_db->where($map)->select();
        $now = time();
        $map = array('hide' => 0, 'last_refresh' => $now);
        # 例如：升级->驳回->编辑->通过
        if (count($info) > 0) {
            $map['deny_msg'] = "change hide to 0(upgrade->deny->edit->accept)";
            $oldsoft = array();
            foreach ($info as $val) {
                if (intval($val['softid']) != intval($softid))
                    $oldsoft[] = $val['softid'];
            }
            $softid_str = implode(",", $oldsoft);
            $result = $soft_db->where("softid in (${softid_str})")->save($map);
            if (!$result)
                return false;
        }

        if ($type == "new")
            $map['upload_tm'] = $now;
        $map['hide'] = 1;
        $result = $soft_db->where("softid=${softid}")->save($map);
        # XXX: 上面的如果改成功了，这里改失败了，回滚吗？回滚失败呢？
        if (!$result)
            return false;
        $soft_file_db = M('soft_file');
        $soft_file_db->where("softid=${softid}")->save(array("package_status" => 1));
        return true;
    }

    //软件管理__软件审核_编辑及新软件通过
    public function soft_permit() {
        if ($this->soft_permit_deny_hook())
            return;
        $softid = $_GET['softid'];
        $type = isset($_GET['type']) ? $_GET['type'] : "new";
		if($_GET['p']){
		  $p = (int)$_GET['p'];
		}else{
		  $p = 1;
		}
		if($_GET['lr']){
		  $lr  = (int)$_GET['lr'];
		}else{
		  $lr  = 10;
		}
        $softids = array();
        if (strstr($softid, ",")) {
            $temp = explode(",", $softid);

            foreach ($temp as $val) {
                if (strlen($val) > 0)
                    $softids[] = $val;
            }
        }
        else {
            $softids[] = $softid;
        }
        $n = 0;
        $c = count($softids);
        $package = '';
        $curr_package = array();
        foreach ($softids as $id) {
            if (!$this->soft_permit_one($id, $type, $package))
                break;
            $n += 1;
            $curr_package[$package] = 1;
            //update
            //记录通过日志 for baidu begin
            $date = time();
            $modify_type = $this->permit_arr[$type];
            $this->update_data_log($id, $modify_type, $date); //记录软件更新日志
            //end 记录通过日志 for baidu
            $sid_str .= $id . ",";
            $this->writelog('审核通过了软件ID为' . $id . '的软件');
        }
        //批量加入专题
        if (!empty($_GET['feature_id'])) {
            $feature_id = $_GET['feature_id'];
            $model = M('feature_soft');
            $feature_soft = $model->where("feature_id={$feature_id} and status=1")->field('package')->select();
            $add_sql = array();
            $add_pkg = array();
            $count = count($feature_soft);
            $feature_package = array();
            foreach ($feature_soft as $item) {
                $feature_package[$item['package']] = 1;
            }
            foreach ($curr_package as $k => $v) {
                if (!isset($feature_package[$k])) {
                    $count++;
                    $add_pkg[] = $k;
                    $add_sql[] = "({$feature_id}, '{$k}', {$count}, 1, 0)";
                }
            }
            if (!empty($add_sql)) {
                $part = implode(',', $add_sql);
                $sql = "insert into sj_feature_soft (`feature_id`, `package`, `rank`, `status`, `special`) values {$part}";
                $model->query($sql);
                $pkg = implode(',', $add_pkg);
                $this->writelog("将包名为{$pkg}的软件加入到id为{$feature_id}的专题");
            }
        }
        if ($n == $c) {
            $task_client = get_task_client();
            $task_client->doHighBackground("sync_filter_cache", json_encode(array("softid" => $softids, 'line' => __LINE__)));
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_new_list/type/' . $type . '/p/' . $p . '/lr/'.$lr.'/');
            $this->success("通过成功！");
        } else {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_new_list/type/' . $type . '/p/' . $p . '/lr/'.$lr.'/');
            $this->error("通过失败（${n}/${c}）！");
        }
    }

    //软件管理__软件列表_软件撤销认领
    public function soft_update_claim() {
        $this->softid = $_GET['softid'];
        if($_GET['p']){
		  $p = (int)$_GET['p'];
		}else{
		  $p = 1;
		}
		if($_GET['lr']){
		  $lr  = (int)$_GET['lr'];
		}else{
		  $lr  = 10;
		}
        $this->soft_db = M('soft');
        $update['dev_id'] = 0;
        $update['claim_status'] = 0;
        $where['softid'] = $this->softid;
        $affect = $this->soft_db->where($where)->save($update);
        if ($affect == 1) {
            $this->writelog('软件ID' . $this->softid . '的认领状态已被撤销');
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_list/'. '/p/' . $p . '/lr/'.$lr.'/');
            $this->success("该软的认领状态已被撤销！");
        } else {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_list' . '/p/' . $p . '/lr/'.$lr.'/');
            $this->error("撤销失败！");
        }
    }

    //软件管理__软件审核_软件更新通过
    public function soft_permit_update() {
        if ($this->soft_permit_deny_hook())
            return;

        $this->softid = $_GET['softid'];
        if ($this->softid[0] == ',') {
            $this->softid = substr($this->softid, 1);
        }


        $type = $_GET['type'];
        if (empty($type)) {
            $type = 'new';
        }
         
		if($_GET['p']){
		  $p = (int)$_GET['p'];
		}else{
		  $p = 1;
		}
		if($_GET['lr']){
		  $lr  = (int)$_GET['lr'];
		}else{
		  $lr  = 10;
		}
		
        $this->soft_db = M('soft');
        $this->soft_file_db = M('soft_file');
        $this->soft_thumb_db = M('soft_thumb');

        //$this->oldsoftlist=$this->soft_db->where('softid in ('.$this->softid.')')->field('softid,package')->select();

        $curr_soft = $this->soft_db->where('softid in (' . $this->softid . ')')->field('package, abi, update_from')->select();
        $curr_package = array();
        for ($i = 0; $i < count($curr_soft); $i++) {
            $curr_abi = $curr_soft[$i]['abi'];
            $curr_package[$curr_soft[$i]['package']] = 1;
            if ($curr_abi == 0)
                $curr_abi = -1;

            $nowtime = time();
            $map = array();
            $map['hide'] = '0';
            $map['last_refresh'] = $nowtime;
            $where = array(
                'package' => $curr_soft[$i]['package'],
                'hide' => 1,
                'status' => 1,
            );
            $infos = $this->soft_db->where($where)->field('abi,softid,package')->select();
            foreach ($infos as $info) {
                if ($info['abi'] == 0)
                    $info['abi'] = -1;
                if (($curr_abi & $info['abi']) == $info['abi']) {  //不懂
                    $where = array(
                        'softid' => $info['softid']
                    );
                    $this->soft_db->where($where)->save($map);
                    //记录通过日志 for baidu begin
                    $date = $nowtime;
                    $mofidy_type = 'delete';
                    $this->update_data_log($info['softid'], $mofidy_type, $date);
                    //下架软件ids
                    $undersids[] = $info['softid'];
                    //end 记录通过日志 for baidu
                }
            }
        }
        import("@.ORG.Http");
        $this->map = '';
        $this->map['status'] = '1';
        $this->map['hide'] = '1';
        $this->map['upload_tm'] = $nowtime;
        $this->map['last_refresh'] = $nowtime;

        $map = '';
        $map['package_status'] = '1';
        $map['upload_time'] = $nowtime;
        //$thumbmap='';
        //$thumbmap['status']='1';
        if (false !== $this->soft_db->where('softid in (' . $this->softid . ')')->save($this->map) && false !== $this->soft_file_db->where('softid in (' . $this->softid . ') and package_status>0')->save($map)) {
            $task_client = get_task_client();
            $data = explode(',', $this->softid);
            $sid_str = '';
            foreach ($data as $sid) {
                //update
                //记录通过日志 for baidu begin
                $date = $nowtime;
                $modify_type = "update";
                $this->update_data_log($sid, $modify_type, $date); //记录软件更新日志
                $sid_str .= $sid . ",";
                //end 记录通过日志 for baidu
            }
            $task_client->doHighBackground("sync_filter_cache", json_encode(array("softid" => $data, 'line' => __LINE__)));
            //批量加入专题
            if (!empty($_GET['feature_id'])) {
                $feature_id = $_GET['feature_id'];
                $model = M('feature_soft');
                $feature_soft = $model->where("feature_id={$feature_id} and status=1")->field('package')->select();
                $add_sql = array();
                $add_pkg = array();
                $count = count($feature_soft);
                $feature_package = array();
                foreach ($feature_soft as $item) {
                    $feature_package[$item['package']] = 1;
                }

                foreach ($curr_package as $k => $v) {
                    if (!isset($feature_package[$k])) {
                        $count++;
                        $add_pkg[] = $k;
                        $add_sql[] = "({$feature_id}, '{$k}', {$count}, 1, 0)";
                    }
                }
                if (!empty($add_sql)) {
                    $part = implode(',', $add_sql);
                    $sql = "insert into sj_feature_soft (`feature_id`, `package`, `rank`, `status`, `special`) values {$part}";
                    $model->query($sql);
                    $pkg = implode(',', $add_pkg);
                    $this->writelog("将包名为{$pkg}的软件加入到id为{$feature_id}的专题");
                }
            }

            $this->writelog('审核通过软件ID为' . $this->softid . '的软件更新');
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_new_list/type/' . $type . '/p/' . $p . '/lr/'.$lr.'/');
            $this->success("通过成功！");
        } else {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_new_list/type/' . $type . '/p/' . $p . '/lr/'.$lr.'/');
            $this->error("通过失败！");
        }
    }

    //导出数据
    public function exportExcel() {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);
        //error_reporting(E_ALL);
        $softlist = array();
        $this->soft_db = M('soft');
        $softdb = M('soft_file');
        $cate_db = M('category');
        $softNote_db = M('soft_note');
        $auth_arr = array('未授权', '已授权');
        $operHide = array(0 => '未隐藏', 1 => '电信', 999 => '全部');
        $softlist = $this->soft_db->where($_SESSION['admin']['soft_list']['where'])->order($_SESSION['admin']['soft_list']['order'])->select();
        foreach ($softlist as $idx => $info) {
            $softfile = $softdb->where('softid =' . $info['softid'])->select();
            $cid = substr($info['category_id'], 1, -1);
            $cateinfo = $cate_db->where('category_id in (' . $cid . ') and status =1')->select();
            foreach ($cateinfo as $cate) {
                $softfile[0]['category_name'] .='[' . $cate['name'] . ']';
            }
            $auths = $softNote_db->where("package = '" . $info['package'] . "'")->field("auth")->select();
            if (empty($auths[0])) {
                $auths[0] = array(
                    'auth' => 0
                );
            }
            $softlist[$idx] = array_merge($info, $softfile[0], $auths[0]);
        }
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="softinfo.csv"');
        header('Cache-Control: max-age=0');
        $fp = fopen('php://output', 'a');
        $head = array('软件ID', '软件名', 'mips适用', '包名', '开发者', '开发者邮箱', '开发者主页', '软件大小', '软件类别', '版本号', '版本', '当前下载量', '原始下载量', '扣除下载量', '评分', '评论数', '授权状态', '运营商隐藏状态', '上传时间', '简介');
        foreach ($head as $i => $v) {
            // CSV的Excel支持GBK编码，一定要转换，否则乱码
            $head[$i] = iconv('utf-8', 'gbk', $v);
        }

        //将数据通过fputcsv写到文件句柄
        fputcsv($fp, $head);
        // 计数器
        $cnt = 0;
        // 每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
        $limit = 100000;
        /* first line */
        //至于读数据应该不用我说了 赋值些变量的问题??
        //表 title
        //echo count($softlist);exit;
        foreach ($softlist as $idx => $info) {
            $cnt++;
            if ($limit == $cnt) { //刷新一下输出buffer，防止由于数据过多造成问题
                ob_flush();
                flush();
                $cnt = 0;
            }
            if ((intval($abi) != 0) && ((intval($abi) & 8) == 0)) {
                $abi = "否";
            } else {
                $abi = "是";
            }
            $list = array();
            $list['softid'] = iconv('utf-8', 'gbk', $info['softid']) . "\t";
            $list['softname'] = iconv('utf-8', 'gbk', str_replace(array("\r\n", "\n", "\r", "\t"), "", $info['softname'])) . "\t";
            $list['abi'] = iconv('utf-8', 'gbk', str_replace(array("\r\n", "\n", "\r", "\t"), "", $info['abi'])) . "\t";
            $list['package'] = iconv('utf-8', 'gbk', $info['package']) . "\t";
            $list['dev_name'] = iconv('utf-8', 'gbk', str_replace(array("\r\n", "\n", "\r", "\t"), "", $info['dev_name'])) . "\t";
            $list['dever_email'] = iconv('utf-8', 'gbk', $info['dever_email']) . "\t";
            $list['dever_page'] = iconv('utf-8', 'gbk', str_replace(array("\r\n", "\n", "\r", "\t"), "", $info['dever_page'])) . "\t";
            $list['filesize'] = iconv('utf-8', 'gbk', $info['filesize']) . "\t";
            $list['category_name'] = iconv('utf-8', 'gbk', str_replace(array("\r\n", "\n", "\r", "\t"), "", $info['category_name'])) . "\t";
            $list['version'] = iconv('utf-8', 'gbk', str_replace(array("\r\n", "\n", "\r", "\t"), "", $info['version'])) . "\t";
            $list['version_code'] = iconv('utf-8', 'gbk', str_replace(array("\r\n", "\n", "\r", "\t"), "", $info['version_code'])) . "\t";
            $list['zh_xia'] = ($info['total_downloaded'] - $info['total_downloaded_detain'] + $info['total_downloaded_add']) . "\t";
            $list['total_downloaded'] = iconv('utf-8', 'gbk', $info['total_downloaded']) . "\t";
            $list['total_downloaded_detain'] = iconv('utf-8', 'gbk', $info['total_downloaded_detain']) . "\t";
            $list['total_downloaded_add'] = iconv('utf-8', 'gbk', $info['total_downloaded_add']) . "\t";
            $list['score'] = iconv('utf-8', 'gbk', str_replace(array("\r\n", "\n", "\r", "\t"), "", $info['score'])) . "\t";
            $list['msgnum'] = iconv('utf-8', 'gbk', str_replace(array("\r\n", "\n", "\r", "\t"), "", $info['msgnum'])) . "\t";
            $list['auth'] = iconv('utf-8', 'gbk', $auth_arr[$info['auth']]) . "\t";
            $list['operatorhide'] = iconv('utf-8', 'gbk', $operHide[$info['operatorhide']]) . "\t";
            $list['date'] = date('Y-m-d H:i:s', (int) $info['upload_tm']) . "\t";
            $list['intro'] = iconv('utf-8', 'gbk', str_replace(array("\r\n", "\n", "\r", "\t"), "", $info['intro'])) . "\t";
            fputcsv($fp, $list);
        }
    }

    //不安全软件列表
    public function soft_unsafe_list() {
        if (empty($_GET['p'])) {
            $this->map = 'safe>=2';
            $this->order = 'last_refresh desc';
            if (!empty($_POST['softid'])) {
                $this->map.=" and softid='" . trim($_POST['softid']) . "'";
            }
            if (!empty($_POST['softname'])) {
                $this->map.=' and softname like "%' . trim($_POST['softname']) . '%"';
            }
            if (!empty($_POST['package'])) {
                $this->map.=' and package like "%' . trim($_POST['package']) . '%"';
            }
            if (!empty($_GET['package'])) {
                $this->map.=' and package like "%' . trim($_GET['package']) . '%"';
            }
            if (!empty($_POST['dev_name'])) {
                $this->map.=' and dev_name like "%' . trim($_POST['dev_name']) . '%"';
            }
            if (!empty($_GET['dev_name'])) {
                $this->map.=' and dev_name like "%' . trim($_GET['dev_name']) . '%"';
            }
            if (!empty($_REQUEST['softinfo'])) {
                $this->map.=' and intro like "%' . trim($_REQUEST['softinfo']) . '%"';
            }
            if (!empty($_GET['dev_id'])) {
                $this->map.=' and dev_id="' . trim($_GET['dev_id']) . '"';
            }
            //广告
            //var_dump($_GET);
            if (!empty($_POST['advertisement'])) {
                $this->assign("advertisement", $_POST['advertisement']);
                $model = new Model();
                $slist = $model->table('sj_soft_file')->where('package_status = 1 and advertisement =' . $_POST['advertisement'] . ' <>0')->field('softid')->select();
                foreach ($slist as $info) {
                    $sid_arr[] = $info['softid'];
                }
                $where = "(" . implode(",", $sid_arr) . ")";
                $this->map .= ' and softid  in ' . $where;
            }
            if (!empty($_POST['categoryid'])) {

                $this->map.=' and (SELECT find_in_set  (' . trim($_POST['categoryid']) . ',`category_id`)>0)';
                //$this->map.=' and category_id like "%,'.preg_replace($_POST['categoryid']).',%"';
            }
            if ($_POST['operatorhide'] != '999' && $_POST['operatorhide'] != '') {

                $this->map.=' and (SELECT find_in_set  (' . trim($_POST['operatorhide']) . ',`operatorhide`)>0)';
                //$this->map.=' and category_id like "%,'.preg_replace($_POST['categoryid']).',%"';
            }

            $_SESSION['admin']['soft_list']['where'] = $this->map;
            $_SESSION['admin']['soft_list']['order'] = $this->order;
        } else {
            $this->map = $_SESSION['admin']['soft_list']['where'];
            $this->order = $_SESSION['admin']['soft_list']['order'];
        }

        $this->soft_db = M('soft');
        import("@.ORG.Page");
        $count = $this->soft_db->where($this->map)->count();
        $Page = new Page($count, 15);
        $this->soft_list = $this->soft_db->where($this->map)->field('softid,package,softname,version,category_id,dev_name,dever_email,total_downloaded,operatorhide,dev_id,score,left(intro,60) intro,last_refresh,update_type')->order($this->order)->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $this->category_db = M('category');
		$category = D('Sj.Category');
		$array_config=array(
			"categoryid"=>"categoryid",
			"selected"=>$_GET['categoryid']
		);
		$conf_list = $category->getCategory($array_config);
        $this->assign('conflist',$conf_list);
        $this->category_db = M('category');
        /* $this->category_list_parent = $this->category_db->field('category_id,name')->where('status=1 and parentid=0')->order('orderid')->select();

        $this->category_list_child = $this->category_db->field('category_id,name,parentid')->where('status=1 and parentid!=0')->order('orderid')->select();

        for ($i = 0; $i < count($this->category_list_parent); $i++) {

            for ($j = 0; $j < count($this->category_list_child); $j++) {
                if ($this->category_list_child[$j]['parentid'] == $this->category_list_parent[$i]['category_id']) {
                    $this->category_list_parent[$i]['child'][] = $this->category_list_child[$j];
                }
            }
        }
        $this->assign('categorylist', $this->category_list_parent); */



        for ($i = 0; $i < count($this->soft_list); $i++) {
            $softid.=",'" . $this->soft_list[$i]['softid'] . "'";
            $package.=',"' . $this->soft_list[$i]['package'] . '"';
        }


        if ($softid[0] == ',') {
            $softid = substr($softid, 1);
        }
        if ($package[0] == ',') {
            $package = substr($package, 1);
        }
        
        $this->soft_note_db = M('soft_note');
        $map = '';
        $map['package'] = array('in', $package);
        $this->soft_note_list = $this->soft_note_db->field('package,left(note,60) note,auth')->where($map)->select();

        $this->soft_file_db = M('soft_file');
        $map = '';
        $map['softid'] = array('in', $softid);
        $this->soft_file_list = $this->soft_file_db->field('softid,advertisement,iconurl,id')->where($map)->select();
        
        //安全扫描结果开始
        for ($i = 0; $i < count($this->soft_file_list); $i++) {
            $sfid.=",'" . $this->soft_file_list[$i]['id'] . "'";
        }

        if ($sfid[0] == ',') {
            $sfid = substr($sfid, 1);
        }
        
        $this->scan_result_db = D('soft_scan_result');
        $sql = " SELECT ssr.sfid,ssr.provider,ssr.time_req,ssr.time_rep,ssr.safe,ssf.softid FROM  sj_soft_scan_result AS ssr".
               " LEFT JOIN  sj_soft_file AS ssf  ON ssr.sfid = ssf.id WHERE ssr.sfid IN (".$sfid.")";
        $this->scan_result_list = $this->scan_result_db->query($sql);
        //echo $this -> scan_result_db -> getLastSql();
        for($i=0;$i<count($this->soft_list);$i++){
		     for($j=0;$j<count($this->scan_result_list);$j++){
			      if($this->scan_result_list[$j]['softid'] == $this->soft_list[$i]['softid']){
				       $this->soft_list[$i]['scan_result_list'][] = $this->scan_result_list[$j];
				  }
			 }
		}
        //安全扫描结果结束
        
        
        $perms_db = M("soft_permission");
        $sql = "select ssp.fileid softid,ssp.permissionid permissionid,sspd.des des from sj_soft_permission as ssp left join
         sj_soft_permission_des as sspd  on ssp.permissionid = sspd.permissionid
             where ssp.fileid in ({$softid})
        ";
        $perms_array = $perms_db->query($sql);
        $perms_infos = array();
        foreach ($perms_array as $info) {
            $perms_infos[$info['softid']][$info['permissionid']] = $info['des'];
        }

        $map = '';
        $map['sfid'] = array('in', array_map(create_function('$a', 'return $a["id"];'), $this->soft_file_list));
        $map['safe'] = array("egt", 2);
        $scan_result_db = M('soft_scan_result');
        $scan_result_list = $scan_result_db->field('sfid,provider,description')->where($map)->select();
        $scan_result_hash = array();
        foreach ($scan_result_list as $val) {
            $des = $val['description'];
            if ($val['provider'] == 1 && substr($val['description'], 0, 1) == '{') {
                $des = json_decode($val['description'], true);
                $des = $des["response"]["trojan"]["description"];
            } elseif ($val['provider'] == 2 && substr($val['description'], 0, 1) == '{') {
                $r_des = json_decode($val['description'], true);
                $des = $r_des["des"];
                if (empty($des))
                    $des = $r_des["app"]["des"];
                if (empty($des))
                    $des = $r_des["res"];
            } elseif ($val['provider'] == 3 && substr($val['description'], 0, 1) == '{') {
				$r_des = json_decode($val['description'], true);
				$des = $r_des['ScanInfo']['responseInfo']['reason'];
				if(empty($des)){
					$des = $val['description'];
				}
			}
            $scan_result_hash[$val['sfid']] .= $des . "<br>";
        }

        $this->operating_db = D('Sj.Operating');
        $this->operating_list = $this->operating_db->where('only_auth=0')->field('oid,mname')->select();

        $this->lists = '';

        for ($i = 0; $i < count($this->operating_list); $i++) {
            $this->lists[$this->operating_list[$i]['oid']] = $this->operating_list[$i]['mname'];
        }
        //dump($this->lists);
        $this->lists[0] = '不隐藏';
        //dump($this->lists);


        $this->category_list_child = $this->category_db->getField('category_id,name');

        # 这复杂度爆表了。。。
        $adlist = $this->ad_select();
        if ($this->soft_list && $this->soft_file_list) {
            for ($i = 0; $i < count($this->soft_list); $i++) {
                for ($j = 0; $j < count($this->soft_note_list); $j++) {
                    if ($this->soft_note_list[$j]['package'] == $this->soft_list[$i]['package']) {
                        $this->soft_list[$i]['note'] = $this->soft_note_list[$j]['note'];
                        $this->soft_list[$i]['auth'] = $this->soft_note_list[$j]['auth'];
                    }
                }
                for ($j = 0; $j < count($this->soft_file_list); $j++) {
                    if ($this->soft_file_list[$j]['softid'] == $this->soft_list[$i]['softid']) {
                        $this->soft_list[$i]['iconurl'] = $this->soft_file_list[$j]['iconurl'];
                        $adv_key = $this->soft_file_list[$j]['advertisement'];
                        $this->soft_list[$i]['advertisement'] = $this->ad($adv_key);
                        $result = $scan_result_hash[$this->soft_file_list[$j]['id']];

                        $this->soft_list[$i]['scan_result'] = $result;
                    }
                }
                $cid = explode(',', $this->soft_list[$i]['category_id']);
                for ($k = 0; $k < count($cid); $k++) {
                    $this->soft_list[$i]['category'].=$this->category_list_child[$cid[$k]];
                }
                $oid = explode(',', $this->soft_list[$i]['operatorhide']);
                for ($k = 0; $k < count($oid); $k++) {
                    $this->soft_list[$i]['operatorhides'].=$this->lists[$oid[$k]];
                }
                $permiss = array('5', '6', '50', '51');
                $permiss_str = '';
                foreach ($perms_infos[$this->soft_list[$i]['softid']] as $key => $info) {
                    if (in_array($key, $permiss) && !empty($info)) {
                        $permiss_str .= $info . "<br/>";
                    }
                }
                $this->soft_list[$i]['permission_desc'] = $permiss_str;
            }
        }

        $Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);

        if ($_GET['p'])
            $this->assign('p', '?p=' . $_GET['p']); else
            $this->assign('p', '');
        $this->assign('adlist', $adlist);
        $this->assign('softlist', $this->soft_list);
        $this->assign('operatinglist', $this->operating_list);
        $this->display();
    }

    public function soft_safe() {
        $this->softid = $_GET['softid'];

        if (empty($this->softid)) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_list');
            $this->error("非法操作失败,如频繁出现，请联系管理员！");
        }
        $this->soft_db = M('soft');

        $map = '';
        $map['safe'] = '1';

        $this->map = '';
        $this->map['softid'] = $this->softid;
        $this->soft_db->where($this->map)->save($map);
        $this->writelog('修改软件ID为' . $this->softid . '的软件为安全');
        $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_unsafe_list');
        $this->success("更新成功，添加成功！");
    }

    public function setOnlySearch() {
        $this->softid = $_GET['softid'];

        if (empty($this->softid)) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_list');
            $this->error("非法操作失败,如频繁出现，请联系管理员！");
        }
        $this->soft_db = M('soft');

        $map = '';
        $map['only_search'] = $_GET['value'];

        $this->map = '';
        $this->map['softid'] = $this->softid;
        $this->soft_db->where($this->map)->save($map);
        $this->writelog('修改软件ID为' . $this->softid . '的软件"仅搜索显示"为' . $_GET['value']);
        $this->success("更新成功！");
    }

    function checkSoftwareState($old, $new) {
        #0历史，1正常，2新软件审核，3下架，4编辑审核，5更新审核，6驳回
        $valid_state = array(
            0 => array(),
            1 => array(0, 3, 4, 5),
            2 => array(3, 6),
            3 => array(4, 5),
            4 => array(6),
            5 => array(6),
            6 => array(2, 4, 5),
        );
        return in_array($new, $valid_state[$old]);
    }

    function soft_undo_deny() {
        
    }

    function soft_preview() {
        if (empty($_GET['softid'])) {
            $this->error("软件ID未提供。");
        }
        $softid = $_GET['softid'];
        $soft_db = M('soft');
        $soft_info = $soft_db->where(array("softid" => $softid))->select();
        if (empty($soft_info)) {
            $this->error("软件${softid}未找到。");
        }
        $soft_thumb_db = M('soft_thumb');
        $soft_thumb_info = $soft_thumb_db->where(array("softid" => $softid, "status" => 1))->select();
        $soft_file_db = M('soft_file');
        $soft_file_info = $soft_file_db->where("softid=${softid} AND package_status > 0")->select();
        $soft_info = $soft_info[0];
        foreach ($soft_file_info as $v) {
            $soft_info['iconurl'] = $v['iconurl'];
            $soft_info['fileurl'] = $v['url'];
        }
        $this->assign('soft_info', $soft_info);
        $this->assign('soft_thumb_info', $soft_thumb_info);
        $this->display();
    }

    function soft_category_list() {
        $pid = $_GET['id'];
        $cate_db = M('category');
        $map['status'] = 1;
        $map['parentid'] = 0;
        $categary_list = $cate_db->where($map)->field('category_id,name,orderid')->order('orderid')->select();

        $this->assign('conflist', $categary_list);

        if (empty($pid)) {
            $pid = $categary_list[0]['category_id'];
        }
        $lists = $cate_db->where('status=1 and parentid=' . $pid)->order('orderid')->select();
		foreach($lists as $key=>$val){
			$lists[$key]['category_id']=$val['category_id'];
			//echo $val['category_id'];
			$list_three=$cate_db->where('status=1 and parentid='.$lists[$key]['category_id'])->order('orderid')->select();
			//print_r($list_three);
			foreach($list_three as $k=>$info){
				//echo $info['category_id'];
				$three[$lists[$key]['category_id']][$k]['category_id']=$info['category_id'];
				$three[$lists[$key]['category_id']][$k]['name']=$info['name'];
				$three[$lists[$key]['category_id']][$k]['parentid']=$info['parentid'];
				$three[$lists[$key]['category_id']][$k]['orderid']=$info['orderid'];
				$three[$lists[$key]['category_id']][$k]['typical']=$info['typical'];
			}
		}
        $this->assign('lists', $lists);
        $this->assign('thepid', $pid);
		$this->assign('three',$three);
        $this->display("soft_category_list");
    }

    function soft_batch_category() {
        $category_id = $_GET['category_id'];

        $known_abis = array(
            'armeabi' => ABI_ARMEABI,
            'armeabi-v7a' => ABI_ARMEABI_V7A,
            'x86' => ABI_X86,
            'mips' => ABI_MIPS,
        );

        $this->map = 'status=1 and hide=1';
        $tag = (isset($_GET['match']) && $_GET['match'] == 1) ? "=" : "<>";
        $match = (isset($_GET['match']) && $_GET['match'] == 1) ? 1 : 0;
        $this->assign('match', $match);
        $this->map .= " and category_id {$tag} ',{$category_id},' ";
        $this->order = 'last_refresh desc';
        if (!empty($_GET['softname'])) {
            $this->assign("softname", $_GET['softname']);
            $this->assign("searchkey", $_GET['softname']);
            $this->map.=' and softname like "%' . trim($_GET['softname']) . '%"';
        }
        if (!empty($_GET['package'])) {
            $this->assign("package", $_GET['package']);
            $this->assign("searchkey", $_GET['package']);
            $this->map.=' and package like "%' . trim($_GET['package']) . '%"';
        }
        if (!empty($_GET['dev_name'])) {
            $this->assign("dev_name", $_GET['dev_name']);
            $this->assign("searchkey", $_GET['dev_name']);
            $this->map.=' and dev_name like "%' . trim($_GET['dev_name']) . '%"';
        }
        if (!empty($_GET['softinfo'])) {
            $this->assign("softinfo", $_GET['softinfo']);
            $this->assign("searchkey", $_GET['softinfo']);
            $this->map.=' and intro like "%' . trim($_GET['softinfo']) . '%"';
        }

        $_SESSION['admin']['soft_list']['where'] = $this->map;
        $_SESSION['admin']['soft_list']['order'] = $this->order;

        $this->soft_db = M('soft');
        import("@.ORG.Page");
        $count = $this->soft_db->where($this->map)->count();
        $param = http_build_query($_GET);
        $Page = new Page($count, 15, $param);
        $this->soft_list = $this->soft_db->where($this->map)->field('softid,package,softname,version,category_id,dev_name,dever_email,total_downloaded,operatorhide,dev_id,score,left(intro,60) intro,last_refresh,only_search,abi')->order($this->order)->limit($Page->firstRow . ',' . $Page->listRows)->select();

        /* $this->category_db = M('category');
        $this->category_list_parent = $this->category_db->field('category_id,name')->where('status=1 and parentid=0')->order('orderid')->select();
        $this->category_list_child = $this->category_db->field('category_id,name,parentid')->where('status=1 and parentid!=0')->order('orderid')->select();
        for ($i = 0; $i < count($this->category_list_parent); $i++) {
            for ($j = 0; $j < count($this->category_list_child); $j++) {
                if ($this->category_list_child[$j]['parentid'] == $this->category_list_parent[$i]['category_id']) {
                    $this->category_list_parent[$i]['child'][] = $this->category_list_child[$j];
                }
            }
        }
        $this->assign('categorylist', $this->category_list_parent); */
		$this->category_db = M('category');
        $conf_list=$this->category_db->where("status=1 and parentid=0")->field('category_id,name,orderid')->order('orderid')->select();
		foreach($conf_list as $m=>$n){
			
			$lists=$this->category_db->where('status=1 and parentid='.$n['category_id'])->order('orderid')->select();
			$conf_list[$m][$n['category_id']]=$lists;
			 foreach($lists as $key=>$val){
				$lists[$key]['category_id']=$val['category_id'];
				$list_three=$this->category_db->where('status=1 and parentid='.$lists[$key]['category_id'])->order('orderid')->select();
				foreach($list_three as $k=>$info){
					$three[$lists[$key]['category_id']][$k]['category_id']=$info['category_id'];
					$three[$lists[$key]['category_id']][$k]['name']=$info['name'];
					$three[$lists[$key]['category_id']][$k]['parentid']=$info['parentid'];
					$three[$lists[$key]['category_id']][$k]['orderid']=$info['orderid'];
					$three[$lists[$key]['category_id']][$k]['typical']=$info['typical'];
				}
			} 
		}
		//print_r($conf_list);
        $this->assign('conflist',$conf_list);
		$this->assign('three',$three);
        for ($i = 0; $i < count($this->soft_list); $i++) {
            $softid.=',"' . $this->soft_list[$i]['softid'] . '"';
            $package.=',"' . $this->soft_list[$i]['package'] . '"';
        }


        if ($softid[0] == ',') {
            $softid = substr($softid, 1);
        }
        if ($package[0] == ',') {
            $package = substr($package, 1);
        }
        $this->soft_note_db = M('soft_note');
        $map = '';
        $map['package'] = array('in', $package);
        $this->soft_note_list = $this->soft_note_db->field('package,left(note,60) note,auth')->where($map)->select();

        $this->soft_file_db = M('soft_file');
        $map = '';
        $map['softid'] = array('in', $softid);
        $map['package_status'] = 1;
        $this->soft_file_list = $this->soft_file_db->field('softid,iconurl,url')->where($map)->select();

        $this->operating_db = D('Sj.Operating');

        $this->operating_list = $this->operating_db->where('only_auth=0')->field('oid,mname')->select();

        $this->lists = '';

        for ($i = 0; $i < count($this->operating_list); $i++) {
            $this->lists[$this->operating_list[$i]['oid']] = $this->operating_list[$i]['mname'];
        }
        $this->lists[0] = '不隐藏';
        $this->category_list_child = $this->category_db->getField('category_id,name');

        include_once SERVER_ROOT . '/tools/functions.php';
        for ($i = 0; $i < count($this->soft_list); $i++) {
            for ($j = 0; $j < count($this->soft_note_list); $j++) {
                if ($this->soft_note_list[$j]['package'] == $this->soft_list[$i]['package']) {
                    $this->soft_list[$i]['note'] = $this->soft_note_list[$j]['note'];
                    $this->soft_list[$i]['auth'] = $this->soft_note_list[$j]['auth'];
                }
            }
            for ($j = 0; $j < count($this->soft_file_list); $j++) {
                if ($this->soft_file_list[$j]['softid'] == $this->soft_list[$i]['softid']) {
                    $this->soft_list[$i]['iconurl'] = $this->soft_file_list[$j]['iconurl'];
                    //$this->soft_list[$i]['detected_addon'] = test_apk_for_addon('/data/att/m.goapk.com'. $this->soft_file_list[$j]['url']);
                }
            }
            $cid = explode(',', $this->soft_list[$i]['category_id']);
            for ($k = 0; $k < count($cid); $k++) {
                $this->soft_list[$i]['category'].=$this->category_list_child[$cid[$k]];
            }
            $oid = explode(',', $this->soft_list[$i]['operatorhide']);
            for ($k = 0; $k < count($oid); $k++) {
                $this->soft_list[$i]['operatorhides'].=$this->lists[$oid[$k]];
            }

            foreach ($known_abis as $abi_key => $abi_value) {
                if ($abi_value & $this->soft_list[$i]['abi'] || $this->soft_list[$i]['abi'] == 0)
                    $this->soft_list[$i]['abis'][] = $abi_key;
            }
        }
        $Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);
        if ($_GET['p'])
            $this->assign('p', '?p=' . $_GET['p']); else
            $this->assign('p', '');
        $this->assign("count", $count);
        $this->assign('softlist', $this->soft_list);
        $this->assign('category_id', $category_id);
        $this->assign('category_list', $this->category_list_child);
        $this->display("soft_batch_category");
    }

    function soft_modify_category() {
        $soft_db = M("soft");
        $category_id = $_POST['category_id'];
        $category_id_old = $_POST['category_id_old'];
        $softid_arr = $_POST['id'];
        $martch_str = $_POST['match'] ? "match/" . $_POST['match'] : '';
        $softid_str = "(" . implode(',', $softid_arr) . ")";
        $data['category_id'] = ",{$category_id},";
        $data['last_refresh'] = time();
        $affect = 0;
        $affect = $soft_db->where("softid in " . $softid_str)->save($data);
        if ($affect) {
            $this->writelog('修改softid为[' . implode(',', $softid_arr) . ']软件 的 软件类别ID 为' . $category_id);
            if ($category_id_old)
                $category_id = $category_id_old;
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_batch_category/' . $martch_str . '/category_id/' . $category_id . '/p/' . $_GET['p']);
            $this->success("更新成功，添加成功！");
        }else {
            $this->error("修改失败！！");
        }
    }

    function soft_app2sd_list() {
        import("@.ORG.Page");
        $soft_app2sd_db = M("soft_app2sd");
        $soft_app2info_db = M("soft_app_info");
        $appinfo_arr = $soft_app2info_db->getField("id,name");
        $where = " where ";
        if ($_GET['srch_key']) {
            if ($_GET['type'] == 1) {
                $where .=" ss.softid = " . $_GET['srch_key'] . " and ";
            } else if ($_GET['type'] == 2) {
                $where .=" ss.package like  '%" . $_GET['srch_key'] . "%' and ";
            } else if ($_GET['type'] == 3) {
                $where .=" ss.softname like '%" . $_GET['srch_key'] . "%' and ";
            }
        }
        if ($_GET['srch_type_key']) {
            $where .="ssa.app2info_id = " . $_GET['srch_type_key'] . " and ";
        }
        $where .= " ssf.package_status = 1 and ss.hide = 1 and ss.status = 1 ";
        $sql = "select ss.softid softid,ss.softname softname,ss.package package,ssa.app2info_id from sj_soft as ss join sj_soft_file as ssf on ss.softid = ssf.softid join sj_soft_app2sd as ssa on
         ssf.id = ssa.fileid ";
        $sql .=$where;
        $result = $soft_app2sd_db->query($sql);
        $count = count($result);
        $Page = new Page($count, 20);
        $Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);
        $limit = " limit " . $Page->firstRow . ',' . $Page->listRows;
        $order = " ORDER BY ss.softid DESC ";
        $sql .= $order;
        $sql .= $limit;
        $soft_list = $soft_app2sd_db->query($sql);
        $this->assign("type", $_GET['type']);
        $this->assign("srch_type_key", $_GET['srch_type_key']);
        $this->assign("appinfo_arr", $appinfo_arr);
        $this->assign("search_key", $_GET['srch_key']);
        $this->assign("softlist", $soft_list);
        $this->display("soft_app2sd_list");
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
        $soft_modify['type'] = $type;
        $soft_modify['date'] = $date;
        $h = date("H", $date);
		permanentlog("data_modify_{$H}.log",json_encode($soft_modify));
        //file_put_contents("{$dir}/data_modify.log", json_encode($soft_modify) . "\n", FILE_APPEND);
    }

    function request_data_log($method, $request_url, $softid, $type, $date) {
        $dir = P_LOG_DIR . "/admin.goapk.com/" . date("Y-m-d", $date);
        if (!is_dir($dir))
            mkdir($dir, 0755);
        file_put_contents($dir . "/curl.log", date("Y-m-d H:i:s", $date) . '=> Took  method ' . $method . ' to send a request to ' . $request_url . "in to softid " . $softid . " " . $type . " \n", FILE_APPEND);
    }

    public function soft_list_cid() {
        $known_abis = array(
            'armeabi' => ABI_ARMEABI,
            'armeabi-v7a' => ABI_ARMEABI_V7A,
            'x86' => ABI_X86,
            'mips' => ABI_MIPS,
        );

        $this->map = 'status=1 and hide=1024  and channel_id <> ""';
        $this->order = 'last_refresh desc';
        if (!empty($_GET['softid'])) {
            $this->assign("softid", $_GET['softid']);
            $this->map.=' and softid="' . trim($_GET['softid']) . '"';
        }
        if (!empty($_GET['softname'])) {
            $this->assign("softname", $_GET['softname']);
            $this->map.=' and softname like "%' . trim($_GET['softname']) . '%"';
        }
        if (!empty($_GET['package'])) {
            $this->assign("package", $_GET['package']);
            $this->map.=' and package like "%' . trim($_GET['package']) . '%"';
        }
        if (!empty($_GET['dev_name'])) {
            $this->assign("dev_name", $_GET['dev_name']);
            $this->map.=' and dev_name like "%' . trim($_GET['dev_name']) . '%"';
        }
        if (!empty($_GET['softinfo'])) {
            $this->assign("softinfo", $_GET['softinfo']);
            $this->map.=' and intro like "%' . trim($_GET['softinfo']) . '%"';
        }
        if (!empty($_GET['dev_id'])) {
            $this->assign("dev_id", $_GET['dev_id']);
            $this->map.=' and dev_id="' . trim($_GET['dev_id']) . '"';
        }
        if (isset($_GET['only_search'])) {
            $this->assign("only_search", $_GET['only_search']);
            $s = trim($_GET['only_search']) == 'y' ? 1 : 0;
            $this->map.=' and only_search="' . $s . '"';
        }
        //广告
        if (!empty($_GET['advertisement'])) {
            $this->assign("advertisement", $_GET['advertisement']);
            $model = new Model();
            $slist = $model->table('sj_soft_file')->where('package_status = 1 and advertisement & ' . $_GET['advertisement'] . ' <> 0')->field('softid')->select();
            foreach ($slist as $info) {
                $sid_arr[] = $info['softid'];
            }
            $where = "(" . implode(",", $sid_arr) . ")";
            $this->map .= ' and softid  in ' . $where;
        }
        if (!empty($_GET['categoryid'])) {
            $this->assign("categoryid", $_GET['categoryid']);
            $this->map.=' and (SELECT find_in_set  (' . trim($_GET['categoryid']) . ',`category_id`)>0)';
            //$this->map.=' and category_id like "%,'.preg_replace($_POST['categoryid']).',%"';
        }
        $this->assign("operatorhide", '999');
        if ($_GET['operatorhide'] != '999' && $_GET['operatorhide'] != '') {
            $this->assign("operatorhide", $_GET['operatorhide']);
            $this->map.=' and (SELECT find_in_set  (' . trim($_GET['operatorhide']) . ',`operatorhide`)>0)';
            //$this->map.=' and category_id like "%,'.preg_replace($_POST['categoryid']).',%"';
        }
        foreach ($_GET as $k => $v) {
            if (strstr($k, 'channel')) {
                $chldata[] = '%,' . $_GET[$k] . ',%';
            }
        }
        if (is_array($chldata)) {
            $str = " and ( ";
            foreach ($chldata as $k => $v) {
                $str .=" channel_id like '" . $v . "' or ";
            }
            $str= substr($str, 0, -3);
			$str .=") ";
            $this->map .= $str;
        }
        $_SESSION['admin']['soft_list']['where'] = $this->map;
        $_SESSION['admin']['soft_list']['order'] = $this->order;

        $this->soft_db = M('soft');
        import("@.ORG.Page");
        $count = $this->soft_db->where($this->map)->count();
        $param = http_build_query($_GET);
        $Page = new Page($count, 15, $param);
        $this->soft_list = $this->soft_db->where($this->map)->field('softid,package,softname,version,category_id,dev_name,dever_email,total_downloaded,total_downloaded_detain,total_downloaded_add,(total_downloaded-total_downloaded_detain+total_downloaded_add) as detain,operatorhide,dev_id,score,left(intro,60) intro,last_refresh,only_search,abi,update_type,channel_id,type,tags')->order($this->order)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		
		$category = D('Sj.Category');
		$array_config=array(
			"categoryid"=>"categoryid",
			"selected"=>$_GET['categoryid']
		);
		$conf_list = $category->getCategory($array_config);
        $this->assign('conflist',$conf_list);
        $this->category_db = M('category');
		/* 
        $this->category_list_parent = $this->category_db->field('category_id,name')->where('status=1 and parentid=0')->order('orderid')->select();

        $this->category_list_child = $this->category_db->field('category_id,name,parentid')->where('status=1 and parentid!=0')->order('orderid')->select();

        for ($i = 0; $i < count($this->category_list_parent); $i++) {

            for ($j = 0; $j < count($this->category_list_child); $j++) {
                if ($this->category_list_child[$j]['parentid'] == $this->category_list_parent[$i]['category_id']) {
                    $this->category_list_parent[$i]['child'][] = $this->category_list_child[$j];
                }
            }
        }
        $this->assign('categorylist', $this->category_list_parent); */



        for ($i = 0; $i < count($this->soft_list); $i++) {
            $softid.=',"' . $this->soft_list[$i]['softid'] . '"';
            $package.=',"' . $this->soft_list[$i]['package'] . '"';
        }


        if ($softid[0] == ',') {
            $softid = substr($softid, 1);
        }
        $perms_db = M("soft_permission");
        $sql = "select ssp.fileid softid,ssp.permissionid permissionid,sspd.des des from sj_soft_permission as ssp left join
         sj_soft_permission_des as sspd  on ssp.permissionid = sspd.permissionid
             where ssp.fileid in ({$softid})
        ";
        $perms_array = $perms_db->query($sql);
        $perms_infos = array();
        foreach ($perms_array as $info) {
            $perms_infos[$info['softid']][$info['permissionid']] = $info['des'];
        }
        if ($package[0] == ',') {
            $package = substr($package, 1);
        }
        $this->soft_note_db = M('soft_note');
        $map = '';
        $map['package'] = array('in', $package);
        $this->soft_note_list = $this->soft_note_db->field('package,left(note,60) note,auth')->where($map)->select();
        //show soft permission
        $perms_db = M("soft_permission");
        $sql = "select ssp.fileid softid,ssp.permissionid permissionid,sspd.des des from sj_soft_permission as ssp left join
         sj_soft_permission_des as sspd  on ssp.permissionid = sspd.permissionid
             where ssp.fileid in ({$softid})
        ";
        $perms_array = $perms_db->query($sql);
        $perms_infos = array();
        foreach ($perms_array as $info) {
            $perms_infos[$info['softid']][$info['permissionid']] = $info['des'];
        }
        //end show soft permission
        //by 张辉时间显示开始
        $zh_soft_time = M("soft_time");
        $sql = "select softid,start_time,end_time from sj_soft_time where softid in ({$softid})";
		//echo $sql;exit;
        $time_array = $zh_soft_time->query($sql);
        //print_r($time_array);exit;
        $time_infos = array();
        foreach ($time_array as $info) {
            $time_infos[$info['softid']]['start_time'] = $info['start_time'];
            $time_infos[$info['softid']]['end_time'] = $info['end_time'];
        }
        //print_r($time_infos);
        //by 张辉时间显示结束

        $this->soft_file_db = M('soft_file');
        $map = '';
        $map['softid'] = array('in', $softid);
        $map['package_status'] = 1;
        $this->soft_file_list = $this->soft_file_db->field('softid,advertisement,iconurl,url')->where($map)->select();

        $this->operating_db = D('Sj.Operating');

        $this->operating_list = $this->operating_db->where('only_auth=0')->field('oid,mname')->select();

        $this->lists = '';

        for ($i = 0; $i < count($this->operating_list); $i++) {
            $this->lists[$this->operating_list[$i]['oid']] = $this->operating_list[$i]['mname'];
        }
        //dump($this->lists);
        $this->lists[0] = '不隐藏';
        //dump($this->lists);


        $this->category_list_child = $this->category_db->getField('category_id,name');
        $adlist = $this->ad_select(); //广告信息
        include_once SERVER_ROOT . '/tools/functions.php';
        if ($this->soft_list) {
            for ($i = 0; $i < count($this->soft_list); $i++) {
                for ($j = 0; $j < count($this->soft_note_list); $j++) {
                    if ($this->soft_note_list[$j]['package'] == $this->soft_list[$i]['package']) {
                        $this->soft_list[$i]['note'] = $this->soft_note_list[$j]['note'];
                        $this->soft_list[$i]['auth'] = $this->soft_note_list[$j]['auth'];
                    }
                }
                for ($j = 0; $j < count($this->soft_file_list); $j++) {
                    if ($this->soft_file_list[$j]['softid'] == $this->soft_list[$i]['softid']) {
                        $this->soft_list[$i]['iconurl'] = $this->soft_file_list[$j]['iconurl'];
                        $adv_key = $this->soft_file_list[$j]['advertisement'];
                        $this->soft_list[$i]['advertisement'] = $this->ad($adv_key);
                        //$this->soft_list[$i]['detected_addon'] = test_apk_for_addon('/data/att/m.goapk.com'. $this->soft_file_list[$j]['url']);
                    }
                }
                $cid = explode(',', $this->soft_list[$i]['category_id']);
                for ($k = 0; $k < count($cid); $k++) {
                    $this->soft_list[$i]['category'].=$this->category_list_child[$cid[$k]];
                }
                $oid = explode(',', $this->soft_list[$i]['operatorhide']);
                for ($k = 0; $k < count($oid); $k++) {
                    $this->soft_list[$i]['operatorhides'].=$this->lists[$oid[$k]];
                }

                foreach ($known_abis as $abi_key => $abi_value) {
                    if ($abi_value & $this->soft_list[$i]['abi'] || $this->soft_list[$i]['abi'] == 0)
                        $this->soft_list[$i]['abis'][] = $abi_key;
                }
                $permiss = array('5', '6', '50', '51');
                $permiss_str = '';
                foreach ($perms_infos[$this->soft_list[$i]['softid']] as $key => $info) {
                    if (in_array($key, $permiss) && !empty($info)) {
                        $permiss_str .= $info . "<br/>";
                    }
                }
                //by 张辉渠道展示时间显示开始
				if(isset($time_infos[$this->soft_list[$i]['softid']])){
					foreach ($time_infos[$this->soft_list[$i]['softid']] as $key => $info) {
						$start_time = $time_infos[$this->soft_list[$i]['softid']]['start_time'];
						$end_time = $time_infos[$this->soft_list[$i]['softid']]['end_time'];
					}
				}else{
					$start_time=0;	
					$end_time=0;
				}
				$this->soft_list[$i]['permission_desc'] = $permiss_str;
                $this->soft_list[$i]['start_time'] = date("Y-m-d", $start_time);
                $this->soft_list[$i]['end_time'] = date("Y-m-d", $end_time);

                //by 张辉渠道展示时间显示结束
            }
        }

        //dump($this->soft_list);


        $Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);

        if ($_GET['p'])
            $this->assign('p', '?p=' . $_GET['p']); 
		else
            $this->assign('p', '');
			
			
			
        foreach ($this->soft_list as $k => $v) {
            $cid_str = substr($v['channel_id'], 1, -1);
            $cname = $this->soft_file_db->table('sj_channel')->where("cid in ({$cid_str})")->findAll();
            foreach ($cname as $k1 => $v1) {
                $this->soft_list[$k]['chname'] .= "<p>{$v1['chname']}</p>";
            }
        }


        if ($_GET['p'])
            $this->assign('p', '?p=' . $_GET['p']); else
            $this->assign('p', '');
        $this->assign('adlist', $adlist);
        $this->assign('softlist', $this->soft_list);
        $cook = substr($_COOKIE['cids'], 2, -1);
        $cookstr = strtr($cook, array('_' => ','));
        $chl = $this->soft_file_db->table('sj_channel')->field("`cid`,`chname`")->where(' `cid` in (' . $cookstr . ')')->select();
        $this->assign('chl_list', $chl);
        $_COOKIE['cids'] = 0;
        $this->display();
    }

    function soft_chl_edit() {
        $channel_model = M('channel');
        $channels = $channel_model->field("`cid`,`chname`")->where(array('status' => 1))->select();
        $this->assign('channel_list', $channels);
        if ($_GET['softid']) {
            $cook = $channel_model->table('sj_soft')->field("`channel_id`")->where("softid = '" . $_GET['softid'] . "'")->find();
            //张辉获取时间语句
            $soft_time = $channel_model->table('sj_soft_time')->field("`start_time`,`end_time`")->where("softid = '" . $_GET['softid'] . "'")->find();
            //张辉获取时间语句
            $cookstr = substr($cook['channel_id'], 1, -1);
        }
        $chl = $channel_model->field("`cid`,`chname`")->where(' `cid` in (' . $cookstr . ')')->select();
        $cook = substr($_COOKIE['cids'], 2, -1);
        $cookstr = strtr($cook, array('_' => ','));
        $chl1 = $channel_model->field("`cid`,`chname`")->where(' `cid` in (' . $cookstr . ')')->select();
        if (is_array($chl1)) {
            $chl = array_merge($chl, $chl1);
        }
       // $this->assoc_unique(&$chl, 'cid');
        $this->assign('chl_list', $chl);
        //张辉获取时间判断语句
        if (!empty($soft_time)) {
            $this->assign('time', $soft_time);
        }
        $time[0] = date("Y-m-d", time());
        $time[1] = date("Y-m-d", (time() + (7 * 86400)));
        $this->assign('time', $time);
        //张辉获取时间判断语句结束
        $this->soft_edit();
    }

    //广告


    function ad_select() {

        $advertise_arr = array(
            1 => '万普',
            2 => '友盟',
            4 => '有米',
            8 => '有米积分墙',
            16 => '橘子',
            32 => '点乐',
            64 => '指点',
            128 => '芒果',
            256 => '谷歌',
            512 => '百度',
            1024 => '赢告',
            2048 => '易传媒',
            4096 => '帷千',
            8192 => '微云广告',
            16384 => '微云积分墙',
            32768 => '哇棒',
            65536 => '随踪',
            131072 => '力美',
            262144 => '聚赢',
            524288 => '架势无线',
            1048576 => '飞云',
            2097152 => '多盟',
            4194304 => '百度统计',
            8388608 => '安沃',
            16777216 => '艾德思奇',
            33554432 => 'Smaato',
            67108864 => 'Lsence',
            134217728 => '乐享（广告）',
            268435456 => '乐享（积分墙）',
            536870912 => 'ADUU',
            1073741824 => 'AdTouch',
        );

        return $advertise_arr;
    }

    function ad($advers_code) {
        $msg = $this->ad_select();
        $msg[0] = '';
        $val = '';
        foreach ($msg as $i => $s) {
            if (($advers_code & $i) != 0) {
                if (strlen($val) > 0)
                    $val .= '|';
                $val .= $s;
            }
        }
        return $val;
    }

    function assoc_unique($arr, $key) {
        $tmp_arr = array();
        foreach ($arr as $k => $v) {
            if (in_array($v[$key], $tmp_arr)) {
                unset($arr[$k]);
            } else {
                $tmp_arr[] = $v[$key];
            }
        }
        sort($arr);
        return $arr;
    }
	//软件表sj_shoper_soft添加
	function zh_soft_addto(){
		$sj_shoper=M("shoper_soft");
		//$list['cid']=$_POST['cid'][0];
		$cid_array=$_POST['cid'];
		//print_r($cid_array);
		//exit;
		
		if(empty($cid_array)){
				$this->error("未选择渠道，请选择渠道后再提交");
			}
		$list['package']=$_POST['package'];
		foreach($cid_array as $key=>$info){
			$result=$sj_shoper->where(array("cid"=>$info,"package"=>$list['package'],"status"=>1))->select();
			if($result){
				$this->error("提交错误当前渠道".$info."下，包名为".$list['package']."已经存在");
			}
		}
		$list['create_tm']=time();
		$list['last_fresh_tm']=time();
		$list['status']=1;
		$list['num']=0;
		//$sj_shoper->create(); 
		//$sj_shoper->add($list); 
		$this->zh_insert($cid_array,$list,$sj_shoper);
		$this->success("添加成功！");
		//echo $sj_shoper->getlastsql();
		//$this->writelog("上传渠道为".$list['cid']."内容为".print_r($val,TRUE)."的软件信息");
		//$this->success("上传成功");
	}
	
	//循环插入数据
	function zh_insert($cid_array,$list,$sj_shoper){
		foreach($cid_array as $k=>$val){
			$list['cid']=$val;
			$sj_shoper->create(); 
			$sj_shoper->add($list); 
			$this->writelog("上传渠道为".$list['cid']."内容为".print_r($list,TRUE)."的软件信息");
			
		}
		
	}
	function  zh_soft_add(){
		$sj_shoper=M("shoper_soft");
		$sj_channel=M("channel");
		
		import("@.ORG.Page");
        $count = $sj_shoper->where("status=1")->count();
        $param = http_build_query($_GET);
        $Page = new Page($count, 10, $param);
		
		$zh_soft_list=$sj_shoper->where("status=1")->order("id")->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($zh_soft_list as $k=>$val){
			$zh_soft_list[$k]['chname']=$sj_channel->where(array("cid"=>$val['cid']))->getField("chname");
			$zh_soft_list[$k]['create_tm']=date("Y-m-d",$val['create_tm']);
		}
		if ($_GET['p'])
            $this->assign('p', $_GET['p']);
		else
        $this->assign('p', '1');
		$show = $Page->show();
        $this->assign("page", $show);
		$this->assign("list",$zh_soft_list);
		$this->display();
	}
	//第三方软件删除功能
	function  zh_three_soft_del(){
		$sj_shoper=M("shoper_soft");
		$id=$_GET['id'];
		$affect = $sj_shoper -> query("update __TABLE__ set status = 0 where id = " .$id);
		$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Soft/zh_soft_add');
		$this->success('删除成功');
	}
	public function zh_three_soft_alldel() {

        $sid=$_GET['id'];

        if(empty($sid)) {
            //$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Message/message_soft');
            $this->error("非法操作失败,如频繁出现，请联系管理员！");
        }
        if($sid[0]==',')
        {
            $sid=substr($sid,1);
        }
		
		$sj_shoper=M("shoper_soft");
        $map='';
        $map['status']='0';
        $this->map='';
        $this->map['id']=array('in',$sid);

        //dump($this->soft_list);
        if(false!==$sj_shoper->where($this->map)->save($map)) {

            $this->writelog('删除了ID为['.$sid.']的第三方软件');
            $this->success("删除成功！");
        }else
        {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Soft/zh_soft_add');
            $this->error("删除失败！");
        }
    }
	function filter_word($str){
        
        $this->pid='badword';
        $this->conf_db = D('Sj.Config');
        $this->map['config_type']=$this->pid;
	
		$res=$this->conf_db->where($this->map)->find();
		$notallowword = $res['configcontent'];
		
		$wordarray = array();
		$wordarray = explode('|',$notallowword);
		$flag = false;
		foreach($wordarray as $value){
			if(strstr($str,$value)){
			  $flag = true;
			  break;
			}
		}	
        return $flag;
		
	}
}

?>

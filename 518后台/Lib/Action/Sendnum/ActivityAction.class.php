<?php
/*
 * 活动管理
 */
class ActivityAction extends CommonAction {

	private $activity;
	private $link_pre;
	private $link_path;
	private $link_log_url;

	public function __construct() {
		parent::__construct();
		$this->activity = D('sendNum.Activity');			
		$this->link_pre = 'http://fx.anzhi.com/activity/activity_page/';
		$this->link_path = ACTIVITY_PAGE;
	}

	public function produceList() {
		$model = D('sendNum.Activity');	
		$where = array(
			'activate_type' => 1,
			'status' => 1
		);
		$this->check_where($where, 'ap_name', 'isset', 'like');
		$this->check_where($where, 'ap_id', 'isset');
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
		list($resetVal,$total,$Page) = $model -> get_activity_page($where,$limit);
		$resetVal = $this->resetVal($resetVal);
		$produceList = $resetVal;
		if(EVN == '9test' || EVN == 'prod'){
			$my_host = 'http://fx.anzhi.com';
		}elseif(EVN == '518test'){
			$my_host = ACTIVITY_URL;
		}
		
		$this -> assign('my_host',$my_host);
		$this -> assign('page', $Page->show());
		$this->assign('jsonDATA', json_encode($resetVal));
		$this->assign('produceList', $produceList);
		$this->display('produceList');
	}

	private function resetVal($produceList) {	
		$pkg = array();
		foreach ($produceList as $key => $val) {	
			if ($val['ap_link']) {
				$produceList[$key]['reset_ap_link'] = $this->link_pre.$val['ap_link'];	
			} else {
				$produceList[$key]['reset_ap_link'] = '未生成';	
			}
			$produceList[$key]['reset_ap_ctm'] = date('Y-m-d<br/>H:i', $produceList[$key]['ap_ctm']);
			if ($val['ap_type'] == 1) {
				$produceList[$key]['reset_ap_type'] = '活动页面';
			}
			if ($val['ap_type'] == 2) {
				$produceList[$key]['reset_ap_type'] = '获奖名单';
			}
			if ($val['ap_type'] == 3) {
				$produceList[$key]['reset_ap_type'] = '活动预告';
			}
			if ($val['ap_type'] == 4) {
				$produceList[$key]['reset_ap_type'] = '等待名单';
			}
			if($val['activate_type'] == 1 && $val['ap_package']){
				$pkg[] = $val['ap_package'];
			}
		}	
		if($pkg){
			$where = array(
				'package' =>  array('in',$pkg),
			);
			$softinfo = get_table_data($where,"sj_soft","package","package,softname");
			foreach ($produceList as $key => $val) {	
				$produceList[$key]['softname'] = $softinfo[$val['ap_package']]['softname'];
			}
		}	
		return $produceList;
	}

	public function addTpl() {
		$this->display('addTpl');
	}

	public function editTpl() {
		if ($_GET['view'] == 1) {
			$this->activityProduce();		
		} else {
			$activityOne = $this->resetVal($this->activity->getActivityOne($_GET['id']));
			$this->assign('activityOne', $activityOne[0]);
			$this->display('editTpl');
		}
	}

	public function activityAdd() {
		$this->assign('jumpUrl', '/index.php/Sendnum/Activity/produceList');
		if (!$_FILES['img']['name']) {
			$this->error('添加失败，请选择活动图片');
		}
		if (($_POST['type'] == 3 || $_POST['type'] == 4) && !$this->noticeLine($_POST['notice'])) {
			$this->error('操作失败，提示文字每行不能超过15个字');
		}
		if (mb_strlen($_POST['name'], 'utf-8') > 30) {
			$this->error('操作失败，活动名称不能超过30个字');
		}
		if (($_POST['type'] == 1 || $_POST['type'] == 3 || $_POST['type'] == 4) && mb_strlen($_POST['rule'], 'utf-8') > 500) {
			$this->error('操作失败，活动规则不能超过500个字');
		}
//		if ($_POST['type'] == 2 && mb_strlen($_POST['award'], 'utf-8') > 500) {
//			$this->error('操作失败，获奖名单不能超过500个字');
//		}
		$img = $_FILES['img'];
		$path = date('Ym/d/');
		if($img['size']){
			$config['multi_config']['img'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
			);
			$list = $this->_uploadapk(0, $config);
			$imgurl = $list['image'][0]['url'];
		}
		$insertData = array(
			'ap_name' => trim($_POST['name']),	
			'ap_package' => trim($_POST['package']),	
			'ap_imgurl' => $imgurl,
			'ap_type' => $_POST['type'],
			'ap_ctm' => time(),
			'ap_utm' => time(),
			'ap_rule' => $_POST['rule'],
			'ap_notice' => $_POST['notice'],
			'ap_award' => $_POST['award'],
		);
		$result = $this->activity->activityAdd($insertData);
		if($result){
			$ap_link = '/activity_'.$result.'.html';
			$this->activity->updatePage('ap_id='.$result, array('ap_link' => $ap_link));
		}
		$this -> writelog("添加活动页面，内容为".json_encode($insertData), 'sj_activity_page',$result,__ACTION__ ,'','add');
		$this->success('添加成功');
	}

	public function activityEdit() {
		$this->assign('jumpUrl', '/index.php/Sendnum/Activity/produceList');
		if (($_POST['type'] == 3 || $_POST['type'] == 4) && !$this->noticeLine($_POST['notice'])) {
			$this->error('操作失败，提示文字每行不能超过15个字');
		}
		if (mb_strlen($_POST['name'], 'utf-8') > 30) {
			$this->error('操作失败，活动名称不能超过30个字');
		}
		if (($_POST['type'] == 1 || $_POST['type'] == 3 || $_POST['type'] == 4) && mb_strlen($_POST['rule'], 'utf-8') > 500) {
			$this->error('操作失败，活动规则不能超过500个字');
		}
//		if ($_POST['type'] == 2 && mb_strlen($_POST['award'], 'utf-8') > 500) {
//			$this->error('操作失败，获奖名单不能超过500个字');
//		}
		$img = $_FILES['img'];
		$path = date('Ym/d/');
		if($img['size']){
			$config['multi_config']['img'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
			);
			$list = $this->_uploadapk(0, $config);
			$imgurl = $list['image'][0]['url'];
			$data['ap_imgurl'] = $imgurl;
		}
		$where = "ap_id=".$_POST['id'];
		$data['ap_name'] = trim($_POST['name']);
		$data['ap_package'] = trim($_POST['package']);
		$data['ap_type'] = $_POST['type'];
		$data['ap_utm'] = time();
		$data['ap_rule'] = $_POST['rule'];
		$data['ap_notice'] = $_POST['notice'];
		$data['ap_award'] = $_POST['award'];


		$log_result = $this->logcheck(array('ap_id'=>$_POST['id']),'sj_activity_page',$data,$this->activity);
		$this->activity->activityEdit($where, $data);
		$this->writelog("修改活动页面id:".$_POST['id'].$log_result, 'sj_activity_page',$_POST['id'],__ACTION__ ,'','edit');
		$this->success('编辑成功');
	}

	private function noticeLine($str) {
		$arr = explode(PHP_EOL, $str);	
		foreach ($arr as $val) {
			if (mb_strlen(trim($val), 'utf-8') > 15) {
				return false;
			}
		}
		return true;
	}

	public function checkFormat() {
		$package = $_GET['package'] ? $_GET['package'] : $_POST['package'];
		$result = $this->activity->checkPackage($package);
		echo $result;
	}

	public function activityDel() {
		$where = 'ap_id='.$_GET['id'];
		$this->activity->activityDel($where);
		$this->writelog('删除活动页面id:'.$_GET['id'], 'sj_activity_page',$_GET['id'],__ACTION__ ,'','del');
		header("location:/index.php/Sendnum/Activity/produceList");
	}


	private function uploadFile() {
		$img = $_FILES['img'];	
		if ($img['name']) {
			$fileExt = substr($img['name'], strrpos($img['name'], '.'));
			$cdndir = '/data/att/m.goapk.com/img/';
			$datedir = date('Ym',time()).'/'.date('d', time()).'/';
			$filename = md5(time().$img['name']).$fileExt;
			$type = $img['type'];
			if (!is_dir($cdndir.$datedir)) {
				mkdir($cdndir.$datedir, 0755, true);	
			}
			copy($img['tmp_name'], $cdndir.$datedir.$filename);
			return $datedir.$filename;
		} else {
			return false;	
		}
	}

	public function activityProduce() {
		$activityOne = $this->activity->getActivityOne($_GET['id']);
		if ($activityOne[0]['ap_type'] == 1) {
			$html = $this->buildHeader($activityOne[0]['ap_name'], $activityOne[0]['ap_imgurl']).
				$this->buildAdd($activityOne[0]['ap_rule'], $activityOne[0]['ap_package']).
				$this->buildFooter();
		} else if ($activityOne[0]['ap_type'] == 2) {
			$html = $this->buildHeader($activityOne[0]['ap_name'], $activityOne[0]['ap_imgurl']).
				$this->buildAward($activityOne[0]['ap_award']).
				$this->buildFooter();
		} else if ($activityOne[0]['ap_type'] == 3) {
			$html = $this->buildHeader($activityOne[0]['ap_name'], $activityOne[0]['ap_imgurl']).
				$this->buildPreview($activityOne[0]['ap_notice'], $activityOne[0]['ap_rule']).
				$this->buildFooter();
		} else if ($activityOne[0]['ap_type'] == 4) {
			$html = $this->buildHeader($activityOne[0]['ap_name'], $activityOne[0]['ap_imgurl']).
				$this->buildWait($activityOne[0]['ap_notice'], $activityOne[0]['ap_rule']).
				$this->buildFooter();
		}
		$this->writelog('生成活动页面id:'.$_GET['id'], 'sj_activity_page',$_GET['id'],__ACTION__ ,'','add');
		if ($_GET['view'] == 1) {
			echo $html;
		} else {
			$page =$_GET['id'];
			$retes = "/activity_".$page.'.html';
			//file_put_contents($this->link_path.$page, $html);
			$this->activity->updatePage('ap_id='.$_GET['id'], array('ap_link' => $retes));
			echo "http://fx.anzhi.com/activity_".$page.'.html';
		}
	}

	private function buildHeader($name, $img) {
		return '<!DOCTYPE html>
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
		<meta content="telephone=no" name="format-detection">
		<meta name="keywords" content="Android,安卓,安卓网,安卓市场,安卓游戏,电子市场,安卓软件,Android游戏,安卓手机,游戏汉化,手机游戏,Android软件,软件下载,最新汉化软件,安卓游戏下载,游戏汉化" />
		<meta name="description" content="安智市场,Android,安卓,安卓网,安卓市场,电子市场,安卓游戏,应用商店-国内最专业的Android安卓电子市场，提供海量安卓软件、Android手机游戏、安卓最新汉化软件、汉化游戏资源及最新APK汉化、汉化破解APP、APK免费下载，致力于为用户打造最贴心的Android安卓手机应用商店" />
		<title>'.$name.'</title>
		<link type="text/css" rel="stylesheet" href="http://fx.anzhi.com/activity/activity_page/css/common.css"/>
		<script src="http://fx.anzhi.com/activity/activity_page/js/jquery.js"></script>
		</head>
		<body>
		<div id="main">
			<div id="banner">
				<img src="http://img3.anzhi.com/img/'.$img.'" alt=""/>
			</div>';
	}
	private function buildAdd($rule, $package) {
		return '<div id="active_intro"><script>
				function isMobel(value)  {  
					if(/^13\d{9}$/g.test(value)||(/^14\d{9}$/g.test(value))||(/^15\d{9}$/g.test(value))||(/^18\d{9}$/g.test(value))){    
						return true;  
					}else{
						return false;  
					}  
				}
				
				function _submit(){
					var url = window.location.href;
					var aid = url.replace(/.*aid=(\d*).*/, "$1");
					var sid = url.replace(/.*sid=([0-9a-z]*).*/, "$1");
					var mobile_phone = $("#mobile_phone").val();
					$("#mobile_phone").css("borderColor", "#CDCDCD");
					if(mobile_phone == "") {  	
						$("#mobile_phone").css("borderColor", "#f00");
						return false;
					 }
					 if(mobile_phone !="" && !isMobel(mobile_phone)) { 
						$("#mobile_phone").css("borderColor", "#f00");
						return false;
					 }
					var phone = $("#mobile_phone").val();
					$.ajax({
						url:"http://fx.anzhi.com/writeLog.php", 
						data:"&sid="+sid+"&aid="+aid+"&package='.$package.'&phone="+phone, 
						type:"get",
						success:function(data) {
							var data = eval("("+data+")");
							window.AnzhiActivitys.downloadForActivity(parseInt(aid), data.ID, "'.$package.'", data.SOFT_NAME, parseInt(data.SOFT_VERSION_CODE), parseInt(data.SOFT_SIZE), 7);
						},
						error:function() {
							alert("页面生成失败");
						}
					});
					return false;
				}
				
				
				$(function() {
					$("#mobile_phone").click(function() {
						if (!$(this).attr("modify")) {
							$(this).val("");	
							$(this).attr("modify", true);
						}
					});
					
				});
				
				</script>
				<h6>填写参加信息：</h6>
				<form method="get" action="" >
					<input type="text" id="mobile_phone" class="inputtext" value="手机号码"/>
					<input type="submit" value="提交" class="submit_btns" onclick="return _submit();" />
				</form>
				<h6>活动规则：</h6>'.nl2br($rule).'</div>';
	}
	private function buildAward($award) {
		return '<div id="active_intro">
				<h6>获奖名单：</h6>'.nl2br($award).'</div>';
	}
	private function buildPreview($notice, $rule) {
		return '<div id="active_acnouce">'.nl2br($notice).'</div>
				<div id="active_intro"><h6>活动规则：</h6>'.nl2br($rule).'</div>';
	
	}
	private function buildWait($notice, $rule) {
		return '<div id="active_acnouce">'.nl2br($notice).'</div>
				<div id="active_intro"><h6>活动规则：</h6>'.nl2br($rule).'</div>';
	}
	private function buildFooter() {
	return '</div>
		</body>
		</html>';
	}


} 

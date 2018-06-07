<?php
include_once (dirname(realpath(__FILE__)).'/init.php');
switch ($_GET['type']) {
    case 'subject_app':
        if (isset($_GET['sf']) && $_GET['sf'] == 'sem') {
            $url = "/subject_sem.html?id={$_GET['subject_id']}&chl_cid={$_GET['chl_cid']}";
            if ($_GET['auto']) {
                $url .= "&auto={$_GET['auto']}";
            }
            header($url);
            exit;
        }
		$morelist = isset($_GET['morelist']) && !empty($_GET['morelist']) ? $_GET['morelist'] : 0;
		$page = 15;
		$subject_id = (int)$_GET['subject_id'];
//    		$res = gomarket_action('soft.GoGetSoftSubjectAllList', array('ID' => $subject_id, 'TYPE' => 1, 'LIST_INDEX_START' => $morelist, 'LIST_INDEX_SIZE' => $page, 'VR' => 1));
		$res = gomarket_action('soft.GoGetSoftSubjectAllList', array('GET_INFO' => TRUE, 'ID' => $subject_id, 'TYPE' => 1, 'VR' => 1, 'LIST_INDEX_SIZE' => 500,'EXTRA_OPTION_FIELD' => array(
			'isoffice',
        )));
		if ($channel == 'bbg' && $subject_id == 31)
		{
			//$model = new GoModel();
			//$option = array(
			//	'table' => 'sj_soft',
			//	'where'	=> array(
			//		'package' => 'cn.goapk.market',
			//		'status' => 1,
			//		'hide' => 1024,
			//		'channel_id' => ",{$channel_info['cid']},"
			//	),
			//);
			//$result = $model->findOne($option);
			$model = load_model('softlist');
            $softid = $model->getPackageToSoftId("cn.goapk.market");
            $softid = $softid[0];
			if (!empty($softid))
			{
				$resultanzhi = $model->getsoftinfos($softid, getFilterOption());
				if (!empty($resultanzhi))
				{
					$temp = $resultanzhi[$softid];
					$resultanzhi = array(
						$temp['softid'],
						getImageHost() . $temp['iconurl'],
						$temp['softname'],
						$temp['score'],
						$temp['msgnum'],
						$temp['dev_name'],
						$temp['costs'],
						$temp['package'],
						$temp['safe'],
						$temp['filesize'],
						$temp['category_id'],
						$temp['total_downloaded'],
						"download.php?softid={$softid}",
						$temp['version_code'],
					);
					$res['DATA'][] = $resultanzhi;
				}
			}
		}
		foreach($res['DATA'] as $key => $value) {
			$i = $k =0;
			$res['DATA'][$key]['scorehtml']="";
			$i = floor($value[3] / 2);
			$k = $value[3] % 2;
			for($i1=$i;$i1>0;$i1--){
				$res['DATA'][$key]['scorehtml'] .='<img alt="" src="/images/star_01.png">';
			}
			if($k!=0)
				$res['DATA'][$key]['scorehtml'] .= '<img alt="" src="/images/star_02.png">';
			if(($i+$k)<5) {
				for($i2=(5-$i-$k);$i2>0;$i2--){
					$res['DATA'][$key]['scorehtml'] .='<img alt="" src="/images/star_03.png">';
				}	
			}
		}
		$weixin_hint = '微信用户请点击微信右上角<br />选择「在浏览器中打开」';
		$tplObj->out['weixin_hint'] = $weixin_hint;
		$tplObj->out['title'] = $res['NAME'];
		$tplObj->out['subject_app'] = $res;
		$tplObj->out['morelist'] = $morelist + $page;
        $tplObj->out['type'] = 'subject_app';
		$tplObj->out['subject_id'] = $subject_id;
        if (empty($_GET['sf'])) {
		  $tplObj->display("subject_app.html"); 
        } else {
            $tplObj->out['chl_cid'] = $_GET['chl_cid'];
            $tplObj->out['auto'] = !empty($_GET['auto']);
            $tplObj->display("subject_sem.html"); 
        }
		break;
    default:
        if (CHL == '231e062b072d7effe6ac1505b3b6ce63f65ea17e') {
            $_SESSION['USER_IMSI'] = 4600300000000;
            $_SESSION['MODEL_OID'] = 1;
        }
            $res = gomarket_action('soft.GoGetSoftSubject', array('LIST_INDEX_SIZE' => 50, 'VR' => 1));
        if (CHL == '231e062b072d7effe6ac1505b3b6ce63f65ea17e') {
            unset($_SESSION['USER_IMSI']);
            unset($_SESSION['MODEL_OID']);
        }
    		foreach ($res['DATA'] as $k => $v){
    			if ($v[1] > 1000000){
    				$res['DATA'][$k][1] = floor($v[1] / 1000000);
    			}
    		}
    		$r = $res['DATA'];
			
    		$tplObj->out['subject_hot'] = array_shift($r);
    		$tplObj->out['subject'] = $r;
            $tplObj->out['title'] = '专题';
            $tplObj->out['list_page'] = $result['list_page'];
            $tplObj->out['type'] = 'subject';
            $tplObj->display("subject.html"); 
        break;
}

function scorehtml($result){
	foreach($result as $key => $value) {
		$i = $k =0;
		$result[$key]['scorehtml']="";
		$i = floor($value[score] / 2);
		$k = $value[score] % 2;
		for($i1=$i;$i1>0;$i1--){
			$result[$key]['scorehtml'] .='<img alt="" src="/images/star_01.png">';
		}
		if($k!=0)
			$result[$key]['scorehtml'] .= '<img alt="" src="/images/star_02.png">';
		if(($i+$k)<5) {
			for($i2=(5-$i-$k);$i2>0;$i2--){
				$result[$key]['scorehtml'] .='<img alt="" src="/images/star_03.png">';
			}	
		}
	}
	return 	$result;
}

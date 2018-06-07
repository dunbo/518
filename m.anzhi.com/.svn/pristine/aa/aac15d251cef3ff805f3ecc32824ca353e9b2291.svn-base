<?php
include_once (dirname(realpath(__FILE__)).'/init.php');

$_SESSION['VERSION_CODE'] = $_GET['version_code'];
$act = $_GET['act'];
$map = array(
	'getlistbyaid' => 1,
	'getsignlistbymid' => 1,
	'getSubjectDetail' => 1,
	'getSubjectCommentList' => 1,
	'getJumpInfo' => 1,
	'launch' => 1,
	'getSoftCommentList' => 1
);

if (isset($map[$act])) {
	$act();
	exit;
}

function getlistbyaid() {
	$param = array(
		'ACTIVITY_ID' => $_GET['aid'],
		'KEY' => 'REQ_NO_FLOW_LIST',
		'VR' => 19
	);
	$res = gomarket_action('v53.GoGetActivitySoftList',$param);
	echo json_encode($res);
}


function getsignlistbymid() {
	$param = array(
		'MONTHID' => $_GET['mid'],
		'KEY' => 'REQ_SIGN_APP_LIST',
		'VR' => 1
	);
	$res = gomarket_action('v65.GoReqSignAppList',$param);
	echo json_encode($res);
}

function getSubjectDetail() {
	$param = array(
		'SUBJECT_ID' => $_GET['feature_id'],
		'KEY' => 'SUBJECT_DETAIL_LIST_DISCOVERY',
		'LIST_INDEX_START' => $_GET['offset'],
		'LIST_INDEX_SIZE' => $_GET['size'],
	);
	$res = gomarket_action('v60.TopicDetail',$param);
	echo json_encode($res);
}

function getSubjectCommentList() {
	$param = array(
		'ID' => $_GET['feature_id'],
		'KEY' => 'SUBJECT_DETAIL_COMMENTS_LIST',
		'LIST_INDEX_START' => $_GET['offset'],
		'LIST_INDEX_SIZE' => $_GET['size'],
		'VR' => 19
	);
	$res = gomarket_action('v60.CommentList',$param);
	echo json_encode($res);
}

function getSoftCommentList(){
	$param = array(
		'PKG' => $_GET['package'],
		'KEY' => 'SOFT_COMMENTS_LIST_NEW',
		'LIST_INDEX_START' => $_GET['offset'],
		'LIST_INDEX_SIZE' => $_GET['size'],
		'VR' => 19
	);
	$res = gomarket_action('v60.CommentList',$param);
	echo json_encode($res);
}

function getJumpInfo($jsonp = false)
{
	$param = array(
		'EXTRA_ID' => $_GET['id'],
		'KEY' => 'GET_EXTRA_INFO',
		'VR' => 19
	);
	$res = gomarket_action('v60.GetExtraInfo',$param);
	if (!$jsonp) {
		echo json_encode($res);
	} else {
		$data = json_encode($res['INFO']);
		$callback = $_GET['callback'];

		if (!$callback) {
			echo <<<EOF
if (typeof(window['AnzhiActivitys']) != 'undefined') {
	window.AnzhiActivitys.launch('{$data}', {$_GET['id']});	
}
EOF;
		} else {
			echo "{$callback}('{$data}', {$_GET['id']});\n";
		}

	}
}

function getCommentDetail() {
	$param = array(
		'SOFT_ID' => $_GET['id'],
		'COMMENT_ID' => $_GET['cid'],
		'KEY' => 'SOFT_COMMENT_DETAIL_LIST',
		'LIST_INDEX_START' => $_GET['offset'],
		'LIST_INDEX_SIZE' => $_GET['size'],
		'VR' => 19,
		'COMMENT_FROM' => $_GET['type'],
	);
	$res = gomarket_action('v60.CommentList',$param);
	echo json_encode($res);
}

function launch()
{
	getJumpInfo(true);
}
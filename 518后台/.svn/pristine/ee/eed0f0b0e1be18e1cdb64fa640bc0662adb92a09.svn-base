<?php

/**
 * 配置说明：
 * EX、FE、AH等代表不同广告位要操作的相关内容。
 * node-为操作节点的相关表。table-为表名；model-为实例化model的用名；find-为要查找的字段。
 * app-为要比对排期的相关表。table-为表名；model-为实例化model的用名；find-为要查找的字段；node_field-要查找到节点id字段；date_field-时间段开始和结束字段。
 * 
 * 注意：
 * SK、SKH、SKD、PR的处理方式和其他类型有些不同，他们没有node相关表，直接操作app相关表。
 */

return array(
	'advert_tables' => array(
		'EX'		=> array(	//首页推荐
			'node'	=> array('table'=>'sj_extent_v1', 'model'=>'extent_v1', 'find'=>'extent_id', 'node_name'=>'extent_name'),
			'app'	=> array('table'=>'sj_extent_soft_v1', 'model'=>'extent_soft_v1', 'find'=>'package', 'node_field'=>'extent_id', 'date_field'=>array('start_at','end_at')) ), //EX
		'FE' 		=> array(	//装机必备
			'node'	=> array('table'=>'sj_feature', 'model'=>'feature', 'find'=>'feature_id', 'node_name'=>'name' ),
			'app'	=> array('table'=>'sj_feature_soft', 'model'=>'feature_soft', 'find'=>'package', 'node_field'=>'feature_id', 'date_field'=>array('start_tm','end_tm')) ), //FE
		'AH'		=> array(	//热门应用
			'node'	=> array('table'=>'sj_category_extent', 'model'=>'category_extent', 'find'=>'extent_id', 'node_name'=>'extent_name' ),
			'app'	=> array('table'=>'sj_category_extent_soft', 'model'=>'category_extent_soft', 'find'=>'package', 'node_field'=>'extent_id', 'date_field'=>array('start_at','end_at')) ), //AH
		'GH'		=> array(	//热门游戏
			'node'	=> array('table'=>'sj_category_extent', 'model'=>'category_extent', 'find'=>'extent_id', 'node_name'=>'extent_name' ),
			'app'	=> array('table'=>'sj_category_extent_soft', 'model'=>'category_extent_soft', 'find'=>'package', 'node_field'=>'extent_id', 'date_field'=>array('start_at','end_at')) ), //GH
		'CA'		=> array(	//类别置顶
			'node'	=> array('table'=>'sj_category_extent', 'model'=>'category_extent', 'find'=>'extent_id', 'node_name'=>'extent_name' ),
			'app'	=> array('table'=>'sj_category_extent_soft', 'model'=>'category_extent_soft', 'find'=>'package', 'node_field'=>'extent_id', 'date_field'=>array('start_at','end_at')) ), //CA
		'TN'		=> array(	//最新
			'node'	=> array('table'=>'sj_category_extent', 'model'=>'category_extent', 'find'=>'extent_id', 'node_name'=>'extent_name' ),
			'app'	=> array('table'=>'sj_category_extent_soft', 'model'=>'category_extent_soft', 'find'=>'package', 'node_field'=>'extent_id', 'date_field'=>array('start_at','end_at')) ), //TN
		'NE'		=> array(	//必备
			'node'	=> array('table'=>'sj_necessary_extent', 'model'=>'necessary_extent', 'find'=>'extent_id', 'node_name'=>'extent_name' ),
			'app'	=> array('table'=>'sj_necessary_extent_soft', 'model'=>'necessary_extent_soft', 'find'=>'package', 'node_field'=>'extent_id', 'date_field'=>array('start_at','end_at')) ), //NE
		'SKH'		=> array(	// 搜索热词推荐
			'node'	=> array('table'=>'sj_search_keywords', 'model'=>'search_keywords', 'find'=>1 ),
			'app'	=> array('table'=>'sj_search_keywords', 'model'=>'search_keywords', 'find'=>'key_word', 'node_field'=>'key_word', 'date_field'=>array('start_tm','end_tm')) ), //SKH
		'SK'		=> array( // 搜索结果运营 ##注意：search_key没有包名字段，无法判断软件对应关系
			'node'	=> array('table'=>'sj_search_key', 'model'=>'search_key', 'find'=>1),
			'app'	=> array('table'=>'sj_search_key','model'=>'search_key', 'find'=>'srh_key', 'node_field'=>'srh_key', 'date_field'=>array('start_tm','stop_tm')) ), //SK
		'SKD'		=> array( // 默认搜索词 ##注意：soft_defaultkeywords没有包名字段，无法判断软件对应关系
			'node'	=> array('table'=>'sj_soft_defaultkeywords', 'model'=>'soft_defaultkeywords', 'find'=>1),
			'app'	=> array('table'=>'sj_soft_defaultkeywords', 'model'=>'soft_defaultkeywords', 'find'=>'key_words', 'node_field'=>'key_words', 'date_field'=>array('start_time','end_time')) ), //SKD
		'PR'		=> array(	// 猜你喜欢
			'node'	=> array('table'=>'sj_soft_recommend', 'model'=>'soft_recommend', 'find'=>1 ),
			'app'	=> array('table'=>'sj_soft_recommend', 'model'=>'soft_recommend', 'find'=>'soft_package', 'node_field'=>'soft_package', 'date_field'=>array('start_tm','end_tm')) ), //PR
		'DL'		=> array(	// 用户还下载了
			'node'	=> array('table'=>'sj_category_extent', 'model'=>'category_extent', 'find'=>'extent_id', 'node_name'=>'extent_name' ),
			'app'	=> array('table'=>'sj_category_extent_soft', 'model'=>'category_extent_soft', 'find'=>'package', 'node_field'=>'extent_id', 'date_field'=>array('start_at','end_at')) ), //DL
		'CF'		=> array(	// 轮播图
			'node'	=> array('table'=>'sj_ad_extent', 'model'=>'ad_extent', 'find'=>'extent_id', 'node_name'=>'extent_name'),
			'app'	=> array('table'=>'sj_ad_new', 'model'=>'ad_new', 'find'=>'package', 'node_field'=>'extent_id', 'date_field'=>array('start_tm','end_tm')) ), //CF
		'IN'		=> array(	// 一键装机
			'node'	=> array('table'=>'sj_lading_category', 'model'=>'lading_category', 'find'=>'id', 'node_name'=>'category_name' ),
			'app'	=> array('table'=>'sj_lading_soft', 'model'=>'lading_soft', 'find'=>'package', 'node_field'=>'category_id', 'date_field'=>array('start_tm','end_tm')) ), //IN
		'PU'		=> array(	// PUSH 推送
			'node'	=> array('table'=>'sj_market_push', 'model'=>'market_push', 'find'=>1 ),
			'app'	=> array('table'=>'sj_market_push', 'model'=>'market_push', 'find'=>'soft_package', 'node_field'=>'soft_package', 'date_field'=>array('start_tm','end_tm'),'type'=>'push_type')), //PU
		'PUP'		=> array(	// PUSH 推送 弹窗
			'node'	=> array('table'=>'sj_market_push', 'model'=>'market_push', 'find'=>1 ),
			'app'	=> array('table'=>'sj_market_push', 'model'=>'market_push', 'find'=>'soft_package', 'node_field'=>'soft_package', 'date_field'=>array('start_tm','end_tm'),'type'=>'push_type')),//push_type:1,push推送 2,弹窗广告 3,被动预下载 
		'SAD'		=> array(	// 开机闪屏(广告闪屏)
			'node'	=> array('table'=>'sj_splash_manage', 'model'=>'splash_manage', 'find'=>1 ),
			'app'	=> array('table'=>'sj_splash_manage', 'model'=>'splash_manage', 'find'=>'package', 'node_field'=>'package', 'date_field'=>array('start_tm','end_tm'),'type'=>'splash_type')), //splash_type:1,普通闪屏 2,广告闪屏
		'RE'		=> array(	// 退出弹窗（返回运营）
			'node'	=> array('table'=>'sj_return_back_ad', 'model'=>'return_back_ad', 'find'=>1 ),
			'app'	=> array('table'=>'sj_return_back_ad', 'model'=>'return_back_ad', 'find'=>'package', 'node_field'=>'package', 'date_field'=>array('start_at','end_at')) ), //PU
));
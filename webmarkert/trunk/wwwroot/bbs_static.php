<?php
header("content-type:text/html; charset=utf-8");
include 'init.php';
$app_res = gomarket_action('soft.GoGetHomeNew', array('PARENT_CAT_ID' => 1, 'ID' => 1, 'LIST_INDEX_START' => 0, 'LIST_INDEX_SIZE' => 7,'VR' => 1));
$apps = $app_res['DATA'];
$game_res = gomarket_action('soft.GoGetHomeNew', array('PARENT_CAT_ID' => 2, 'ID' => 1, 'LIST_INDEX_START' => 0, 'LIST_INDEX_SIZE' => 8,'VR' => 1));
$games = $game_res['DATA'];
$filter_option = array(
    'abi' => 3
);



$filter_logic = pu_load_logic('filter', array('filter_option' => $filter_option) );
$softlist_logic = pu_load_logic('softlist', array('filter_logic' => $filter_logic));
$soft_logic = pu_load_logic('soft', array('filter_logic' => $filter_logic));
$gomarket_data = $soft_logic->get_soft_all_data_by_package('cn.goapk.market');

$anzhi = array($gomarket_data['softid'],getImageHost(). $gomarket_data['iconurl'],$gomarket_data['softname']);

array_unshift($apps, $anzhi);
if(count($apps) < 8 && count($games) < 8){
echo "";
exit;
}
?>
<style type="text/css">
html,body{margin:0;padding:0;}
#new { height: 272px; overflow: hidden;width: 180px; font-family:"微软雅黑";
}
.head{overflow:hidden;background: url(/bbsImg/New_MenuBG.jpg) repeat scroll 0 0 transparent;height:27px; width:100%;}
.head #h_l{ background:url(/bbsImg/New_MenuLeft.gif) no-repeat scroll 0 0 transparent; height: 27px; float:left; line-height:34pxcolor:#6A6D74; font-weight:bold;font-size:14px; overflow:hidden;width:60px;}
.head .h_m{height: 27px; overflow: hidden;float:left;width:114px;display:inline;}
.head #h_r{background:url(/bbsImg/New_MenuRight.jpg) repeat scroll 0 0 transparent;height: 27px;width: 6px; float:left;}

.h_m a{color: #6A6D74;font-size: 12px;float: left; display:block;  height: 27px;line-height:35px;text-align: center;width: 50px;}

.h_m .s{background: url(/bbsImg/New_menuOn.gif) repeat scroll 0 0 transparent; color:#0066CC;font-weight: bold;}

.ulbox{  border: 1px solid #D8D8D8;border-top:none; height: 244px; overflow: hidden; }
.ulbox li{ height: 26px; line-height: 26px; overflow: hidden; position: relative;width: 158px;border-bottom:1px dashed #0066CC; float:left; display:inline;cursor:pointer;margin:0 10px;}
.ulbox  ul{ margin:0; padding:0; list-style:none; float:right;}
.img{ height: 26px;overflow: hidden; width: 32px; float:left;}
.img img{ height: 24px; width: 24px;}
.name{ height: 25px; line-height:25px; overflow: hidden;float:left;padding-left:5px;width:84px;}
.name a{color:#6A6D74; font-size:12px;text-decoration:none;}
.name a:hover{color:#0066CC;}
/*.ulbox li .time{ color: #6A6D74; font-size: 12px; float:left;}
.ulbox li .down{  height: 20px; left: 161px; position: absolute;text-align: right;top: 5px;width: 49px; }
.ulbox li.s .down a{background:url(bbsImg/01.gif) no-repeat scroll transparent; height: 20px;width: 49px;display:inline-block; }
.ulbox li.s .down a:hover{background:url(bbsImg/02.gif) no-repeat scroll transparent; }
.ulbox li.s .time{display:none;}
.ulbox li.s .down{display:block;}*/
.ulbox li.morebtn{ text-align: center; line-height:20px;border-bottom:none;padding-top:3px;}
.ulbox li.morebtn a,.ulbox li.morebtn a#moreb{ color:#0066CC;font-size: 12px;text-decoration: underline;}
.ulbox li.morebtn a:hover,.ulbox li.morebtn a#moreb:hover{color:#0066CC; text-decoration: underline;}

</style>

<script src="/js/jquery-1.4.2.min.js"type="text/javascript"></script>
<script  defer="defer"  language="javascript">
$(document).ready(function(){
change();
showdown("num0");
showdown("num1");
});
function change(){
var tab = $(".h_m a");
var obj = $(".ulbox ul");
$(".ulbox ul:gt(0)").hide();
tab.mouseover(function(){
    var index = $(this).index();
    $(this).addClass("s").siblings("a").removeClass("s");
    obj.eq(index).show().siblings("ul").hide();
})
}

function showdown(id){
var tab = $("#"+id+" li");
tab.mouseover(function(){
    $(this).addClass("s").siblings("li").removeClass("s");
})
}
</script>
<div id="new">
    <div class="head">
        <div id="h_l"></div>
        <div class="h_m">
            <a class="s">应用</a><a class="">游戏</a>
      </div>
        <div id="h_r"></div>
  </div>

    <div class="ulbox">
        <ul id="num0">
        <?php foreach($apps as $index => $info){ ?>
          <li  class="s">
                <div class="img"><img  src="<?php echo getImageHost(). $info['1'];?>"/></div>
                <div class="name"><a href="<?php echo '/soft_'.$info['0'].'.html';?>" target="_blank" title="来自安智市场"><?php echo $info['2'];?></a></div>

            </li>
            <?php } ?>
            <li class="morebtn"><a href="/applist.html" target="_blank" id="moreb" style=" text-decoration: underline;">查看更多应用</a></li>
        </ul>

        <ul id="num1">
        <?php foreach($games as $index => $info){?>
            <li>
                <div class="img"><img  src="<?php echo getImageHost(). $info['1'];?>"/></div>
                <div class="name"><a href="<?php echo '/soft_'.$info['0'].'.html';?>" target="_blank"  title="来自安智市场"><?php echo $info['2'];?></a></div>
            </li>
            <?php } ?>
            <li class="morebtn"><a href="/gamelist.html"  target="_blank" id="moreb" style=" text-decoration: underline;">查看更多应用</a></li>
        </ul>
    </div>

</div>


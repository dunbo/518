<?php
  require 'jssdk.class.php';

  #define('APPID', 'wxec183e363b67a077');
  #define('APPSECRET', '23d06b5414dd9e11f79d8201f2693307');
  define('APPID', 'wxb3dfed519f72089b');
  define('APPSECRET', '961e39ad659b7968e2108b67d595a617');
  define('SELFURL', 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);

  //echo( SELFURL );

  $jsSDK = new JSSDK(APPID,APPSECRET);

  $signPackage = $jsSDK->GetSignPackage();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">

  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no,minimal-ui"/>

  <!--
  回退的余地
  <meta name="viewport" content="width=640,maximum-scale=1.0,user-scalable=no"/>
  -->
  <title>如果我变成狗，你还会爱我吗</title>
  <link rel="stylesheet" href="http://img3.anzhi.com/static/weixin/5/css/animation.css">
  <link rel="stylesheet" href="http://img3.anzhi.com/static/weixin/5/css/layout.css" />

  <script src="http://img3.anzhi.com/static/weixin/5/jquery_1.8.3_min.js"></script>
  <script src="http://img3.anzhi.com/static/weixin/5/lib.js"></script>
  <script src="http://img3.anzhi.com/static/weixin/5/preloadjs-0.4.1.min.js"></script>
  <script src="http://img3.anzhi.com/static/weixin/5/resource1.js"></script>
  <script src="http://img3.anzhi.com/static/weixin/5/gameClass.js"></script>
  <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
  <script>
    $(function () {
      var game = new h5Game();
      var loadQeue = new createjs.LoadQueue(false);
      loadQeue.loadManifest(source);
      loadQeue.on("progress", function(event){
        $('.load_process').css('width',(event.progress*100) +'%');
      });
      loadQeue.on('complete',function () {
        game.createGameC(53);
        $("#loading").hide();
        game.story();
      });
      $('body').bind('touchmove',function () {
        return false;
      });

    });
  </script>
</head>
<body>
  <div id="wrapbox">
    <div class="arrow_up"><span></span></div>
    <div id="loading" >
      <div class="load_icon"></div>
      <div class="load_process_box">
        <div class="load_process"></div>
      </div>
      <div class="textBounce">
        <img src="http://img3.anzhi.com/static/weixin/5/images/loading_text1.png" class="animated pulse sfast loop" alt="">
        <img src="http://img3.anzhi.com/static/weixin/5/images/loading_text2.png" class="animated pulse middle loop" alt="">
        <img src="http://img3.anzhi.com/static/weixin/5/images/loading_text3.png" class="animated pulse normal loop"alt="">
      </div>
    </div>


    <div class="page1">
      <div class="gongzhu_text">
      </div>
      <div class="gongzhu"></div>
      <div class="wangzi_text"></div>
      <div class="wangzi"></div>
      <div class="story_text">
        <img src="http://img3.anzhi.com/static/weixin/5/images/page1_1.png" class="story_text_load" alt="">
        <img src="http://img3.anzhi.com/static/weixin/5/images/page1_2.png" class="story_text_load" alt="">
        <img src="http://img3.anzhi.com/static/weixin/5/images/page1_3.png" class="story_text_load" alt="">
        <img src="http://img3.anzhi.com/static/weixin/5/images/page1_4.png" class="story_text_load" alt="">
        <img src="http://img3.anzhi.com/static/weixin/5/images/page1_5.png" class="story_text_load" alt="">
        <img src="http://img3.anzhi.com/static/weixin/5/images/page1_6.png" class="story_text_load" alt="">
        <img src="http://img3.anzhi.com/static/weixin/5/images/page1_7.png" class="story_text_load" alt="">
        <img src="http://img3.anzhi.com/static/weixin/5/images/page1_7.png" class="story_text_load" alt="">
      </div>
    </div>
    <div class="page2">
      <div class="page2_text"></div>
    </div>
    <div class="page3">
      <div class="page3_text1"></div>
      <div class="page3_text2"></div>
      <div class="page3_text3"></div>
    </div>

    <div class="page4">
      <div class="wangzi"></div>
      <div class="page4_text1"></div>
      <div class="page4_text2"></div>
      <div class="page4_text3"></div>
      <div class="mojing"></div>
    </div>
    <div class="rules">
      <div class="textWrap">
        <p>
          <img src="http://img3.anzhi.com/static/weixin/5/images/rule_text1.png" alt="">
        </p>
        <p>
          <img src="http://img3.anzhi.com/static/weixin/5/images/rule_text2.png" alt="">
        </p>
        <p>
          <img src="http://img3.anzhi.com/static/weixin/5/images/rule_text3.png" alt="">
        </p>
        <p>
          <img src="http://img3.anzhi.com/static/weixin/5/images/rule_text4.png" alt="">
        </p>
        <p>
          <img src="http://img3.anzhi.com/static/weixin/5/images/rule_text5.png" alt="">
        </p>
      </div>
    </div>
    <div class="gameContent" id="gameBox">
      <div class="shadowBg">
      </div>
      <div class="coutDwon3"></div>
      <div class="coutDwon2"></div>
      <div class="coutDwon1"></div>
      <div class="coutDwon0"></div>
      <div class="game_girl">
      </div>
      <div class="timeProcess">
        <div class="process_bar"></div>
      </div>
  </div>
  <div class="share_shadow">
  </div>
  <div class="shareIcon"></div>
  <div class="share_tips"></div>
  <div class="success">

    <div class="success_text1"></div>
    <div class="success_text2"></div>
    <div class="logo">
    </div>
  </div>
  <div class="success_shareline">
      <a href="javascript:;" class="replay">
        <img src="http://img3.anzhi.com/static/weixin/5/images/replay_btn.png" alt="">
      </a>
      <a href="javascript:;" class="share">
        <img src="http://img3.anzhi.com/static/weixin/5/images/share_btn.png" alt="">
      </a>
    </div>
  <div class="failed">
    <div class="failed_user1"></div>
    <div class="failed_user2"></div>
    <div class="logo">
    </div>
  </div>
  <div class="failed_shareLine">
      <a href="javascript:;" class="replay">
        <img src="http://img3.anzhi.com/static/weixin/5/images/replay_btn.png" alt="">
      </a>
      <a href="javascript:;" class="share">
        <img src="http://img3.anzhi.com/static/weixin/5/images/share_btn.png" alt="">
      </a>
    </div>
  <div class="eventTips">
  </div>
  </div>

  <audio src="http://img3.anzhi.com/static/weixin/5/music/2.mp3" preload id="count"></audio>
  <audio src="http://img3.anzhi.com/static/weixin/5/music/3.mp3" preload id="go"></audio>
  <audio src="http://img3.anzhi.com/static/weixin/5/music/1.mp3" preload id="bg_music"></audio>
  <div style="display:none;">
    <script>
var _hmt = _hmt || [];
(function() {
  var hm = document.createElement("script");
  hm.src = "//hm.baidu.com/hm.js?37792f21b8e6da16e95f2169520e9dbe";
  var s = document.getElementsByTagName("script")[0];
  s.parentNode.insertBefore(hm, s);
})();
</script>

  </div>
<script>

$(function(){
    wx.config({
        debug: false,
        appId: '<?php echo $signPackage["appId"];?>',
        timestamp: <?php echo $signPackage["timestamp"];?>,
        nonceStr: '<?php echo $signPackage["nonceStr"];?>',
        signature: '<?php echo $signPackage["signature"];?>',
        jsApiList: ['onMenuShareTimeline','onMenuShareAppMessage','onMenuShareQQ']
      });

    wx.ready(function(){

      setTimeout(function() {
        wx.onMenuShareTimeline({
       title: '如果我变成狗，你还会爱我吗', // 分享标题
       link: '<?php echo SELFURL ?>', // 分享链接
       imgUrl: 'http://can.dfun.com.cn/static/images/share_img.png', // 分享图标
       success: function () {
        // 用户确认分享后执行的回调函
        $.get('save.php',function() {
          window.location.href = "qr.html";
        });
       }
      });
      wx.onMenuShareAppMessage({
       title: '如果我变成狗，你还会爱我吗', // 分享标题
       desc: '', // 分享描述
       link: '<?php echo SELFURL ?>', // 分享链接
       imgUrl: 'http://can.dfun.com.cn/static/images/share_img.png', // 分享图标
       type: '', // 分享类型,music、video或link，不填默认为link
       dataUrl: '',
       trigger:function(res){
        //alert(res);
       },
       success: function () {
        $.get('save.php',function() {
          window.location.href = "qr.html";
        });
       }
      });
      },200);
    });

  });
</script>

</body>
</html>

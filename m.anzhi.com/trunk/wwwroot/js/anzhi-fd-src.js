/**
 * 安智极速下载调用代码，若安装市场则调起市场，未安装则下载市场，安装打开后再调起对应的动作
 * 调用方式sample
 * 
 * var id = 1763945;
 * var pkg = 'com.yinhan.shenmo.anzhi';
 * var js_param = {type:'details', id:id, pkg:pkg, flag:1, from:2};
 * var php_param = {type:'details', softid:id};
 * Azfd.success = function(){//已安装市场的回调函数，未指定不做任何处理};
 * Azfd.error = function(){//未安装市场调起的动作，未指定则下载市场};
 * Azfd.share_download(js_param, php_param); 
 */

var Azfd = (function () {
  function Construct() {
    this.port_arr = [12345, 23456];
    this.invoke_count = 0;
    this.ping_count = 0;
    this.max_request = this.port_arr.length;
    this.has_market = false;
    this.url_prefix = 'http://fd.anzhi.com';
    this.lock = false;
    this.success = null; //已安装市场回调函数
    this.error = null; //未安装市场回调函数
    this.container = document.getElementById("az_spirit");

    if (this.container == null) {
      this.container = document.createElement('div');
      this.container.id = 'az_spirit';
      document.body.appendChild(this.container);
    }
    var ifr = document.createElement('iframe');
    ifr.src = '/ua.php';
    ifr.style.display = 'none';
    this.container.appendChild(ifr);

    this.share_download = function (js_param, php_param) {
      if (this.lock) {
        return;
      }
      this.lock = true;

      this.has_market = false;
      this.invoke_count = 0;
      this.ping_count = 0;

      


      this.container.innerHTML = "";
      if (!this.port_arr) {
        return;
      }
      
      var scheme_params = '';
      var action = '';
      action = js_param['type'];
      if (typeof(js_param['callback']) != 'undefined') {
        this.success = js_param['callback'];
      } else {
        this.success = null;
      }
      var js_str = '';
      for (var k in js_param) {
        if (k != 'callback') {
          js_str += '&' + k + '=' + js_param[k];
        }
        if (k != 'type' && k != 'callback') {
          if (scheme_params == '') {
            scheme_params += '?' + k + '=' + js_param[k];
          } else {
            scheme_params += '&' + k + '=' + js_param[k];
          }
        }
      }
      var php_str = '';
      var auto_download = true;
      for (var k in php_param) {
        if (k == 'disable_auto' && php_param[k] == true) {
          auto_download = false;
        } else {
          php_str += '&' + k + '=' + php_param[k];
        }
      }
      
      var _this = this;
      //进行本地sock调用
      this.invoke_client(js_str, function(){

        setTimeout(function () {
          if (!_this.has_market) {
            //未检测到市场安装则采用scheme/intent方式调用
            _this.invoke_scheme(action, scheme_params);
            //若市场被调起，则延迟检测市场存活状态
            setTimeout(function () {
              js_str = '&type=ping&id=unknown';
              _this.invoke_client(js_str, function(){
                _this.lock = false;
                if (!_this.has_market) {
                  //市场安装检测失败的处理
                  var r = Math.floor(Math.random() * 1000000000);
                  var php_url = '/fast.php?r=' + r + php_str;
                  if (_this.error != null) {
                    _this.error(php_url);
                  } else if (auto_download) {
                    window.location.href = php_url;
                  }
                }
              });
            }, 1000);
          } else {
            _this.lock = false;
          }
        }, 1000);
      });
      _this.lock = false;
    };
    
    this.invoke_client = function(js_str, callback) {
      var rand = 0;
      var js_url = '';
      
      for (var i = 0; i < this.max_request; i++) {
        rand = Math.floor(Math.random() * 1000000000);
        js_url = this.url_prefix + ':' + this.port_arr[i] + '/query?callback=Azfd.invoke_callback&r=' + rand + js_str;
        var m = document.createElement("script");
        m.type = 'text/javascript';
        m.src = js_url;
        m.async = true;
        this.container.appendChild(m);
        _log(js_url);
      }
      callback();
    };
    
    this.invoke = function(action, scheme_params) {
      var schemePrefix = 'anzhimarket';
      var intentPackage = 'cn.goapk.market';

      var schemeUrl = schemePrefix + '://' + action + scheme_params;
      var intentUrl = 'intent://'+ action + scheme_params+'#Intent;scheme='+ schemePrefix +';package=' + intentPackage + ';end';

      _log(schemeUrl);
      var ifr = document.createElement('iframe');
      ifr.src = schemeUrl;
      ifr.style.display = 'none';
      this.container.appendChild(ifr);

      if ("webkitHidden" in document) {
        _log("has webkitHidden attribute " + document.webkitHidden);
        if (!document.webkitHidden) {
          _log(intentUrl);
          ifr.src = intentUrl;

          if (!document.webkitHidden) {
            _log(intentUrl + "click");
            var openIntentLink = document.getElementById('openIntentLink');
            if (!openIntentLink) {
              openIntentLink = document.createElement('a');
              openIntentLink.id = 'openIntentLink';
              openIntentLink.style.display = 'none';
              this.container.appendChild(openIntentLink);
            }
            openIntentLink.href = intentUrl;
            openIntentLink.dispatchEvent(this.customClickEvent());
          }
        }
      }
    };


    this.invoke_scheme = function(action, scheme_params) {
      return this.invoke(action, scheme_params);

      var ua = navigator.userAgent.toLowerCase();
      var noIntentTest = /aliapp|360 aphone|weibo|windvane|ucbrowser/.test(ua);
      var hasIntentTest = /chrome|samsung/.test(ua);
      var isAndroid = /android|adr/.test(ua) && !(/windows phone/.test(ua));
      var canIntent = !noIntentTest && hasIntentTest && isAndroid;
    
      if (ua.indexOf('m353')>-1 && !noIntentTest) {
        canIntent = false;
      }
      var schemePrefix = 'anzhimarket';
      var intentPackage = 'cn.goapk.market';
      
      if (!canIntent) {
        var schemeUrl = schemePrefix + '://' + action + scheme_params;

        var ifr = document.createElement('iframe');
        ifr.src = schemeUrl;
        ifr.style.display = 'none';
        this.container.appendChild(ifr);
      } else {
        var intentUrl = 'intent://'+ action + scheme_params+'#Intent;scheme='+ schemePrefix +';package=' + intentPackage + ';end';
        
        var openIntentLink = document.getElementById('openIntentLink');
        if (!openIntentLink) {
          openIntentLink = document.createElement('a');
          openIntentLink.id = 'openIntentLink';
          openIntentLink.style.display = 'none';
          this.container.appendChild(openIntentLink);
        }
        openIntentLink.href = intentUrl;
        openIntentLink.dispatchEvent(this.customClickEvent());
      }
    };
    
    this.customClickEvent = function () {
      var clickEvt;
      if (window.CustomEvent) {
        clickEvt = new window.CustomEvent('click', {
          canBubble: true,
          cancelable: true
        });
      } else {
        clickEvt = document.createEvent('Event');
        clickEvt.initEvent('click', true, true);
      }

      return clickEvt;
    };

    this.invoke_callback = function(){
      this.has_market = true;
      if (this.success !== null) {
        var magic = arguments[0];
        var version = parseInt(arguments[1]);
        var firmware = arguments[2];
        var flag = arguments[3];

        if (typeof(this.success) == 'function') {
          this.success(magic, version, firmware, flag);
        } else if (typeof(this.success) == 'string') {
          window[this.success](magic, version, firmware, flag);
        }
      }
    };

    function _log(msg)
    {
        //var d = new Date();
        //var t = d.getTime();
        //document.getElementById("_debug").innerHTML = document.getElementById("_debug").innerHTML + t + ' ' +msg + '<br>';
    }

  }
  var obj = new Construct();
  return obj;
})();

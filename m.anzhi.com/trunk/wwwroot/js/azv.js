/*===================================视频自动播放设置开始================================*/    
    //是否已弹出过提示信息
	var video_tishi = 0;
	var m_network = '';
	var open_type = 0; //自动播放开关
	var msg = "您正在使用运营商网络，继续观看将产生流量，建议wi-fi时观看";
	var setsilent = false;
	get_network_status();
	checkvideosilent(); //调起客户端静音播放设置
	var videobj = document.getElementsByTagName("video");
	for(var i=0;i<videobj.length;i++){
		videobj[i].addEventListener('play',function(){
			try{
				video_play_auto(this);
				setVideoSilent(this);
				
			}catch(e){
			}
		}) 
	}
	//如果为wifi定时监控网络状态
	if(open_type==2){
		var net_work_set = setInterval("_get_newwork_type()",8000);
	}
	function _get_newwork_type() {
		if(video_tishi != 1){
			get_network_status();
			if(open_type != 2){
				clearInterval(net_work_set);
				for(var i=0;i<videobj.length;i++){
					if(videobj[i].paused == false){
						var _play = confirm(msg);
						if(_play != true){
							getplay(i,0);
							break;
						}else{
							video_tishi = 1;
						}
					}
				}
			}
		}	
	}
	//监控手机滚动事件
	window.onscroll=function(){
		if(open_type != 3){
			var v_play = 0;
			var sc_height = window.screen.height;
			for(var i=0; i<videobj.length; i++){
				if(open_type != 3 || video_tishi == 1){
					var _top = videobj[i].getBoundingClientRect().top;
					var _bottom = videobj[i].getBoundingClientRect().bottom;
					var _height = videobj[i].clientHeight;
					var height = _height / 2;
					_height = _height / 2 - _height;
					if(_top>_height && _bottom>height && _top<sc_height && _bottom<sc_height){

						if(v_play == 0){
							//如果当前视频未播放则播放该视频
							if(videobj[i].paused == true){
								getplay(i,1);
							}
							v_play = 1;
						}else{
							//如果当前为播放状态则停止播放
							if(videobj[i].paused == false){
								getplay(i,0);
							}
						}
					}else{
						//如果当前为播放状态则停止播放
						if(videobj[i].paused == false){
							getplay(i,0);
						}
					}
				}
			}
		}
	}
	//手机播放按钮点击监控
	function video_play_auto(obj){
		if(open_type == 4){
			if(!video_tishi){
					if(obj.paused == false){
						var _play = confirm(msg);
						if(_play != true){
							obj.pause();
						}else{
							video_tishi = 1;
						}
					}
			}
		}
	}
	//获取网络状态
	function get_network_status(){
		try{

			playtype = window.AnzhiActivitys.getAutoPlayType(); //1 任何网络自动播放；2 只有wifi下自动播放；3不自动播放

			switch(playtype)
			{
				case 1:			
					open_type = 1; 
				  break;
				case 2:
					m_network = window.AnzhiActivitys.getNetWorkType();
				  	open_type = 2; 
					if(m_network!='WF'){
						open_type = 4; //移动网络
					}
				  break;
				case 3:
					open_type = 3;
				  break;
				default:
		
			}


		}catch(err){
			open_type = 5; //始终播放 旧值 history_net
		}
	}


// void postCallback(String json)
// 由端来调用 js方法
// 参数：
// Json：格式
// {"CALLBACK":"完整的回调js方法含参数","POST":"1 主线程post执行，0 当前线程直接回调"}

	// videoobj 视频对象  type 1 播放 0 暂停
	function getplay(id,type){
		var json = '';
		var callback = 'javascript:getautoplay('+id+','+type+');';
		try{
			json = '{"CALLBACK":"'+callback+'","POST":"1"}';				
			window.AnzhiActivitys.postCallback(json);
		}catch(e){
			getautoplay(id,type)
		}
	}

	function getautoplay(id,type){
			if(type){				
				videobj[id].play();
			}else{
				videobj[id].pause();				
			}

	}



	function setVideoSilent(obj){
		if(setsilent){
			obj.muted = true;
		}else{
			obj.muted = false;
		}
	}



	function checkvideosilent(){
		try{
			setsilent = window.AnzhiActivitys.videoInSilentMode();
		}catch(e){

		}
	}
/*===================================视频自动播放设置结束================================*/
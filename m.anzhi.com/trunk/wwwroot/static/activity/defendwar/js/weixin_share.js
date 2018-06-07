wx.config({
	debug: debug_mode, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
	appId: wx_share_config.appId, // 必填，公众号的唯一标识
	timestamp: wx_share_config.timestamp, // 必填，生成签名的时间戳
	nonceStr: wx_share_config.nonceStr, // 必填，生成签名的随机串
	signature: wx_share_config.signature,// 必填，签名，见附录1
	jsApiList: ['onMenuShareTimeline','onMenuShareAppMessage','onMenuShareQQ','onMenuShareWeibo','onMenuShareQZone'] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
});
wx.ready(function(){
	// 分享到朋友圈
	wx.onMenuShareTimeline({
		title: share_title, // 分享标题
		link: share_url, // 分享链接
		imgUrl: share_img, // 分享图标
		success: function () { 
			// 用户确认分享后执行的回调函数
		},
		cancel: function () { 
			// 用户取消分享后执行的回调函数
		}
	});
	// 分享给朋友
	wx.onMenuShareAppMessage({
		title: share_title, // 分享标题
		desc: share_desc, // 分享描述
		link: share_url, // 分享链接
		imgUrl: share_img, // 分享图标
		type: '', // 分享类型,music、video或link，不填默认为link
		dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
		success: function () { 
			// 用户确认分享后执行的回调函数
		},
		cancel: function () { 
			// 用户取消分享后执行的回调函数
		}
	});
	// 分享到QQ
	wx.onMenuShareQQ({
		title: share_title, // 分享标题
		desc: share_desc, // 分享描述
		link: share_url, // 分享链接
		imgUrl: share_img, // 分享图标
		success: function () { 
		   // 用户确认分享后执行的回调函数
		},
		cancel: function () { 
		   // 用户取消分享后执行的回调函数
		}
	});
	// 分享到腾讯微博
	wx.onMenuShareWeibo({
		title: share_title, // 分享标题
		desc: share_desc, // 分享描述
		link: share_url, // 分享链接
		imgUrl: share_img, // 分享图标
		success: function () { 
		   // 用户确认分享后执行的回调函数
		},
		cancel: function () { 
			// 用户取消分享后执行的回调函数
		}
	});
	// 分享到QQ空间
	wx.onMenuShareQZone({
		title: share_title, // 分享标题
		desc: share_desc, // 分享描述
		link: share_url, // 分享链接
		imgUrl: share_img, // 分享图标
		success: function () { 
		   // 用户确认分享后执行的回调函数
		},
		cancel: function () { 
			// 用户取消分享后执行的回调函数
		}
	});
});
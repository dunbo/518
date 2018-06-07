var page = 1, lock = false;
function formore(mores,container,actions){
	 $(mores).html("正在加载<img src='/uc_tpl/images/waiting.gif' alt='加载中' />");
	 if(lock) {
         return;
      }
	lock = true;	
	$.ajax({
			type: "GET",
			url: actions+page,
			dataType: "html",
			success: function(html){
				page++;
				if(!/<(.*)>.*<\/\1>/.test(html)){
					$(mores).empty();
					$(mores).html('<a href="javascript:void(0);">没有更多！</a>');
				} else {
					$(container).after(html);
					var pages = page-1;
					var more = '<a href="#li'+pages+'" onclick="formore(\''+mores+'\',\''+container+'\',\''+actions+'\');">点击查看更多</a>';
					$(mores).html(more);
					lock = false;
				}
			}
	});	
}
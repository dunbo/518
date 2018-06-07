var ajax_list = {};
var all_soft_status = "{";

for (var i=0;i<num;i++) {
    ajax_list[i] = false;
}
var c = 0;
function check_ajax_status()
{
    var i = c;
    c++;
    ajax_list[i] = true;
    
    var is_check = true;
    for(var j in ajax_list) {
        if (!ajax_list[j]) {
            is_check = false;
            break;
        }
    }
    if (!is_check) return;
    
    for( var soft_id in g_soft_info ){
        all_soft_status +="\""+soft_id+"\":{\"version\":\""+g_soft_info[soft_id]['version']+"\",\"packageName\":\""+g_soft_info[soft_id]['packageName']+"\"},";
    }
    all_soft_status +="\"\":\"\"}";
	
    App1.g_soft_info = g_soft_info;
    App1.style = {add:"ins05",adding:"ins04",installing:"ins03",oneInstall:"ins01",open:"ins02"};
    //给客户端发送需要状态的软件 ,页面列表的软件状态（比如已安装），需要在页面加载完后，给客户端发送页面里的软件信息。
    var client_p = "jinriremen_data|"+all_soft_status;
    try{window.external.SoftMgr_Notify(2,client_p);}catch(e){}
}
/**
 * Created by anbei on 14-8-27.
 */
$(document).ready(function(){
    $('.recommend li .soft_item').live({mouseover:function(){
        $(this).css('backgroundColor','#eff1f3');
        $(this).children('.stars').css('display','none');
        $(this).children('.down').css('display','block');
        $(this).next(".pop_soft").show();
		if(!$(this).next(".pop_soft").children('.soft_code').has('src').length){
			//alert($(this).next(".pop_soft").children('.soft_code').attr('rel'));
			$(this).next(".pop_soft").children('.soft_code').html('<img src="'+$(this).next(".pop_soft").children('.soft_code').attr('rel')+'">');
		}
    },
        mouseout:function(){
            $(this).css('backgroundColor','');
            $(this).children('.stars').css('display','block');
            $(this).children('.down').css('display','none');
            $(this).next(".pop_soft").hide();
        }
    });
});




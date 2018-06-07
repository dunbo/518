$(function(){
	$('.form_arrow').click(function(){
		 $(this).parent().find(".select_type").toggle();
		 $(this).parent().find('.form_arrow').toggleClass('arrow_up');
	});
	$('.select_input').click(function(){
		$('.select_type li').unbind();
		 $(this).parent().find(".select_type").toggle();
		 $(this).parent().find('.form_arrow').toggleClass('arrow_up');
		 $('.select_type li').click(function(){
			var data_type = $(this).text();
			$(this).parents('.feedback_form_item').find(".select_input").val(data_type);
			$(this).parents('.feedback_form_item').parents().find(".select_type").hide();
			$(this).parents('.feedback_form_item').find('.form_arrow').toggleClass('arrow_up');
		});
	});
	$('.select_type li').click(function(){
		var data_type = $(this).text();
		$(this).parents('.feedback_form_item').find(".select_input").val(data_type);
		$(this).parents('.feedback_form_item').parents().find(".select_type").hide();
		$(this).parents('.feedback_form_item').find('.form_arrow').toggleClass('arrow_up');
	});
})
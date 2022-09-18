(function($) {  // Avoid conflicts with other libraries

'use strict';

//need post count 0.6.0
phpbb.addAjaxCallback('order_an_item', function(res) {
	alert("addAjaxCallback::order_an_item"+JSON.stringify(res));
	
	const typesData = JSON.parse(res.TYPE_DATA);
	
	const postId = res.POST_ID;
	const typeId = res.TYPE_ID;
	const deleteUrl = res.REACTION_DELETE;
	const viewUrl = res.VIEW_URL;
	
	alert(typesData+":"+postId+":"+deleteUrl+":"+viewUrl);	
}); 

$("#simpleshop_order_button").click(function() {
	let postData = {};
	postData['user_id'] = $('#simpleshop_user_id').val();
	let items = [];

	const form = $(this).closest("form");
	form.find('.simpleshop_item').each(function() { 
		let select = $(this);
		let item = {};
		item['id'] = select.attr("name");
		item['value'] = select.val();
		items.push(item);
	});
	
	postData['items'] = items;

  	$.ajax({
		type: 'post',
		url: form[0].action,
		datatype:'json',
		data: postData,
		success: function (response) {
			if (response.success && response.statistic)
			{
				let statistic = response.statistic;
				statistic.forEach((stat) => {
					$('#statistic_all_'+stat.id).html(stat.count)
					$('#statistic_user_'+stat.id).html(stat.user_count);
				});
			}
		}
	});
});

$("#simpleshop_items_report").click(function() {
	const form = $(this).closest("form");
  	$.ajax({
		type: 'post',
		url: form[0].action,
		datatype:'json',
		success: function (response) {
			if (response.success && response.title && response.content)
			{
				$(".simpleshop_report_title").html(response.title);
				$(".simpleshop_report_content").html(response.content);

				$("#simpleshop_sale_panel").addClass('d-none');
				$("#simpleshop_report_panel").removeClass('d-none');
			}
		}
	});	
});

$("#simpleshop_person_report").click(function() {
	const form = $(this).closest("form");
  	$.ajax({
		type: 'post',
		url: form[0].action,
		datatype:'json',
		success: function (response) {
			if (response.success && response.title && response.content)
			{
				$(".simpleshop_report_title").html(response.title);
				$(".simpleshop_report_content").html(response.content);

				$("#simpleshop_sale_panel").addClass('d-none');
				$("#simpleshop_report_panel").removeClass('d-none');
			}
		}
	});
});

})(jQuery); // Avoid conflicts with other libraries

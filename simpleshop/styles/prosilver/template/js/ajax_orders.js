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
			alert("ajax::order_an_item"+JSON.stringify(response));
			//document.getElementById("new_select").innerHTML=response; 
		}
	});
});

})(jQuery); // Avoid conflicts with other libraries

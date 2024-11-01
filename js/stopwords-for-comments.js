var stopword = jQuery('#stopword').val('');

get_stopwords_for_comments();

/////////////////////////////\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

jQuery('#submit_stopwords_for_comments').on('click', function() {

	var stopword = jQuery('#stopword').val();

	console.log(stopword);

	jQuery.ajax({

		url: '/wp-admin/admin-ajax.php',
		type: 'GET',
		data: 'action=set_stopwords_for_comments&stopword='+stopword,
		
		success: function( data ) {

			var stopword = jQuery('#stopword').val('');
			get_stopwords_for_comments();
			jQuery('.result_message').html(data);
			setTimeout(hide_notice, 3000);
		}
	});		
});

/////////////////////////////\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

function get_stopwords_for_comments() {

	jQuery.ajax({

	url: '/wp-admin/admin-ajax.php',
	type: 'GET',
	data: 'action=get_stopwords_for_comments',
	
	success: function( data ) {

			//console.log(data)

			jQuery('.one_byten_stopwords_for_comments_current_list').html(data);

			delete_stopwords_for_comments();
		}
	});		
}

////////////////////////////////\\\\\\\\\\\\\\\\\\\\\\\\\\\\

function delete_stopwords_for_comments() {

	jQuery('.one_byten_stopwords_for_comments_current_list_item').on('click', function() {

		jQuery(this).css('background', '#8c8f94');

		jQuery(this).css('color', '#fff');

		var stopword_id = jQuery(this).data('stopword_id');

		jQuery.ajax({

			url: '/wp-admin/admin-ajax.php',
			type: 'GET',
			data: 'action=delete_stopwords_for_comments&stopword_id='+stopword_id,
			
			success: function( data ) {

				var stopword = jQuery('#stopword').val('');
				get_stopwords_for_comments();
				jQuery('.result_message').html(data);
				setTimeout(hide_notice, 2000);
			}
		});		
	});
}

////////////////////////////////////\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

function hide_notice() {

	jQuery('.result_message').html('');
}

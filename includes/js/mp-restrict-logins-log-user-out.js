jQuery(document).ready(function($){
							
		var postData = {
			action: 'mp_restrict_logins_log_user_out',
		};
					
		mp_repo_changelog_request = $.ajax({
			type: "POST",
			data: postData,
			url: mp_restrict_login_logout_vars.ajax_url,
			success: function (response) {
				
				//Send user to logout page
				window.location = mp_restrict_login_logout_vars.logout_page_url;
			}
		}).fail(function (data) {
			console.log(data);
		});	
		
});
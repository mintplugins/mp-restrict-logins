<?php
/**
 * Functions used by the MP Restrict Logins Plugin
 *
 * @link http://moveplugins.com/doc/
 * @since 1.0.0
 *
 * @package    MP Restrict Logins
 * @subpackage functions
 *
 * @copyright   Copyright (c) 2014, Move Plugins
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @author      Philip Johnston
 */
 
 /**
 * Check if the subscriber should be logged in on page loads
 *
 * @since    1.0.0
 * @link     http://moveplugins.com/doc/
 * @see      function_name()
 * @param    array $args See link for description.
 * @return   void
 */
 function mp_restrict_logins_page_load_check(){
					
	//If this user is a subscriber
	if ( !current_user_can( 'delete_posts' ) ){

		$user_id = get_current_user_id();

		//If the user has no cookie, he hasn't logged in. So how would you ever end up here? Only if you were suddenly using a different user id probably as a super admin
		if( !isset( $_COOKIE['mp_restrict_logins_user_session_timeout_' . $user_id] ) ){

			return;

		}

		//If second time cookie is set
		if ( isset( $_COOKIE['mp_restrict_logins_second_time_or_later' . $user_id] ) ){

			//If this is the second page-load since logging in or later
			if ( $_COOKIE['mp_restrict_logins_second_time_or_later' . $user_id] ){		

				//See what time this session was set to be cancelled at
				$user_timeout_transient = get_transient( 'mp_restrict_logins_user_timeout_' . $user_id);

				//If cookie and transient are not the same value (somebody else has logged in and changed the transient but not your cookie),
				//Or if you have been away for more than 31 seconds (you weren't here to 'hold' the account and 'kick the can' down the street)
				if( $_COOKIE['mp_restrict_logins_user_session_timeout_' . $user_id] != $user_timeout_transient || $user_timeout_transient < time() ){
					
					//Tell wp_enqueue_scripts to log the user out when it loads
					global $mp_restrict_logins_log_user_out;
					$mp_restrict_logins_log_user_out = true;

				}
				//If cookie matches transient and we are still within the timeout
				else{

					//Extend the user's session
					mp_restrict_logins_extend_session( $user_id );

				}
			}
			//If this is the first time logging in since logging out last
			else{

				//create second time or later cookie
				setcookie( 'mp_restrict_logins_second_time_or_later' . $user_id, true, 2147483647, '/' ); 

			}
		//If second time cookie is not set - this is the first time logging in since last cookie clear
		} else{

			//create second time or later cookie
			setcookie( 'mp_restrict_logins_second_time_or_later' . $user_id, true, 2147483647, '/' ); 

		}

	}

}
add_action( 'wp_loaded', 'mp_restrict_logins_page_load_check' );

/**
 * Enqueue the scripts used to log the user out using ajax. Conditional load based on previous check stored in $mp_restrict_logins_log_user_out;
 *
 * @since    1.0.0
 * @link     http://moveplugins.com/doc/
 * @see      function_name()
 * @param    array $args See link for description.
 * @return   void
 */
function mp_restrict_logins_enqueue_ajax_logout(){
	
	global $mp_restrict_logins_log_user_out;
	
	if ($mp_restrict_logins_log_user_out){
		//Then log the user out so they can log back in
		wp_enqueue_script( 'mp_restrict_logins_log_user_out_ajax', plugins_url( '/js/mp-restrict-logins-log-user-out.js', dirname( __FILE__ ) ) );
		wp_localize_script( 'mp_restrict_logins_log_user_out_ajax', 'mp_restrict_login_logout_vars', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'logout_page_url' => add_query_arg( array( 'session_timed_out' => true ), get_bloginfo( 'wpurl' ) . '/wp-login.php' ),
		));
	}
}
add_action( 'wp_enqueue_scripts', 'mp_restrict_logins_enqueue_ajax_logout' );
add_action( 'admin_enqueue_scripts', 'mp_restrict_logins_enqueue_ajax_logout' );

/**
 * Ajax callback function used to log the user out
 *
 * @since    1.0.0
 * @link     http://moveplugins.com/doc/
 * @see      function_name()
 * @param    array $args See link for description.
 * @return   void
 */
function mp_restrict_logins_log_user_out_ajax_callback(){
	
	//Remove the cookie that says we should show an error
	setcookie("mp_restrict_logins_oops", "", time()-3600, '/' );
	
	//Remove "second time or later" cookie 
	setcookie( 'mp_restrict_logins_second_time_or_later' . $user_id, false, 2147483647, '/' ); 
		
	wp_logout();
	
	echo "logged out";
	die();
}
add_action( 'wp_ajax_mp_restrict_logins_log_user_out', 'mp_restrict_logins_log_user_out_ajax_callback' );
add_action( 'wp_ajax_nopriv_mp_restrict_logins_log_user_out', 'mp_restrict_logins_log_user_out_ajax_callback' );
			
/**
 * Set user transient upon login or log user out if somebody's already logged in
 *
 * @since    1.0.0
 * @link     http://moveplugins.com/doc/
 * @see      function_name()
 * @param    array $args See link for description.
 * @return   void
 */
function mp_restrict_logins_user_login($user_login, $user) {
	
	//If this user is a subscriber
	if ( !current_user_can( 'delete_posts' ) ){
		
		$user_id = $user->ID;
		
		//See what time this session was set to be cancelled at
		$user_timeout = get_transient( 'mp_restrict_logins_user_timeout_' . $user_id);
				
		//If timeout for this user hasn't passed yet, you can't log in yet
		//And now another user is trying to "login"
		if ( time() < $user_timeout && !empty( $user_timeout ) ){
			
			//Store error cookie so we can show error to user on logout page
			setcookie("mp_restrict_logins_oops", true, time()+3600 );  /* expire in 1 hour but we delete it on next page load*/
			
			//Destroy transient cookie	
			setcookie( 'mp_restrict_logins_user_session_timeout_' . $user_id, time()-3600, time() - 120, '/' );  
			
			//Log user out
			wp_logout();
			
		}
		
		//If this user is signing in and is the only one using the account
		else{
			
			//destroy "second time or later" cookie - because this is the first time
			setcookie( 'mp_restrict_logins_second_time_or_later' . $user_id, false, 2147483647, '/' ); 
			
			//destroy error cookie 
			setcookie("mp_restrict_logins_oops", false, time()-3600 );  /* expire in 1 hour but we delete it on next page load*/
						
			mp_restrict_logins_extend_session( $user_id );
					
		}
	}
		
}
add_action('wp_login', 'mp_restrict_logins_user_login', 10, 2);

/**
 * Delete user transient upon log out
 *
 * @since    1.0.0
 * @link     http://moveplugins.com/doc/
 * @see      function_name()
 * @param    array $args See link for description.
 * @return   void
 */
function mp_restrict_logins_clear_transient_on_logout() {
	
	//If this user is a subscriber
	if ( !current_user_can( 'delete_posts' ) ){
		
		$user_id = get_current_user_id();
		
		//Remove the cookie that says we should show an error
		setcookie("mp_restrict_logins_oops", "", time()-3600, '/' );
		
		//Remove "second time or later" cookie 
		setcookie( 'mp_restrict_logins_second_time_or_later' . $user_id, false, 2147483647, '/' ); 
			
		//Set user timeout to have no value. They have stopped 'holding' control of this account.
    	delete_transient( 'mp_restrict_logins_user_timeout_' . get_current_user_id() );
				
	}
	
}
add_action('wp_logout', 'mp_restrict_logins_clear_transient_on_logout');

/**
 * Check if we should show error message and set global variable used later on on login page
 *
 * @since    1.0.0
 * @link     http://moveplugins.com/doc/
 * @see      function_name()
 * @param    array $args See link for description.
 * @return   void
 */
function mp_restrict_logins_cookie_check(){
	
	if ( isset( $_GET['session_timed_out'] ) ){
		global $mp_restrict_logins_oops;
		
		$mp_restrict_logins_oops = 'session_timed_out';
	}
	//If we should show the error
	elseif( isset( $_COOKIE["mp_restrict_logins_oops"] )){
		
		global $mp_restrict_logins_oops;
		
		if( $_COOKIE["mp_restrict_logins_oops"] ){
			
			//Set a global variable used to show error later in the page
			$mp_restrict_logins_oops = 'someone_else_logged_in';
			
			//Remove the cookie that says we should show an error
			setcookie("mp_restrict_logins_oops", "", time()-3600, '/' );
		
		}
		
	}
};
add_action( 'init', 'mp_restrict_logins_cookie_check' );

/**
 * Enqueue JS error message on login page
 *
 * @since    1.0.0
 * @link     http://moveplugins.com/doc/
 * @see      function_name()
 * @param    array $args See link for description.
 * @return   void
 */
function mp_restrict_logins_enqueue_errors(){

	global $mp_restrict_logins_oops;
	
	//If we should show the error
	if ( !empty( $mp_restrict_logins_oops ) ){

		//If the session timed out
		if( $mp_restrict_logins_oops == 'someone_else_logged_in' ){
			
			$error_message = __( 'Oops! This user is already Logged-In in another location!', 'mp_user_restrict' );
			
		}
		else{
			$error_message = __( 'Session Timed Out. Please log in again.', 'mp_user_restrict' );
		}
		
		//Show the error
		wp_enqueue_script( 'mp_restrict_logins_error_js', plugins_url( '/js/mp-restrict-logins-error.js', dirname( __FILE__ ) ) );
		wp_localize_script( 'mp_restrict_logins_error_js', 'mp_restrict_login_error_vars', array(
			'error_message' => $error_message
		));
		
	}

}
add_action( 'login_head', 'mp_restrict_logins_enqueue_errors' );

/**
 * Change heartbeat to pulse every 15 seconds
 *
 * @since    1.0.0
 * @link       http://moveplugins.com/doc/
 * @see      function_name()
 * @param  array $args See link for description.
 * @return   void
 */
function mp_restrict_logins_heartbeat_settings( $settings ) {
    $settings['interval'] = 15; //Anything between 15-60
    return $settings;
}
add_filter( 'heartbeat_settings', 'mp_restrict_logins_heartbeat_settings' );

/**
 * Change heartbeat to pulse every 15 seconds
 *
 * @since    1.0.0
 * @link       http://moveplugins.com/doc/
 * @see      function_name()
 * @param  array $args See link for description.
 * @return   void
 */
function mp_restrict_logins_heartbeat_scripts($hook_suffix) {

	// Make sure the JS part of the Heartbeat API is loaded.
	wp_enqueue_script('heartbeat');

	// Output the test JS.
	add_action( 'admin_print_footer_scripts', 'mp_restrict_logins_heartbeat_js', 20 );
	add_action( 'wp_print_footer_scripts', 'mp_restrict_logins_heartbeat_js', 20 );
}
add_action( 'admin_enqueue_scripts', 'mp_restrict_logins_heartbeat_scripts' );
add_action( 'wp_enqueue_scripts', 'mp_restrict_logins_heartbeat_scripts' );

/**
 * Modify data  upon heartbeat tick and send message back
 *
 * @since    1.0.0
 * @link       http://moveplugins.com/doc/
 * @see      function_name()
 * @param  array $args See link for description.
 * @return   void
 */
function mp_restrict_logins_heartbeat_recieved( $response, $data ) {
 	
	$response['kicked_can'] = "Not Subscriber so NOT KICKED!";
	
	//If this user is a subscriber
	if ( !current_user_can( 'delete_posts' ) ){
		
		$response['kicked_can'] = "Subscriber but no logged-in-check so NOT KICKED!";
		
		// Make sure we only run our query if the mp_restrict_logins key is present
		if( $data['mp_restrict_logins'] == 'mp_restrict_logins_logged_in_check' ) {
			
			//get user ID
			$user_id = get_current_user_id();
			
			//Get transient to see if user is already logged in
			$user_timeout = get_transient( 'mp_restrict_logins_user_timeout_' . $user_id);
			
			$response['kicked_can'] = 'User ID is: ' . $user_id . ' and user timeout transient is: ' . $user_timeout . ' while current time is: ' . time() . ' so NOT KICKED!';
			
			//If the current time is greater than the users timeout, 
			//User either quit browser without logging out, or completely left the site
			//and needs to log in again
			if ( $user_timeout < time() ){
								
				//Log user out
				wp_logout();
				
			}
			//This user has never left after logging in.
			else{
			
				//Re Set "transient" to tell system that user is still logged in and 'holding' this account
				//Think of this as "Kicking the can down the road".	Like the US debt, it'll crash eventually, but not right now.	
				mp_restrict_logins_extend_session( $user_id );
				
				 // Send back the fact that we kicked the can
				$response['kicked_can'] = 'Tansient kicked from: ' . $user_timeout . ' to: ' . get_transient( 'mp_restrict_logins_user_timeout_' . $user_id) . ', Cookie kicked to: ' . $_COOKIE['mp_restrict_logins_user_session_timeout_' . $user_id] . 'for user: ' . $user_id;
				
			}
	 
		}
		
	}
		
    return $response;
}
add_filter( 'heartbeat_received', 'mp_restrict_logins_heartbeat_recieved', 10, 2 );

/**
 * Use this function to test using the console.
 *
 * @since    1.0.0
 * @link       http://moveplugins.com/doc/
 * @see      function_name()
 * @param  array $args See link for description.
 * @return   void
 */
function mp_restrict_logins_heartbeat_js() {
	?>
	<script>
	
	jQuery(document).ready( function($) {
		
		// Hook into the heartbeat-send
		$(document).on('heartbeat-send', function(e, data) {
			data['mp_restrict_logins'] = 'mp_restrict_logins_logged_in_check';
		});
	
		// Listen for the custom event "heartbeat-tick" on $(document). This fire's once every minute that the page is open.
		$(document).on( 'heartbeat-tick', function(e, data) {
					
			if ( !data['kicked_can'] )
				return;

			//console.log(data['kicked_can']);
				
		});
	});

	</script>
	<?php
}

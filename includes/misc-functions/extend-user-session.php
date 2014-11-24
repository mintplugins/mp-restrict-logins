<?php
/**
 * Functions used by the MP Restrict Logins Plugin
 *
 * @link http://mintplugins.com/doc/
 * @since 1.0.0
 *
 * @package    MP Restrict Logins
 * @subpackage functions
 *
 * @copyright   Copyright (c) 2014, Mint Plugins
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @author      Philip Johnston
 */
 
/**
 * Reset the timeout for the user and the cookie
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/
 * @see      function_name()
 * @param    array $args See link for description.
 * @return   void
 */
function mp_restrict_logins_extend_session( $user_id ){

	//If we're not doing ajax
	//if (!defined('DOING_AJAX')){	
		
		//Re Set "transient" to tell system that user is still logged in and 'holding' this account for another 31 seconds (make sure it's double your heartbeat tick).
		//Think of this as "Kicking the can down the road".	Like the US debt, it'll crash eventually, but not right now.	
		set_transient( 'mp_restrict_logins_user_timeout_' . $user_id, time() + 300, 24 * HOUR_IN_SECONDS );
		
		//Set "session" upon user sign in - expires 31 seconds from the current time.	
		$_SESSION['mp_restrict_logins_user_session_timeout_' . $user_id] = time() + 300;
		
		/* Let's keep this here for testing purposes when we need it 	*/		
		//echo 'Transient extended to ' . get_transient( 'mp_restrict_logins_user_timeout_' . $user_id) . ' for user ' . $user_id;
		//echo '<br />';
		//echo 'Session extended to ' . $_SESSION['mp_restrict_logins_user_session_timeout_' . $user_id];
		//*/				
	//}
						
	
}





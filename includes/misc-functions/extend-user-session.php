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
 * Reset the timeout for the user and the cookie
 *
 * @since    1.0.0
 * @link     http://moveplugins.com/doc/
 * @see      function_name()
 * @param    array $args See link for description.
 * @return   void
 */
function mp_restrict_logins_extend_session( $user_id ){
	
	//Re Set "transient" to tell system that user is still logged in and 'holding' this account for another 31 seconds (make sure it's double your heartbeat tick).
	//Think of this as "Kicking the can down the road".	Like the US debt, it'll crash eventually, but not right now.	
	set_transient( 'mp_restrict_logins_user_timeout_' . $user_id, time() + 31, 24 * HOUR_IN_SECONDS );
		
	//Set "cookie" upon user sign in - expires 20 seconds from the current time.		
	setcookie( 'mp_restrict_logins_user_session_timeout_' . $user_id, time() + 31, 2147483647, '/' ); 
	$_COOKIE['mp_restrict_logins_user_session_timeout_' . $user_id] =  time() + 31;
	
}





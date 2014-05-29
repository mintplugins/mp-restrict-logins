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
 * Show option to clear transient block for user on user's edit page
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/
 * @see      function_name()
 * @param    array $args See link for description.
 * @return   void
 */
function mp_restrict_logins_remove_block_button( $profileuser ){
	
	?>
    
    <h3><?php _e( 'Remove Login Block - MP Rescrict Logins Plugin', 'mp_restrict_logins' ) ?></h3>
    
    <a class="button" href="<?php echo add_query_arg( array( 'user_id' => $profileuser->ID, 'mp_restrict_logins_remove_block' => true ), admin_url( 'user-edit.php' ) ); ?>"><?php echo __( 'Remove all blocks for this user', 'mp_restrict_logins' ); ?></a>

    <?php
}
add_action( 'edit_user_profile', 'mp_restrict_logins_remove_block_button' );


/**
 * Clear user block transient 
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/
 * @see      function_name()
 * @param    array $args See link for description.
 * @return   void
 */
function mp_restrict_logins_remove_block(){
	
	//If we should remove this block and have permission to
	if ( isset( $_GET['mp_restrict_logins_remove_block'] ) && current_user_can( 'create_users' ) ){
		
		//Set the timeout for this use to the current time. They have stopped 'holding' control of this account.
    	delete_transient( 'user_timeout_' . get_current_user_id() );
		
		//Show notice that block is removed
		add_action( 'admin_notices', function(){ 
			 ?>
			<div class="updated">
				<p><?php _e( 'User can now log in.', 'mp_restrict_logins' ); ?></p>
			</div>
			<?php
		});
		
	}
	
}
add_action( 'init', 'mp_restrict_logins_remove_block' );





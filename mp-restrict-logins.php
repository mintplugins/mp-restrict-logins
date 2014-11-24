<?php
/*
Plugin Name: MP Restrict Logins
Plugin URI: http://mintplugins.com
Description: Keep "Subscriber" accounts from being used simultaneously. Perfect for subscription websites.
Version: 1.0.0.5
Author: Mint Plugins
Author URI: http://mintplugins.com
Text Domain: mp_restrict_logins
Domain Path: languages
License: GPL2
*/

/*  Copyright 2014  Phil Johnston  (email : phil@mintplugins.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Mint Plugins Core.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Mint Plugins Core, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
|--------------------------------------------------------------------------
| CONSTANTS
|--------------------------------------------------------------------------
*/
// Plugin version
if( !defined( 'MP_RESTRICT_LOGINS_VERSION' ) )
	define( 'MP_RESTRICT_LOGINS_VERSION', '1.0.0.5' );

// Plugin Folder URL
if( !defined( 'MP_RESTRICT_LOGINS_PLUGIN_URL' ) )
	define( 'MP_RESTRICT_LOGINS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Plugin Folder Path
if( !defined( 'MP_RESTRICT_LOGINS_PLUGIN_DIR' ) )
	define( 'MP_RESTRICT_LOGINS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Plugin Root File
if( !defined( 'MP_RESTRICT_LOGINS_PLUGIN_FILE' ) )
	define( 'MP_RESTRICT_LOGINS_PLUGIN_FILE', __FILE__ );

/*
|--------------------------------------------------------------------------
| GLOBALS
|--------------------------------------------------------------------------
*/



/*
|--------------------------------------------------------------------------
| INTERNATIONALIZATION
|--------------------------------------------------------------------------
*/

function mp_restrict_logins_textdomain() {

	// Set filter for plugin's languages directory
	$mp_restrict_logins_lang_dir = dirname( plugin_basename( MP_RESTRICT_LOGINS_PLUGIN_FILE ) ) . '/languages/';
	$mp_restrict_logins_lang_dir = apply_filters( 'mp_restrict_logins_languages_directory', $mp_restrict_logins_lang_dir );


	// Traditional WordPress plugin locale filter
	$locale        = apply_filters( 'plugin_locale',  get_locale(), 'mp-restrict-logins' );
	$mofile        = sprintf( '%1$s-%2$s.mo', 'mp-restrict-logins', $locale );

	// Setup paths to current locale file
	$mofile_local  = $mp_restrict_logins_lang_dir . $mofile;
	$mofile_global = WP_LANG_DIR . '/mp-restrict-logins/' . $mofile;

	if ( file_exists( $mofile_global ) ) {
		// Look in global /wp-content/languages/mp_restrict_logins folder
		load_textdomain( 'mp_restrict_logins', $mofile_global );
	} elseif ( file_exists( $mofile_local ) ) {
		// Look in local /wp-content/plugins/message_bar/languages/ folder
		load_textdomain( 'mp_restrict_logins', $mofile_local );
	} else {
		// Load the default language files
		load_plugin_textdomain( 'mp_restrict_logins', false, $mp_restrict_logins_lang_dir );
	}

}
add_action( 'init', 'mp_restrict_logins_textdomain', 1 );

/*
|--------------------------------------------------------------------------
| INCLUDES
|--------------------------------------------------------------------------
*/
function mp_restrict_logins_include_files(){
	/**
	 * If mp_core isn't active, stop and install it now
	 */
	if (!function_exists('mp_core_textdomain')){
		
		/**
		 * Include Plugin Checker
		 */
		require( MP_RESTRICT_LOGINS_PLUGIN_DIR . '/includes/plugin-checker/class-plugin-checker.php' );
		
		/**
		 * Include Plugin Installer
		 */
		require( MP_RESTRICT_LOGINS_PLUGIN_DIR . '/includes/plugin-checker/class-plugin-installer.php' );
			
		/**
		 * Check if wp_core in installed
		 */
		require( MP_RESTRICT_LOGINS_PLUGIN_DIR . 'includes/plugin-checker/included-plugins/mp-core-check.php' );
		
	}
	/**
	 * Otherwise, if mp_core is active, carry out the plugin's functions
	 */
	else{
		
		/**
		 * Update script - keeps this plugin up to date
		 */
		require( MP_RESTRICT_LOGINS_PLUGIN_DIR . 'includes/updater/mp-restrict-logins-update.php' );
				
		/**
		 * Misc Functions
		 */
		require( MP_RESTRICT_LOGINS_PLUGIN_DIR . 'includes/misc-functions/misc-functions.php' );
		
		/**
		 * Clear Settings
		 */
		require( MP_RESTRICT_LOGINS_PLUGIN_DIR . 'includes/misc-functions/clear-settings.php' );
		
		/**
		 * Extend User Session Function
		 */
		require( MP_RESTRICT_LOGINS_PLUGIN_DIR . 'includes/misc-functions/extend-user-session.php' );
					
	}
}
add_action('plugins_loaded', 'mp_restrict_logins_include_files', 9);
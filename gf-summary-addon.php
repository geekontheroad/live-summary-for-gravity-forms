<?php
/*
Plugin Name: Live Summary for Gravity Forms
Plugin URI: https://geekontheroad.com/gravity-forms-live-summary
Description: This free plugin helps you to easily add a live summary sidebar next to any gravity forms.  
Version: 1.2.10
Author: Geek on the Road
Author URI: https://geekontheroad.com
Text Domain: live-summary-for-gravity-forms
Domain Path: /languages


------------------------------------------------------------------------
Copyright 2020-2023 Geek on the Road OÜ.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/


define( 'GF_SUMMARY_ADDON_VERSION', '1.2.10' );
define('GOTRGF_PLUGIN_DIR', plugin_dir_url(__FILE__));
define('GOTRGF_PLUGIN_PATH', plugin_dir_path(__FILE__));

 
class GF_Summary_AddOn_Bootstrap {
 
    /**
     * Load plugin
     */
    public static function load() {
 
        if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
            return;
        }  
 
		//include our class
        require_once( 'class-gravitysummaryaddon.php' );
		
		//require our retrieve function for ajax calls
		require_once( 'retrieve-summary-fields.php' );
 
        GFAddOn::register( 'GFSummaryAddOn' );
    }

    // /**
    //  * Add link to pro landing page.     
    //  *
    //  * @since 1.1.4
    //  *
    //  * @param  array  $links List of existing plugin action links.
    //  * @return array         List of modified plugin action links.
    //  */
     public static function gotrgffree_plugin_action_links( $links ) {

        $links = array_merge( array(
             '<a href="' . esc_url( "https://geekontheroad.com/live-summary-for-gravity-forms" )  . '" style="font-weight:bold; color: red;" target="_blank">' . __( 'Buy Pro', 'live-summary-for-gravity-forms' ) . '</a>'
             //admin_url( '/options-general.php'
         ), $links );

         return $links;

     }
    
    
    }



add_action( 'gform_loaded', array( 'GF_Summary_AddOn_Bootstrap', 'load' ), 5 );

// //add a buy link on the plugins page if the pro version is not installed
 if(!class_exists("GOTRGF_PRO_LIVE_SUMMARY_ADDON")) {
     add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( 'GF_Summary_AddOn_Bootstrap', 'gotrgffree_plugin_action_links' ) );
 }
 
function gf_summary_addon() {
	//register new instance of class
    return GFSummaryAddOn::get_instance();
}
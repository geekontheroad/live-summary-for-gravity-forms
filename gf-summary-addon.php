<?php
/**
 * Plugin Name: Live Summary for Gravity Forms
 * Plugin URI: https://geekontheroad.com/gravity-forms-live-summary
 * Description: This free plugin helps you to easily add a live summary sidebar next to any gravity forms.
 * Version: 1.2.11
 * Author: Geek on the Road
 * Author URI: https://geekontheroad.com
 * Text Domain: live-summary-for-gravity-forms
 * Domain Path: /languages
 *
 * Copyright 2020-2026 Geek on the Road OÜ.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package live-summary-for-gravity-forms
 */

defined( 'ABSPATH' ) || exit;

define( 'GF_SUMMARY_ADDON_VERSION', '1.2.11' );
define( 'GOTRGF_PLUGIN_DIR', plugin_dir_url( __FILE__ ) );
define( 'GOTRGF_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Loads the add-on and handles the plugin-level admin UI.
 */
class GF_Summary_AddOn_Bootstrap {

	/**
	 * Option flag set on activation to show the welcome notice.
	 */
	const WELCOME_OPTION = 'gotrgf_show_welcome_notice';

	/**
	 * Option with the timestamp after which the review request is shown.
	 *
	 * @since 1.2.11
	 */
	const REVIEW_OPTION = 'gotrgf_review_after';

	/**
	 * Load plugin
	 */
	public static function load() {

		if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
			return;
		}

		require_once GOTRGF_PLUGIN_PATH . 'class-gravitysummaryaddon.php';
		require_once GOTRGF_PLUGIN_PATH . 'retrieve-summary-fields.php';

		GFAddOn::register( 'GFSummaryAddOn' );
	}

	/**
	 * URL of the Pro landing page, tagged with the placement the link was clicked from.
	 *
	 * @since 1.2.11
	 *
	 * @param string $medium The placement, used as utm_medium.
	 *
	 * @return string
	 */
	public static function pro_url( $medium ) {
		return add_query_arg(
			array(
				'utm_source'   => 'free-plugin',
				'utm_medium'   => $medium,
				'utm_campaign' => 'upgrade',
			),
			'https://geekontheroad.com/live-summary-for-gravity-forms/'
		);
	}

	/**
	 * Add "Get started" and "Upgrade to Pro" links to the plugins list.
	 *
	 * @since 1.1.4
	 *
	 * @param  array $links List of existing plugin action links.
	 * @return array         List of modified plugin action links.
	 */
	public static function gotrgffree_plugin_action_links( $links ) {
		return array_merge(
			array(
				'<a href="' . esc_url( admin_url( 'admin.php?page=gf_edit_forms' ) ) . '">' . esc_html__( 'Get started', 'live-summary-for-gravity-forms' ) . '</a>',
				'<a href="' . esc_url( self::pro_url( 'plugins-row' ) ) . '" target="_blank" rel="noopener" style="color: #d63638; font-weight: 700;">' . esc_html__( 'Upgrade to Pro', 'live-summary-for-gravity-forms' ) . '</a>',
			),
			$links
		);
	}

	/**
	 * Flag the getting started message on activation.
	 *
	 * @since 1.2.11
	 */
	public static function activate() {
		update_option( self::WELCOME_OPTION, 1 );
	}

	/**
	 * Remove the welcome message flag and the review request date on deactivation.
	 *
	 * @since 1.2.11
	 */
	public static function deactivate() {
		delete_option( self::WELCOME_OPTION );
		delete_option( self::REVIEW_OPTION );
	}
}

add_action( 'gform_loaded', array( 'GF_Summary_AddOn_Bootstrap', 'load' ), 5 );
add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( 'GF_Summary_AddOn_Bootstrap', 'gotrgffree_plugin_action_links' ) );
register_activation_hook( __FILE__, array( 'GF_Summary_AddOn_Bootstrap', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'GF_Summary_AddOn_Bootstrap', 'deactivate' ) );

/**
 * Get the add-on instance.
 *
 * @return GFSummaryAddOn
 */
function gf_summary_addon() {
	return GFSummaryAddOn::get_instance();
}

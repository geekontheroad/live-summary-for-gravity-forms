<?php
/**
 * Live Summary add-on class.
 *
 * @package live-summary-for-gravity-forms
 */

defined( 'ABSPATH' ) || exit;

GFForms::include_addon_framework();

/**
 * Live Summary add-on, built on the Gravity Forms Add-On Framework.
 */
class GFSummaryAddOn extends GFAddOn {

	/**
	 * Coupon code for Live Summary Pro, shown in the Pro panel of the form settings.
	 *
	 * @since 1.2.11
	 */
	const PRO_COUPON = 'LIVESUMMARY20';

	// phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore -- Property names are defined by GFAddOn.

	/**
	 * Add-on version.
	 *
	 * @var string
	 */
	protected $_version = GF_SUMMARY_ADDON_VERSION;

	/**
	 * Minimum Gravity Forms version.
	 *
	 * @var string
	 */
	protected $_min_gravityforms_version = '2.5';

	/**
	 * Add-on slug. Shared with the Pro version so form settings carry over.
	 *
	 * @var string
	 */
	protected $_slug = 'gravitysummary';

	/**
	 * Relative path to the main plugin file.
	 *
	 * @var string
	 */
	protected $_path = 'gravitysummary/gf-summary-addon.php';

	/**
	 * Full path to this file.
	 *
	 * @var string
	 */
	protected $_full_path = __FILE__;

	/**
	 * Add-on title.
	 *
	 * @var string
	 */
	protected $_title = 'Gravity Forms Live Summary';

	/**
	 * Add-on short title.
	 *
	 * @var string
	 */
	protected $_short_title = 'Live Summary';

	/**
	 * Single instance of this class.
	 *
	 * @var GFSummaryAddOn|null
	 */
	private static $_instance = null;

	/**
	 * Defines the capability needed to access the Add-On form settings page.
	 *
	 * @since  1.2.2
	 * @var    string $_capabilities_form_settings The capability needed to access the Add-On form settings page.
	 */
	protected $_capabilities_form_settings = 'gravityforms_edit_forms';

	/**
	 * Defines the capability needed to access the Add-On settings page.
	 *
	 * @since  1.2.2
	 * @var    string $_capabilities_settings_page The capability needed to access the Add-On settings page.
	 */
	protected $_capabilities_settings_page = 'gravityforms_edit_settings';

	// phpcs:enable PSR2.Classes.PropertyDeclaration.Underscore

	/**
	 * Get an instance of this class.
	 *
	 * @return GFSummaryAddOn
	 */
	public static function get_instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new GFSummaryAddOn();
		}

		return self::$_instance;
	}

	/**
	 * Runs when the add-on version changes.
	 *
	 * @since 1.2.11
	 *
	 * @param string $previous_version The previously installed version.
	 */
	public function upgrade( $previous_version ) {
		// The v1.1 settings migration has been removed; clean up its flag.
		delete_option( 'gotrgf_succesfull_migration_complete' );

		// Existing sites that already use the summary get the review request a week after updating.
		if ( ! empty( $previous_version ) && ! get_option( GF_Summary_AddOn_Bootstrap::REVIEW_OPTION ) ) {
			foreach ( GFAPI::get_forms() as $form ) {
				if ( gotrgf_retrieve_summary_fields::is_summary_enabled( $form ) ) {
					update_option( GF_Summary_AddOn_Bootstrap::REVIEW_OPTION, time() + 7 * DAY_IN_SECONDS );
					break;
				}
			}
		}
	}

	/**
	 * Show a getting started message on the Gravity Forms screens after activation.
	 * Uses the Gravity Forms dismissible messages, so each user can dismiss it and it only shows on Gravity Forms pages.
	 *
	 * @since 1.2.11
	 */
	private function maybe_add_welcome_message() {
		if ( ! get_option( GF_Summary_AddOn_Bootstrap::WELCOME_OPTION ) || ! GFForms::is_gravity_page() ) {
			return;
		}

		$text = sprintf(
			'<strong>%1$s</strong> %2$s <a href="%3$s" target="_blank" rel="noopener">%4$s</a>',
			esc_html__( 'Live Summary is ready.', 'live-summary-for-gravity-forms' ),
			esc_html__( 'To add a summary to a form, open the form\'s Settings → Live Summary, turn on the summary and tick "Show in summary" on the fields you want.', 'live-summary-for-gravity-forms' ),
			esc_url( GF_Summary_AddOn_Bootstrap::pro_url( 'welcome-notice' ) ),
			esc_html__( 'Need more? See Pro', 'live-summary-for-gravity-forms' )
		);

		GFCommon::add_dismissible_message( $text, 'gotrgf_welcome', 'success', 'gravityforms_edit_forms' );
	}

	/**
	 * Ask for a review on WordPress.org once the summary has been in use for a while.
	 * Asked once: each user can dismiss it, and it only shows on Gravity Forms pages.
	 *
	 * @since 1.2.11
	 */
	private function maybe_add_review_message() {
		$review_after = (int) get_option( GF_Summary_AddOn_Bootstrap::REVIEW_OPTION );

		if ( ! $review_after || time() < $review_after || ! GFForms::is_gravity_page() ) {
			return;
		}

		$text = sprintf(
			'<strong>%1$s</strong> %2$s <a href="%3$s" target="_blank" rel="noopener">%4$s</a> · %5$s <a href="%6$s" target="_blank" rel="noopener">%7$s</a>',
			esc_html__( 'Enjoying Live Summary?', 'live-summary-for-gravity-forms' ),
			esc_html__( 'A quick review on WordPress.org helps other Gravity Forms users find it.', 'live-summary-for-gravity-forms' ),
			esc_url( 'https://wordpress.org/support/plugin/live-summary-for-gravity-forms/reviews/#new-post' ),
			esc_html__( 'Leave a review', 'live-summary-for-gravity-forms' ),
			esc_html__( 'Something not working?', 'live-summary-for-gravity-forms' ),
			esc_url( 'https://wordpress.org/support/plugin/live-summary-for-gravity-forms/' ),
			esc_html__( 'Get support', 'live-summary-for-gravity-forms' )
		);

		GFCommon::add_dismissible_message( $text, 'gotrgf_review', 'success', 'gravityforms_edit_forms' );
	}

	/**
	 * Save the form settings. Once a form uses the summary, stop the getting started message and plan the review request.
	 *
	 * @since 1.2.11
	 *
	 * @param array $form     The form object.
	 * @param array $settings The settings to save.
	 *
	 * @return bool
	 */
	public function save_form_settings( $form, $settings ) {
		$result = parent::save_form_settings( $form, $settings );

		if ( $result && ! empty( $settings['show_summary'] ) ) {
			delete_option( GF_Summary_AddOn_Bootstrap::WELCOME_OPTION );

			if ( ! get_option( GF_Summary_AddOn_Bootstrap::REVIEW_OPTION ) ) {
				update_option( GF_Summary_AddOn_Bootstrap::REVIEW_OPTION, time() + 14 * DAY_IN_SECONDS );
			}
		}

		return $result;
	}

	/**
	 * Backend stylesheet for the form editor and the Live Summary form settings.
	 * Enqueued through the Add-On Framework so it also loads when no-conflict mode is on.
	 *
	 * @since 1.2.11
	 *
	 * @return array
	 */
	public function styles() {
		$styles = array(
			array(
				'handle'  => 'gotrgf_summary_backend_styles',
				'src'     => GOTRGF_PLUGIN_DIR . 'css/gotrgf-summary-backend-styles.css',
				'version' => $this->_version,
				'enqueue' => array(
					array( 'admin_page' => array( 'form_editor', 'form_settings' ) ),
				),
			),
		);

		return array_merge( parent::styles(), $styles );
	}

	/**
	 * Register hooks used in the frontend, backend and during AJAX requests.
	 */
	public function init() {
		parent::init();

		/**
		 * Hide the summary when the confirmation page loads
		 *
		 * @since v1.1
		 *
		 * @param string|array $confirmation The confirmation message or redirect.
		 * @param array        $form         The current form.
		 */
		function gotrgf_remove_summary_from_confirmation_message( $confirmation, $form ) {
			if ( ! is_string( $confirmation ) ) {
				return $confirmation;
			}

			/**
			 * Add filter so this script can be dynamically removed if needed
			 *
			 * @since v1.1
			 */
			$remove = apply_filters( 'gotrgf_add_scripts_to_confirmation', true, $form );

			if ( $remove ) {
				$confirmation .= GFCommon::get_inline_script_tag( "window.top.jQuery(document).on('gform_confirmation_loaded', function () { jQuery('#gotrgf_form_container_" . absint( rgar( $form, 'id' ) ) . " .gotrgf_summary_wrapper').remove(); } );" );
			}

			return $confirmation;
		}
		add_filter( 'gform_confirmation', 'gotrgf_remove_summary_from_confirmation_message', 10, 2 );
	}

	/**
	 * Register hooks used in the backend.
	 */
	public function init_admin() {
		parent::init_admin();

		$this->maybe_add_welcome_message();
		$this->maybe_add_review_message();

		/**
		 * Load styles on the block editor to ensure the summary display correctly there
		 *
		 * @since v1.2.6
		 */
		function gotr_enqueue_plugin_preview_styles() {
			wp_enqueue_style( 'gravity_summary_css', GOTRGF_PLUGIN_DIR . 'css/gravity-summary-all.css', array(), GF_SUMMARY_ADDON_VERSION, 'all' );
		}
		add_action( 'enqueue_block_editor_assets', 'gotr_enqueue_plugin_preview_styles' );

		/**
		 * Add field setting
		 *
		 * @param int $position The position of the settings block.
		 * @param int $form_id  The current form ID.
		 */
		function gotr_live_summary_settings( $position, $form_id ) {

			// Create settings on position 25 (right after Field Label).
			if ( 25 === (int) $position ) {
				?>
				<li class="gotr_live_summary_setting field_setting">
					<input type="checkbox" id="field_summary_setting" onclick="SetFieldProperty('liveSummaryField', this.checked);" />
					<label for="field_summary_setting" style="display:inline;">
						<?php esc_html_e( 'Show in summary', 'live-summary-for-gravity-forms' ); ?>
						<?php gform_tooltip( 'form_field_summary_setting' ); ?>
					</label>
					<p class="gotrgf_pro_hint">
						<?php esc_html_e( 'Tired of ticking fields one by one?', 'live-summary-for-gravity-forms' ); ?>
						<a href="<?php echo esc_url( GF_Summary_AddOn_Bootstrap::pro_url( 'field-editor' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Pro lets you pick and reorder all fields on one screen.', 'live-summary-for-gravity-forms' ); ?></a>
					</p>
				</li>
				<li class="gotr_live_summary_pro_setting field_setting">
					<input type="checkbox" id="field_summary_pro_setting" disabled />
					<label for="field_summary_pro_setting" style="display:inline;">
						<?php esc_html_e( 'Show in summary', 'live-summary-for-gravity-forms' ); ?>
					</label>
					<a class="gotrgf_pro_badge" href="<?php echo esc_url( GF_Summary_AddOn_Bootstrap::pro_url( 'field-type' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Pro', 'live-summary-for-gravity-forms' ); ?></a>
					<p class="gotrgf_pro_hint"><?php esc_html_e( 'Live Summary Pro can show this field type in the summary.', 'live-summary-for-gravity-forms' ); ?></p>
				</li>
				<?php
				/**
				 * Action to add more field settings
				 *
				 * @since 1.2.0
				 */
				do_action( 'gotrgf_live_summary_field_settings', $form_id );
			}
		}
		add_action( 'gform_field_standard_settings', 'gotr_live_summary_settings', 10, 2 );

		/**
		 * Inject the supporting script into the form editor page.
		 */
		function gotrgf_editor_script() {
			?>
			<script type='text/javascript'>
				//adding setting to our supported fields, and the locked Pro setting to the field types only Pro supports
				function gotrgf_add_field_setting(types, setting) {
					types.forEach(function(type) {
						if (typeof fieldSettings[type] === 'string') {
							fieldSettings[type] += ', ' + setting;
						}
					});
				}
				gotrgf_add_field_setting(<?php echo wp_json_encode( gotrgf_retrieve_summary_fields::FIELD_TYPES ); ?>, '.gotr_live_summary_setting');
				gotrgf_add_field_setting(<?php echo wp_json_encode( gotrgf_retrieve_summary_fields::get_pro_field_types() ); ?>, '.gotr_live_summary_pro_setting');

				//binding to the load field settings event to initialize the checkbox
				jQuery(document).on('gform_load_field_settings', function(event, field, form){
					jQuery( '#field_summary_setting' ).prop( 'checked', Boolean( rgar( field, 'liveSummaryField' ) ) );
				});
			</script>
			<?php
		}
		add_action( 'gform_editor_js', 'gotrgf_editor_script' );

		/**
		 * Add the tooltip for the field setting.
		 *
		 * @param array $tooltips The registered tooltips.
		 *
		 * @return array
		 */
		function gotrgf_add_field_tooltips( $tooltips ) {
			$title                                  = esc_html__( 'Live summary', 'live-summary-for-gravity-forms' );
			$description                            = esc_html__( 'Check this box to include this field in the live summary', 'live-summary-for-gravity-forms' );
			$tooltips['form_field_summary_setting'] = sprintf( '<h6>%s</h6> <p>%s</p>', $title, $description );

			/**
			 * New filter to add more tooltips for the field settings
			 *
			 * @since v1.2.0
			 */
			return apply_filters( 'gotrgf_add_field_tooltips', $tooltips );
		}
		add_filter( 'gform_tooltips', 'gotrgf_add_field_tooltips' );
	}

	/**
	 * Return the SVG icon URL for the settings pages.
	 *
	 * @since 1.1.5
	 *
	 * @return string
	 */
	public function get_menu_icon() {
		return GOTRGF_PLUGIN_DIR . 'images/settings-icon.svg';
	}

	/**
	 * All fields registered here go into the Live Summary form settings tab.
	 *
	 * @param array $form The current form.
	 *
	 * @return array Array of settings sections for the form.
	 */
	public function form_settings_fields( $form ) {

		$general_fields = array(
			array(
				'name'         => 'show_summary',
				'type'         => 'toggle',
				'description'  => esc_html__( 'Show a live summary next to the form.', 'live-summary-for-gravity-forms' ),
				'toggle_label' => esc_html__( 'Turn on summary', 'live-summary-for-gravity-forms' ),
				'label'        => esc_html__( 'Turn on summary', 'live-summary-for-gravity-forms' ),
			),
		);

		// Next step, shown directly under the toggle as soon as the summary is switched on.
		if ( ! gotrgf_retrieve_summary_fields::has_selected_fields( $form ) ) {
			$general_fields[] = array(
				'name'       => 'gotrgf_no_fields_notice',
				'type'       => 'html',
				'html'       => $this->get_no_fields_notice_html( $form ),
				'dependency' => array(
					'live'   => true,
					'fields' => array(
						array( 'field' => 'show_summary' ),
					),
				),
			);
		}

		if ( gotrgf_retrieve_summary_fields::product_fields_found( $form ) ) {
			$total_field = array(
				'name'         => 'show_total',
				'type'         => 'toggle',
				'description'  => esc_html__( 'Show the order total under the summary.', 'live-summary-for-gravity-forms' ),
				'toggle_label' => esc_html__( 'Show total in summary', 'live-summary-for-gravity-forms' ),
				'label'        => esc_html__( 'Show total in summary', 'live-summary-for-gravity-forms' ),
			);
		} else {
			$total_field = array(
				'name' => 'gotrgf_total_unavailable',
				'type' => 'html',
				'html' => sprintf(
					'<p class="gotrgf_total_unavailable">%s</p>',
					esc_html__( 'Add a Product field to the form to show a total.', 'live-summary-for-gravity-forms' )
				),
			);
		}

		$fields = array(
			array(
				'title'  => esc_html__( 'General Settings', 'live-summary-for-gravity-forms' ),
				'class'  => 'gform-settings-panel--half gotrgf_general_settings',
				'fields' => $general_fields,
			),
			array(
				'title'  => esc_html__( 'Total Settings', 'live-summary-for-gravity-forms' ),
				'class'  => 'gform-settings-panel--half gotrgf_total_settings',
				'fields' => array( $total_field ),
			),
			array(
				'title'  => esc_html__( 'More options', 'live-summary-for-gravity-forms' ),
				'class'  => 'gform-settings-panel--half gotrgf_locked_settings',
				'fields' => array(
					array(
						'name' => 'gotrgf_locked_settings',
						'type' => 'html',
						'html' => $this->get_locked_settings_html(),
					),
				),
			),
			array(
				'title'  => esc_html__( 'Live Summary Pro', 'live-summary-for-gravity-forms' ),
				'class'  => 'gform-settings-panel--half gotrgf_pro_panel',
				'fields' => array(
					array(
						'name' => 'gotrgf_pro_banner',
						'type' => 'html',
						'html' => $this->get_pro_panel_html(),
					),
				),
			),
		);

		return apply_filters( 'gotrgf_live_summary_form_settings_fields', $fields, $form );
	}

	/**
	 * Warning shown on the form settings page when no fields are selected for the summary.
	 *
	 * @since 1.2.11
	 *
	 * @param array $form The current form.
	 *
	 * @return string
	 */
	private function get_no_fields_notice_html( $form ) {
		$editor_url = admin_url( 'admin.php?page=gf_edit_forms&id=' . absint( rgar( $form, 'id' ) ) );

		return sprintf(
			'<div class="alert warning" role="alert"><p><strong>%1$s</strong></p><p>%2$s</p><p><a class="button primary" href="%3$s">%4$s</a></p></div>',
			esc_html__( 'No fields are selected for the summary yet.', 'live-summary-for-gravity-forms' ),
			esc_html__( 'Edit the form, click a field and tick "Show in summary" in its General settings. Repeat this for every field you want in the summary.', 'live-summary-for-gravity-forms' ),
			esc_url( $editor_url ),
			esc_html__( 'Open the form editor', 'live-summary-for-gravity-forms' )
		);
	}

	/**
	 * Disabled Pro-only settings, so users see what is available while configuring.
	 * These controls have no name attribute and are never saved.
	 *
	 * @since 1.2.11
	 *
	 * @return string
	 */
	private function get_locked_settings_html() {
		$settings = array(
			array(
				'label'       => __( 'Summary position', 'live-summary-for-gravity-forms' ),
				'value'       => __( 'Right of the form', 'live-summary-for-gravity-forms' ),
				'description' => __( 'Show the summary left or right of the form.', 'live-summary-for-gravity-forms' ),
			),
			array(
				'label'       => __( 'Sticky summary', 'live-summary-for-gravity-forms' ),
				'value'       => __( 'Off', 'live-summary-for-gravity-forms' ),
				'description' => __( 'Keep the summary in view while the user scrolls.', 'live-summary-for-gravity-forms' ),
			),
			array(
				'label'       => __( 'Mobile summary', 'live-summary-for-gravity-forms' ),
				'value'       => __( 'Below the form', 'live-summary-for-gravity-forms' ),
				'description' => __( 'A summary bar with a tap-to-open sheet on mobile.', 'live-summary-for-gravity-forms' ),
			),
			array(
				'label'       => __( 'Style preset', 'live-summary-for-gravity-forms' ),
				'value'       => __( 'Original', 'live-summary-for-gravity-forms' ),
				'description' => __( 'Modern, Minimal, Receipt and Dark presets, or design your own.', 'live-summary-for-gravity-forms' ),
			),
		);

		$pro_url = GF_Summary_AddOn_Bootstrap::pro_url( 'locked-setting' );
		$html    = '';

		foreach ( $settings as $setting ) {
			$html .= sprintf(
				'<div class="gotrgf_locked_setting"><div class="gotrgf_locked_setting__label">%1$s <a class="gotrgf_pro_badge" href="%2$s" target="_blank" rel="noopener">%3$s</a></div><select disabled aria-label="%1$s"><option>%4$s</option></select><p class="gotrgf_locked_setting__description">%5$s</p></div>',
				esc_html( $setting['label'] ),
				esc_url( $pro_url ),
				esc_html__( 'Pro', 'live-summary-for-gravity-forms' ),
				esc_html( $setting['value'] ),
				esc_html( $setting['description'] )
			);
		}

		// Pro has 60+ form settings: update this when Pro's settings change a lot.
		$html .= sprintf(
			'<p class="gotrgf_locked_more"><a href="%1$s" target="_blank" rel="noopener">%2$s</a></p>',
			esc_url( GF_Summary_AddOn_Bootstrap::pro_url( 'locked-settings-more' ) ),
			esc_html__( '+ 60 more settings in Live Summary Pro →', 'live-summary-for-gravity-forms' )
		);

		return $html;
	}

	/**
	 * Panel listing the main Pro features.
	 *
	 * @since 1.2.11
	 *
	 * @return string
	 */
	private function get_pro_panel_html() {
		$features = array(
			__( 'Drag-and-drop field picker: choose and reorder all fields on one screen', 'live-summary-for-gravity-forms' ),
			__( 'The Live Summary field: add a submission preview anywhere in the form, such as right before the submit button', 'live-summary-for-gravity-forms' ),
			__( 'Style presets and full design control, no CSS needed', 'live-summary-for-gravity-forms' ),
			__( 'Mobile summary bar and sticky summary', 'live-summary-for-gravity-forms' ),
			__( 'Product table with subtotal, tax, shipping and coupons', 'live-summary-for-gravity-forms' ),
			__( 'The {live_summary} merge tag for confirmations and notification emails', 'live-summary-for-gravity-forms' ),
		);

		$items = '';
		foreach ( $features as $feature ) {
			$items .= '<li>' . esc_html( $feature ) . '</li>';
		}

		$demo_url = add_query_arg(
			array(
				'utm_source'   => 'free-plugin',
				'utm_medium'   => 'settings-panel',
				'utm_campaign' => 'demo',
			),
			'https://gravitysummary.geekontheroad.com/'
		);

		// The discount parameter applies the coupon automatically at the Easy Digital Downloads checkout.
		$pro_url = add_query_arg( 'discount', self::PRO_COUPON, GF_Summary_AddOn_Bootstrap::pro_url( 'settings-panel' ) );

		return sprintf(
			'<p class="gotrgf_pro_intro">%1$s</p><p class="gotrgf_pro_description">%9$s</p><ul class="gotrgf_pro_features">%2$s</ul><div class="gotrgf_pro_coupon"><span>%3$s</span> <code>%4$s</code></div><p class="gotrgf_pro_actions"><a class="gotrgf_pro_button" href="%5$s" target="_blank" rel="noopener">%6$s</a> <a class="gotrgf_pro_demo" href="%7$s" target="_blank" rel="noopener">%8$s</a></p>',
			esc_html__( 'Full control over your live summary.', 'live-summary-for-gravity-forms' ),
			$items,
			esc_html__( 'Save 20% with code', 'live-summary-for-gravity-forms' ),
			esc_html( self::PRO_COUPON ),
			esc_url( $pro_url ),
			esc_html__( 'Get Pro with 20% off', 'live-summary-for-gravity-forms' ),
			esc_url( $demo_url ),
			esc_html__( 'Try the demo', 'live-summary-for-gravity-forms' ),
			esc_html__( 'Match your brand, place summaries anywhere and give mobile visitors a summary bar, without writing CSS.', 'live-summary-for-gravity-forms' )
		);
	}

	/**
	 * Register hooks used in the frontend.
	 */
	public function init_frontend() {
		parent::init_frontend();

		/**
		 * Maybe enqueue all our frontend scripts and styles.
		 *
		 * @param array $form The current form.
		 */
		function maybe_enqueue_custom_frontend_scripts_and_styles( $form ) {
			$display_summary = gotrgf_retrieve_summary_fields::should_display_summary( $form );

			$styles = array();

			if ( $display_summary ) {
				$styles[] = array(
					'handle'  => 'gravity_summary_css',
					'src'     => GOTRGF_PLUGIN_DIR . 'css/gravity-summary-all.css',
					'deps'    => array(),
					'version' => GF_SUMMARY_ADDON_VERSION,
					'media'   => 'all',
				);
			}

			/**
			 * Filter the styles that are being loaded in the frontend
			 *
			 * @param Array $styles same as wp_enqueue_style
			 * @since v1.1
			 */
			$styles = apply_filters( 'gotrgf_custom_styles_frontend', $styles, $form );

			foreach ( (array) $styles as $style ) {
				$handle = isset( $style['handle'] ) ? $style['handle'] : '';
				$src    = isset( $style['src'] ) ? $style['src'] : '';
				$deps   = isset( $style['deps'] ) ? $style['deps'] : array();
				$ver    = isset( $style['version'] ) ? $style['version'] : null;
				$media  = isset( $style['media'] ) ? $style['media'] : 'all';
				wp_enqueue_style( $handle, $src, $deps, $ver, $media );
			}

			$scripts = array();

			if ( $display_summary ) {
				$scripts[] = array(
					'handle'    => 'summary_change_js',
					'src'       => GOTRGF_PLUGIN_DIR . 'js/summary-change.js',
					'deps'      => array( 'jquery' ),
					'version'   => GF_SUMMARY_ADDON_VERSION,
					'in_footer' => true,
				);

				$scripts[] = array(
					'handle'    => 'fields_js',
					'src'       => GOTRGF_PLUGIN_DIR . 'js/fields.js',
					'deps'      => array( 'jquery' ),
					'version'   => GF_SUMMARY_ADDON_VERSION,
					'in_footer' => true,
				);
			}

			/**
			 * Filter the scripts that are being loaded in the frontend
			 *
			 * @param Array Array of $scripts[] same as wp_enqueue_script
			 * @since v1.1
			 */
			$scripts = apply_filters( 'gotrgf_custom_scripts_frontend', $scripts, $form );

			foreach ( (array) $scripts as $script ) {
				wp_enqueue_script( $script['handle'], $script['src'], $script['deps'], $script['version'], $script['in_footer'] );
			}
		}
		add_action( 'gform_enqueue_scripts', 'maybe_enqueue_custom_frontend_scripts_and_styles', 10, 1 );

		/**
		 * Print the summary data and init script for this form.
		 * The field list is rendered server-side so no AJAX request is needed.
		 *
		 * @since v1.2.4
		 *
		 * @param array $form The current form.
		 */
		function gotrgf_maybe_load_summary_jquery( $form ) {
			if ( ! gotrgf_retrieve_summary_fields::is_summary_enabled( $form ) ) {
				return;
			}

			$fields = gotrgf_retrieve_summary_fields::get_summary_fields( $form );
			if ( empty( $fields ) ) {
				// The admin-only notice is rendered server-side, nothing to initialise.
				return;
			}

			$form_id = absint( $form['id'] );
			$data    = array(
				'fields'          => $fields,
				'nothingSelected' => __( 'Nothing selected yet', 'live-summary-for-gravity-forms' ),
			);

			wp_add_inline_script( 'summary_change_js', 'window.gotrgf_summary_data = window.gotrgf_summary_data || {}; window.gotrgf_summary_data[' . $form_id . '] = ' . wp_json_encode( $data ) . ';', 'before' );
			wp_add_inline_script( 'summary_change_js', 'jQuery(function () { gotrgf_gravity_summary_update(' . $form_id . '); });', 'after' );
		}
		add_action( 'gform_register_init_scripts', 'gotrgf_maybe_load_summary_jquery' );

		/**
		 * Modify the form output
		 * This is where the frontend magic happens
		 */
		add_filter(
			'gform_get_form_filter',
			function ( $form_string, $form ) {

				if ( ! gotrgf_retrieve_summary_fields::should_display_summary( $form ) ) {
					return $form_string;
				}

				$form_id    = absint( $form['id'] );
				$has_fields = gotrgf_retrieve_summary_fields::has_selected_fields( $form );

				// Include the total if the form has a product field and the setting is switched on.
				if ( gotrgf_retrieve_summary_fields::product_fields_found( $form ) && ! empty( $form['gravitysummary']['show_total'] ) ) {
					/**
					 * Add filter to change the total label
					 *
					 * @param Str $total_label
					 * @since v1.1
					 */
					$total_label = apply_filters( 'gotrgf_modify_total_label', __( 'Total', 'live-summary-for-gravity-forms' ), $form );

					/**
					 * Add a filter to allow output inside the total container at the beginning
					 *
					 * @since v1.1.10
					 */
					$before_total = apply_filters( 'gotrgf_total_container_start', '', $form );

					/**
					 * Add a filter to allow output inside the total container at the end
					 *
					 * @since v1.1.10
					 */
					$after_total = apply_filters( 'gotrgf_total_container_end', '', $form );

					$total_string = sprintf(
						"<div class='gotrgf_summary_total'> %s<div class='gotrgf_label'>%s</div> <div class='gotrgf_total_right gotrgf_price_amount'></div>%s</div>",
						$before_total,
						esc_html( $total_label ),
						$after_total
					);
				} else {
					$total_string = '';
				}

				/**
				 * Add filter to modify the summary title
				 *
				 * @param Str $summary_title
				 * @since v1.1
				 */
				$summary_title = apply_filters( 'gotrgf_modify_main_summary_title', __( 'Summary', 'live-summary-for-gravity-forms' ), $form );

				/**
				 * Add filter to remove title
				 *
				 * @param Bool $enable_title
				 * @param Array $form
				 * @since v1.1
				 */
				$enable_title = apply_filters( 'gotrgf_enable_main_summary_title', 1, $form );

				/**
				 * Add filter to change the side of the summary
				 * options are left or right as a string
				 *
				 * @param Str left or right
				 * @since v1.1
				 */
				$summary_side = apply_filters( 'gotrgf_specify_summary_side', 'right', $form );

				ob_start();

				/**
				 * Action hook just before the main container opens
				 * Example: Use to output form specific styling from the settings
				 */
				do_action( 'gotrgf_before_form_container', $form );

				// $form_string is the form markup generated by Gravity Forms and must not be escaped.
				// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
				if ( 'left' === $summary_side ) {
					?>
				<div id="gotrgf_form_container_<?php echo esc_attr( $form_id ); ?>" class='gotrgf_form_container'>
					<?php
				} else {
					?>
				<div id="gotrgf_form_container_<?php echo esc_attr( $form_id ); ?>" class='gotrgf_form_container'><div class='gotrgf_form_wrapper'>
					<?php
					echo $form_string;
					echo '</div>';
				}
				?>

			<div class='gotrgf_summary_wrapper'>

				<?php
				/**
				 * Add action hook to add html before the main summary container
				 *
				 * @since v1.1
				 */
				do_action( 'gotrgf_before_form_overview_container', $form );
				?>

			<div id="gotrgf_form_overview_container_<?php echo esc_attr( $form_id ); ?>" class='gotrgf_form_overview_container'>
				<?php
					/**
					 * Add action hook to add html before title div
					 *
					 * @since v1.1
					 */
					do_action( 'gotrgf_before_summary_title', $form );

				if ( 1 === (int) $enable_title ) {
					?>
				<div class='gotrgf_summary_title'>
					<h5><?php echo esc_html( $summary_title ); ?></h5>
				</div>
					<?php
				}

				/**
				 * Add action hook to add html before the summary lines div
				 *
				 * @since v1.1
				 */
				do_action( 'gotrgf_before_summary_lines', $form );
				?>
				<div class='gotrgf_summary_lines'>
					<?php
					if ( ! $has_fields ) {
						// should_display_summary() only lets this through for users who can edit the form.
						printf(
							'<div class="gotrgf_admin_notice"><p><strong>%1$s</strong></p><p><a href="%2$s">%3$s</a></p><p class="gotrgf_admin_notice__small">%4$s</p></div>',
							esc_html__( 'Live Summary is on, but no fields are selected yet.', 'live-summary-for-gravity-forms' ),
							esc_url( admin_url( 'admin.php?page=gf_edit_forms&id=' . $form_id ) ),
							esc_html__( 'Select fields in the form editor', 'live-summary-for-gravity-forms' ),
							esc_html__( 'Only form administrators see this message.', 'live-summary-for-gravity-forms' )
						);
					}
					?>
				</div>
					<?php
					/**
					 * Add action hook to add html after the summary lines div
					 *
					 * @since v1.1
					 */
					do_action( 'gotrgf_after_summary_lines', $form );

					// Output the summary total area. The label is escaped, the rest is plugin markup and filter output.
					echo $total_string;

					/**
					 * Add action hook to add html after the total
					 *
					 * @since v1.1
					 */
					do_action( 'gotrgf_after_summary_total', $form );

					$preloader_url = apply_filters( 'gotrgf_change_preloader_image_url', GFCommon::get_base_url() . '/images/spinner.svg', $form );
					?>

					<img src="<?php echo esc_url( $preloader_url ); ?>" id="gotrgf_preloader_image_<?php echo esc_attr( $form_id ); ?>" class="gotrgf_preloader_image gotrgf_hide_preloader" alt="">
			</div>

				<?php
				/**
				 * Add action hook to add html after the main summary container
				 */
				do_action( 'gotrgf_after_form_overview_container', $form );
				?>

		</div>
				<?php if ( 'left' === $summary_side ) { ?>
			<div class='gotrgf_form_wrapper'>
					<?php echo $form_string; ?>
			</div>
		<?php } ?>

		</div>

				<?php
				// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped

				return ob_get_clean();
			},
			10,
			2
		);
	}
}

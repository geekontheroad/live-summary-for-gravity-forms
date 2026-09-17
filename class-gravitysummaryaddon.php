<?php

GFForms::include_addon_framework();
class GFSummaryAddOn extends GFAddOn {
 
    protected $_version = GF_SUMMARY_ADDON_VERSION;
	protected $_min_gravityforms_version = '2.5';
	protected $_slug = 'gravitysummary';
	protected $_path = 'gravitysummary/gf-summary-addon.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Gravity Forms Live Summary';
	protected $_short_title = 'Live Summary';

	private static $_instance = null;

	/**
	 * Defines the capability needed to access the Add-On form settings page.
	 *
	 * @since  1.2.2
	 * @access protected
	 * @var    string $_capabilities_form_settings The capability needed to access the Add-On form settings page.
	 */
	protected $_capabilities_form_settings = 'gravityforms_edit_forms';

	/**
	 * Defines the capability needed to access the Add-On settings page.
	 *
	 * @since  1.2.2
	 * @access protected
	 * @var    string $_capabilities_settings_page The capability needed to access the Add-On settings page.
	 */
	protected $_capabilities_settings_page = 'gravityforms_edit_settings';


	/**
	 * Holds the url to the upgrade banner
	 * Can be changed in the constructor
	 * 
	 * @since 1.2.7
	 * @var  string 
	 */
	private $upgrade_banner_url = "";



	public function __construct() {
		parent::__construct();

		//set the upgrade banner url
		$this->upgrade_banner_url = $this->get_base_url() ."/images/live-summary-upgrade-banner.jpg";
	}

	

	/**
	 * Get an instance of this class.
	 *
	 * @return GFSummaryAddOn
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new GFSummaryAddOn();
		}

		return self::$_instance;
	}


	
	
	
	// # SCRIPTS & STYLES -----------------------------------------------------------------------------------------------

	/**
	 * Return the scripts which should be enqueued.
	 * Not being used anymore since v1.1.3 , scripts and styles are now loaded through gform_enqueue_scripts to ensure they only load on pages with a form that has a summary enabled
	 * @return Array
	 */
	public function scripts() {	
		$scripts = array();
		return array_merge( parent::scripts(), $scripts );
	}

	/**
	 * Return the stylesheets which should be enqueued.
	 * Not being used anymore since v1.1.3 , scripts and styles are now loaded through gform_enqueue_scripts to ensure they only load on pages with a form that has a summary enabled
	 * @return Array
	 */
	public function styles() {		
		$styles = array();
		return array_merge( parent::styles(), $styles );
	}

	
	
 
    public function pre_init() {
        parent::pre_init();
        // add tasks or filters here that you want to perform during the class constructor - before WordPress has been completely initialized
		
		
    }
 
    public function init() {
        parent::init();
        // add tasks or filters here that you want to perform both in the backend and frontend and for ajax requests
		
		//get the ajaxurl for frontend ajax calls
        //add_action( 'wp_enqueue_scripts', 'add_frontend_ajax' );
		
        //function add_frontend_ajax() {
			//wp_enqueue_script( 'summary_change_js',  plugin_dir_url(__FILE__) . '/js/summary-change.js', array('jquery') );
           // wp_localize_script( 'summary_change_js', 'frontendajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
       /// }
  	 	
		/** add our retrieve function to admin ajax**/
		add_action( 'wp_ajax_gotrgf_retrieve_gravity_summary_fields', array('gotrgf_retrieve_summary_fields', 'retrieve_gravity_summary_fields') );
		add_action( 'wp_ajax_nopriv_gotrgf_retrieve_gravity_summary_fields', array('gotrgf_retrieve_summary_fields', 'retrieve_gravity_summary_fields') );
		
		add_action( 'wp_ajax_gotrgf_gravity_summary_retrieve_field_object', array('gotrgf_retrieve_summary_fields', 'gravity_summary_retrieve_field_object') );
		add_action( 'wp_ajax_nopriv_gotrgf_gravity_summary_retrieve_field_object', array('gotrgf_retrieve_summary_fields', 'gravity_summary_retrieve_field_object') );

		add_action( 'wp_ajax_gotrgf_gravity_summary_format_money', array('gotrgf_retrieve_summary_fields', 'gravity_summary_format_money') );
		add_action( 'wp_ajax_nopriv_gotrgf_gravity_summary_format_money', array('gotrgf_retrieve_summary_fields', 'gravity_summary_format_money') );


		



		/**
		 * This function is temporary and its purpose is to migrate the settings from the form settings to the new settings page.
		 * Making sure that people don't loose their settings after updating	 * 
		 * @since v1.1
		***/
		function gotrgf_migrate_to_new_settings_page() {	
			
			if(!class_exists('GFAPI')) {
				return;
			}
	
			//if our option exist then we already migrated
			if(get_option('gotrgf_succesfull_migration_complete') == true) {
				return;
			}
		
			$all_forms = GFAPI::get_forms();
				
			foreach($all_forms as $key => $form) {
				if (!array_key_exists("show_summary", $form)) {
					continue;
				}
				$current_summary_setting = $form['show_summary'] == "" ? 0 : 1;
				$current_total_setting = $form['show_total'] == "" ? 0 : 1;
		
				$form["gravitysummary"]['show_summary'] = $current_summary_setting;
				unset($form['show_summary']);
		
				$form["gravitysummary"]['show_total'] = $current_total_setting;
				unset($form['show_total']);
					
				$update = GFAPI::update_form( $form );
				
			}
	
			add_option('gotrgf_succesfull_migration_complete', true);
			
		}
		add_action('admin_head', 'gotrgf_migrate_to_new_settings_page', 10);
		add_action('wp_head', 'gotrgf_migrate_to_new_settings_page', 10);




	


		/**
		 * Hide the summary when the confirmation page loads
		 * @since v1.1
		 */
		
		function gotrgf_remove_summary_from_confirmation_message( $confirmation, $form, $entry ) {
			if ( ! is_string( $confirmation ) ) {
				return $confirmation;
			}

			$remove = true;
			/**
			 * Add filter so this script can be dynamically removed if needed
			 * @since v1.1
			 */
			$remove = apply_filters("gotrgf_add_scripts_to_confirmation", $remove,$form);

			if($remove == true) {
				$confirmation .= GFCommon::get_inline_script_tag( "window.top.jQuery(document).on('gform_confirmation_loaded', function () { jQuery('#gotrgf_form_container_".rgar($form,"id")." .gotrgf_summary_wrapper').remove(); /*jQuery('.gotrgf_form_wrapper').removeClass('gotrgf_form_wrapper');*/ } );" );
			}

			return $confirmation;
		}
		add_filter( 'gform_confirmation', 'gotrgf_remove_summary_from_confirmation_message', 10, 3 );
		
    }
 









    public function init_admin() {
        parent::init_admin();
		
		/**
		 * load styles on the block editor to ensure the summary display correctly there
		 * 
		 * @since v1.2.6 
		 * **/
		add_action( 'enqueue_block_editor_assets', "gotr_enqueue_plugin_preview_styles" );		
		function gotr_enqueue_plugin_preview_styles(  ) {
			wp_enqueue_style( 'gravity_summary_css', GOTRGF_PLUGIN_DIR . '/css/gravity-summary-all.css', array(), GF_SUMMARY_ADDON_VERSION, 'all' );				
		}


		/**
		 * Load backend stylesheet
		 * @since v1.2.5 
		 */
		function gotr_enqueue_backend_styles() {
			//making sure it only loads when on gravity forms page
			global $pagenow;
			if ( $pagenow === 'admin.php' && isset( $_GET['page'] ) && $_GET['page'] === 'gf_edit_forms' ) {
				wp_enqueue_style( 'gotrgf_summary_backend_styles', plugins_url( '/css/gotrgf-summary-backend-styles.css', __FILE__ ), array(), GF_SUMMARY_ADDON_VERSION, 'all' );
			}
		}
		add_action( 'admin_enqueue_scripts', 'gotr_enqueue_backend_styles' );


		
		
		/** 
		 * Add field setting
		 **/
		add_action( 'gform_field_standard_settings', 'gotr_live_summary_settings', 10, 2 );
		function gotr_live_summary_settings( $position, $form_id ) {

			//create settings on position 25 (right after Field Label)
			if ( $position == 25 ) {
				?>
				<li class="gotr_live_summary_setting field_setting">
					<input type="checkbox" id="field_summary_setting" onclick="SetFieldProperty('liveSummaryField', this.checked);" />
					<label for="field_summary_setting" style="display:inline;">
						<?php esc_html_e("Show in summary", "live-summary-for-gravity-forms"); ?>
						<?php gform_tooltip("form_field_summary_setting") ?>
					</label>
				</li>
				<?php
				/**
				 * Action to add more field settings
				 * 
				 * @since 1.2.0
				 */
				do_action("gotrgf_live_summary_field_settings", $form_id);
			}
		}
		
		
		//Action to inject supporting script to the form editor page
		add_action( 'gform_editor_js', 'gotrgf_editor_script' );
		function gotrgf_editor_script(){
			?>
			<script type='text/javascript'>
				//adding setting to our supported fields
				fieldSettings.text += ', .gotr_live_summary_setting';
				fieldSettings.radio += ', .gotr_live_summary_setting';
				fieldSettings.checkbox += ', .gotr_live_summary_setting';
				fieldSettings.select += ', .gotr_live_summary_setting';
				fieldSettings.number += ', .gotr_live_summary_setting';
				fieldSettings.email += ', .gotr_live_summary_setting';
				fieldSettings.textarea += ', .gotr_live_summary_setting';
				fieldSettings.date += ', .gotr_live_summary_setting';
				fieldSettings.phone += ', .gotr_live_summary_setting';
				fieldSettings.website += ', .gotr_live_summary_setting';
				fieldSettings.name += ', .gotr_live_summary_setting';
				fieldSettings.address += ', .gotr_live_summary_setting';
				fieldSettings.multiselect += ', .gotr_live_summary_setting';
				fieldSettings.username += ', .gotr_live_summary_setting';
				fieldSettings.product += ', .gotr_live_summary_setting';
				fieldSettings.option += ', .gotr_live_summary_setting';
				fieldSettings.time += ', .gotr_live_summary_setting';
				fieldSettings.shipping += ', .gotr_live_summary_setting';
				
				//binding to the load field settings event to initialize the checkbox
				jQuery(document).on('gform_load_field_settings', function(event, field, form){
					jQuery( '#field_summary_setting' ).prop( 'checked', Boolean( rgar( field, 'liveSummaryField' ) ) );
				});
			</script>
			<?php
		}
		
		//Filter to add a new tooltip
		add_filter( 'gform_tooltips', 'gotrgf_add_field_tooltips' );
		function gotrgf_add_field_tooltips( $tooltips ) {			
			$title = esc_html__('Live summary', 'live-summary-for-gravity-forms');
			$description = esc_html__('Check this box to include this field in the live summary', 'live-summary-for-gravity-forms');
			$tooltips['form_field_summary_setting'] = sprintf("<h6>%s</h6> <p>%s</p>",$title,$description);

			/**
			 * New filter to add more tooltips for the field settings
			 * 
			 * @since v1.2.0
			 */
			$tooltips = apply_filters("gotrgf_add_field_tooltips", $tooltips);

			return $tooltips;
		}


	
    }

	/**
	 * Return the SVG icon URL for the settings pages.
	 *
	 * @since 1.1.5
	 *
	 * @return string
	 */
	public function get_menu_icon() {		
		//return 'dashicons-list-view dashicons';
		return plugin_dir_url(__FILE__) . "images/settings-icon.svg";
	}
	



	/**
		 * All fields registerd here go into the Form Settings tab when creating a new form
		 *
		 * @param $form The current form
		 *
		 * @return array Array for settings fields for the form
		 */
	

		function form_settings_fields( $form ) {

			

			$fields = array();

			$fields =  array(
					array(
						"title"  => "General Settings",
						"class" => "gform-settings-panel--half gotrgf_general_settings",
						"fields" => array(						
							array(
								'name'         => 'show_summary',
								'type'         => 'toggle',
								'description'  => esc_html__( 'Set this switch to on to show a live summary next to the form', 'live-summary-for-gravity-forms' ),
								'toggle_label' => esc_html__( 'Turn on summary', 'live-summary-for-gravity-forms' ),
								'label'		   => esc_html__( 'Turn on summary', 'live-summary-for-gravity-forms' ),
							),
							
						),
					),
					array(
						"title" => "Total Settings",
						"class" => "gform-settings-panel--half gotrgf_total_settings",
						"fields" => array(
							array(
							'name'         => 'show_total',
							'type'         => 'toggle',
							'description'   => esc_html__( 'If this is turned on AND there are products in the form than a total will show under the summary.', 'live-summary-for-gravity-forms' ),
							'toggle_label' => esc_html__( 'Show total in summary', 'live-summary-for-gravity-forms' ),
							'label' => esc_html__( 'Show total in summary', 'live-summary-for-gravity-forms' ),
							),
						)
						),
				);
				
				if(!class_exists("GOTRGFLiveSummaryPro")) {
					$fields[] =	array(						
							"class" => "gform-settings-panel",
							"fields" => array(
								array(
								'name'         => 'show_total',
								'type'         => 'html',
								'html'		   => "<a href='https://geekontheroad.com/live-summary-for-gravity-forms?utm_source=probanner' target='_blank'><img src='" . $this->upgrade_banner_url . "' style='width:100%;'></a>"
								
								,
								),
							)
						);
				}

				
				
				return apply_filters( 'gotrgf_live_summary_form_settings_fields', $fields, $form );
			
	}
	



	
	


		
 
    public function init_frontend() {
        parent::init_frontend();

		




		/**
		 * maybe Enqueue all our frontend scripts and styles
		 */

		add_action( 'gform_enqueue_scripts', 'maybe_enqueue_custom_frontend_scripts_and_styles', 10, 2 );

		function maybe_enqueue_custom_frontend_scripts_and_styles( $form, $is_ajax ) {
			//check if there is a live summary field in the form
			$summary_fields_found = GFAPI::get_fields_by_type( $form, array( 'gotrgf_live_summary_field' ), false );
			/**
			 * Add custom styles to the frontend
			 */
			$styles = array();			
					
			if (!empty($summary_fields_found) || (array_key_exists('gravitysummary',$form) &&  $form["gravitysummary"]['show_summary'])) {

				$styles[] = array(
						'handle'  => 'gravity_summary_css',
						'src'     => GOTRGF_PLUGIN_DIR . '/css/gravity-summary-all.css',
						'deps'	  => array(),
						'version' => GF_SUMMARY_ADDON_VERSION,
						'media'   => 'all'
					);

			}
			

			/**
			 * Filter the styles that are being loaded in the frontend
			 * 
			 * @param Array $styles same as wp_enqueue_style
			 * @since v1.1
			 */
			$styles = apply_filters("gotrgf_custom_styles_frontend", $styles, $form);

			if(!empty($styles)) {
				foreach( $styles as $style) {
					$handle = isset($style['handle']) ? $style['handle'] : ""; 
					$src = isset($style['src']) ? $style['src'] : ""; 					
					$deps = isset($style['deps']) ? $style['deps'] : array(); 
					$ver = isset($style['version']) ? $style['version'] : null; 
					$media = isset($style['media']) ? $style['media'] : "all"; 
					wp_enqueue_style( $handle, $src, $deps, $ver, $media );
				}
			}			


			/**
			 * Add custom scripts to the frontend
			 */
			$scripts = array();

			
			if (!empty($summary_fields_found) || (array_key_exists('gravitysummary',$form) &&  $form["gravitysummary"]['show_summary'])) {	
					
				$scripts[] =  array(
							'handle'  => 'summary_change_js',
							'src'     => GOTRGF_PLUGIN_DIR . 'js/summary-change.js',					
							'deps'    => array( 'jquery' ),
							'version' => GF_SUMMARY_ADDON_VERSION,
							'in_footer' => true
						);

				$scripts[] = array(
							'handle'  => 'fields_js',
							'src'     => GOTRGF_PLUGIN_DIR . 'js/fields.js',					
							'deps'    => array( 'jquery' ),
							'version' => GF_SUMMARY_ADDON_VERSION,
							'in_footer' => true
						);

					
			}
			
			
			/**
			 * Filter the scripts that are being loaded in the frontend
			 * 
			 * @param Array Arra of $scripts[] same as wp_enqueue_style
			 * @since v1.1
			 */
			$scripts = apply_filters("gotrgf_custom_scripts_frontend", $scripts, $form);

			

			if(!empty($scripts)) {
				foreach( $scripts as $script) {
					$handle = $script['handle'];
					$src = $script['src'];
					$deps = $script['deps'];
					$ver = $script['version'];
					$in_footer = $script['in_footer'];
					wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
				}
			}

			//print our ajaxurl to the frontend
			//MOVED TO SEPARATE FUNCTION since v1.2.4
			// wp_add_inline_script( 'summary_change_js', 'var gotr_frontendajax = ' . json_encode( array(
			// 	'ajaxurl' => admin_url( 'admin-ajax.php' ),			
			// ) ), 'before' );


			
				
		}





		/**
		  * Print inline scripts if needed
		  * 
		  * @since v1.2.4
		  ***/
		add_action( 'gform_register_init_scripts', 'gotrgf_maybe_load_summary_jquery' );
		function gotrgf_maybe_load_summary_jquery( $form ) {

			//check if there is a live summary field in the form
			$summary_fields_found = GFAPI::get_fields_by_type( $form, array( 'gotrgf_live_summary_field' ), false );
				
			if (!empty($summary_fields_found) || (array_key_exists('gravitysummary',$form) &&  $form["gravitysummary"]['show_summary'])) {	
				//INIT SUMMARY
				wp_add_inline_script( 'summary_change_js', 'jQuery(document).ready(function() { gotrgf_show_preloader('.$form['id'].'); gotrgf_retrieve_fields('.$form['id'].'); gotrgf_gravity_summary_update('.$form['id'].'); });' , 'after' );
				
				/**
				 * Including the post ID in the inline script so we can send it to PHP during ajax calls
				 * @since v1.2.6 
				 */
				global $post;

				//print our ajaxurl to the frontend
				wp_add_inline_script( 'summary_change_js', 'var gotr_frontendajax = ' . json_encode( array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),		
					'post_id' => $post->ID
				) ), 'before' );
			}	
		}

			
		
		



		/**
		 * Modify the form output
		 * This is where the frontend magic happens 
		 */
		add_filter( 'gform_get_form_filter', function ( $form_string, $form ) {	
			


			//check if our class exists
			if(!class_exists("gotrgf_retrieve_summary_fields")) {
				return $form_string;
			}

			if(!array_key_exists('gravitysummary',$form) ) {
				//no summary needed
				return $form_string;
			}

			//setting is turned off so don't add summary markup
			if (!$form["gravitysummary"]['show_summary']) {
				return $form_string;
			}	

			/**
			 * Start frontend manipulation
			 */
			

			
			$form_id = $form['id'];
			//add stuff before the form
			
			
			//check if this form has at least one product field in it
			$products = gotrgf_retrieve_summary_fields::product_fields_found($form["id"]);
			
			//check if the form total setting is switched to on
			$show_total = $form["gravitysummary"]["show_total"];
			
			if ($products == true and $show_total == 1) { // include the total if it has a product field and it is switched on
				/**
				 * Add filter to change the total label
				 * @param Str $total_label
				 * @since v1.1
				 */
				$total_label = "Total";
				$total_label = apply_filters("gotrgf_modify_total_label", $total_label,$form);

				/**
				 * Add a filter to allow output inside the total container at the beginning
				 * 
				 * @since v1.1.10
				 */
				$before_total = "";
				$before_total = apply_filters("gotrgf_total_container_start",$before_total, $form);

				/**
				 * Add a filter to allow output inside the total container at the beginning
				 * 
				 * @since v1.1.10
				 */
				$after_total = "";
				$after_total = apply_filters("gotrgf_total_container_end",$after_total, $form);

				$total_string = sprintf("<div class='gotrgf_summary_total'> %s" .
										 "<div class='gotrgf_label'>%s</div> " .
										 "<div class='gotrgf_total_right gotrgf_price_amount'></div>" . 
										 "</div>", 
										 $before_total,
										 esc_html__($total_label,"live-summary-for-gravity-forms"),
										 $after_total);
			} else {
				$total_string = "";
			}	

			

			/** 
			 * add filter to modify the summary title
			 * @param Str $summary_title
			 * @since v1.1
			 * **/
			$summary_title = "Summary";
			$summary_title = apply_filters("gotrgf_modify_main_summary_title",$summary_title,$form);



			/**
			 * Add filter to remove title 
			 * @param Bool $enable_title
			 * @param Array $form
			 * @since v1.1
			 */
			$enable_title = 1;			
			$enable_title = apply_filters("gotrgf_enable_main_summary_title",$enable_title,$form);
			

			/**
			 * Add filter to change the side of the summary
			 * options are left or right as a string
			 * @param Str left or right
			 * @since v1.1
			 */
			$summary_side = "right"; //default right
			$summary_side = apply_filters("gotrgf_specify_summary_side",$summary_side,$form);
			
			
			ob_start();

			/**
			 * Action hook just before the main container opens
			 * Example: Use to output form specific styling from the settings
			 */
			do_action("gotrgf_before_form_container",$form);


			if($summary_side == "left") {
				?>
				<div id="gotrgf_form_container_<?php echo $form_id; ?>" class='gotrgf_form_container'>
				<?php
			} else {				
				?>
				<div id="gotrgf_form_container_<?php echo $form_id; ?>" class='gotrgf_form_container'><div class='gotrgf_form_wrapper'> 
				<?php
				echo $form_string;
			}
			
			
			
			
			if($summary_side == "right") {
				echo "</div>";
			}
			?>		


			<div class='gotrgf_summary_wrapper'>
			
			<?php
			/**
			 * Add action hook to add html before the main summary container
			 * @since v1.1
			 */			
			do_action("gotrgf_before_form_overview_container",$form);
			?>
			
			<div id="gotrgf_form_overview_container_<?php echo $form["id"]; ?>" class='gotrgf_form_overview_container'>
				<?php
					/**
					 * Add action hook to add html before title div
					 * @since v1.1
					 */	
					do_action("gotrgf_before_summary_title",$form);
				?>

				<?php
				if ($enable_title == 1) {
				?>
				<div class='gotrgf_summary_title'>
					<h5><?php echo esc_html__($summary_title,"live-summary-for-gravity-forms") ?></h5>
				</div>
				<?php
				}
					/**
					 * Add action hook to add html before the summary lines div
					 * @since v1.1
					 */	
					do_action("gotrgf_before_summary_lines",$form);
				?>
				<div class='gotrgf_summary_lines'></div>
					<?php
					/**
					 * Add action hook to add html before the summary lines div
					 * @since v1.1
					 */	
					do_action("gotrgf_after_summary_lines", $form);


					//output the summary total area
					echo $total_string; 

					/**
					 * Add action hook to add html before title div
					 * @since v1.1
					 */	
					do_action("gotrgf_after_summary_total",$form);

					//$preloader_url = GOTRGF_PLUGIN_DIR . "images/loading-icon.svg";
					$preloader_url = content_url() . "/plugins/gravityforms/images/spinner.svg";
					$preloader_url = apply_filters("gotrgf_change_preloader_image_url",$preloader_url, $form);
					?>
					
					<img src=<?php echo $preloader_url; ?> id="gotrgf_preloader_image_<?php echo $form['id']; ?>" class="gotrgf_preloader_image gotrgf_hide_preloader">
			</div>
			
			<?php
			/**
			 * Add action hook to add html after the main summary container
			 */	
			do_action("gotrgf_after_form_overview_container",$form);
			?>

		</div>
		<?php if ($summary_side !== "right") { ?>
			<div class='gotrgf_form_wrapper'>
			<?php echo $form_string; ?>
			</div>
		<?php } ?>
		
		</div>

		<?php
			$form_string = ob_get_contents();
			ob_get_clean();


			

			//return the new form
			return $form_string;
		}, 10, 2 );
	}
 











    public function init_ajax() {
        parent::init_ajax();
        // add tasks or filters here that you want to perform only during ajax requests
		
    }
}
<?php
/**
 * Class that will handle all the communications between PHP and the GF database on one side and Javascript and the front end on the other side.
 * 
 * @access public
 * 
 * @author Johan from Geekontheroad. <https://geekontheroad.com>
 */

class gotrgf_retrieve_summary_fields {

	public static function init() {
        $class = __CLASS__;
        new $class;
    }

    public function __construct() {
         
    }

	/**
	 * Writes a log message to the Live Summary addon logs
	 * 
	 * @param String $method from where the message originates
	 * @param String $message Message to log
	 */
	public static function log_debug($method,$message) {
		$instance = new GFSummaryAddOn;
		$instance->log_debug($method . "() " . $message);
	}






	/**
	 * This function will check a formID and get all the fields for the summary
	 * 
	 * @return Mixed Json object of an array that contains all the fieldIDs for the summary
	 * 
	 */
	public static function retrieve_gravity_summary_fields () {
		
		self::log_debug(__METHOD__ , "... Running ");

		if ( isset($_REQUEST) ) {
			//check if the gravity class exists
			if (!class_exists("GFAPI")) {
				self::log_debug(__METHOD__ , "... Class GFAPI not found, aborting ");
				return;
			}

			//get form id from request sanitize it and make sure it is valid
			$formID = sanitize_key($_GET["formid"]);
			if(!is_numeric($formID)) {
				return;
			}

			//retrieve form object and make sure it is valid
			$form = GFAPI::get_form( intval($formID) );
			if($form === false) {
				return;
			}

			$post_id = sanitize_key($_GET["post_id"]);

			//check if there are any live summary fields in the form
			$summary_fields_found = GFAPI::get_fields_by_type( $form, array( 'gotrgf_live_summary_field' ), false );

			//abort if the summary is turned off and there are no live summary fields
			if( (!isset($form["gravitysummary"]) || !is_array($form["gravitysummary"]) || !isset($form["gravitysummary"]['show_summary'])) && empty($summary_fields_found) ) {
			//if(!$form["gravitysummary"]['show_summary'] && empty($summary_fields_found)) {
				return;
			}

			//retrieve all the fields of this form
			$fields = $form['fields'];
			//start new array that will store all the valid ids
			$fields_in_summary = array();
			//loop through all ids
			foreach ($fields as $index => $field) {
				//declare some variables
				$id = $field['id'];
				
				$type = $field['type'];
				//get timeformat for time fields, other fields just get empty value
				$timeFormat = $type == "time" ? $field['timeFormat'] : ""; 
				//get datetype for date fields
				$dateType = $type == "date" ? $field['dateType'] : ""; 
				//get dateformat for date fields
				$dateFormat = $type == "date" ? $field['dateFormat'] : ""; 
				//get the type of product 
				$inputType = $type == "product" ? $field['inputType'] : ""; 
				//get the type of product 
				$optionType = $type == "option" ? $field['inputType'] : ""; 
				//get the type of shipping
				$shippingType = $type == "shipping" ? $field['inputType'] : "";
				
				//get current field object
				$field_obj = GFAPI::get_field( $formID, $id );
				if($field_obj === false) {
					return;
				}
				
				if ($field_obj) {
					
					//see if this field has to be in the summary
					$insummary = $field_obj->liveSummaryField;
					//the label now supports certain mergetags such as embed_post etc
					$label = isset($field_obj->liveSummaryLabel) && !empty($field_obj->liveSummaryLabel) ? self::maybe_replace_mergetags_label($field_obj->liveSummaryLabel, $post_id) : $field['label'];
					$classes = isset($field_obj->liveSummaryClasses) && !empty($field_obj->liveSummaryClasses) ? $field_obj->liveSummaryClasses : "";
					$imageChoices = isset($field_obj->gotr_images_enabled) && !empty($field_obj->gotr_images_enabled) ? $field_obj->gotr_images_enabled : "";
					$displayChoiceLabels = isset($field_obj->liveSummaryShowChoiceLabels) && !empty($field_obj->liveSummaryShowChoiceLabels) ? $field_obj->liveSummaryShowChoiceLabels : "";

					if ($insummary) {
						//add to fieldids array that will be returned at the end of this function
						$fields_in_summary[] = array("id"=>$id, "label"=>$label, "type"=>$type, "timeFormat"=>$timeFormat, "dateType"=>$dateType, "inputType"=>$inputType, "optionType"=>$optionType, "shippingType"=>$shippingType, "dateFormat"=>$dateFormat, "classes"=>$classes, "imagechoices"=>$imageChoices, "choiceLabels"=>$displayChoiceLabels);
					}
				}
				
			}


			/**
			 * Add a filter to change the order of items in the summary 
			 * 
			 * How to use:
			 * Return an array of field ids. The script will display the items in that order. any ids not defined will be added to the end
			 * 
			 * @since v1.2.2
			 */
			$order = array();
			//$order = apply_filters("gotrgf_change_summary_items_order", $order, $form);
			$order = gf_apply_filters( 'gotrgf_change_summary_items_order', array( $form['id'] ), $order, $form );

			if(is_array($order) && !empty($order)){
				$reordered_fields = array();
				// we need to reorder the $fields_in_summary
				foreach($order as $key => $field_id) {
					$key = array_search($field_id, array_column($fields_in_summary, 'id'));
					$reordered_fields[] = $fields_in_summary[$key];
				}

				//we have to make sure that all summary activated fields end up in the summary (a user could forget to list an ID in the filter) so lets add all remaining fields to the end
				foreach($fields_in_summary as $key => $field) {
					$found = array_search($field['id'], array_column($reordered_fields, 'id'));	

					if($found === false) {		
						//found a field that is not listed yet so add it to te end				
						$keytwo = array_search($field['id'], array_column($fields_in_summary, 'id'));
						$reordered_fields[] = $fields_in_summary[$keytwo];
					} else {						
						continue;
					}			

				}
				$fields_in_summary = $reordered_fields;
			}


			//return fieldIDS array as json object 
			header('Content-Type: application/json');
			echo json_encode($fields_in_summary);	

			//stop here
			die();
			
		}
	}



	/**
	 * This method will check a string for the existence of any mergetags that are available without the entry object such as user and post tags
	 * 
	 * @param string $text   The text to filter
	 * @param int $post_id   The post ID where the request came from
	 * @param bool $url_encode whether to encode the value
	 * 
	 * @return string $text
	 */
	public static function maybe_replace_mergetags_label($text, $post_id, $url_encode=false) {

		if ( strpos( $text, '{' ) !== false ) {

			//embed post and custom fields
			preg_match_all( "/\{embed_post:(.*?)\}/", $text, $ep_matches, PREG_SET_ORDER );
			preg_match_all( "/\{custom_field:(.*?)\}/", $text, $cf_matches, PREG_SET_ORDER );			

			if ( ! empty( $ep_matches ) || ! empty( $cf_matches ) ) {
				$post = get_post($post_id);
				$is_singular = true;
				$post_array  = GFCommon::object_to_array( $post );

				//embed_post
				foreach ( $ep_matches as $match ) {
					$full_tag = $match[0];
					$property = $match[1] == "post_id" ? "ID" : $match[1];
					$value    = $is_singular ? $post_array[ $property ] : '';
					$text     = str_replace( $full_tag, $url_encode ? urlencode( $value ) : $value, $text );
				}

				//custom_field
				foreach ( $cf_matches as $match ) {
					$full_tag           = $match[0];
					$custom_field_name  = $match[1];
					$custom_field_value = $is_singular && ! empty( $post_array['ID'] ) ? get_post_meta( $post_array['ID'], $custom_field_name, true ) : '';
					$text               = str_replace( $full_tag, $url_encode ? urlencode( $custom_field_value ) : $custom_field_value, $text );
				}
			} 

			return GFCommon::replace_variables_prepopulate($text);
		}

		return $text;

	}




	/**
	 * returns true if product fields are found
	 * 
	 * @param Int|String $form_id the id of the form 
	 * 
	 * @return Bool        true if any fields are found for this form
	 **/

	public static function product_fields_found($form_id) {
		self::log_debug(__METHOD__ , "... Running for form " . $form_id);
		
		//check if the gravity class exists
		if (!class_exists("GFAPI")) {
			self::log_debug(__METHOD__ , " Aborting, Class GFAPI not found " . $form_id);			
			return;
		}

		//sanitize and check if we have a number
		$formID = sanitize_key($form_id);
		if(!is_numeric($formID)) {
			self::log_debug(__METHOD__ , " problem with form id ");			
			return;
		}

		//get form and validate it Return false if anything wrong with $form
		$form = GFAPI::get_form( $form_id );
		if($form === false) {
			self::log_debug(__METHOD__ , " problem with form object " . $form_id);
			return false;
		}

		//check if there are any live summary fields in the form
		$summary_fields_found = GFAPI::get_fields_by_type( $form, array( 'gotrgf_live_summary_field' ), false );

			//abort if the summary is turned off and there aren't any summary fields
		if(!$form["gravitysummary"]['show_summary'] && empty($summary_fields_found)) {			
			self::log_debug(__METHOD__ , " no summary needed, abort executing ");
			return false;
		}

		$fields = $form['fields'];
		
		if($fields) {
			self::log_debug(__METHOD__ , " Start looping through fields ");
			
			foreach ($fields as $index => $field) {
				$field_type = $field["type"];
				if ($field_type == "product") {
					self::log_debug(__METHOD__ , " Product field found. Finished ");					
					return true;
				}
			}
			self::log_debug(__METHOD__ , " No Product fields found. Finished ");	
			return false;
		}
	}






	/**
	 * return a form object as json for ajax handler
	 * 
	 * @return Mixed json object of a gravity form object
	**/

	public static function gravity_summary_retrieve_field_object () {
		if ( isset($_REQUEST) ) {
			self::log_debug(__METHOD__ , " Running ");
			//check if the gravity class exists
			if (!class_exists("GFAPI")) {
				self::log_debug(__METHOD__ , " Gravity GFAPI does not exist ");				
				return;
			}

			//get form id from request
			$formID = sanitize_key( $_GET["formid"] );
			if(is_numeric($formID)) {
				self::log_debug(__METHOD__ , " problem with formID while retrieving field object ");
				return;
			}
			
			$fieldID = sanitize_key( $_GET["fieldid"] );
			if(is_numeric($fieldID)) {
				self::log_debug(__METHOD__ , " problem with fieldid while retrieving field object ");				
				return;
			}
			
			$field_obj = GFAPI::get_field( intval($formID), intval($fieldID) );
			
			if ($field_obj) {
				//return fieldIDS array as json object 
				header('Content-Type: application/json');
				echo json_encode($field_obj);	
				die();
			} else {
				header('Content-Type: application/json');
				echo json_encode("failed");
				die();
			}
		}
	}




	

	/**
	 * This function formats a number received from ajax and turns it into the right currency
	 */
	public static function gravity_summary_format_money() {
		if ( isset($_REQUEST) ) {
			//check if the gravity class exists			
			if (!class_exists("GFCommon")) {
				error_log("GFcommon does not exist");
				return;
			}

			$value_to_format = $_GET["value"] ;

			require_once( GFCommon::get_base_path() . '/currency.php' );
			$currency = new RGCurrency( GFCommon::get_currency() );
			$formatted_value = $currency->to_money( $value_to_format );
			
			echo html_entity_decode($formatted_value);
			
			die();
		}
	}
			
}
add_action( 'plugins_loaded', array( 'gotrgf_retrieve_summary_fields', 'init' ));
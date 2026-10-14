<?php
/**
 * Helpers that collect the summary configuration of a form.
 *
 * @package live-summary-for-gravity-forms
 * @author  Johan from Geekontheroad. <https://geekontheroad.com>
 */

defined( 'ABSPATH' ) || exit;

/**
 * Reads the summary settings and selected fields of a form.
 */
class gotrgf_retrieve_summary_fields {

	// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Gravity Forms field property names.

	/**
	 * The field types that can be shown in the summary.
	 *
	 * @since 1.2.11
	 */
	const FIELD_TYPES = array( 'text', 'radio', 'checkbox', 'select', 'number', 'email', 'textarea', 'date', 'phone', 'website', 'name', 'address', 'multiselect', 'username', 'product', 'option', 'time', 'shipping', 'image_choice', 'multi_choice' );

	/**
	 * Field types only Live Summary Pro can show in the summary: core and Gravity Wiz fields, and the Pro add-on fields.
	 *
	 * @since 1.2.11
	 */
	const PRO_FIELD_TYPES = array( 'hidden', 'section', 'coupon', 'subtotal', 'tax', 'discount', 'gotrgf_eu_vat_field', 'uid' );

	/**
	 * Get the field types only Live Summary Pro can show in the summary.
	 * Besides the known types, this includes add-on fields that are built on a core input, as Pro supports those too.
	 *
	 * @since 1.2.11
	 *
	 * @return string[]
	 */
	public static function get_pro_field_types() {
		$types       = self::PRO_FIELD_TYPES;
		$held_back   = array( 'quiz', 'poll', 'survey', 'chainedselect' );
		$core_inputs = array(
			'multiselect' => 'GF_Field_MultiSelect',
			'select'      => 'GF_Field_Select',
			'radio'       => 'GF_Field_Radio',
			'checkbox'    => 'GF_Field_Checkbox',
			'textarea'    => 'GF_Field_Textarea',
			'number'      => 'GF_Field_Number',
			'email'       => 'GF_Field_Email',
			'hidden'      => 'GF_Field_Hidden',
			'text'        => 'GF_Field_Text',
		);

		foreach ( GF_Fields::get_all() as $type => $field ) {
			if ( in_array( $type, $held_back, true ) || 0 === strpos( $type, 'post_' ) || GFCommon::is_product_field( $type ) ) {
				continue;
			}

			if ( isset( $core_inputs[ (string) $field->inputType ] ) ) {
				$types[] = $type;
				continue;
			}

			foreach ( $core_inputs as $class ) {
				if ( $field instanceof $class ) {
					$types[] = $type;
					break;
				}
			}
		}

		return array_values( array_diff( array_unique( $types ), self::FIELD_TYPES ) );
	}

	/**
	 * Whether the summary is switched on in the form settings.
	 *
	 * @since 1.2.11
	 *
	 * @param array $form The form object.
	 *
	 * @return bool
	 */
	public static function is_summary_enabled( $form ) {
		return ! empty( rgars( $form, 'gravitysummary/show_summary' ) );
	}

	/**
	 * Whether at least one field has "Show in summary" ticked.
	 *
	 * @since 1.2.11
	 *
	 * @param array $form The form object.
	 *
	 * @return bool
	 */
	public static function has_selected_fields( $form ) {
		foreach ( (array) rgar( $form, 'fields' ) as $field ) {
			if ( ! empty( $field->liveSummaryField ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Whether the summary should be rendered for the current visitor.
	 * Without selected fields the summary is only shown to users who can edit the form, so they see how to set it up.
	 *
	 * @since 1.2.11
	 *
	 * @param array $form The form object.
	 *
	 * @return bool
	 */
	public static function should_display_summary( $form ) {
		if ( ! self::is_summary_enabled( $form ) ) {
			return false;
		}

		return self::has_selected_fields( $form ) || GFCommon::current_user_can_any( 'gravityforms_edit_forms' );
	}

	/**
	 * Get the fields that have to be shown in the summary, with the settings the frontend script needs.
	 *
	 * @since 1.2.11 Replaces the retrieve_gravity_summary_fields AJAX endpoint.
	 *
	 * @param array $form The form object.
	 *
	 * @return array
	 */
	public static function get_summary_fields( $form ) {
		$post_id           = is_singular() ? get_queried_object_id() : 0;
		$fields_in_summary = array();

		foreach ( (array) rgar( $form, 'fields' ) as $field ) {
			if ( empty( $field->liveSummaryField ) ) {
				continue;
			}

			// Image Choice and Multiple Choice fields are radio buttons or checkboxes on the page, so they are read like those.
			$type  = in_array( $field->type, array( 'image_choice', 'multi_choice' ), true ) ? $field->get_input_type() : $field->type;
			$label = ! empty( $field->liveSummaryLabel ) ? self::maybe_replace_mergetags_label( $field->liveSummaryLabel, $post_id ) : $field->label;

			$fields_in_summary[] = array(
				'id'           => $field->id,
				'label'        => $label,
				'type'         => $type,
				'timeFormat'   => 'time' === $type ? $field->timeFormat : '',
				'dateType'     => 'date' === $type ? $field->dateType : '',
				'inputType'    => 'product' === $type ? $field->inputType : '',
				'optionType'   => 'option' === $type ? $field->inputType : '',
				'shippingType' => 'shipping' === $type ? $field->inputType : '',
				'dateFormat'   => 'date' === $type ? $field->dateFormat : '',
				'classes'      => ! empty( $field->liveSummaryClasses ) ? $field->liveSummaryClasses : '',
				'imagechoices' => ! empty( $field->gotr_images_enabled ) ? $field->gotr_images_enabled : '',
				'choiceLabels' => ! empty( $field->liveSummaryShowChoiceLabels ) ? $field->liveSummaryShowChoiceLabels : '',
			);
		}

		/**
		 * Add a filter to change the order of items in the summary
		 *
		 * How to use:
		 * Return an array of field ids. The script will display the items in that order. Any ids not defined will be added to the end
		 *
		 * @since v1.2.2
		 */
		$order = gf_apply_filters( array( 'gotrgf_change_summary_items_order', $form['id'] ), array(), $form );

		if ( is_array( $order ) && ! empty( $order ) ) {
			$fields_by_id = array_column( $fields_in_summary, null, 'id' );
			$reordered    = array();

			foreach ( $order as $field_id ) {
				if ( isset( $fields_by_id[ $field_id ] ) ) {
					$reordered[] = $fields_by_id[ $field_id ];
					unset( $fields_by_id[ $field_id ] );
				}
			}

			// Make sure all selected fields end up in the summary, even if they are missing from the filtered order.
			$fields_in_summary = array_merge( $reordered, array_values( $fields_by_id ) );
		}

		return $fields_in_summary;
	}

	/**
	 * This method will check a string for the existence of any mergetags that are available without the entry object such as user and post tags
	 *
	 * @param string $text       The text to filter.
	 * @param int    $post_id    The ID of the post the form is embedded in.
	 * @param bool   $url_encode Whether to encode the value.
	 *
	 * @return string $text
	 */
	public static function maybe_replace_mergetags_label( $text, $post_id, $url_encode = false ) {

		if ( strpos( $text, '{' ) === false ) {
			return $text;
		}

		// Embed post and custom fields.
		preg_match_all( '/\{embed_post:(.*?)\}/', $text, $ep_matches, PREG_SET_ORDER );
		preg_match_all( '/\{custom_field:(.*?)\}/', $text, $cf_matches, PREG_SET_ORDER );

		if ( ! empty( $ep_matches ) || ! empty( $cf_matches ) ) {
			$post       = $post_id ? get_post( $post_id ) : null;
			$post_array = $post ? GFCommon::object_to_array( $post ) : array();

			foreach ( $ep_matches as $match ) {
				$property = 'post_id' === $match[1] ? 'ID' : $match[1];
				$value    = (string) rgar( $post_array, $property );
				$text     = str_replace( $match[0], $url_encode ? rawurlencode( $value ) : $value, $text );
			}

			foreach ( $cf_matches as $match ) {
				$value = $post ? (string) get_post_meta( $post->ID, $match[1], true ) : '';
				$text  = str_replace( $match[0], $url_encode ? rawurlencode( $value ) : $value, $text );
			}
		}

		return GFCommon::replace_variables_prepopulate( $text );
	}

	/**
	 * Returns true if the form contains a product field.
	 *
	 * @param array|int $form The form object or form ID.
	 *
	 * @return bool
	 */
	public static function product_fields_found( $form ) {
		if ( ! is_array( $form ) ) {
			$form = GFAPI::get_form( absint( $form ) );
		}

		if ( ! is_array( $form ) ) {
			GFSummaryAddOn::get_instance()->log_debug( __METHOD__ . '(): Invalid form, aborting.' );
			return false;
		}

		return ! empty( GFAPI::get_fields_by_type( $form, array( 'product' ) ) );
	}
}

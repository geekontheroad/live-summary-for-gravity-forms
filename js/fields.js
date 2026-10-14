/**
 * Reads the current value of a field for the summary.
 * Every public method returns escaped HTML, so the result can be inserted into the summary as is.
 */
class summary_fields_value {

	constructor(form_id, field_id) {
		this.form_id = form_id;
		this.field_id = field_id;
		this.input_id = "#input_" + form_id + "_" + field_id;
	}

	/** check if a field is hidden with conditional logic
	 * Returns false if it is hidden
	 * Return true if not hidden by conditional logic
	**/
	is_not_hidden_by_conditional_logic(field_type, field_producttype) {
		if (field_producttype == "singleproduct" || field_producttype == "calculation") {
			return !jQuery("#ginput_base_price_" + this.form_id + "_" + this.field_id).is(":disabled");
		}

		return !jQuery(this.input_id).is(":disabled");
	}

	/**
	 * Value of the checked radio button of this field
	 **/
	checked_radio_value() {
		return jQuery(this.input_id + " input[type='radio']:checked").val();
	}

	/**
	 * Build the summary html for a choice with a price, stored as "label|price"
	 * Returns undefined when there is no value or no label (a placeholder)
	 **/
	priced_choice(current_value) {
		if (!current_value) {
			return;
		}

		var value_arr = current_value.split("|");
		var selected_label = value_arr[0];
		var selected_price = value_arr[value_arr.length - 1];

		if (selected_label == "") {
			return;
		}

		return "<span class='gotrgf_unit_label'>" + gotrgf_escape_html(selected_label) + "</span> - <span class='gotrgf_unit_price'>" + gotrgf_escape_html(gotrgf_format_to_money(selected_price)) + "</span>";
	}

	/**
	 * Method to check any normal input types
	 * Normal means just a input or select field that has a value
	**/
	normal_input() {
		return gotrgf_escape_html(jQuery(this.input_id).val());
	}

	/**
	 * Method to handle radio buttons
	 **/
	radio() {
		return gotrgf_escape_html(this.checked_radio_value());
	}

	/**
	 * Method to handle checkboxes
	 **/
	checkbox() {
		var values = [];
		jQuery(this.input_id + " input[type='checkbox']:checked").each(function(i, ob) {
			values.push(gotrgf_escape_html(jQuery(ob).val()));
		});
		return values.join("<br> ");
	}

	/**
	 * Method to handle advanced name field
	 **/
	name() {
		var input_id = this.input_id;

		//name specific classes. If these classes are present then the fields are there too and we need to check them. The extra integer is the extra number gravity adds to these special subfields
		var class_names = [
			["has_prefix", 2],
			["has_first_name", 3],
			["has_middle_name", 4],
			["has_last_name", 6],
			["has_suffix", 8],
		];

		var full_name = "";

		//loop through the classes and get only values from existing fields that are not empty
		class_names.forEach(function(item) {
			if (jQuery(input_id).hasClass(item[0])) {
				var sub_field_value = jQuery(input_id + "_" + item[1]).val();
				if (sub_field_value) {
					full_name += gotrgf_escape_html(sub_field_value) + " ";
				}
			}
		});

		return full_name;
	}

	/**
	 * Method to handle address fields
	 **/
	address() {
		var input_id = this.input_id;

		//address specific classes. If these classes are present then the fields are there too and we need to check them. The extra integer is the extra number gravity adds to these special subfields
		var class_names = [
			["has_street", 1],
			["has_street2", 2],
			["has_city", 3],
			["has_state", 4],
			["has_zip", 5],
			["has_country", 6],
		];

		//subfields that are followed by a line break
		var next_lines = [1, 2, 4];

		var full_address = "";

		//loop through the classes and get only values from existing fields that are not empty
		class_names.forEach(function(item) {
			if (jQuery(input_id).hasClass(item[0])) {
				var sub_field_value = jQuery(input_id + "_" + item[1]).val();
				if (sub_field_value) {
					full_address += gotrgf_escape_html(sub_field_value) + (next_lines.includes(item[1]) ? "<br>" : " ");
				}
			}
		});

		return full_address;
	}

	/**
	 * Method to handle multi select fields
	 **/
	multiselect() {
		//multi select field returns an array of all selected values, or null when nothing is selected
		var input_values = jQuery(this.input_id).val() || [];

		return input_values.map(gotrgf_escape_html).join("<br>");
	}

	/**
	 * Method to handle date fields
	 **/
	date(dateType, dateFormat) {
		/**gravity date types are
		 * 1. datefield (3 input fields)
		 * 2. datedropdown (3 select fields)
		 * 3. datepicker (1 input field)
		 *
		 * Date formats are:
		 *  dd/mm/yyyy -> dmy
		 *	mm/dd/yyyy -> mdy
		 *	dd-mm-yyyy -> dmy_dash
		 *	dd.mm.yyyy -> dmy_dot
		 *	yyyy/mm/dd -> ymd_slash
		 *	yyyy-mm-dd -> ymd_dash
		 *	yyyy.mm.dd -> ymd_dot
		 *
		 * In the fieldids _1=month   _2=day   _3=year
		 **/
		var full_date = "";

		if (dateType == "datepicker") {
			full_date = jQuery(this.input_id).val();
		} else {
			var month = jQuery(this.input_id + "_1").val();
			var day = jQuery(this.input_id + "_2").val();
			var year = jQuery(this.input_id + "_3").val();

			if (month && day && year) {
				switch (dateFormat) {
					case "dmy":
						full_date = day + "/" + month + "/" + year;
						break;
					case "dmy_dash":
						full_date = day + "-" + month + "-" + year;
						break;
					case "dmy_dot":
						full_date = day + "." + month + "." + year;
						break;
					case "ymd_slash":
						full_date = year + "/" + month + "/" + day;
						break;
					case "ymd_dash":
						full_date = year + "-" + month + "-" + day;
						break;
					case "ymd_dot":
						full_date = year + "." + month + "." + day;
						break;
					default:
						full_date = month + "/" + day + "/" + year;
				}
			}
		}

		return gotrgf_escape_html(full_date);
	}

	/**
	 * Method to handle time fields
	 **/
	time(timeFormat) {
		//information of which format is stored in the form object per field as "timeFormat" with either 12 or 24 //subfields are hours -> 1 minutes -> 2 am/pm -> 3
		var hours = jQuery(this.input_id + "_1").val();
		var minutes = jQuery(this.input_id + "_2").val();
		var full_time = "";

		if (hours && minutes) {
			if (timeFormat == "12") {
				full_time = hours + ":" + minutes + " " + jQuery(this.input_id + "_3").val();
			} else if (timeFormat == "24") {
				full_time = hours + ":" + minutes;
			}
		}

		return gotrgf_escape_html(full_time);
	}

	product(inputType) {
		/** different types are singleproduct, select, radio, price, hidden, calculation **/

		if (inputType == "singleproduct" || inputType == "calculation") {
			//current value of the baseprice
			var single_price = jQuery("#ginput_base_price_" + this.form_id + "_" + this.field_id).val();
			//quantity field name => we need to use the name as the id is hidden when the quantity is hidden
			var single_quantity = parseInt(jQuery("#gform_" + this.form_id + " input[name='input_" + this.field_id + ".3']").val());

			//return quantity and price
			if (single_quantity > 0) {
				return "<span class='gotrgf_unit_quantity'>" + single_quantity + " x </span><span class='gotrgf_unit_price'>" + gotrgf_escape_html(single_price) + "</span>";
			}

		} else if (inputType == "select") {
			return this.priced_choice(jQuery(this.input_id).val());

		} else if (inputType == "radio") {
			return this.priced_choice(this.checked_radio_value());

		} else if (inputType == "price") {
			var current_value = jQuery(this.input_id).val();

			if (!current_value) {
				return;
			}

			//resolve currency constructor: gform.Currency since GF 2.9, fall back to legacy global
			var GotrgfCurrencyCtor = (typeof gform !== "undefined" && gform.Currency) ? gform.Currency : Currency;
			var gotrgf_currency = new GotrgfCurrencyCtor(gf_global.gf_currency_config);
			//this one does not use the gotrgf_format_to_money because it seems to always have the right format already except for the currency
			var entered_price_formatted_currency = gotrgf_currency.toMoney(current_value);

			return "<span class='gotrgf_unit_quantity'>1 x</span> <span class='gotrgf_unit_price'>" + gotrgf_escape_html(entered_price_formatted_currency) + "</span>";
		}
	}

	option(optionType) {
		/**There are three types of options: select, checkbox, radio */

		if (optionType == "select") {
			return this.priced_choice(jQuery(this.input_id).val());

		} else if (optionType == "radio") {
			return this.priced_choice(this.checked_radio_value());

		} else if (optionType == "checkbox") {
			var self = this;
			var values = [];
			jQuery(this.input_id + " input[type='checkbox']:checked").each(function(i, ob) {
				values.push(self.priced_choice(jQuery(ob).val()));
			});
			return values.filter(Boolean).join("<br> ");
		}
	}

	shipping(shippingType) {
		//options are singleshipping, select, radio
		if (shippingType == "singleshipping") {
			//formatting happens by gf in the field
			return gotrgf_escape_html(jQuery(this.input_id).val());

		} else if (shippingType == "select") {
			return this.priced_choice(jQuery(this.input_id).val());

		} else if (shippingType == "radio") {
			return this.priced_choice(this.checked_radio_value());
		}
	}
}

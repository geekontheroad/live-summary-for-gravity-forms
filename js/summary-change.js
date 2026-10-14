/**
 * Gravity Live summary update script
 **/

/**
 * Escape a value so it can be safely inserted as HTML.
 *
 * @param {*} value The value to escape.
 * @returns {String} The escaped value.
 */
function gotrgf_escape_html(value) {
    if (value === undefined || value === null) {
        return "";
    }

    return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function gotrgf_show_preloader(formId) {
    jQuery("#gotrgf_form_overview_container_" + formId).css("opacity", ".5");
    jQuery("#gotrgf_preloader_image_" + formId).removeClass("gotrgf_hide_preloader");
}

function gotrgf_hide_preloader(formId) {
    setTimeout(function() {
        jQuery("#gotrgf_form_overview_container_" + formId).css("opacity", "1");
        jQuery("#gotrgf_preloader_image_" + formId).addClass("gotrgf_hide_preloader");
    }, 500);
}

/**
 * Get the summary data that was printed for a form, or false if this form has no summary.
 *
 * @param {Int} formId The form ID.
 * @returns {Object|Boolean}
 */
function gotrgf_get_summary_data(formId) {
    if (!window.gotrgf_summary_data || !window.gotrgf_summary_data[formId]) {
        return false;
    }

    return window.gotrgf_summary_data[formId];
}

/**
 * update the summary
 * outputs the summary to the html
 **/
function gotrgf_gravity_summary_update(formId) {

    var summary_data = gotrgf_get_summary_data(formId);

    //abort if this form has no summary
    if (!summary_data) {
        return;
    }

    //create output variable
    var output = "";

    //loop through array of field ids
    jQuery.each(summary_data.fields, function(index, value) {

        var field_type = value['type'];
        var field_value = "";

        //initiate class to retrieve value based on field type
        let get_values = new summary_fields_value(formId, value['id']);

        // only proceed if this field is not hidden by conditional logic
        // every getter returns escaped HTML
        if (get_values.is_not_hidden_by_conditional_logic(field_type, value['inputType'])) {

            switch (field_type) {
                case "radio":
                    field_value = get_values.radio();
                    break;

                case "checkbox":
                    field_value = get_values.checkbox();
                    break;

                case "name":
                    field_value = get_values.name();
                    break;

                case "address":
                    field_value = get_values.address();
                    break;

                case "multiselect":
                    field_value = get_values.multiselect();
                    break;

                case "time":
                    field_value = get_values.time(value['timeFormat']);
                    break;

                case "date":
                    field_value = get_values.date(value['dateType'], value['dateFormat']);
                    break;

                case "product":
                    field_value = get_values.product(value['inputType']);
                    break;

                case "option":
                    field_value = get_values.option(value['optionType']);
                    break;

                case "shipping":
                    field_value = get_values.shipping(value['shippingType']);
                    break;

                default:
                    field_value = get_values.normal_input();
                    break;
            }
        }

        if (field_value) {
            var line_unique_id = "gsline_" + formId + "_" + gotrgf_escape_html(value['id']); //unique id for each line based on the field id
            output += "<div id='" + line_unique_id + "' class='gotrgf_summary_line'> <div class='gotrgf_line_part_left'> " + gotrgf_escape_html(value['label']) + " </div> <div class='gotrgf_line_part_right'> " + field_value + " </div> </div>";
        }
    });

    if (output == "") { //output still empty so nothing was selected
        /**
         * Add filter so we can modify the text for nothing selected on the front end
         *
         * @param String nothing_selected_text is the text we are filtering
         * @param Int formId is the current form id
         *
         * @param String the new nothing selected text
         *
         * @since v1.1.7
         ***/
        var nothing_selected_text = gform.applyFilters('gotrgf_change_nothing_selected_text', summary_data.nothingSelected, formId);
        output = "<p class='gotrgf_nothing_selected'>" + gotrgf_escape_html(nothing_selected_text) + "</p>";
    }

    //output summary to html
    jQuery("#gotrgf_form_overview_container_" + formId + " .gotrgf_summary_lines").html(output);

    gotrgf_hide_preloader(formId);
}

/**
 * This function is used to properly format numbers that have to appear as currency
 * it utilizes gf default Currency function
 *
 * This function is used for the total and for separate product fields
 *
 * @param {String|Int} text
 * @returns {String} currency
 */
function gotrgf_format_to_money(text) {
    //resolve currency constructor: gform.Currency since GF 2.9, fall back to legacy global
    var GotrgfCurrencyCtor = (typeof gform !== "undefined" && gform.Currency) ? gform.Currency : Currency;
    var gotrgf_currency = new GotrgfCurrencyCtor(gf_global.gf_currency_config);

    //get some currency details from the above Currency instance
    var decimals = gotrgf_currency['currency']['decimals'];
    var decimal_separator = gotrgf_currency['currency']['decimal_separator'];
    var thousand_separator = gotrgf_currency['currency']['thousand_separator'];
    var symbol_padding = gotrgf_currency['currency']['symbol_padding'];

    //convert the number to the proper number format (not currency yet here)
    var number = gotrgf_currency.numberFormat(text, decimals, decimal_separator, thousand_separator, symbol_padding);

    //convert the number from above to the proper currency
    return gotrgf_currency.toMoney(number);
}

/**
 * Load the above summary update script when any input changes or when conditional logic changes
 **/
if (typeof gform !== "undefined") {
    gform.addAction('gform_input_change', function(elem, formId, fieldId) {
        if (gotrgf_get_summary_data(formId)) {
            gotrgf_show_preloader(formId);
            gotrgf_gravity_summary_update(formId);
        }
    }, 10, 3);

    gform.addAction('gform_post_conditional_logic_field_action', function(formId, action, targetId, defaultValues, isInit) {
        if (gotrgf_get_summary_data(formId)) {
            gotrgf_show_preloader(formId);
            gotrgf_gravity_summary_update(formId);
        }
    });

    /**
     * update the total
     **/
    gform.addFilter('gform_product_total', function(total, formId) {
        // total here is not formatted yet, it just comes as xx.xx
        //convert the total to the proper currency and display in the summary
        jQuery("#gotrgf_form_overview_container_" + formId + " .gotrgf_price_amount").text(gotrgf_format_to_money(total));

        return total;
    }, 100, 2);
}

/**
 * Refresh the summary when a (multi-page or AJAX) form is rendered again
 **/
jQuery(document).on('gform_post_render', function(event, formId) {
    gotrgf_gravity_summary_update(formId);
});

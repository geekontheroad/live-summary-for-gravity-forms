/**
 * Gravity Live summary update script
 **/

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
 * Ajax handler to retrieve all summaryfields when the form loads
 * Places the result in the window.fields
 */
function gotrgf_retrieve_fields(form_id) {

    jQuery.ajax({
        url: gotr_frontendajax.ajaxurl,
        type: 'GET',
        data: {
            'action': 'gotrgf_retrieve_gravity_summary_fields',
            'formid': form_id,
            'post_id': gotr_frontendajax.post_id
        },

        beforeSend: function() {
            gotrgf_show_preloader(form_id);
        },

        success: function(data) {
            window.gotrgfformid = form_id; //store form id no matter what
            gotrgf_gravity_summary_update(form_id); //trigger update so that we can show error message if no fields are selected in the backend

            //abort if something went wrong on the server side. Could be a second form on the page or a nested form
            if (data == 0) {
                return;
            }
            //store fields and form id
            window.fields = data;

            //trigger summary update
            if (window.fields) {
                gotrgf_gravity_summary_update(form_id);
                gotrgf_hide_preloader(form_id);
            }

        },
        error: function(data) {
            console.log("An error occured while retrieving the field ids.");

        }
    });
}


/**
 * AJAX handler to get a full JSON form object from a PHP function
 **/

function gotrgf_retrieve_one_field(form_id, field_id) {

    jQuery.ajax({
        url: gotr_frontendajax.ajaxurl,
        type: 'GET',
        data: {
            'action': 'gotrgf_gravity_summary_retrieve_field_object',
            'formid': form_id,
            'fieldid': field_id,
        },

        success: function(data) {
            if (data == 0) {
                return;
            }
            return data;

        },
        error: function(data) {
            console.log("An error occured while retrieving the field ids.");

        }
    });
}












/**
 * update the summary
 * outputs the summary to the html
 **/
function gotrgf_gravity_summary_update(formId) {

    //abort if the stored formid doesn't match the given formid
    if (window.gotrgfformid != formId) {
        return;
    }

    //create output variable
    var output = "";

    //ids of the fields we need to show in the summary if they have a value
    var summary_fields = window.fields;

    if (!summary_fields) {
        //there are no fields selected to show
        var output = '<p>There are no fields selected. Please see documentation for explanation: <a href="https://geekontheroad.com/documentation/live-summary-for-gravity-forms/select-fields-to-show" target="_blank">Open doc</a></p>'
        jQuery(".gotrgf_summary_lines").html(output);
        gotrgf_hide_preloader(formId);
        return;
    }


    //loop through array of field ids
    jQuery.each(summary_fields, function(index, value) {

        //define field specific variables
        var field_type = value['type'];
        var field_label = value['label'];
        var field_timeformat = value['timeFormat'];
        var field_datetype = value['dateType'];
        var field_dateformat = value['dateFormat'];
        var field_producttype = value['inputType']; //what type of product
        var field_shippingtype = value['shippingType']; //what type of shipping field
        var field_optionType = value['optionType']; //what kind of field type is the option




        var input_html_id = "#field_" + formId + "_" + value['id'];


        //initiate class to retrieve value based on field type
        let get_values = new summary_fields_value(formId, value['id']);

        //if (jQuery(input_html_id).is(":visible")) {
        if (get_values.is_not_hidden_by_conditional_logic(field_type, field_producttype)) {
            // only proceed if this field is visible



            switch (field_type) {
                case "text":
                    var field_value = get_values.normal_input();
                    break;

                case "select":
                    var field_value = get_values.normal_input();
                    break;

                case "radio":
                    var field_value = get_values.radio();
                    break;

                case "checkbox":
                    var field_value = get_values.checkbox();
                    break;

                case "name":
                    var field_value = get_values.name();
                    break;

                case "address":
                    var field_value = get_values.address();
                    break;

                case "multiselect":
                    var field_value = get_values.multiselect();
                    break;

                case "time":
                    var field_value = get_values.time(field_timeformat);
                    break;

                case "date":
                    var field_value = get_values.date(field_datetype, field_dateformat);
                    break;

                case "product":
                    var field_value = get_values.product(field_producttype);
                    break;

                case "option":
                    var field_value = get_values.option(field_optionType);
                    break;

                case "shipping":
                    var field_value = get_values.shipping(field_shippingtype);
                    break;

                default:
                    var field_value = get_values.normal_input();
                    break;
            }

        } // end of visible if

        if (field_value) { //there is a value and its not a product so output it
            var line_unique_id = "gsline_" + formId + "_" + value['id']; //unique id for each line based on the field id
            output += "<div id=" + line_unique_id + " class='gotrgf_summary_line'> <div class='gotrgf_line_part_left'> " + field_label + " </div> <div class='gotrgf_line_part_right'> " + field_value + " </div> </div>";
        }



    });



    if (output == "") { //output still empty so nothing was selected
        var nothing_selected_text = "Nothing Selected yet";
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
        nothing_selected_text = gform.applyFilters('gotrgf_change_nothing_selected_text', nothing_selected_text, formId);
        output = "<p class='gotrgf_nothing_selected'>" + nothing_selected_text + "</p>";
    }

    //output summary to html
    jQuery(".gotrgf_summary_lines").html(output);

    gotrgf_hide_preloader(formId);

}




/** Form load **/
/**
 * REPLACED BY AN INLINE SCRIPT
 * SINCE v1.2.4
jQuery(document).on('gform_post_render', function(event, form_id, current_page) {
    //load all the fields that need to be in the summary
    gotrgf_show_preloader(form_id);
    gotrgf_retrieve_fields(form_id);
    gotrgf_gravity_summary_update(form_id);
});
**/







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
    //new instance of currency
    //resolve currency constructor: gform.Currency since GF 2.9, fall back to legacy global
    var GotrgfCurrencyCtor = (typeof gform !== "undefined" && gform.Currency) ? gform.Currency : Currency;
    var gotrgf_currency = new GotrgfCurrencyCtor(gf_global.gf_currency_config);

    //get some currency details from the above Currency instance
    var currency_name = gotrgf_currency['currency']['name'];
    var decimals = gotrgf_currency['currency']['decimals'];
    var symbol_left = gotrgf_currency['currency']['symbol_left'];
    var symbol_right = gotrgf_currency['currency']['symbol_right'];
    var decimal_separator = gotrgf_currency['currency']['decimal_separator'];
    var thousand_separator = gotrgf_currency['currency']['thousand_separator'];
    var symbol_padding = gotrgf_currency['currency']['symbol_padding'];

    //convert the number to the proper number format (not currency yet here)
    var number = gotrgf_currency.numberFormat(text, decimals, decimal_separator, thousand_separator, symbol_padding);

    //convert the number from above to the proper currency
    var currency = gotrgf_currency.toMoney(number);

    //return properly formatted currency
    return currency;
}



/**
 * Load the above summary update script when any input changes or when conditional logic changes
 **/
if (typeof gform !== "undefined") {
    gform.addAction('gform_input_change', function(elem, formId, fieldId) {
        if (isNaN(formId) == false) {
            gotrgf_show_preloader(formId);
            gotrgf_gravity_summary_update(formId);
        }

    }, 10, 3);
}

if (typeof gform !== "undefined") {
    gform.addAction('gform_post_conditional_logic_field_action', function(formId, action, targetId, defaultValues, isInit) {
        if (isNaN(formId) == false) {
            gotrgf_show_preloader(formId);
            gotrgf_gravity_summary_update(formId);
        }
    });
}





/**
 * update the total
 **/


if (typeof gform !== "undefined") {
    gform.addFilter('gform_product_total', function(total, formId) {
        // total here is not formatted yet, it just comes as xx.xx
        //convert the total to the proper currency and display in the summary
        jQuery(".gotrgf_price_amount").html(gotrgf_format_to_money(total));

        return total;
    }, 100, 2);
}
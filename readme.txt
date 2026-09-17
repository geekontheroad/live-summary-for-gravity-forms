=== Live Summary for Gravity Forms ===
Contributors: geekontheroad
Donate link: https://geekontheroad.com/donate
Author URI: https://geekontheroad.com
Tags: gravityforms, order-summary, live-summary, gravity-summary, gravity-forms
Requires at least: 4.7
Tested up to: 7.0
Stable tag: 1.2.10
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This simple and handy plugin will add a live summary next to any gravity form. No coding required. 

== Description ==

**This simple and free plugin helps you to easily add a live summary to any gravity forms. No coding required. Simply check the fields you want to see and save your form.**

[TRY DEMO HERE](https://gravitysummary.geekontheroad.com/) 

[GET PRO VERSION HERE](https://geekontheroad.com/live-summary-for-gravity-forms/) 


= How to set it up =

1. Install the plugin
1. The plugin has its own settings page since v1.1. Find it under Form > Settings > Live Summary
1. Switch on the "Turn on summary" checkbox. 
1. Decide which fields to show in the summary by checking the checkbox in the field settings of each field that you want to show.
1. Summary will now work
1. Optional: Show a total at the bottom. This will automatically work if you add any product fields to the form. To turn this total off, simply go back to the settings page and toggle the switch that says "show total in summary"
[screenshot section](https://wordpress.org/plugins/live-summary-for-gravity-forms/screenshots/)


> Conditional logic supported

= Currently the following field types are supported in the free version: =

* Single Line Text
* Paragraph Text
* Drop Down
* Number
* Checkboxes
* Radio Buttons
* Name
* Date
* time
* Phone
* Address
* Website
* Username
* Email
* Multi Select
* Product
* Total
* Shipping

= More fields are supported in the PRO version = 



= PRO version =

Currently available PRO features are:

* NEW: Added new setting for choice fields (radio, select, checkboxes) which allows you to display the choice label instead of its value
* NEW: Added support for hidden fields
* NEW: Added support for Section fields. 
* NEW: Added support for EU VAT field
* NEW: Added support for coupon fields
* NEW: Support for Gravity Forms Ecommerce fields (subtotal, tax, discount)
* NEW: Added support for Jetsloth Image Choices
* Change the field label shown in the summary + mergetag support in label
* Setting to change the "nothing selected" text
* Add a custom css class to the field line in the summary
* Adds a new fieldtype to Gravity that will output a live summary anywhere in your form!
* Adds support for merge tags inside HTML fields. (currently only working in multi-page forms)
* Make the summary sticky on scroll (very helpful for longer forms)
* Easily change default labels (Title and Total)
* Change the side of the summary (right or left)
* Brand Styler to configure things like fonts, border settings, background settings and padding settings
* Show summary on the confirmation page (experimental setting)

More things are possible with this plugin such as moving the button just under the summary or adding a logo above it for example. This and more can currently only be done with custom code snippets. I provide these snippets free for Pro customers. 

[GET PRO VERSION HERE](https://geekontheroad.com/live-summary-for-gravity-forms/) 


= Other plugins from the same developer =
* EU Vat for gravity forms: Easily calculate and validate European VAT (taxes) in gravity forms [GET IT HERE HERE](https://geekontheroad.com/eu-vat-for-gravity-forms/) 
* Coinbase Commerce for gravity forms: Accept cryptocurrency payments with Coinbase Commerce and Gravity Forms [GET IT HERE HERE](https://geekontheroad.com/coinbase-commerce-for-gravity-forms/) 


> Compatible with Gravity forms 2.5 and up. 


= Feature requests =
This is a relatively new plugin and I am looking into how I can make the plugin better. Do you have an idea for this plugin? Please let me know in the support tab or mail me directly.


= Found any bugs? = 
Please let me know before leaving a bad review. Chances are high that I will be able to fix it!


= Hire Me =
Are you looking for a professional to do a gravity forms related job? I have several years of experience with gravity forms and much longer with coding. Find out more on [my website](https://geekontheroad.com)




== Frequently Asked Questions ==

= I installed the plugin but I don't see a summary? =

You have to turn on the summary per form. The plugin has its own settings page under form settings. Look for GF Summary Add-on in the form settings menu. 

= The summary remains empty? =

Don’t forget to turn on the checkbox “turn on summary” in the general settings of each field that needs to show in the summary. Still not working? --> please contact me.

= I don't see a total in the summary? =

There are two conditions to see the total in the summary. Firstly you have to have at least one product field in the form. Secondly you need to make sure that the "Show total" setting is turned on. You can find this setting on the settings page.

= I want to show the summary inside the form instead of next to the form?

This is possible with the Pro version of this plugin. It adds a new field to gravity forms and also adds support for live merge tags in HTML fields. Purchase Pro version [here](https://geekontheroad.com/live-summary-for-gravity-forms/) 

= I want to change the output of the summary, do you help? =

Yes, send me a message and tell me what you would like and I will do my best to help you. (Paid service)

== Screenshots ==

1. Empty summary shows next to the form on laptop and under the form on mobile.
2. Summary is filled while making selections in the form.
3. Plugin settings page. Here you can turn the summary on or off.
4. Field settings. All supported fields have a checkbox to control visibility in the summary




== Changelog ==
= 1.2.10 =
* Fix: Replaced deprecated Gravity Forms `Currency` JS class with `gform.Currency` (deprecated since GF 2.9), removing console warnings. Backward compatible with older GF via fallback.

= 1.2.9 =
* Fix tags and short description notice
* Fix minimum WP version notice

= 1.2.8 = 
* Dev: Fixed PHP notice.

= 1.2.7 = 
* Improvement: Changed plugin title from GF Live summary Addon to Live Summary
* Improvement: Added new Upgrade banner with new PRO features

= 1.2.6 =
* Dev: changed a function name
* Fix: load styles on WP block editor preview
* Fix: When 2 forms on the same page, the summary of the wrong form could be removed upon submission of the second form
* Update Readme file

= 1.2.5 =
* Fix: Plugin icon showed too big in Safari browser. Added CSS fix.

= 1.2.4 =
* Dev: Improved init process of summary to make sure it works when there are multiple forms on one page

= 1.2.3 =
* Improvement: Added message when there are no fields selected for the summary

= 1.2.2 = 
* Dev: Added support for capabilities. 

= 1.2.1.1 =
* Dev: Added filter "gotrgf_change_summary_items_order" to change the order of items in the summary

= 1.2.1 =
* Bug: Fix wrong spinner url

= 1.2 =
* Improvement: Redesign total area
* Improvement: Added Preloader when summary updates
* Bug: Fixed hook 'gotrgf_after_summary_lines' not working
* Dev: Added new hook to filter the backend tooltips of the plugin
* Dev: Added new hook to change the preloader image. Hookname: gotrgf_change_preloader_image_url

= 1.1.9 =
* Bug: Fix problem with Gravity Summary field when the sidebar summary was never used on the form

= 1.1.8 =
* Bug: Fix 3 PHP notices on forms without a summary

= 1.1.7 =
* feature: Added new filter to change nothing selected text (gotrgf_change_nothing_selected_text)

= 1.1.6 =
* Bug: Fixed product option showing if no value was entered (placeholders)
* Enhancement: Added css selectors to labels(gotrgf_unit_label) and unit prices (gotrgf_unit_price)

= 1.1.5 =
* Fix: Fixed Option field now showing in summary with radio/checkbox type
* Enhancement: Custom settings icon added
* dev: Better way to localize ajaxurl variable

= 1.1.4 =
* Feature: Added support for the new Summary Field included with the Pro version

= 1.1.3 =
* Enhancement: Plugin frontend styles and scripts now only load on pages with a form that has the summary enabled.
* Bug: Fixed javascript error when adding entries to a GP nested form
* Bug: Fixed banner width on settings page

= 1.1.2 =
* Bug: Fixed dateformat not properly formatted when selecting date type other than datepicker
* Bug: Fixed Total displayed wrongly with certain currencies using commas
* Misc: Added Pro Banner on settings page

= 1.1.1 =
* Bug: Fixed total not updating when adding/removing coupon

= 1.1 =
* Bug: Fixed empty summary on pageload in some cases.
* Feature: Summary now hides on confirmation page by default.
* Enhancement: Added own settings page, settings are removed from the form settings
* Enhancement: Added 7 hooks and 7 filters. Documentation coming soon.
* Removed: form settings js page as reduntant

= 1.0.8 = 
Changed name to Live Summary for Gravity Forms

= 1.0.7 =
* Feature: Added support for all shipping types
* Enhancement: Added unique id to the form container

= 1.0.6 =
* Enhancement: Added unique ids to each line item inside the summary
* Bug: Fixed error in console on pages without a form

= 1.0.5 =
* Feature: Added i18n support (plugin can be translated)
* Bug: Fixed missing tooltip in field settings

= 1.0.4 =
* Bug: Fixed missing css when some field types were used separately. 

= 1.0.3 =
* Bug: Fixed Fatal Error when products found 

= 1.0.2 =
* First Public Release (16-12-2021)


== Upgrade Notice ==



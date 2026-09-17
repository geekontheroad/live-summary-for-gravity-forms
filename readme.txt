=== Live Summary for Gravity Forms ===
Contributors: geekontheroad
Donate link: https://geekontheroad.com/donate
Tags: gravity-forms, order-summary, order-form, form-preview, submission-preview
Requires at least: 6.5
Tested up to: 7.1
Stable tag: 1.2.10
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Show a live summary or order preview next to any Gravity Form. Updates as users type. No coding required.

== Description ==

Long forms and order forms lose people. Live Summary for Gravity Forms shows users exactly what they have filled in, updating in real time as they go, so they can check their answers before they submit.

Add a summary panel beside any Gravity Form in a few clicks. Tick the fields you want to include and the summary builds itself, including product lines, quantities and a running total.

[Try the live demo](https://gravitysummary.geekontheroad.com/?utm_source=wporg&utm_medium=readme&utm_campaign=demo)

= What the free version gives you =

* A live summary panel beside any Gravity Form
* Choose exactly which fields appear, per form
* Updates instantly as people type and select
* A running total on forms with product fields
* Respects your form's conditional logic, so hidden fields drop out of the summary automatically
* Works with every currency Gravity Forms supports
* Built on the official Gravity Forms Add-On Framework
* No coding required

= Perfect for =

* Order forms and product configurators
* Quote and estimate requests
* Booking and reservation forms
* Long multi-page forms where people need to review their answers
* Any form where mistakes are expensive

= Supported fields =

The free version supports Single Line Text, Paragraph Text, Drop Down, Number, Checkboxes, Radio Buttons, Name, Date, Time, Phone, Address, Website, Username, Email, Multi Select, Product, Total and Shipping.

More field types are supported in the Pro version.

= Pro version =

Pro turns the summary into a fully designed part of your form:

* Style presets - Modern, Minimal, Receipt and Dark, plus your own saved presets
* Full design control from the UI: fonts, colours, borders, spacing and shadows
* Drag-and-drop field selector with reordering
* The Live Summary field - place one or more summaries anywhere inside the form
* {live_summary} merge tag for confirmations and notifications
* Sticky summary that follows the user while scrolling
* Mobile summary bar with a tap-to-open sheet
* Collapsible page groups with edit links back to earlier steps
* Grouped product table with a detailed product and options breakdown
* Subtotal, tax, shipping, coupons and discounts
* EU VAT support, including reverse charge
* Works with GP eCommerce Fields, Jetsloth Image Choices, GravityView, Gravity PDF and Gravity Flow
* Prefix and suffix per field, and FontAwesome icons
* Copy settings between forms
* 20+ developer hooks

[Get the Pro version](https://geekontheroad.com/live-summary-for-gravity-forms/?utm_source=wporg&utm_medium=readme&utm_campaign=pro)

= Other plugins from the same developer =

* [EU VAT for Gravity Forms](https://geekontheroad.com/eu-vat-for-gravity-forms/?utm_source=wporg&utm_medium=readme&utm_campaign=crosssell) - calculate and validate European VAT in Gravity Forms
* [Coinbase Commerce for Gravity Forms](https://geekontheroad.com/coinbase-commerce-for-gravity-forms/?utm_source=wporg&utm_medium=readme&utm_campaign=crosssell) - accept cryptocurrency payments with Coinbase Commerce

= Feature requests and bugs =

Have an idea, or found a bug? Tell me in the support forum before leaving a review. Chances are high I can fix it.

= Hire me =

Looking for someone to build something Gravity Forms related? I have years of experience with Gravity Forms and much longer with code. Find out more at [geekontheroad.com](https://geekontheroad.com/?utm_source=wporg&utm_medium=readme&utm_campaign=hireme).

Compatible with Gravity Forms 2.5 and up.

== Installation ==

1. Install and activate the plugin.
2. Go to Forms > Settings > Live Summary.
3. Switch on "Turn on summary".
4. Edit the form and tick "Show in summary" on each field you want to include. You will find it in the field's General settings.
5. The summary now appears beside your form.
6. Optional: to show a running total, add at least one Product field and switch on "Show total in summary" on the same settings page.

== Frequently Asked Questions ==

= I installed the plugin but I don't see a summary =

The summary is turned on per form. Go to Forms > Settings > Live Summary and switch on "Turn on summary".

= The summary shows but stays empty =

Turning the summary on is only half of it. You also choose which fields appear. Edit each field you want included and tick "Show in summary" in its General settings.

= I don't see a total in the summary =

Two things are needed. The form must contain at least one Product field, and "Show total in summary" must be switched on in Forms > Settings > Live Summary.

= Does the summary respect conditional logic? =

Yes. Fields hidden by conditional logic are removed from the summary automatically and come back when the field is shown again.

= Does it work with products, quantities and totals? =

Yes. Product, Option and Shipping fields appear with their quantities and prices, and the summary keeps a running total in your form's currency.

= Can I show the summary inside the form instead of beside it? =

That is a Pro feature. Pro adds a Live Summary field you can drop anywhere in the form, and you can use more than one per form. [See the Pro version](https://geekontheroad.com/live-summary-for-gravity-forms/?utm_source=wporg&utm_medium=readme&utm_campaign=faq).

= Can I show the choice label instead of the stored value? =

For drop downs, radio buttons and checkboxes this is a Pro setting. [See the Pro version](https://geekontheroad.com/live-summary-for-gravity-forms/?utm_source=wporg&utm_medium=readme&utm_campaign=faq).

= Can I hide the summary on certain steps of a multi-page form? =

That is a Pro setting. Pro lets you choose exactly which steps show the summary. [See the Pro version](https://geekontheroad.com/live-summary-for-gravity-forms/?utm_source=wporg&utm_medium=readme&utm_campaign=faq).

= Can I style the summary? =

The free version gives every summary line its own ID so you can target it with your own CSS. Pro adds full styling from the UI, including fonts, colours, borders, spacing and ready-made style presets.

= Can I put the summary in a confirmation or notification email? =

That is a Pro feature, using the {live_summary} merge tag. [See the Pro version](https://geekontheroad.com/live-summary-for-gravity-forms/?utm_source=wporg&utm_medium=readme&utm_campaign=faq).

= Can you change the output for me? =

Yes, tell me what you need and I will do my best to help. This is a paid service.

== Screenshots ==

1. A live order summary with a running total, updating as people make their choices.
2. The summary builds itself as the form is filled in.
3. The summary carries across every step of a multi-page form, including names, addresses and shipping.
4. Turn the summary on per form. No coding required.
5. Tick one box on any supported field to include it in the summary.
6. Pro: style the summary to match your site, and group it into collapsible sections with edit links back to earlier steps.
7. On mobile the free version places the summary under the form. Pro adds a summary bar with a tap to open sheet.

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
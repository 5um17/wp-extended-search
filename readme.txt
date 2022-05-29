=== WP Extended Search ===
Contributors: 5um17
Tags: search, postmeta, taxonomy, advance search, category search, page search, tag search, author search, search forms, woocommerce search
Requires at least: 4.0
Tested up to: 6.0
Requires PHP: 7.0
Stable tag: 2.1.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Extend search functionality to search in selected post meta, taxonomies, post types, and all authors.

== Description ==
WP Extended Search is a lightweight and powerful search plugin.

With this plugin you can customize WordPress search to search in Post Meta, Author names, Categories, Tags, or Custom Taxonomies via admin settings. You can control the default behavior of WordPress to search in the post title, post content, and post excerpt.

Apart from customizing default search behavior, you can create multiple search settings, and then you can assign these settings to custom search forms.
For custom search forms, you have the option to choose from a widget, shortcode, PHP function, or HTML.

= Features =

* Search in selected meta keys
* Search in category, tags or custom taxonomies
* Search in the post author name
* Include or exclude any public post type
* Control whether to search in title or content or excerpt or all
* Compatible with WooCommerce. Search in product SKU, Attributes, and custom fields, etc.
* Create unlimited search settings to use with custom search forms.
* Add search forms using a widget, shortcode or PHP function. Also works with custom searchform.php
* Exclude old content from search results (Older than admin specified date)
* Customize the number of posts to display on the search results page
* Customize SQL relation (AND/OR) between search terms
* Customize order of search results
* Control whether to match search query exactly or partially
* Limit attachment results by mime type e.g. display only pdf files in search results.
* Translation ready
* Compatible with WPML

= Links =

* [Complete documentation](https://wpes.secretsofgeeks.com/)
* [GitHub repository](https://github.com/5um17/wp-extended-search)

== Installation ==

* Install WP Extended Search from the 'Plugins' section in your dashboard (Plugins > Add New > Search for 'WP Extended Search').
  Or
  Download WP Extended Search and upload it to your webserver via your FTP application. The WordPress codex contains [instructions on how to do this here](https://wordpress.org/support/article/managing-plugins/#manual-plugin-installation-1).
* Activate the plugin and navigate to (Settings > Extended Search) to choose your desired search settings.

== Frequently Asked Questions ==

= Is it compatible with WooCommerce? =

Yes, It is. When WooCommerce is active you will see a new checkbox in setting "Optimize for Products Search" select it and then the search results will use the default WooCommerce template or the template from your theme.

= How can I add a search form? =

You can use the Widget, Shortcode, and PHP function to display the search form. Also, you can add a hidden HTML field to your existing searchform.php template.

= Can I use my default search form widget? =

Yes, you can. You can modify the global setting to alter the results from the default search form. An extra search form is needed only when you would like to have more than one search criteria on the site.

= Can I use this plugin just to add a specific search form to my site and leave the WordPress default search as it is? =

Yes, you can. Go to the Search Settings and select Global in setting name, there you will see a button to disable the global search.
As global search is disabled now you can add a new search form with specific search criteria.

= How can I customize the search form? =

You can add custom CSS to the theme to style the search form. Or you can set CSS classes in the widget, shortcode, and PHP function.
Also, you can add a complete custom template for each search setting in the theme.

== Screenshots ==
1. WP Extented Search settings page
2. Add/Delete Settings
3. Edit setting name

== Upgrade Notice ==

= 2.0 =
2.0 went through code refactoring and major feature updates. Please make a complete site backup before upgrading.

== Changelog ==

= 2.1.1 - 2022-03-19 =
* Fixed recommendations notice issue with cache plugins.
* Minor fixes.

= 2.1 - 2022-03-13 =
* Added new feature to limit attachments by mime type.
* Fixed issue with add to cart link on search results page.
* Other minor improvements.

= 2.0.3 - 2021-08-04 =
* Fixed search issue in WC settings > Advanced tab
* Fixed fatal error with WP 5.8 on new widget screen (Still not compatible with block-based widgets.)

= 2.0.2 - 2021-04-11 =
* Fixed issue with WooCommerce pages.
* Fixed issue with backend REST requests.
* Added Select2 for admin pages.

= 2.0.1 - 2020-12-18 =
* Fixed errors in dashboard for non-admin users.

= 2.0 - 2020-12-11 =
**Upgrade Notice**
*2.0 went through code refactoring and major feature updates. Please make a complete site backup before upgrading.*

* Added new feature to add multiple search settings.
* Added WooCommerce product search support.
* Complete code refactoring.

= 1.3.1 - 2020-07-25 =
* Fixed issue with ACF relationship post field admin search.
* Fixed issue with Elementor admin search.
* Added support for new query variable [`disable_wpes`](https://github.com/5um17/wp-extended-search/issues/1#issuecomment-661307679) to disable WPES search. 

= 1.3 - 2019-10-21 =
* Fixed media search issue
* Fixed conflict with search when adding new menu item in backend
* Added setting link in plugin action links
* Added feature to modify search results order
* Added feature to match search query exactly or partially

= 1.2 - 2018-08-17 =
* Fixed bbPress forum posts dissappear
* Fixed issue with Ajax calls
* Added feature to control search in post excerpt
* Added compatibility with WPML
* Added new filter wp_es_terms_relation_type
* Updated wpes_posts_search filter. Now you can access $wp_query object as a second argument

= 1.1.2 - 2016-06-06 =
* Fixed media library search is not working when plugin is active. Thanks @gazettco
* Fixed escaping issue in SQL query. Thanks again @brurez
* Dropped support of WP version older than 4.0

= 1.1.1 - 2016-01-26 =
* Added feature to support Ajax calls
* Fixed text domain issue
* Fixed `suppress_filters` issue. Thanks @brurez

= 1.1 - 2015-06-21 =
* Added feature to search in post author name
* Added feature to customize number of posts per search results page
* Added feature to control SQL query relation between search terms
* Added new filters in settings wpes_meta_keys, wpes_tax and wpes_post_types

= 1.0.2 - 2015-01-11 =
* Added support for post_type parameter in URL
* Exclude old content from search results

= 1.0.1 - 2014-10-03 =
* Fixed taxonomy table join issue
* Added new filters wpes_meta_keys_query, wpes_tax_args, wpes_post_types_args, wpes_enabled, wpes_posts_search
* Internationalized plugin.

= 1.0 - 2014-09-14 =
* First Release
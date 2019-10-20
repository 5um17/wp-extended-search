=== WP Extended Search ===
Contributors: 5um17
Tags: search, postmeta, taxonomy, advance search, category search, page search, tag search, author search, search results, posts per page, exact search
Requires at least: 4.0
Tested up to: 5.2.4
Stable tag: 1.3
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Extend default search to search in selected post meta, taxonomies, post types and all authors.

== Description ==
Control WordPress default search to search in Post Meta, Categories, Tags or Custom Taxonomies via admin settings. Admin can select meta keys to search in, also can control the default behavior of search in post title or post content. 
 
You can include or exclude post types to appear in search results.

= Features =

* Search in selected meta keys
* Search in selected in-built or custom taxonomies
* Search in post author name
* Include or exclude any public post type
* Control whether to search in title or content or excerpt or all
* Exclude old content from search results (Older than admin specified date)
* Customize the number of posts to display on search results page
* Customize SQL relation (AND/OR) between search terms
* Customize order of search results
* Control whether to match search query exactly or partially
* Translation ready
* Compatible with WPML

= Links =

* [Complete documentation](https://www.secretsofgeeks.com/2014/09/wordpress-search-tags-and-categories.html)
* [GitHub repository](https://github.com/5um17/wp-extended-search)

== Installation ==

* Install WP Extended Search from the 'Plugins' section in your dashboard (Plugins > Add New > Search for 'WP Extended Search').
  Or
  Download WP Extended Search and upload it to your webserver via your FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).
* Activate the plugin and navigate to (Settings > Extended Search) to choose your desired search settings.

== Frequently Asked Questions ==

= Do you have any question? =

Please use plugin [support forum](https://wordpress.org/support/plugin/wp-extended-search) 

== Screenshots ==
1. WP Extented Search settings page

== Changelog ==

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
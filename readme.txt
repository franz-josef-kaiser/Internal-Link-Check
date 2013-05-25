=== Plugin Name ===
Plugin Name:		Internal Link Checker
Plugin URI:		http://unserkaiser.com/plugins/internal-link-checker/
Author:			Franz Josef Kaiser, Patrick Matsumura, Rodolfo Buaiz
Author URI:		http://unserkaiser.com/
Tags:			admin, link, links, meta, box, meta_box, missing, blogroll, broken, maintenance, posts, 404
Requires at least:	3.1
Tested up to:		3.4
Stable tag:		1.1

Adds a meta box to the post edit screen that shows all internal links from other posts/pages/post-types to the currently displayed post.

== Description ==
= Internal Link Checker =

Adds a meta box to the post edit screen that shows all internal links from other posts/pages/post-types to the currently displayed post. The plugin works out of the box.


== Installation ==

No explanation needed - works out of the box. Just activate and be safe.


== Frequently Asked Questions ==

= How-to =

By default, the plugin gets this columns from <code>wp_posts</code> table: <code>ID, post_title, post_date, post_content, post_type</code>.
And its Meta Box displays the following: ID, Title and Date.
In case you want to extend its functionality you can use the available hooks

== Master filter ==

Used to modify the SQL query, add the meta box to other post types, add columns to the meta box and set the sortable ones
<pre>
function internal_links_master_function( $args, $context )
{
	switch ( $context )
	{
		# Modify the columns retrived from the database
		case 'sql':
			return array( 'ID', 'post_title', 'post_date', 'post_content', 'post_excerpt', 'post_type' );
		break;
		# Add Meta Box to other post types
		case 'metabox':
			return array( 'post', 'page' );
		break;
		# Add columns to the metabox
		case 'columns':
			$columns = array(
				 'ID' => 'ID'
				,'post_title' => 'Título'
				,'post_date'  => 'Fecha'
				,'post_type'  => 'Tipo'
				,'post_excerpt'  => 'Resumo'
			);
			return $columns;
		break;
		# Set Meta Box sortable columns
		case 'sortables':
			$sortable = array(
				 'ID' => array( 'ID', true )
				,'post_title'  => array( 'post_date', true )
				,'post_date'  => array( 'post_date', true )
				,'post_type'  => array( 'post_type', true )
			);
			return $sortable;
		break;
	}
	return $args;
}

function internal_links_apply_filter()
{
	add_filter( 'internal_links_master_filter', 'internal_links_master_function', 10, 2 );
}
add_action( 'admin_init', 'internal_links_apply_filter', 10 );
</pre>

== Modify posts per page == 

<pre>
function posts_per_page_ilc($number) 
{
    return 8;
}
add_filter( 'internal_links_per_page', 'posts_per_page_ilc' );
</pre>


= Languages =

Translation ready.
If you want to help translating, please contact me on G+.

Included:
EN/DE (Patrick Matsumura)
pt_BR/es_ES (Rodolfo Buaiz)

== Screenshots ==

1. meta box in post edit admin UI screen


== Changelog ==

= v0.1 =
First version

= v0.1.1 =
Should now work with post titles & post slugs/names

= v0.1.2 =
Meta box content now displays inside an unordered list

= v0.1.3 =
Added screenshot, readme.txt for wp.org repo & fixed not echoing if there are no links

= v0.2 =
Moved to class to clean up global namespace
Added next step for translations

= v0.2.1 =
Minor fixes
Added translation
props Patrick Matsumura

= v0.2.2 =
Added authors file
Made links unique and minor fixes

= v0.2.4 =
Added MarkUp & MarkUp filter functions that work based on an initial settings array
Can now be easier extended or used on front

= v0.2.5 =
Added auto-correction for li elements & container

= v0.2.6 =
Added singular/plural translation strings
Grouped results by post type column

= v0.2.6.1 =
Reworked translation files

= v0.2.6.2 =
Avoids loading on every $_REQUEST. Now only loads on post.php in admin (post/page/cpt new/edit) screens

= v0.2.6.3 =
Added mu-plugins directory for l10n lang files loading

= v0.2.6.4 =
Changed meta box title to "Internal Links" to not have long titles in screen options tab

= v0.2.6.5 =
Added wrapper function to call plugin data

= v0.2.6.6 =
Moved Text Domain to plugin header comment for easier maintainance and transparency

= v0.2.7 =
Added native WP admin UI table to the meta box

= v0.2.7.1 =
Moved to admin edit post permalinks

= v0.2.8 =
Added better update messages for plugin list screen

= v0.2.9 =
Check for remote readme file availibility before get file contents on update
The meta box content filter disappeared and was replaced by an action to override the content

= v0.3 =
Final stable release with new WP Admin Tables class API in use. Typo & Language fixes, fully translated

= v0.4 =
Now supports pagination for the meta box to take less space in the UI in cases where there are more than just a few links

= v0.5 =
Now supports sorting by column

= v0.5.1 =
Separate textdomain function

= v0.5.2 =
Prepare SQL

= v0.5.3 =
Code styling

= v0.5.4 =
Empty posts fix

= v0.6 =
Major improvements in code length

= v0.6.1 =
Shortened admin table class

= v1.1 =
Fully tested and stable.
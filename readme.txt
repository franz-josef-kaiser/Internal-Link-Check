=== Plugin Name ===
Plugin Name:		Internal Link Checker
Plugin URI:			https://github.com/franz-josef-kaiser/Internal-Link-Check
Author:				Franz Josef Kaiser, Patrick Matsumura
Author URI:			https://plus.google.com/u/0/107110219316412982437
Tags:				admin, link, links, meta, box, meta_box, missing, 404 
Requires at least:	0.x
Tested up to:		3.3
Stable tag:			0.2.2

== Description ==
= Internal Link Checker =

Adds a meta box to the post edit screen that shows all internal links from other posts to the currently displayed post. This way you can easily check if you should fix links before deleting a post. There are no options needed. The plugin works out of the box.


== Installation ==

No explanation needed - works out of the box. Just activate and be safe.

== Frequently Asked Questions ==

= How-to =

This shows how to modify the output inside the internal link checker meta box (in case you want to extend its functionality):

<pre>
function modify_check_link_meta_box_content( $result, $links )
{
	global $post;

	// Uncomment the follwing line to see what the $links array contains
	// The links array contains all posts (and their respective data) that link to the current post
	/*
	echo '<'.'pre>';
		print_r( $links );
	echo '<'.'/pre>';
	 */

	// Now handle the result:
	foreach ( $result as $link )
	{
		// do stuff
	}

	return $result;
}
add_filter( 'internal_links_meta_box', 'modify_check_link_meta_box_content', 10, 2 );
</pre>

= Languages =

Translation ready.
If you want to help translating, please contact me on G+.

Included:
EN/DE (Patrick Matsumura)

== Changelog ==

= v0.1. =
First version

= v0.1.1. =
Should now work with post titles & post slugs/names.

= v0.1.2. =
Meta box content now displays inside an unordered list.

= v0.1.3. =
Added screenshot, readme.txt for wp.org repo & fixed not echoing if there are no links.

= v0.2. =
Moved to class to clean up global namespace.
Added next step for translations.

= v0.2.1 =
Minor fixes.
Added translation.
props Patrick Matsumura

= v0.2.2 =
Added authors file.
Made links unique and minor fixes.

== Screenshots ==

1. meta box in post edit admin UI screen

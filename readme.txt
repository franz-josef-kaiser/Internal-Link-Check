=== Plugin Name ===
Plugin Name:		Internal Link Checker
Plugin URI:			https://github.com/franz-josef-kaiser/Internal-Link-Check
Author:				Franz Josef Kaiser
Author URI:			https://github.com/franz-josef-kaiser
Tags:				wptouch, iphone, ipod, theme, apple, mac, bravenewcode, ajax, mobile, android, blackberry, smartphone, 
Requires at least:	0.x
Tested up to:		3.1.1
Stable tag:			0.1.2.

= Internal Link Checker =

Adds a meta box to the post edit screen that shows all internal links from other posts to the currently displayed post. This way you can easily check if you should fix links before deleting a post. There are no options needed. The plugin works out of the box.


== Installation ==

No explanation needed - works out of the box. Just activate and be save.

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

Translation ready

== Changelog ==

= v0.1 =
first version

= v0.1.1 =
should now work with post titles & post slugs/names

= v0.1.2 =
meta box content now displays inside an unordered list

== Screenshots ==

1. meta box in post edit admin UI screen

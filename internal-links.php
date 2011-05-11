<?php
/*
Plugin Name:	Internal links check
Plugin URI:		https://github.com/franz-josef-kaiser/Internal-Link-Check
Description:	Adds a meta box to the post edit screen that shows all internal links from other posts to the currently displayed post. This way you can easily check if you should fix links before deleting a post. There are no options needed. The plugin works out of the box.
Author:			Franz Josef Kaiser
Author URI: 	https://github.com/franz-josef-kaiser
Version:		0.1.1
License:		GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html

	(c) Copyright 2012 - 201X by AUTHOR

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
*/

// Secure: doesn't allow to load this file directly
if( ! class_exists('WP') ) 
{
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

if ( ! defined ( 'LINKCHECKER_TEXTDOMAIN' ) )
	define( 'LINKCHECKER_TEXTDOMAIN', 'linkchecker_textdomain' );

	/**
	 * Adds the meta box to the post edit screen
	 */
	function check_post_links_meta_box()
	{
		add_meta_box( 
			'',
			__( 'Posts linking to this posts internally:', LINKCHECKER_TEXTDOMAIN ),
			'check_post_links_meta_box_cb',
			'post' 
	    );
	}
	add_action( 'add_meta_boxes', 'check_post_links_meta_box' );

	/**
	 * Adds the meta box content that displays the post links
	 */
	function check_post_links_meta_box_cb()
	{
		global $wpdb, $post;

		$links = $wpdb->get_results( "
			SELECT ID, post_title, post_date, post_content 
			FROM wp_posts 
			WHERE post_content 
			LIKE '%".$post->post_title."%' OR '%".$post->post_name."%' 
			ORDER BY post_date
		" );

		$result = array();
		if ( $links )
		{
			foreach( $links as $link )
				$result[] = '<a href="'.get_permalink( $post->ID ).'">'.$link->post_title.'</a>';
		}
		else 
		{
			__( 'No posts are linking to this post.', LINKCHECKER_TEXTDOMAIN );
			return;
		}

		// Filter the result or add anything
		$result = apply_filters( 'internal_links_meta_box', $result, $links );
		
		if ( $result )
		{
			echo '<ul>';
				foreach ( $result as $link )
				{
					echo '<li>'.$link.'</li>';
				}
			echo '</ul>';
		}
	}
	add_action( 'add_meta_boxes', 'check_post_links_meta_box' );
?>
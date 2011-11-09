<?php
/*
Plugin Name:	Internal links check
Plugin URI:		https://github.com/franz-josef-kaiser/Internal-Link-Check
Description:	Adds a meta box to the post edit screen that shows all internal links from other posts to the currently displayed post. This way you can easily check if you should fix links before deleting a post. There are no options needed. The plugin works out of the box.
Author:			Franz Josef Kaiser
Author URI: 	https://github.com/franz-josef-kaiser
Version:		0.2.4
License:		GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html

	(c) Copyright 2010 - 2011 by Franz Josef Kaiser

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

// Secure: don't allow to load this file directly
if( ! class_exists( 'WP' ) ) 
{
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

// init class
if ( is_admin() )
	add_action( 'init', array( 'oxoLinkCheck', 'init' ) );

if ( ! class_exists( 'oxoLinkCheck' ) )
{

/**
 * @author Franz Josef Kaiser
 */
class oxoLinkCheck
{
	/**
	 * Settings
	 * @var (array)
	 */
	public $settings = array(
		 'element'			=> 'li'
		,'element_class'	=> ''
		,'container'		=> 'ul'
		,'container_class'	=> ''
		,'nofollow'			=> true
		,'echo'				=> true
	);

	/**
	 * Counter var for linkin posts
	 * @var (integer)
	 */
	public $counter;

	/**
	 * Container for sql result
	 * @var (array)
	 */
	public $sql_result;

	/**
	 * Constant for translation .po/.mo files
	 * @var (string)
	 */
	const TEXTDOMAIN = 'ilc';


	/**
	 * Init - calls the class
	 * @return void
	 */
	static public function init()
	{
		$class = __CLASS__ ;

		// Class available in global scope
		if ( empty ( $GLOBALS[ $class ] ) )
			$GLOBALS[ $class ] = new $class;
		
		$dir = basename( dirname( __FILE__ ) );
		load_plugin_textdomain( self::TEXTDOMAIN, false, "{$dir}/lang" );			
	}


	/**
	 * Constructor
	 */
	public function __construct()
	{
		add_action( 'add_meta_boxes', array( &$this, 'add_meta_box' ) );
	}

	
	/**
	 * Adds the meta box to the post edit screen
	 *
	 * @return void
	 */
	function add_meta_box()
	{
		// do math first
		$this->get_sql_result();

		// add meta box
		add_meta_box( 
			'',
			sprintf( __( 'Posts linking to this post internally: %d', self::TEXTDOMAIN ), $this->counter ),
			array( &$this, 'meta_box_cb' ),
			'post' 
		);
	}


	/**
	 * SQL Query
	 * Adds content to two class vars: The resulting array & the counter
	 * @return (object) $links 
	 */
	public function get_sql_result()
	{
		// get_permalink() cares about rewrite rules
		$current_link = get_permalink( $GLOBALS['post']->ID );
		// sql
		$links = $GLOBALS['wpdb']->get_results( "
			SELECT ID, post_title, post_date, post_content 
			FROM {$GLOBALS['wpdb']->prefix}posts 
			WHERE post_content 
			LIKE '%{$current_link}%' 
			ORDER BY post_date
		" );

		// Counter for meta box title
		$this->counter = count( $links );

		return $this->sql_result = $links;
	}


	/**
	 * Meta Box callback function
	 * 
	 * @return (string) $output
	 */
	function meta_box_cb()
	{
		$links = $this->sql_result;

		if ( ! $links )
			return _e( 'No posts are linking to this post.', self::TEXTDOMAIN );

		$results = array();
		foreach( $links as $linkin_post )
		{
			$link = get_permalink( $linkin_post->ID );
			// If already in array: short circuit via ID
			# if ( in_array( $linkin_post->ID, array_keys( $result ), true ) )
				# continue;
			# @todo maybe sort by post type
			# $result[ $linkin_post->post_type ][ $linkin_post->ID ] = "<a href='{$link}'>{$linkin_post->post_title}</a>";
			$results[ $linkin_post->ID ] = "<a href='{$link}'%nofollow%>{$linkin_post->post_title}</a>";
		}

		// Filter the result or add anything
		$results = apply_filters( 'internal_links_meta_box', $results, $links );

		return $this->markup( $results );
	}


	/**
	 * Builds the markup
	 * 
	 * @uses markup_filter()
	 * @param (array) $results | SQL Query results ordered
	 * @return (string) $output | Html markup
	 */
	public function markup( $results )
	{
		$output = '';
		foreach ( $results as $link )
		{
			$output .= "<%el%%el_class%>{$link}</%el%>";
		}

		if ( $this->settings['container'] )
		{
			$output = "<%container%%container_class%>{$output}</%container%>";
		}

		$output = $this->markup_filter( $output );

		# >>>> return
		if ( $this->settings['echo'] )
			return print $output;

		return $output;
	}


	/**
	 * Replaces markup placeholders
	 * 
	 * @param (string) $input
	 * @return (string) $markup
	 */
	public function markup_filter( $input )
	{
		$markup = strtr( 
			 $input
			,array(
			 	 '%el%'					=> $this->settings['element']
				 // auto correct wrong container types for <li> elements to <ul>
				,'%container%'			=> 'li' === $this->settings['element']	? 'ul' : $this->settings['container']
				,'%el_class%'			=> $this->settings['element_class']		? " class='{$this->settings['element_class']}'" : ''
				,'%container_class%'	=> $this->settings['container_class']	? " class='{$this->settings['container_class']}'" : ''
				,'%nofollow%'			=> $this->settings['nofollow']			? ' rel="nofollow"' : ''
			) 
		);

		return $markup;
	}
} // END Class oxoLinkCheck

} // endif;
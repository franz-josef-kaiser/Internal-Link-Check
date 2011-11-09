<?php
/*
Plugin Name:	Internal links check
Plugin URI:		https://github.com/franz-josef-kaiser/Internal-Link-Check
Description:	Adds a meta box to the post edit screen that shows all internal links from other posts to the currently displayed post. This way you can easily check if you should fix links before deleting a post. There are no options needed. The plugin works out of the box.
Author:			Franz Josef Kaiser, Patrick Matsumura
Author URI: 	https://plus.google.com/u/0/107110219316412982437
Version:		0.2.6.5
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
add_action( 'init', array( 'oxoLinkCheck', 'init' ) );

if ( ! class_exists( 'oxoLinkCheck' ) )
{

/**
 * @author Franz Josef Kaiser
 * 
 * translation tutorial
 * @link http://wordpress.stackexchange.com/questions/33312/how-to-translate-plural-forms-for-themes-plugins-with-poedit/33314#33314
 */
class oxoLinkCheck
{
	/**
	 * Settings
	 * @var (array)
	 */
	public $args = array(
		 'element'			=> 'li'
		,'element_class'	=> ''
		 // Att.: <ol> will be auto converted to <ul> 
		,'container'		=> ''
		,'container_class'	=> ''
		,'nofollow'			=> false
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
	public $sql_results;

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
		if ( is_admin() )
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
		$this->get_sql_results();

		// add meta box
		add_meta_box( 
			 'link-check'
			,sprintf( 
				 _n(
					 'One post linking to this post internally'
					,'Posts linking to this post internally: %s'
					,$this->counter
					,self::TEXTDOMAIN 
				 )
				,zeroise( number_format_i18n( $this->counter ), 2 )
			 )
			,array( &$this, 'output' )
			,'post' 
		);
	}


	/**
	 * SQL Query
	 * Adds content to two class vars: The resulting array & the counter
	 * 
	 * @return (object) $links 
	 */
	public function get_sql_results()
	{
		// get_permalink() cares about rewrite rules
		$current_link = get_permalink( $GLOBALS['post']->ID );
		// SQL: newest first
		$links = $GLOBALS['wpdb']->get_results( "
			SELECT ID, post_title, post_date, post_content, post_type 
			FROM {$GLOBALS['wpdb']->prefix}posts 
			WHERE post_content 
			LIKE '%{$current_link}%' 
			ORDER BY post_date DESC
		" );

		// Counter for meta box title
		$this->counter = count( $links );

		return $this->sql_results = $links;
	}


	/**
	 * Builds the output
	 * Also used as meta box callback function
	 * 
	 * @uses markup()
	 * @return (string) $output
	 */
	function output()
	{
		if ( ! $this->sql_results )
			return _e( 'No posts are linking to this post.', self::TEXTDOMAIN );

		$results = array();
		foreach( $this->sql_results as $post )
		{
			$link		= get_permalink( $post->ID );
			// If no title was set: we care about it
			$no_title	= __( 'No title set', self::TEXTDOMAIN );
			$title		= $post->post_title ? $post->post_title : "<em>{$no_title}</em>";
			// Add to results array
			$results[ $post->post_type ][ $post->ID ] = "<a href='{$link}'>{$title}</a>";
		}

		// Filter the result or add anything
		$results = apply_filters( 'internal_links_meta_box', $results, $this->sql_results );

		// Build markup
		$output = '';
		foreach ( $results as $name => $posts )
		{
			$name	 = _n( ucfirst( $name ), ucfirst( $name ).'s', count( $posts ), self::TEXTDOMAIN );
			$output .= "<h4>{$name}:</h4>";
			$output .= $this->markup( $posts );
		}

		# >>>> return
		if ( $this->args['echo'] )
			return print $output;

		return $output;
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

		// In case someone forgot to set a container if the choosen element is 'li'
		if ( $this->args['container'] OR 'li' === $this->args['element'] )
		{
			$output  = "<%container%%container_class%>{$output}</%container%>";
		}

		$output = $this->markup_filter( $output );

		return $output;
	}


	/**
	 * Replaces markup placeholders
	 * Deletes placeholders if the settings array contains an empty string
	 * 
	 * @param (string) $input
	 * @return (string) $markup
	 */
	public function markup_filter( $input )
	{
		$markup = strtr( 
			 $input
			,array(
			 	 '%el%'					=> $this->args['element']
				 // auto correct wrong container types for <li> elements to <ul>
				,'%container%'			=> 'li' === $this->args['element']	? 'ul' : $this->args['container']
				,'%el_class%'			=> $this->args['element_class']		? " class='{$this->args['element_class']}'" : ''
				,'%container_class%'	=> $this->args['container_class']	? " class='{$this->args['container_class']}'" : ''
				,'%nofollow%'			=> $this->args['nofollow']			? ' rel="nofollow"' : ''
			) 
		);

		return $markup;
	}
} // END Class oxoLinkCheck

} // endif;
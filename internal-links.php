<?php
/*
Plugin Name:	Internal links check
Plugin URI:		https://github.com/franz-josef-kaiser/Internal-Link-Check
Description:	Adds a meta box to the post edit screen that shows all internal links from other posts to the currently displayed post. This way you can easily check if you should fix links before deleting a post. There are no options needed. The plugin works out of the box.
Author:			Franz Josef Kaiser, Patrick Matsumura
Author URI: 	https://plus.google.com/u/0/107110219316412982437
Version:		0.2.7.1
Text Domain:	ilc
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
add_action( 'init', array( 'ilcInit', 'init' ) );

if ( ! class_exists( 'ilcInit' ) )
{

/**
 * Factory
 * @author Franz Josef Kaiser
 * 
 * translation tutorial
 * @link http://wordpress.stackexchange.com/questions/33312/how-to-translate-plural-forms-for-themes-plugins-with-poedit/33314#33314
 */
class ilcInit
{
	/**
	 * Plugin Base directory
	 * @var (string)
	 */
	public static $dir;

	/**
	 * Relative Path from plugin root dir
	 * @var (string)
	 */
	public static $rel_path;

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
	 * Container for sql result
	 * @var (array)
	 */
	public $sql_results;


	/**
	 * Init
	 * Instantiates the class and loads translation files
	 * 
	 * @return void
	 */
	static public function init()
	{
		$class = __CLASS__ ;

		// Class available in global scope
		if ( empty ( $GLOBALS[ $class ] ) )
			$GLOBALS[ $class ] = new $class;

		// l10n translation files
		$dir		= basename( dirname( __FILE__ ) );
		// in plugins directory
		$l10n_file	= load_plugin_textdomain( 'ilc', false, "{$dir}/lang" );
		// in mu-plugins directory
		if ( ! $l10n_file )
			load_muplugin_textdomain( 'ilc', "{$dir}/lang" );
	}


	/**
	 * Constructor
	 * 
	 * @return void
	 */
	public function __construct()
	{
		# >>>> Class vars
		$this->dir			= plugin_dir_path( __FILE__ );
		$this->rel_path		= basename( dirname( __FILE__ ) );
		# <<<< Class vars

		if ( is_admin() )
		{
			// avoid loading on every admin $_REQUEST
			// abort if not on post.php (post/page/cpt edit/new) screens
			if ( 'post.php' !== $GLOBALS['pagenow'] )
				return;

			add_action( 'admin_init',		array( &$this, 'load_extensions' ) );
			add_action( 'add_meta_boxes',	array( &$this, 'add_meta_box' ) );
		}
	}


	/**
	 * Extension/File/Class loader
	 * 
	 * @return void
	 */
	public function load_extensions()
	{
		foreach ( array( 'admin_table' ) as $extension )
		{
			$file = "{$this->dir}/{$extension}.php";
			if ( is_readable( $file ) )
				include_once $file;
		}
	}


	/**
	 * Plugin Header Comment data
	 *
	 * @uses   get_plugin_data
	 * @param (string) $value | default = 'Version'; Valid: see Header Comment Block
	 * @return (string) 
	 */
	private function get_plugin_data( $value = 'Version' ) 
	{
		$plugin_data = get_plugin_data( __FILE__ );
		return $plugin_data[ $value ];
	}


	/**
	 * Wrapper for get_plugin_data()
	 * 
	 * @return (string) $textdomain
	 */
	public function get_textdomain() 
	{
		return $this->get_plugin_data( 'TextDomain' );
	}

	
	/**
	 * Adds the meta box to the post edit screen
	 *
	 * @return void
	 */
	public function add_meta_box()
	{
		// add meta box
		add_meta_box( 
			 'link-check'
			,__( 'Internal Links', $this->get_textdomain() )
			,array( &$this, 'load_table' )
			,'post' 
		);
	}


	/**
	 * Adds a native admin UI table
	 * Callback fn for add_meta_box()
	 * 
	 * @return void
	 */
	public function load_table()
	{
		// Display table
		$table = new ilcTable();
		$table->prepare_items();
		$table->display();

		// Display number of posts
		$count = count( $GLOBALS['wpdb']->last_result );
		printf( 
			 _n(
				 'One post linking to this post.'
				,'Posts linking to this post: %s'
				,$count
				,$this->get_textdomain() 
			 )
			,zeroise( number_format_i18n( $count ), 2 )
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
		return $GLOBALS['wpdb']->get_results( "
			SELECT ID, post_title, post_date, post_content, post_type 
			FROM {$GLOBALS['wpdb']->prefix}posts 
			WHERE post_content 
			LIKE '%{$current_link}%' 
			ORDER BY post_date DESC
		" );
	}


	public function the_sql_results()
	{
		return isset( $this->sql_results ) ? $this->sql_results : self::get_sql_results();
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
			return _e( 'No posts are linking to this post.', $this->get_textdomain() );

		# >>>> build links array sorted by post type
		$results = array();
		foreach( $this->sql_results as $post )
		{
			$link		= get_permalink( $post->ID );

			// If no title was set: we care about it
			$no_title	= __( 'No title set', $this->get_textdomain() );
			$title		= $post->post_title ? $post->post_title : "<em>{$no_title}</em>";

			$results[ $post->post_type ][ $post->ID ] = "<a href='{$link}'>{$title}</a>";
		}
		# <<<< build links array

		// Filter the result or add anything
		$results = apply_filters( 'internal_links_meta_box', $results, $this->sql_results );

		# >>>> markup
		foreach ( $results as $name => $posts )
		{
			$name	 = _n( ucfirst( $name ), ucfirst( $name ).'s', count( $posts ), $this->get_textdomain() );
			$output .= $this->markup( $posts );
		}
		# <<<< markup

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
} // END Class ilcInit

} // endif;
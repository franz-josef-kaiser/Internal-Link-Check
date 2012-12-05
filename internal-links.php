<?php
! defined( 'ABSPATH' ) AND exit;
/*
Plugin Name: Internal links check
Plugin URI:  https://github.com/franz-josef-kaiser/Internal-Link-Check
Description: Adds a meta box to the post edit screen that shows all internal links from other posts to the currently displayed post. This way you can easily check if you should fix links before deleting a post. There are no options needed. The plugin works out of the box.
Author:      Franz Josef Kaiser, Patrick Matsumura
Author URI:  https://unserkaiser.com
Version:     0.6.1
Text Domain: ilc
License:     GPL v2 @link http://www.gnu.org/licenses/old-licenses/gpl-2.0.html

	(c) Copyright 2010 - 2012 by Franz Josef Kaiser <mailto: office (a) unserkaiser.com>

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
*/



if ( ! class_exists( 'ilcInit' ) )
{
	// init class
	add_action( 'admin_init', array( 'ilcInit', 'init' ), 0 );

/**
 * Factory
 * 
 * @author     Franz Josef Kaiser <wecodemore@gmail.com>
 * @copyright  © Franz Josef Kaiser 2012
 * 
 * @since      0.1
 * 
 * @package    WordPress
 * @subpackage Internal Link Checker bootstrap
 * 
 * @license    GNU GPL 2
 */
class ilcInit
{
	/**
	 * Instance
	 * 
	 * @access protected
	 * @var    object
	 */
	static protected $instance;


	/**
	 * Used for update notices
	 * Fetches the readme file from the official plugin repo trunk.
	 * Adds to the "in_plugin_update_message-$file" hook
	 * 
	 * @access public
	 * @var    string
	 */
	public $remote_readme = 'http://plugins.trac.wordpress.org/browser/internal-link-checker/trunk/readme.txt?format=txt';


	/**
	 * Settings
	 * 
	 * @since  0.2.2
	 * @access public
	 * @var    array
	 */
	public $args = array(
		 'element'         => 'li'
		,'element_class'   => ''
		 // Att.: <ol> will be auto converted to <ul> 
		,'container'       => ''
		,'container_class' => ''
		,'nofollow'        => false
		,'echo'            => true
	);


	/**
	 * Container for SQL result
	 * 
	 * @since  0.2
	 * @access public
	 * @var    array
	 */
	public $sql_results;


	/**
	 * Sets the meta box name
	 * Used to determin in the extended WP_List_Table class
	 * in which context the meta box is. 
	 * Needed to determine if the whole UI should be shown
	 * 
	 * @since  0.6
	 * @access public
	 * @var    unknown_type
	 */
	public $meta_box_name = 'link-check';


	/**
	 * Init
	 * Instantiates the class and loads translation files
	 * 
	 * @since  0.2
	 * @return void
	 */
	static public function init()
	{
		null === self :: $instance AND self :: $instance = new self;
		return self :: $instance;
	}


	/**
	 * Constructor
	 * 
	 * @since  0.2
	 * @return void
	 */
	public function __construct()
	{
		global $pagenow;

		// Load translation file
		add_action( 'admin_init', array( $this, 'load_textdomain' ) );

		if ( is_admin() )
		{
			// Better update message
			if ( 'plugins.php' === $pagenow )
			{
				$hook  = "in_plugin_update_message-";
				$hook .= basename( __FILE__ )."/";
				$hook .= basename( dirname( __FILE__ ) );
				add_action( $hook, array( $this, 'update_message' ), 20, 2 );
			}
			// avoid loading on every admin $_REQUEST
			// abort if not on post.php (post/page/cpt edit/new) screens
			elseif ( 'post.php' === $pagenow )
			{
				add_action( 'admin_init',     array( $this, 'load_extensions' ) );
				add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
			}
		}
	}

	/**
	 * Load plugin translation
	 *
	 * @link   http://wordpress.stackexchange.com/a/33314 Translation Tutorial by the author
	 * @return void
	 */
	static function load_textdomain() 
	{
		global $locale;

		// l18n translation files
		$dir    = plugin_dir_path( __FILE__ );
		$domain = 'ilc';

		// in themes/plugins/mu-plugins directory
		load_textdomain( 
			 $domain
			,"{$dir}lang/{$domain}-{$locale}.mo"
		);
	}


	/**
	 * Extension/File/Class loader
	 * 
	 * @since  0.2.7
	 * @return void
	 */
	public function load_extensions()
	{
		$files = array( 
			 'admin_table' 
		);

		foreach ( $files as $extension )
		{
			$file = plugin_dir_path( __FILE__ )."/{$extension}.php";
			if ( is_readable( $file ) )
				include_once $file;
		}
	}


	/**
	 * Plugin Header Comment data
	 *
	 * @since  0.2.8
	 * @uses   get_plugin_data
	 * @param  string $value default = 'Version'; Valid: see Header Comment Block
	 * @return string $data
	 */
	static private function get_plugin_data( $value = 'Version', $mark_up = true ) 
	{
		$data = get_plugin_data( __FILE__, $mark_up );
		return $data[ $value ];
	}


	/**
	 * SQL Query
	 * Adds content to two class vars: The resulting array & the counter
	 * 
	 * @since  0.2
	 * @return object $links 
	 */
	public function get_sql_results()
	{
		global $wpdb;

		// get_permalink() cares about rewrite rules
		$current_link = get_permalink( $GLOBALS['post']->ID );
		// SQL: newest first
		$sql_results = $wpdb->get_results( 
			 $wpdb->prepare( "
				SELECT ID, post_title, post_date, post_content, post_type 
					FROM %s
				WHERE post_content 
					LIKE %s
				ORDER BY %s %s
			 " )
			,"{$wpdb->prefix}posts"
			,'%'.like_escape( $current_link ).'%'
			,$this->orderby
			,$this->order
		);

		return $sql_results;
	}


	/**
	 * Wrapper to return the sql results for the admin table class
	 * 
	 * @since  0.2.7
	 * @see    WP_List_Table::prepare_items()
	 * @return array $sql_results
	 */
	public function the_sql_results()
	{
		return isset( $this->sql_results ) ? $this->sql_results : self :: get_sql_results();
	}

	
	/**
	 * Adds the meta box to the post edit screen
	 *
	 * @since  0.2
	 * @return void
	 */
	public function add_meta_box()
	{
		add_meta_box( 
			 $this->meta_box_name
			,__( 'Internal Links', 'ilc' )
			,array( $this, 'load_table' )
			,'post' 
		);
	}


	/**
	 * Adds a native admin UI table
	 * Callback fn for add_meta_box()
	 * 
	 * @since  0.2 | renamed from meta_box_cb()
	 * @return void
	 */
	public function load_table()
	{
		// Action: Overrides the content of the meta box
		if ( has_action( 'internal_links_meta_box' ) )
			return do_action( 'internal_links_meta_box', $this->the_sql_results() );

		// Display table
		new ilcTable( $this->meta_box_name );
	}


	/**
	 * Builds the output
	 * 
	 * @since  0.2
	 * @uses   markup()
	 * @return string $output
	 */
	function output()
	{
		if ( ! $this->sql_results )
			return _e( 'No posts are linking to this post.', 'ilc' );

		# >>>> build links array sorted by post type
		$results = array();
		foreach( $this->sql_results as $post )
		{
			$link     = get_permalink( $post->ID );

			// If no title was set: we care about it
			$no_title = __( 'No title set', 'ilc' );
			$title    = $post->post_title ? $post->post_title : "<em>{$no_title}</em>";

			$results[ $post->post_type ][ $post->ID ] = "<a href='{$link}'>{$title}</a>";
		}
		# <<<< build links array

		// Filter the result or add anything
		$results = apply_filters( 'internal_links', $results, $this->sql_results );

		# >>>> markup
		foreach ( $results as $name => $posts )
		{
			$name = _n(
				 ucfirst( $name )
				,ucfirst( $name ).'s'
				,count( $posts )
				,'ilc'
			);
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
	 * @since  0.2
	 * @uses   markup_filter()
	 * @param  array $results SQL Query results ordered
	 * @return string $output Html markup
	 */
	public function markup( $results )
	{
		$output = '';
		foreach ( $results as $link )
		{
			$output .= "<%EL%%EL_CLASS%>{$link}</%EL%>";
		}

		// In case someone forgot to set a container if the choosen element is 'li'
		if ( ! $this->args['container'] OR 'li' === $this->args['element'] )
		{
			$output  = "<%CONTAINER%%CONTAINER_CLASS%>{$output}</%CONTAINER%>";
		}

		$output = $this->markup_filter( $output );

		return $output;
	}


	/**
	 * Replaces markup placeholders
	 * Deletes placeholders if the settings array contains an empty string
	 * 
	 * @since  0.2
	 * @param  string $input
	 * @return string $markup
	 */
	public function markup_filter( $input )
	{
		return strtr( 
			 $input
			,array(
			 	 '%EL%'              => $this->args['element']
				 // auto correct wrong container types for <li> elements to <ul>
				,'%CONTAINER%'       => 'li' === $this->args['element'] ? 'ul' : $this->args['container']
				,'%EL_CLASS%'        => $this->args['element_class']    ? " class='{$this->args['element_class']}'" : ''
				,'%CONTAINER_CLASS%' => $this->args['container_class']  ? " class='{$this->args['container_class']}'" : ''
				,'%NOFOLLOW%'        => $this->args['nofollow']         ? ' rel="nofollow"' : ''
			) 
		);
	}


	/* =============== Helper & other ================= */


	/**
	 * Displays an update message for plugin list screens.
	 * Shows only the version updates from the current until the newest version
	 * 
	 * @since  0.2.8
	 * @param  array  $plugin_data
	 * @param  object $r
	 * @return string $output
	 */
	public function update_message( $plugin_data, $r )
	{
		if ( ! is_readable( $this->remote_readme ) )
			return;

		// readme contents
		$data      = file_get_contents( $this->remote_readme );
		$changelog = stristr( $data, '== Changelog ==' );
		$changelog = stristr( $changelog, '== Screenshots ==', true );
		// only return for the current & later versions
		$curr_ver  = $this->get_plugin_data();
		$changelog = stristr( $changelog, "= v{$curr_ver}" );

		# >>>> output
		$output  = '<hr /><div style="font-weight: normal;">';
		$output .= sprintf( __( 
				 'The Update from %1$s to %2$s brings you the following new features, bug fixes and additions.'
				,'ilc' )
			,$curr_ver
			,$r->new_version 
		);
		$output .= "<pre>{$changelog}</pre>";
		$output .= sprintf( 
			 __( 
				 'You can also check the nightly builds of %1$sour development repository%2$s on GitHub. If you got ideas, feature request or want to help with pull requests, please feel free to do so on GitHub.%3$s'
				,'ilc'
			 )
			,'<a href="https://github.com/franz-josef-kaiser/Internal-Link-Check">'
			,'</a>'
			,'</div>'
		);
		# <<<< output

		return print $output;
	}
} // END Class ilcInit

} // endif;
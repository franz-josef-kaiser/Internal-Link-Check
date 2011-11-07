<?php
/*
Plugin Name:	Internal links check
Plugin URI:		https://github.com/franz-josef-kaiser/Internal-Link-Check
Description:	Adds a meta box to the post edit screen that shows all internal links from other posts to the currently displayed post. This way you can easily check if you should fix links before deleting a post. There are no options needed. The plugin works out of the box.
Author:			Franz Josef Kaiser
Author URI: 	https://github.com/franz-josef-kaiser
Version:		0.2
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
	// Counter var for linkin posts
	public $counter;

	// Container for sql result
	public $sql_result;

    // Constant for translation .po/.mo files
    const TEXTDOMAIN = 'linkchecker_textdomain';

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
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        add_action( 'admin_init', array( &$this, 'lang' ) );
        add_action( 'add_meta_boxes', array( &$this, 'add_meta_box' ) );
    }

    /**
     * Loads the translation files
     * 
     * @return void
     */
    function lang()
    {
        $dir	= get_stylesheet_directory();
    	$locale	= get_locale();
    	$file	= "{$dir}/lang/{$locale}.php";

    	// Translation
    	load_theme_textdomain( 'LANG', "{$dir}/lang" );

    	locate_template( $file, true );
    }


	/**
	 * Adds the meta box to the post edit screen
     
     * @return void
	 */
	function add_meta_box()
	{
		// do math first
		$this->get_sql_result();

		// add meta box
		add_meta_box( 
			'',
			sprintf( __( 'Posts linking to this posts internally: %d', self::TEXTDOMAIN ), $this->counter ),
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

		$result = array();
		if ( $links )
		{
			foreach( $links as $linkin_post )
			{
                # @todo Make links unique
                $link = get_permalink( $linkin_post->ID );
				$result[] = "<a href='$link'>{$linkin_post->post_title}</a>";
			}
		}
		else 
		{
			return _e( 'No posts are linking to this post.', self::TEXTDOMAIN );
		}

		// Filter the result or add anything
		$result = apply_filters( 'internal_links_meta_box', $result, $links );
		
		if ( $result )
		{
			$output = '<ul>';
				foreach ( $result as $link )
				{
					$output .= "<li>{$link}</li>";
				}
			$output .= '</ul>';
		}

		return print $output;
	}
} // END Class oxoLinkCheck

} // endif;
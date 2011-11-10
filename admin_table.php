<?php
// Secure: don't allow to load this file directly
if( ! class_exists( 'WP' ) ) 
{
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}




/**
 * Extension for the WP core list table class
 * @author FJ Kaiser
 * @tutorial @link http://codex.wordpress.org/Class_Reference/WP_List_Table
 * @example @link http://wordpress.org/extend/plugins/custom-list-table-example
 */
class ilcTable extends WP_List_Table
{
	/**
	 * Constructor
	 * 
	 * @return void
	 */
	public function __construct()
	{
		// parent class vars
		$screen			= get_current_screen();
		$this->screen	= $screen->id;

		parent :: __construct( array(
			 'singular'	=> 'internal link'
			,'plural'	=> 'internal links'
			,'ajax'		=> false
		) );
	}


	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::no_items()
	 */
	function no_items() 
	{
		_e( 'No links found.', 'ilc' );
	}


	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::get_columns()
	 */
	public function get_columns()
	{
		return array(
			 'ID'			=> __( 'ID', 'ilc' )
			,'post_title'	=> __( 'Title', 'ilc' )
			,'post_date'	=> __( 'Date', 'ilc' )
		);
	}


	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::get_sortable_columns()
	 */
	function get_sortable_columns() 
	{
		return array(
			 'ID'			=> 'ID'
			,'post_title'	=> 'post_title'
			,'post_date'	=> 'post_date'
		);
	}


	/**
	 * Prepare data for display
	 * Must get defined in extended class here
	 * (non-PHPdoc)
	 * @see WP_List_Table::prepare_items()
	 */
	public function prepare_items()
	{
		$columns		= $this->get_columns();
		$hidden			= array();
		$sortable		= $this->get_sortable_columns();

        $this->_column_headers = array( 
        	 $columns
        	,$hidden
        	,$sortable 
        );

        $data = ilcInit::the_sql_results();

        // Pagination Data
        /*
		$per_page		= 5;
        $current_page	= $this->get_pagenum();
        $total_items	= count( $data );
        $this->set_pagination_args( array (
        	 // Calculate the total number of items
             'total_items'	=> $total_items
             // Determine how many items to show on a page
            ,'per_page'		=> $per_page
             // Calculate the total number of pages
            ,'total_pages'	=> ceil( $total_items / $per_page )
        ) );
        */

        /*
        $this->process_bulk_action();
        */

        $this->items	= $data;
	}


	/**
	 * A single column
	 * Must get defined in extended class here
	 * 
	 * @param (object) $item
	 * @param (string) $column_name
	 */
	public function column_default( $item, $column_name )
	{
		return $item->$column_name;
	}
}
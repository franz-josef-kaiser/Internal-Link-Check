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
	 * l10n translation domain
	 * @var (string)
	 */
	var $textdomain;


	/**
	 * Constructor
	 * 
	 * @return void
	 */
	public function __construct()
	{
		// textdomain
		$trace				= debug_backtrace();
		$plugin_data		= get_plugin_data( $trace[0]['file'] );
		$this->textdomain	= $plugin_data['TextDomain'];
		// screen
		$screen				= get_current_screen();
		$this->screen		= $screen->id;

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
	public function no_items() 
	{
		_e( 'No links found.', $this->textdomain );
	}


	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::get_columns()
	 */
	public function get_columns()
	{
		return array(
			 'ID'			=> __( 'ID',	$this->textdomain )
			,'post_title'	=> __( 'Title',	$this->textdomain )
			,'post_date'	=> __( 'Date',	$this->textdomain )
		);
	}


	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::get_sortable_columns()
	 */
	public function get_sortable_columns() 
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
		$sortable		= array(); # $this->get_sortable_columns();

        $this->_column_headers = array( 
        	 $columns
        	,$hidden
        	,$sortable 
        );

        $data			= ilcInit::the_sql_results();
        // Prepare the data
        $permalink		= __( 'Permalink to:', $this->textdomain );
		foreach ( $data as $key => $post )
		{
			$link		= get_edit_post_link( $post->ID );

			// If no title was set: we care about it
			$no_title	= __( 'No title set', $this->textdomain );
			$title		= ! $post->post_title ? "<em>{$no_title}</em>" : $post->post_title;

			$data[ $key ]->post_title = "<a title='{$permalink} {$title}' href='{$link}'>{$title}</a>";
		}

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


	/**
	 * Temp. Override of table nav to avoid gaps in UI
	 * (non-PHPdoc)
	 * @see WP_List_Table::display_tablenav()
	 */
	public function display_tablenav( $which )
	{
		return;
	}
}
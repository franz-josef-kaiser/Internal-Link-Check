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
 * 
 * Renders the contents of the meta box
 * 
 * @author FJ Kaiser
 * 
 * @package ILC
 * @subpackage WP List Table extension
 * @license GNU GPL 2
 * 
 * @see /wp-admin/includes/class-wp-comments-list-table.php Comments List Table class.
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
			,'ajax'		=> true
		) );
	}


	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::ajax_user_can()
	 */
	function ajax_user_can() 
	{
		return current_user_can( 'edit_posts' );
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
		$sortable		= $this->get_sortable_columns();

        $this->_column_headers = array( 
        	 $columns
        	,$hidden
        	,$sortable 
        );

        // SQL results
        $posts			= ilcInit :: the_sql_results();

        # >>>> Pagination
        // Per Page Data
			$per_page		= 5;
	        $current_page	= $this->get_pagenum();
	        $total_items	= count( $posts );
	        $this->set_pagination_args( array (
	        	 // Calculate the total number of items
	             'total_items'	=> $total_items
	             // Determine how many items to show on a page
	            ,'per_page'		=> $per_page
	             // Calculate the total number of pages
	            ,'total_pages'	=> ceil( $total_items / $per_page )
	        ) );
			// Setup first and last post index/key for current posts array filter
	        $last_post		= $current_page * $per_page;
	        // count one post up as we'd have null else
	        $first_post		= $last_post - $per_page +1;
	        // In case the last page doesn't hold as many objects as the other pages hold: set to last element
	        if ( $last_post > $total_items )
	        	$last_post = $total_items;
	        // Setup the range of keys/indizes that contain the posts on the currently displayed page(d)
	        // flip keys with values as the range outputs the range in the values
	        $range			= array_flip( range( $first_post - 1, $last_post - 1, 1 ) );
	        // Filter out the posts we're not displaying on the current page
	        $posts_array	= array_intersect_key( $posts, $range );
        # <<<< Pagination

        // Prepare the data
        $permalink		= __( 'Edit:', $this->textdomain );
		foreach ( $posts_array as $key => $post )
		{
			$link		= get_edit_post_link( $post->ID );

			// If no title was set: we care about it
			$no_title	= __( 'No title set', $this->textdomain );
			$title		= ! $post->post_title ? "<em>{$no_title}</em>" : $post->post_title;

			$posts[ $key ]->post_title = "<a title='{$permalink} {$title}' href='{$link}'>{$title}</a>";
		}

        $this->items	= $posts_array;
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
	 * Override of table nav to avoid breaking with bulk actions & according nonce field
	 * (non-PHPdoc)
	 * @see WP_List_Table::display_tablenav()
	 * @access protected
	 */
	function display_tablenav( $which ) {
		# if ( 'top' == $which )
			# wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">
			<!-- 
			<div class="alignleft actions">
				<?php # $this->bulk_actions( $which ); ?>
			</div>
			 -->
			<?php
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?>
			<br class="clear" />
		</div>
		<?php
	}
}
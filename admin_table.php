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
 * @author Franz Josef Kaiser
 * 
 * @package ILC
 * @subpackage WP List Table extension
 * @license GNU GPL 2
 * @since 0.2.7
 * 
 * @see /wp-admin/includes/class-wp-comments-list-table.php Comments List Table class.
 * @tutorial @link http://codex.wordpress.org/Class_Reference/WP_List_Table
 * @example @link http://wordpress.org/extend/plugins/custom-list-table-example
 */
class ilcTable extends WP_List_Table
{
	/**
	 * l10n translation domain
	 * Retrieved via init class
	 * 
	 * @since 0.2.7
	 * @var (string)
	 */
	var $textdomain;


	/**
	 * Meta Box name
	 * Retrieved via init class
	 * 
	 * @since 0.6
	 * @var (string)
	 */
	var $meta_box_name;


	/**
	 * Order SQL results ASC/DESC
	 * Set by $_GET (query arg)
	 * 
	 * @since 0.5
	 * @var (string)
	 */
	var $order;


	/**
	 * Order SQL results by columns
	 * Set by $_GET (query arg)
	 * 
	 * @since 0.5
	 * @var (string)
	 */
	var $orderby;


	/**
	 * Constructor
	 * 
	 * @param (string) $textdomain 
	 * @param (string) $meta_box_name
	 * @return void
	 */
	public function __construct( $textdomain, $meta_box_name )
	{
		// textdomain
		$this->textdomain	= $textdomain;
		// meta box name
		$this->meta_box_name= $meta_box_name;

		// screen
		$this->set_screen();

		// Args for the SQL query, based on query vars in $_GET
		$this->set_order();
		$this->set_orderby();

		// Setup
		parent :: __construct( array(
			 'singular'	=> 'internal link'
			,'plural'	=> 'internal links'
			,'ajax'		=> true
		) );

		// Display Output
		$this->prepare_items();
		# echo '<form id="form-search-ilc">';
		# $this->search_box( __( 'Search', $this->textdomain ), 'search-ilc' );
		$this->display();
		# echo '</form>';
	}


	/**
	 * Sets the current screen name
	 * @return void
	 */
	public function set_screen()
	{
		$screen			= get_current_screen();
		$this->screen	= $screen->id;
	}


	/**
	 * Sets the current order argument for the SQL Query
	 * based on the $_GET array
	 * @return void
	 */
	public function set_order()
	{
		$order = 'DESC';
		if ( isset( $_GET['order'] ) AND $_GET['order'] )
			$order = $_GET['order'];
		$this->order = $order;
	}


	/**
	 * Sets the current orderby argument for the SQL Query
	 * based on the $_GET array
	 * @return void
	 */
	public function set_orderby()
	{
		$orderby = 'post_date';
		if ( isset( $_GET['orderby'] ) AND $_GET['orderby'] )
			$orderby = $_GET['orderby'];
		$this->orderby = $orderby;
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
	 * @see WP_List_Table::get_views()
	 */
	public function get_views()
	{
		# Only needed if we're going to add bulk actions and
		# want a @magic (idea) history tracking:
		# "Where was/is this post linked"
		# We should add some query vars to target the different views,
		# that have run through some of the bulk actions
		# Not sure how to target those posts: by adding some custom post_meta data maybe?
		# @example: array( 'removed_link' => $post->ID )

		// @example display
		#foreach ( array( 'test', 'bla', 'foo', 'bar' ) as $e )
		#	$example[] = "<a href='#'>{$e}</a>";
		#return $example;

		return array();
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
			 'ID'			=> array( 'ID', true )
			,'post_title'	=> array( 'post_title', true )
			,'post_date'	=> array( 'post_date', true )
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
	 	# @since 0.4
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


	/**
	 * Disables the views for 'side' context as there's not enough free space in the UI
	 * Only displays them on screen/browser refresh. Else we'd have to do this via Ajax DB update.
	 * 
	 * @since 0.6
	 * (non-PHPdoc)
	 * @see WP_List_Table::extra_tablenav()
	 */
	public function extra_tablenav( $which )
	{
		// Abort for empty views array - needed during development, maybe later if settings are present
		$views = $this->get_views();
		if ( empty( $views ) )
			return;

		// Get all meta boxes
		$curr_meta_boxes = $GLOBALS['wp_meta_boxes'][ $this->screen ];
		// Loop through the context/priority array
		foreach ( $curr_meta_boxes as $context => $priority )
		{
			foreach ( $priority as $name => $meta_boxes )
			{
				// Check if the link meta box is in the current priority
				$link_box = in_array( $this->meta_box_name, array_keys( $meta_boxes ) );
				// If so: abort
				if ( $link_box AND 'side' === $context )
					return;
			}
		}

		// If we're not in the 'side' context, display the views
		$this->views();
	}
}
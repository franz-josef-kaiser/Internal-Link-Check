<?php

defined( 'ABSPATH' ) or exit;

require_once "TableViewInterface.php";

/**
 * Class ILVTableView
 */
class ILCTableView extends WP_List_Table implements ILCTableViewInterface
{
	public $order = 'DESC';

	public $orderby = 'post_date';

	private $queryObject = null;


	public function __construct()
	{
		// Prevent rendering on object creation.

		# $this->setQueryObject( $queryObject );
	}

	public function render()
	{
		// meta box name
		# $this->meta_box_name = $meta_box_name;

		// Args for the SQL query, based on query vars in $_GET
		$this->setOrder();
		$this->setOrderBy();

		// Setup
		parent::__construct( array(
			'singular' => 'internal link',
			'plural'   => 'internal links',
			'ajax'     => true,
			'screen'   => null,
		) );

		// Display Output
		$this->prepare_items();

		# echo '<form id="form-search-ilc">';
		# $this->search_box( __( 'Search', 'ilc' ), 'search-ilc' );
		$this->display();
		# echo '</form>';
	}

	public function setOrder()
	{
		isset( $_GET['order'] )
			AND $this->order = esc_attr( $_GET['order'] );
	}

	public function setOrderBy()
	{
		isset( $_GET['orderby'] )
			AND $this->orderby = esc_attr( $_GET['orderby'] );
	}

	public function getOrder()
	{
		return $this->order;
	}

	public function getOrderBy()
	{
		return $this->orderby;
	}

	/**
	 * @param ILCSQLResultsInterface $queryObject
	 * @return $this
	 */
	public function setQueryObject( ILCSQLResultsInterface $queryObject  )
	{
		$this->queryObject = $queryObject;

		return $this;
	}

	public function getQueryObject()
	{
		return $this->queryObject;
	}

	/**
	 * @see WP_List_Table::ajax_user_can()
	 */
	public function ajax_user_can()
	{
		return current_user_can( 'edit_posts' );
	}

	/**
	 * @see WP_List_Table::no_items()
	 */
	public function no_items()
	{
		_e( 'No links found.', 'ilc' );
	}

	/**
	 * @see WP_List_Table::get_views()
	 * @return array
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

	public function get_columns()
	{
		return array(
			'ID'         => __( 'ID', 'ilc' ),
			'post_title' => __( 'Title', 'ilc' ),
			'post_date'  => __( 'Date', 'ilc' ),
		);
	}

	public function get_sortable_columns()
	{
		return array(
			'ID'         => array( 'ID', true ),
			'post_title' => array( 'post_title', true ),
			'post_date'  => array( 'post_date', true ),
		);
	}

	public function prepare_items()
	{
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array(
			$columns,
			$hidden,
			$sortable
		);

		// SQL results
		$posts = $this->getQueryObject();
		empty( $posts ) AND $posts = array();

		# >>>> Pagination
		// Per Page Data
		$per_page     = 5;
		$current_page = $this->get_pagenum();
		$total_items  = count( $posts );
		$this->set_pagination_args( array(
			// Calculate the total number of items
			'total_items' => $total_items,
			// Determine how many items to show on a page
			'per_page'    => $per_page,
			// Calculate the total number of pages
			'total_pages' => ceil( $total_items / $per_page ),
		) );

		// Setup first and last post index/key for current posts array filter.
		$last_post = $current_page * $per_page;
		// In case the last page doesn't hold as many objects,
		// as the other pages hold: set to last element.
		$last_post > $total_items AND $last_post = $total_items;
		// count one post up as we'd have null else
		$first_post = $last_post - $per_page +1;

		// Setup the range of keys/indizes that contain
		// the posts on the currently displayed page(d).
		// Flip keys with values as the range outputs the range in the values.
		$range = array_flip( range( $first_post - 1, $last_post - 1, 1 ) );

		// Filter out the posts we're not displaying on the current page.
		$data = is_object( $posts )
			? $posts->getResults()
			: $posts;
		$postsArray = array_intersect_key( $posts->getResults(), $range );
		# <<<< Pagination

		// Prepare the data
		$permalink = __( 'Edit:', 'ilc' );
		foreach ( $postsArray as $key => $post )
		{
			$link     = get_edit_post_link( $post->ID );
			$title  = empty( $post->post_title )
				? sprintf( "<em>%s</em>", __( 'No title set', 'ilc' ) )
				: $post->post_title;

			$posts[ $key ]->post_title = "<a title='{$permalink} {$title}' href='{$link}'>{$title}</a>";
		}

		$this->items = $postsArray;
	}

	/**
	 * A single column
	 * Must get defined in extended class here
	 * @param  object $item
	 * @param  string $column_name
	 * @return string $item->column_name
	 */
	public function column_default( $item, $column_name )
	{
		return $item->{$column_name};
	}


	/**
	 * Override of table nav to avoid breaking with bulk actions & according nonce field
	 * @see WP_List_Table::display_tablenav()
	 * @param  string $which
	 * @return string HTML
	 */
	public  function display_tablenav( $which )
	{
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
	 * Only displays them on screen/browser refresh. Else we'd have to do this via an AJAX DB update.
	 * @since  0.6
	 * @see    WP_List_Table::extra_tablenav()
	 * @param  $which string
	 * @return string HTML
	 */
	public function extra_tablenav( $which )
	{
		global $wp_meta_boxes;

		// Abort for empty views array - needed during development, maybe later if settings are present
		$views = $this->get_views();
		if ( empty( $views ) )
			return '';

		// Loop through the context/priority array
		foreach ( $wp_meta_boxes[ get_current_screen()->id ] as $context => $priority )
		{
			foreach ( $priority as $name => $meta_boxes )
			{
				// Check if the link meta box is in the current priority
				$link_box = in_array( $this->meta_box_name, array_keys( $meta_boxes ) );
				// If so: Abort
				if (
					$link_box
					AND 'side' === $context
				)
					return '';
			}
		}

		// If we're not in the 'side' context, display the views
		return $this->views();
	}
}
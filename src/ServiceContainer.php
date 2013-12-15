<?php

defined( 'ABSPATH' ) or exit;


require_once "ServiceContainerInterface.php";

/**
 * Class ILCServiceContainer
 */
class ILCServiceContainer implements Iterator, ILCServiceContainerInterface
{
	private $pointer = 0;

	private $services = array();

	public function __construct()
	{
		$this->pointer = 0;

		# add_action( 'add_meta_boxes', array( $this, 'prepare' ), 0, 2 );

		return $this;
	}

	/*public function prepare( $post_type, $post )
	{
		// Add info for query
		$this->services['metabox.query']->setOrder( $this->services['metabox.table']->getOrder() );
		$this->services['metabox.query']->setOrderBy( $this->services['metabox.table']->getOrderBy() );
		// Set table data, retrieved via Query
		#$this->services['metabox.query']->setResults();
		$this->services['metabox.table']->setData( new $this->services['metabox.query'] );
	}*/

	public function offsetExists( $offset )
	{
		return isset( $this->services[ $offset ] );
	}

	public function offsetSet( $offset, $value )
	{
		is_null( $offset )
			? $this->services[] = $value
			: $this->services[ $offset ] = $value;
	}

	public function offsetGet( $offset )
	{
		return isset( $this->services[ $offset ] )
			? $this->services[ $offset ]
			: NULL;
	}

	public function offsetUnset( $offset )
	{
		unset( $this->services[ $offset ] );
	}

	public function count()
	{
		return count( array_keys( $this->services ) );
	}

	public function current()
	{
		return $this->services[ $this->pointer ];
	}

	public function next()
	{
		++$this->pointer;
	}

	public function key()
	{
		return $this->pointer;
	}

	public function valid()
	{
		return isset( $this->services[ $this->pointer ] );
	}

	public function rewind()
	{
		$this->pointer = 0;
	}
}
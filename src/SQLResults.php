<?php

defined( 'ABSPATH' ) or exit;

require_once "SQLResultsInterface.php";

/**
 * Class ILCSQLResults
 */
class ILCSQLResults implements ILCSQLResultsInterface
{
	public $query = "";

	public $results = array();

	public $order = 'DESC';

	public $orderby = 'post_date';


	public function __construct()
	{
		$this->pointer = 0;

		$this->setQuery();
		$this->prepareQuery();
		$this->setResults( $this->getQuery() );
	}

	public function setQuery()
	{
		global $wpdb;

		$this->query = <<<SQL
SELECT ID, post_title, post_date, post_content, post_type
	FROM {$wpdb->posts}
WHERE post_content LIKE %s
ORDER BY %s %s
LIMIT 0, 999
SQL;
	}

	public function getQuery()
	{
		return $this->query;
	}

	public function prepareQuery()
	{
		global $wpdb;

		$this->query = $wpdb->prepare(
			$this->getQuery(),
			// get_permalink() cares about rewrite rules
			$this->getValue(),
			$this->getOrderBy(),
			$this->getOrder()
		);
	}

	public function setOrderBy( $orderby )
	{
		$this->orderby = $orderby;
	}

	public function getOrderBy()
	{
		return $this->orderby;
	}

	public function setOrder( $order )
	{
		$this->order = $order;
	}

	public function getOrder()
	{
		return $this->order;
	}

	public function getValue()
	{
		return "%".like_escape( get_permalink( get_the_ID() ) )."%";
	}

	public function setResults( $query )
	{
		global $wpdb;

		$this->results = $wpdb->get_results( $query );
	}

	public function getResults()
	{
		return $this->results;
	}

	public function getIterator()
	{
		return new ArrayIterator( $this->results );
	}

	public function count()
	{
		return count( array_keys( $this->results ) );
	}

	public function offsetExists( $offset )
	{
		return isset( $this->results[ $offset ] );
	}

	public function offsetGet( $offset )
	{
		return $this->results[ $offset ];
	}

	public function offsetSet( $offset, $value )
	{
		is_null( $offset )
			? $this->results[] = $value
			: $this->results[ $offset ] = $value;
	}

	public function offsetUnset( $offset )
	{
		unset( $this->results[ $offset ] );
	}
}
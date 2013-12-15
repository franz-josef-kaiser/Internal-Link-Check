<?php
defined( 'ABSPATH' ) OR exit;

/**
 * Interface ILCSQLResultsInterface
 */
interface ILCSQLResultsInterface extends IteratorAggregate, ArrayAccess, Countable
{
	/**
	 * @return Array $results
	 */
	public function getResults();
}
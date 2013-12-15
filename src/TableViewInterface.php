<?php

defined( 'ABSPATH' ) or exit;

/**
 * Interface ILCTableViewInterface
 */
interface ILCTableViewInterface
{
	public function __construct();

	/**
	 * This method should echo things instead of the constructor
	 */
	public function render();

	public function setOrder();

	public function setOrderBy();

	public function getOrder();

	public function getOrderBy();

	/**
	 * @param ILCSQLResultsInterface $queryObject
	 */
	public function setQueryObject( ILCSQLResultsInterface $queryObject );

	/**
	 * @return ILCSQLResultsInterface $object
	 */
	public function getQueryObject();
}
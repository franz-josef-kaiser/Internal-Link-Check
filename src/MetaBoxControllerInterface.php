<?php
defined( 'ABSPATH' ) OR exit;

/**
 * Interface ILCMetaBoxInterface
 */
interface ILCMetaBoxControllerInterface
{
	public function addBox();

	/**
	 * @param WP_Post $post
	 * @param Array $data
	 */
	public function render( WP_Post $post, Array $data );

	/**
	 * @param ILCSQLResultsInterface $model
	 */
	public function setModel( ILCSQLResultsInterface $model );

	/**
	 * @return ILCSQLResultsInterface
	 */
	public function getModel();

	/**
	 * @param ILCTableViewInterface $view
	 */
	public function setView( ILCTableViewInterface $view );

	/**
	 * @return ILCSQLResultsInterface
	 */
	public function getView();
}
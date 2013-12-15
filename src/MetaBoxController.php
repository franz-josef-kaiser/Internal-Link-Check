<?php
defined( 'ABSPATH' ) OR exit;

require_once "MetaBoxControllerInterface.php";

/**
 * Class ILCi18n
 */
class ILCMetaBoxController implements ILCMetaBoxControllerInterface
{
	private $allowed = array();

	private $view = NULL;

	private $model = NULL;

	public function setAllowed( Array $allowed )
	{
		$this->allowed = $allowed;
	}

	public function setView( ILCTableViewInterface $view )
	{
		$this->view = $view;
	}

	public function getView()
	{
		return $this->view;
	}

	public function setModel( ILCSQLResultsInterface $model )
	{
		$this->model = $model;
	}

	public function getModel()
	{
		return $this->model;
	}

	public function addBox()
	{
		add_meta_box(
			'internal-links',
			__( 'Internal Links', 'ilc' ),
			array( $this, 'render' ),
			'post',
			'advanced',
			'default',
			array( $this, 'getModel' )
		);
	}

	public function render( WP_Post $post, Array $data )
	{
		$table = $this->getView();
		$table->setQueryObject( $this->getModel() );
		$table->render();
	}

	private function isAllowed( $value )
	{
		return in_array( $value, $this->allowed );
	}
}
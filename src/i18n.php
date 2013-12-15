<?php
defined( 'ABSPATH' ) OR exit;

require_once "i18nInterface.php";

/**
 * Class ILCi18n
 */
class ILCi18n implements ILCi18nInterface
{
	/**
	 * @param $file
	 */
	public function __construct( $file )
	{
		$this->loadFile( $file );
	}

	/**
	 * @param $file
	 * @return bool
	 */
	public function loadFile( $file )
	{
		return load_plugin_textdomain(
			'ilc',
			false,
			plugin_basename( $file )."/lang"
		);
	}
}
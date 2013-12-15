<?php
/**
* Plugin Name: Internal links checker
* Plugin URI:  https://github.com/franz-josef-kaiser/Internal-Link-Check
* Description: Adds a meta box to the post edit screen that shows all internal links from other posts to the currently displayed post. This way you can easily check if you should fix links before deleting a post. There are no options needed. The plugin works out of the box.
* Author:      Franz Josef Kaiser, Patrick Matsumura
* Author URI:  https://unserkaiser.com
* Version:     1.0
* Text Domain: ilc
* License:     MIT
* © Copyright 2010-2013 by Franz Josef Kaiser <wecodemore@gmail.com>
*/

defined( 'ABSPATH' ) or exit;

add_action( 'edit_form_advanced', 'ILCLoad' );
function ILCLoad()
{
	/**
	 * SPL Libraries haven't been mandatory with PHP 5.2 ... what a pitty.
	$files = new ILCFileLoader( new DirectoryIterator( plugin_dir_path( __FILE__ ) ) );
	foreach ( $files as $file ) {
		var_dump( $file->getFilename() );
	}
	*/

	require_once plugin_dir_path( __FILE__ )."src/ServiceContainer.php";
	$app = new ILCServiceContainer();

	require_once plugin_dir_path( __FILE__ )."src/i18n.php";

	require_once plugin_dir_path( __FILE__ )."src/MetaBoxController.php";
	require_once plugin_dir_path( __FILE__ )."src/TableView.php";
	require_once plugin_dir_path( __FILE__ )."src/SQLResults.php";

	$app['i18n']          = new ILCi18n( __FILE__ );
	$app['metabox.ctrl']  = new ILCMetaBoxController();
	$app['metabox.table'] = new ILCTableView();
	$app['metabox.query'] = new ILCSQLResults();

	$app['metabox.ctrl']->setModel( $app['metabox.query'] );
	$app['metabox.ctrl']->setView( $app['metabox.table'] );
	$app['metabox.ctrl']->addBox();
}

# SPL only shipped with PHP 5.3+ ...
# and then I could use much more sophisticated stuff than this one.
class ILCFileLoader extends FilterIterator
{
	public function __construct( DirectoryIterator $dir )
	{
		parent::__construct( $dir );
	}

	public function accept()
	{
		return 'php' === parent::current()->getExtension()
			AND ! parent::current()->isDot();
	}
}
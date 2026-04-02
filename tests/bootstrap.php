<?php

/**
 * Disable User Login Unit Tests Bootstrap
 */
class SSDUL_Unit_Tests_Bootstrap {

	/** @var SSDUL_Unit_Tests_Bootstrap instance */
	protected static $instance = null;

	/** @var string directory where wordpress-tests-lib is installed */
	public $wp_tests_dir;

	/** @var string testing directory */
	public $tests_dir;

	/** @var string plugin directory */
	public $plugin_dir;

	/**
	 * Setup the unit testing environment.
	 */
	public function __construct() {

		// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions, WordPress.PHP.DevelopmentFunctions
		ini_set( 'display_errors', 'on' );
		error_reporting( E_ALL );
		// phpcs:enable WordPress.PHP.DiscouragedPHPFunctions, WordPress.PHP.DevelopmentFunctions

		// Ensure server variable is set for WP email functions.
		if ( ! isset( $_SERVER['SERVER_NAME'] ) ) {
			$_SERVER['SERVER_NAME'] = 'localhost';
		}

		$this->tests_dir    = dirname( __FILE__ );
		$this->plugin_dir   = dirname( $this->tests_dir );
		$this->wp_tests_dir = getenv( 'WP_TESTS_DIR' ) ? getenv( 'WP_TESTS_DIR' ) : '/tmp/wordpress-tests-lib';

		// load test function so tests_add_filter() is available
		require_once $this->wp_tests_dir . '/includes/functions.php';

		// load the WP testing environment
		require_once $this->wp_tests_dir . '/includes/bootstrap.php';

		// load testing framework
		$this->includes();

		// load plugin
		$this->load_plugin();
	}

	/**
	 * Load Disable User Login.
	 */
	public function load_plugin() {
		require_once $this->plugin_dir . '/disable-user-login.php';
	}

	/**
	 * Load test cases and factories.
	 */
	public function includes() {
		require_once $this->tests_dir . '/framework/class-ssdul-unit-test-case.php';
	}

	/**
	 * Get the single class instance.
	 *
	 * @return SSDUL_Unit_Tests_Bootstrap
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
SSDUL_Unit_Tests_Bootstrap::instance();

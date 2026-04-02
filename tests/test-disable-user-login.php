<?php

/**
 * Disable User Login test case.
 */
class Test_SSDUL extends SSDUL_Unit_Test_Case {

	/**
	 * Plugin instance.
	 *
	 * @var SS_Disable_User_Login_Plugin
	 */
	protected $plugin;

	/**
	 * Setup test.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->plugin = SSDUL();
	}

	/**
	 * Test plugin has static instance.
	 */
	public function test_instance() {
		$this->assertInstanceOf( 'SS_Disable_User_Login_Plugin', $this->plugin );
	}

	/**
	 * Test that all constants are set.
	 */
	public function test_constants() {
		$this->assertTrue( defined( 'SS_DISABLE_USER_LOGIN_FILE' ) );
		$this->assertTrue( defined( 'SS_DISABLE_USER_LOGIN_VERSION' ) );
		$this->assertTrue( defined( 'SS_DISABLE_USER_LOGIN_DIR' ) );
		$this->assertTrue( defined( 'SS_DISABLE_USER_LOGIN_URL' ) );
	}

	/**
	 * Test version string is set and matches expected format.
	 */
	public function test_version() {
		$version = SS_Disable_User_Login_Plugin::version();
		$this->assertNotEmpty( $version );
		$this->assertMatchesRegularExpression( '/^\d+\.\d+\.\d+$/', $version );
	}

	/**
	 * Test user meta key is correct.
	 */
	public function test_user_meta_key() {
		$this->assertSame( '_is_disabled', SS_Disable_User_Login_Plugin::user_meta_key() );
	}

	/**
	 * Test disabled user is blocked from login.
	 */
	public function test_disabled_user_cannot_login() {
		$user_id = $this->factory->user->create( array(
			'role'      => 'subscriber',
			'user_pass' => 'testpassword',
		) );

		update_user_meta( $user_id, '_is_disabled', 1 );

		$user   = get_user_by( 'id', $user_id );
		$result = $this->plugin->user_login( $user, $user->user_login, 'testpassword' );

		$this->assertWPError( $result );
		$this->assertSame( 'disable_user_login_user_disabled', $result->get_error_code() );
	}

	/**
	 * Test enabled user is allowed to login.
	 */
	public function test_enabled_user_can_login() {
		$user_id = $this->factory->user->create( array(
			'role' => 'subscriber',
		) );

		$user   = get_user_by( 'id', $user_id );
		$result = $this->plugin->user_login( $user, $user->user_login, 'password' );

		$this->assertInstanceOf( 'WP_User', $result );
	}

	/**
	 * Test user with disabled meta set to 0 can login.
	 */
	public function test_user_with_disabled_meta_zero_can_login() {
		$user_id = $this->factory->user->create( array(
			'role' => 'subscriber',
		) );

		update_user_meta( $user_id, '_is_disabled', 0 );

		$user   = get_user_by( 'id', $user_id );
		$result = $this->plugin->user_login( $user, $user->user_login, 'password' );

		$this->assertInstanceOf( 'WP_User', $result );
	}

	/**
	 * Test that existing WP_Error is passed through.
	 */
	public function test_wp_error_passed_through() {
		$error  = new WP_Error( 'test_error', 'Test error message' );
		$result = $this->plugin->user_login( $error, 'user', 'pass' );

		$this->assertSame( $error, $result );
	}

	/**
	 * Test application passwords are disabled for disabled users.
	 */
	public function test_application_passwords_disabled_for_disabled_user() {
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$user    = get_user_by( 'id', $user_id );

		update_user_meta( $user_id, '_is_disabled', 1 );

		$result = $this->plugin->maybe_disable_application_passwords_for_user( true, $user );
		$this->assertFalse( $result );
	}

	/**
	 * Test application passwords remain enabled for enabled users.
	 */
	public function test_application_passwords_enabled_for_enabled_user() {
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$user    = get_user_by( 'id', $user_id );

		$result = $this->plugin->maybe_disable_application_passwords_for_user( true, $user );
		$this->assertTrue( $result );
	}

	/**
	 * Test disabled column shows "Disabled" for disabled user.
	 */
	public function test_users_column_shows_disabled() {
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		update_user_meta( $user_id, '_is_disabled', 1 );

		$output = $this->plugin->manage_users_column_content( '', 'disable_user_login', $user_id );
		$this->assertSame( 'Disabled', $output );
	}

	/**
	 * Test disabled column is empty for enabled user.
	 */
	public function test_users_column_empty_for_enabled_user() {
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		$output = $this->plugin->manage_users_column_content( '', 'disable_user_login', $user_id );
		$this->assertSame( '', $output );
	}

	/**
	 * Test disabled column doesn't affect other columns.
	 */
	public function test_users_column_passthrough_for_other_columns() {
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		$output = $this->plugin->manage_users_column_content( 'original', 'other_column', $user_id );
		$this->assertSame( 'original', $output );
	}

	/**
	 * Test users columns filter adds disabled column.
	 */
	public function test_manage_users_columns() {
		$columns = $this->plugin->manage_users_columns( array() );
		$this->assertArrayHasKey( 'disable_user_login', $columns );
		$this->assertSame( 'Disabled', $columns['disable_user_login'] );
	}

	/**
	 * Test bulk actions are registered.
	 */
	public function test_bulk_actions_registered() {
		$actions = $this->plugin->bulk_action_disable_users( array() );
		$this->assertArrayHasKey( 'enable_user_login', $actions );
		$this->assertArrayHasKey( 'disable_user_login', $actions );
	}

	/**
	 * Test can_disable prevents disabling self.
	 */
	public function test_cannot_disable_self() {
		$admin_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		$this->assertFalse( $this->plugin->can_disable( $admin_id ) );
	}

	/**
	 * Test can_disable allows admin to disable other users.
	 */
	public function test_admin_can_disable_other_user() {
		$admin_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$user_id  = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $admin_id );

		$this->assertTrue( $this->plugin->can_disable( $user_id ) );
	}

	/**
	 * Test non-admin cannot disable users.
	 */
	public function test_non_admin_cannot_disable() {
		$editor_id = $this->factory->user->create( array( 'role' => 'editor' ) );
		$user_id   = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $editor_id );

		$this->assertFalse( $this->plugin->can_disable( $user_id ) );
	}

	/**
	 * Test get_edit_cap returns correct capability.
	 */
	public function test_get_edit_cap() {
		$this->assertSame( 'edit_users', $this->plugin->get_edit_cap() );
	}

	/**
	 * Test force_logout destroys all user sessions.
	 */
	public function test_force_logout() {
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		// Create a session for the user.
		$sessions = WP_Session_Tokens::get_instance( $user_id );
		$sessions->create( time() + 3600 );

		$this->assertNotEmpty( $sessions->get_all() );

		$this->plugin->force_logout( $user_id );

		// Refresh the session manager.
		$sessions = WP_Session_Tokens::get_instance( $user_id );
		$this->assertEmpty( $sessions->get_all() );
	}

	/**
	 * Test disabled_login_attempt action fires when disabled user tries to login.
	 */
	public function test_disabled_login_attempt_action_fires() {
		$user_id = $this->factory->user->create( array(
			'role'      => 'subscriber',
			'user_pass' => 'testpassword',
		) );

		update_user_meta( $user_id, '_is_disabled', 1 );

		$action_fired = false;
		add_action( 'disable_user_login.disabled_login_attempt', function () use ( &$action_fired ) {
			$action_fired = true;
		} );

		$user = get_user_by( 'id', $user_id );
		$this->plugin->user_login( $user, $user->user_login, 'testpassword' );

		$this->assertTrue( $action_fired );
	}

	/**
	 * Test disabled message filter works.
	 */
	public function test_disabled_message_filter() {
		$user_id = $this->factory->user->create( array(
			'role'      => 'subscriber',
			'user_pass' => 'testpassword',
		) );

		update_user_meta( $user_id, '_is_disabled', 1 );

		add_filter( 'disable_user_login.disabled_message', function () {
			return 'Custom disabled message';
		} );

		$user   = get_user_by( 'id', $user_id );
		$result = $this->plugin->user_login( $user, $user->user_login, 'testpassword' );

		$this->assertWPError( $result );
		$this->assertStringContainsString( 'Custom disabled message', $result->get_error_message() );

		remove_all_filters( 'disable_user_login.disabled_message' );
	}

	/**
	 * Test disabled column persists after meta update via integer value.
	 *
	 * Regression test for GitHub issue #15 — the "Disabled" column
	 * must reflect the stored meta regardless of whether the value
	 * was saved as int 1 or string "1".
	 */
	public function test_column_displays_disabled_with_integer_meta() {
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		update_user_meta( $user_id, '_is_disabled', 1 );

		$output = $this->plugin->manage_users_column_content( '', 'disable_user_login', $user_id );
		$this->assertSame( 'Disabled', $output );
	}

	/**
	 * Test disabled column persists with string meta value.
	 *
	 * Regression test for GitHub issue #15.
	 */
	public function test_column_displays_disabled_with_string_meta() {
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		update_user_meta( $user_id, '_is_disabled', '1' );

		$output = $this->plugin->manage_users_column_content( '', 'disable_user_login', $user_id );
		$this->assertSame( 'Disabled', $output );
	}
}

<?php
/**
 * Main Plugin Class
 *
 * @package Disable User Login
 */

/**
 * Disable User Login plugin main class
 */
final class SS_Disable_User_Login_Plugin {

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	private static $version = '1.1.0';

	/**
	 * Plugin singleton instance
	 *
	 * @var SS_Disable_User_Login_Plugin
	 */
	private static $instance;

	/**
	 * The user meta key to use for storing whether the user is disabled.
	 *
	 * @var string
	 */
	private static $user_meta_key = '_is_disabled';

	/**
	 * Returns the plugin version
	 *
	 * @return string
	 */
	public static function version() {
		return self::$version;
	}

	/**
	 * Singleton instance
	 *
	 * @return SS_Disable_User_Login_Plugin   SS_Disable_User_Login_Plugin object
	 */
	public static function get_instance() {

		if ( empty( self::$instance ) && ! ( self::$instance instanceof SS_Disable_User_Login_Plugin ) ) {

			self::$instance = new SS_Disable_User_Login_Plugin();
			self::$instance->load_plugin_textdomain();
			self::$instance->add_hooks();
			do_action( 'disable_user_login.loaded' );

		}

		return self::$instance;

	} //end function instance

	/**
	 * Hide constructor for this singleton
	 *
	 * @since 1.0.0
	 */
	private function __construct() {

	}

	/**
	 * Setup the plugin action hooks and filters
	 */
	private function add_hooks() {

		// Actions
		add_action( 'edit_user_profile',          array( $this, 'add_disabled_field'          )        );
		add_action( 'personal_options_update',    array( $this, 'save_disabled_field'         )        );
		add_action( 'edit_user_profile_update',   array( $this, 'save_disabled_field'         )        );
		add_action( 'manage_users_custom_column', array( $this, 'manage_users_column_content' ), 10, 3 );
		add_action( 'admin_footer-users.php',	  array( $this, 'manage_users_css'            )        );
		add_action( 'admin_notices',              array( $this, 'bulk_disable_user_notices'   )        );

		// Filters
		add_filter( 'authenticate',               array( $this, 'user_login'                  ), 1000, 3 );
		add_filter( 'manage_users_columns',       array( $this, 'manage_users_columns'	      )        );
		add_filter( 'wpmu_users_columns',         array( $this, 'manage_users_columns'        )        );
		add_filter( 'bulk_actions-users',         array( $this, 'bulk_action_disable_users'   )        );
		add_filter( 'handle_bulk_actions-users',  array( $this, 'handle_bulk_disable_users'   ), 10, 3 );

	} //end function add_hooks

	/**
	 * Gets the capability associated with banning a user
	 * @return string
	 */
	public function get_edit_cap() {

		return is_multisite() ? 'manage_network_users' : 'edit_users';

	} //end function get_edit_cap

	/**
	 * Load Localization files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/plugins/disable-user-login/disable-user-login-{lang}_{country}.mo
	 *      - WP_CONTENT_DIR/plugins/disable-user-login/languages/disable-user-login-{lang}_{country}.mo
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {

		// Set filter for plugin's languages directory.
		$disable_user_login_lang_dir = dirname( plugin_basename( SS_DISABLE_USER_LOGIN_FILE ) ) . '/languages/';

		// Traditional WordPress plugin locale filter.
		// get locale in {lang}_{country} format (e.g. en_US).
		$locale = apply_filters( 'plugin_locale', get_locale(), 'disable-user-login' );

		$mofile = sprintf( '%1$s-%2$s.mo', 'disable-user-login', $locale );

		// Look for wp-content/languages/woocommerce-mailchimp/woocommerce-mailchimp-{lang}_{country}.mo
		$mofile_global1 = WP_LANG_DIR . '/disable-user-login/' . $mofile;

		// Look in wp-content/languages/plugins/woocommerce-mailchimp
		$mofile_global2 = WP_LANG_DIR . '/plugins/disable-user-login/' . $mofile;

		if ( file_exists( $mofile_global1 ) ) {

			load_textdomain( 'disable-user-login', $mofile_global1 );

		} elseif ( file_exists( $mofile_global2 ) ) {

			load_textdomain( 'disable-user-login', $mofile_global2 );

		} else {

			// Load the default language files.
			load_plugin_textdomain( 'disable-user-login', false, $disable_user_login_lang_dir );

		}

	} //end function load_plugin_textdomain

	/**
	 * Add the Disabled field to user profile
	 *
	 * @since 1.0.0
	 * @param object $user
	 */
	public function add_disabled_field( $user ) {

		// Only show this option to users who can delete other users
		if ( ! current_user_can( $this->get_edit_cap() ) )
			return;
		?>
		<table class="form-table">
			<tbody>
				<tr>
					<th>
						<label for="disable_user_login"><?php _e(' Disable User Account', 'disable_user_login' ); ?></label>
					</th>
					<td>
						<input type="checkbox" name="disable_user_login" id="disable_user_login" value="1" <?php checked( 1, get_the_author_meta( self::$user_meta_key, $user->ID ) ); ?> />
						<span class="description"><?php _e( 'If checked, the user cannot login with this account.' , 'disable_user_login' ); ?></span>
					</td>
				</tr>
			<tbody>
		</table>
		<?php
	}

	/**
	 * Saves the custom Disabled field to user meta
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id
	 */
	public function save_disabled_field( $user_id ) {

		// Don't disable super admins.
		if ( is_multisite() && is_super_admin( $user_id ) ) {
			return;
		}

		// Only worry about saving this field if the user has access.
		if ( ! current_user_can( $this->get_edit_cap() ) )
			return;

		$disabled = isset( $_POST['disable_user_login'] ) ? 1 : 0;

		update_user_meta( $user_id, self::$user_meta_key, $disabled );
	}

	/**
	 * After login check to see if user account is disabled
	 *
	 * @since 1.0.0
	 * @param object $user
	 * @param string $username
	 * @param string $password
	 */
	public function user_login( $user, $username, $password ) {

		if ( is_a( $user, 'WP_User' ) ) {
			$disabled = get_user_meta( $user->ID, self::$user_meta_key, true );

			// Is the use logging in disabled?
			if ( $disabled ) {
				return new WP_Error( 'disable_user_login_user_disabled', apply_filters( 'disable_user_login.disabled_message', __( '<strong>ERROR</strong>: Account disabled.', 'disable_user_login' ) ) );
			}
		}

		//Pass on any existing errors
		return $user;
	}

	/**
	 * Add custom disabled column to users list
	 *
	 * @since 1.0.0
	 * @param array $defaults
	 * @return array
	 */
	public function manage_users_columns( $defaults ) {

		$defaults['disable_user_login'] = __( 'Disabled', 'disable-user-login' );
		return $defaults;
	}

	/**
	 * Set content of disabled users column
	 *
	 * @since 1.0.0
	 * @param empty $empty
	 * @param string $column_name
	 * @param int $user_ID
	 * @return string
	 */
	public function manage_users_column_content( $empty, $column_name, $user_ID ) {

		if ( $column_name == 'disable_user_login' ) {
			if ( get_the_author_meta( self::$user_meta_key, $user_ID ) == 1 ) {
				return __( 'Disabled', 'disable-user-login' );
			}
		}
	}

	/**
	 * Specifiy the width of our custom column
	 *
	 * @since 1.0.0
 	 */
	public function manage_users_css() {
		echo '<style type="text/css">.column-disable_user_login { width: 80px; }</style>';
	}

	/**
	 * Add bulk actions to enable/disable users
	 * @since 1.0.6
	 */
	public function bulk_action_disable_users($bulk_actions) {
		$bulk_actions['enable_user_login']  = __( 'Enable',  'disable-user-login' );
		$bulk_actions['disable_user_login'] = __( 'Disable', 'disable-user-login' );
		return $bulk_actions;
	}

	/**
	 * Handle the bulk action to enable/disable users
	 * @since 1.0.6
	 */
	public function handle_bulk_disable_users($redirect_to, $doaction, $user_ids) {
		if ($doaction !== 'disable_user_login' && $doaction !== 'enable_user_login'){
			return $redirect_to;
		}

		$disabled = ($doaction === 'disable_user_login') ? 1 : 0;

		foreach ( $user_ids as $user_id ){
			update_user_meta( $user_id, self::$user_meta_key, $disabled );
		}

		if ($disabled){
			$redirect_to = add_query_arg( 'disable_user_login', count($user_ids), $redirect_to );
			$redirect_to = remove_query_arg( 'disable_user_login', $redirect_to );
		} else {
			$redirect_to = add_query_arg( 'disable_user_login',  count($user_ids), $redirect_to );
			$redirect_to = remove_query_arg( 'disable_user_login', $redirect_to );
		}
		return $redirect_to;
	}

	/**
	 * Add admin notices after enabling/disabling users
	 * @since 1.0.6
	 */
	public function bulk_disable_user_notices() {
		if (! empty( $_REQUEST['disable_user_login'] ) ){
			$updated = intval( $_REQUEST['disable_user_login'] );
			printf( '<div id="message" class="updated">' .
				_n( 'Enabled %s user.',
					'Enabled %s users.',
					$updated,
					'disable-user-login'
				) . '</div>', $updated );
		}

		if (! empty( $_REQUEST['disable_user_login'] ) ){
			$updated = intval( $_REQUEST['disable_user_login'] );
			printf( '<div id="message" class="updated">' .
				_n( 'Disabled %s user.',
					'Disabled %s users.',
					$updated,
					'disable-user-login'
				) . '</div>', $updated );
		}
	}

} //end class SS_Disable_User_Login_Plugin

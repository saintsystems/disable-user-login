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
	private static $version = '1.3.2';

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
	 * Returns the user meta key
	 *
	 * @return string
	 */
	public static function user_meta_key() {
		return self::$user_meta_key;
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
		add_filter( 'manage_users_custom_column', array( $this, 'manage_users_column_content' ), 10, 3 );
		add_action( 'admin_footer-users.php',	  array( $this, 'manage_users_css'            )        );
		add_action( 'admin_notices',              array( $this, 'bulk_disable_user_notices'   )        );

		// Disabled hook
		add_action( 'disable_user_login.user_disabled', array( $this, 'force_logout' ), 10, 1 );

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

		// Look for wp-content/languages/disable-user-login/disable-user-login-{lang}_{country}.mo
		$mofile_global1 = WP_LANG_DIR . '/disable-user-login/' . $mofile;

		// Look in wp-content/languages/plugins/disable-user-login
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
						<label for="disable_user_login"><?php _e('Disable User Account', 'disable-user-login' ); ?></label>
					</th>
					<td>
						<input type="checkbox" name="disable_user_login" id="disable_user_login" value="1" <?php checked( 1, get_the_author_meta( self::$user_meta_key, $user->ID ) ); ?> />
                        <label for="disable_user_login"><span class="description"><?php _e( 'If checked, the user cannot login with this account.' , 'disable-user-login' ); ?></span></label>
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

		if ( ! $this->can_disable( $user_id ) ) {
			return;
		}

		$disabled = isset( $_POST['disable_user_login'] ) ? 1 : 0;

		// Store disabled status before update
		$originally_disabled = $this->is_user_disabled( $user_id );

		// Update the user's disabled status
		update_user_meta( $user_id, self::$user_meta_key, $disabled );

		$this->maybe_trigger_enabled_disabled_actions( $user_id, $originally_disabled, $disabled );
	}

	/**
	 * Returns whether or not the passed $user_id can be disabled
	 * @since 1.3.1
	 */
	public function can_disable( $user_id ) {
		// Don't disable super admins.
		if ( is_multisite() && is_super_admin( $user_id ) ) {
			return false;
		}

		// Make sure the user has access
		if ( ! current_user_can( $this->get_edit_cap() ) ) {
			return false;
		}

		// Don't disable the currently logged in user.
		if ( $user_id == get_current_user_id() ) {
			return false;
		}

		return true;
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

			// Is the user logging in disabled?
			if ( $this->is_user_disabled( $user->ID ) ) {

				/**
				 * Trigger an action when a disabled user attempts
				 * to login.
				 *
				 * @param WP_User $user The user who attempted to login
				 *
				 * @since 1.2.0
				 */
				do_action( 'disable_user_login.disabled_login_attempt', $user );

				return new WP_Error( 'disable_user_login_user_disabled', apply_filters( 'disable_user_login.disabled_message', __( '<strong>ERROR</strong>: Account disabled.', 'disable-user-login' ) ) );
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
	 * @since 1.3.2
	 * @param empty $output
	 * @param string $column_name
	 * @param int $user_id
	 * @return string
	 */
	public function manage_users_column_content( $output, $column_name, $user_id ) {

		if ( $column_name == 'disable_user_login' ) {
			if ( get_the_author_meta( self::$user_meta_key, $user_id ) == 1 ) {
				return __( 'Disabled', 'disable-user-login' );
			}
		}

		return $output; // always return, otherwise we overwrite stuff from other plugins.
	}

	/**
	 * Specify the width of our custom column
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
	public function handle_bulk_disable_users( $redirect_to, $doaction, $user_ids ) {
		if ( $doaction !== 'disable_user_login' && $doaction !== 'enable_user_login' ) {
			return $redirect_to;
		}

		$disabled = $doaction === 'disable_user_login' ? 1 : 0;

		$affected_user_count = 0;

		foreach ( $user_ids as $user_id ) {

			if ( $disabled === 1 && ! $this->can_disable( $user_id ) ) {
				continue;
			}

			// Store disabled status before update
			$originally_disabled = $this->is_user_disabled( $user_id );

			update_user_meta( $user_id, self::$user_meta_key, $disabled );

			$this->maybe_trigger_enabled_disabled_actions( $user_id, $originally_disabled, $disabled );

			$affected_user_count++;
		}

		if ( $disabled ) {
			$redirect_to = add_query_arg( 'disable_user_login', $affected_user_count, $redirect_to );
			$redirect_to = remove_query_arg( 'disable_user_login', $redirect_to );
		} else {
			$redirect_to = add_query_arg( 'disable_user_login',  $affected_user_count, $redirect_to );
			$redirect_to = remove_query_arg( 'disable_user_login', $redirect_to );
		}
		return $redirect_to;
	}

	/**
	 * Add admin notices after enabling/disabling users
	 * @since 1.0.6
	 */
	public function bulk_disable_user_notices() {
		if ( ! empty( $_REQUEST['disable_user_login'] ) ){
			$updated = intval( $_REQUEST['disable_user_login'] );
			printf( '<div id="message" class="updated">' .
				_n( 'Enabled %s user.',
					'Enabled %s users.',
					$updated,
					'disable-user-login'
				) . '</div>', $updated );
		}

		if ( ! empty( $_REQUEST['disable_user_login'] ) ){
			$updated = intval( $_REQUEST['disable_user_login'] );
			printf( '<div id="message" class="updated">' .
				_n( 'Disabled %s user.',
					'Disabled %s users.',
					$updated,
					'disable-user-login'
				) . '</div>', $updated );
		}
	}

	/**
	 * Checks if a user is disabled
	 *
	 * @since  1.2.0
	 *
	 * @param int $user_id The user ID to check
	 * @return boolean true if disabled, false if enabled
	 */
	private function is_user_disabled( $user_id ) {

		// Get user meta
		$disabled = get_user_meta( $user_id, self::$user_meta_key, true );

		// Is the user logging in disabled?
		if ( $disabled == '1' ) {
			return true;
		}

		return false;

	} //end function is_user_disabled

	/**
	 * Conditionally trigger enabled/disabled action hooks based on change in user disabled status.
	 *
	 * @since  1.2.0
	 * @access private
	 *
	 * @param $user_id             int 	   The user ID of the affected user.
	 * @param $originally_disabled boolean Whether or not the user was previously disabled.
	 * @param $disabled            boolean Whether or not the user is currently disabled.
	 */
	private function maybe_trigger_enabled_disabled_actions( $user_id, $originally_disabled, $disabled ) {

		/**
		 * Trigger an action when a disabled user's account has been
		 * enabled.
		 *
		 * @since 1.2.0
		 * @param int $user_id The ID of the user being enabled
		 */
		if ( $originally_disabled && $disabled == 0 ) {
			do_action( 'disable_user_login.user_enabled', $user_id );
		}
 		/**
		 * Trigger an action when an enabled user's account is disabled
		 *
		 * @since 1.2.0
		 * @param int $user_id The ID of the user being disabled
		 */
		if ( ! $originally_disabled && $disabled == 1 ) {
			do_action( 'disable_user_login.user_disabled', $user_id );
		}

	} //end function maybe_trigger_enabled_disabled_actions

	/**
	 * Force the passed $user_id to logout of WP
	 * @since 1.3.0
	 * @param int $user_id The ID of the user to logout
	 */
	public function force_logout( $user_id ) {

		// Get all sessions for $user_id
		$sessions = WP_Session_Tokens::get_instance( $user_id );

		// Destroy all the sessions for the user.
		$sessions->destroy_all();

	} //end function force_logout

} //end class SS_Disable_User_Login_Plugin

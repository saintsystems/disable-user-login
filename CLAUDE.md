# Disable User Login — Free Plugin

## Overview

Provides the ability to disable user accounts and prevent them from logging in. Checkbox on user profile, quick-link enable/disable on user list, bulk actions, and custom disabled message settings.

- **Repo:** `saintsystems/disable-user-login` on GitHub
- **WordPress.org:** https://wordpress.org/plugins/disable-user-login/
- **Minimum Requirements:** PHP 7.4+, WordPress 6.2+

## Architecture

- Entry: `disable-user-login.php` → `SS_Disable_User_Login_Plugin` (singleton, accessed via `SSDUL()`)
- Loads at priority 11 on `plugins_loaded`
- Single class file in `includes/`

### Key Class

`SS_Disable_User_Login_Plugin` handles everything:

| Area | How |
|---|---|
| **Disable check** | `is_user_disabled($user_id)` — reads `_is_disabled` user meta via `get_user_meta()` |
| **Login block** | `user_login()` hooked to `authenticate` filter at priority 1000. Returns `WP_Error` for disabled users. |
| **Profile UI** | `add_disabled_field()` on `edit_user_profile` — renders checkbox. `save_disabled_field()` on `edit_user_profile_update`. |
| **Users list** | Custom "Disabled" column via `manage_users_columns` / `manage_users_custom_column`. Quick-link enable/disable via AJAX (`ssdul_enable_disable_user`). |
| **Bulk actions** | `bulk_action_disable_users` / `handle_bulk_disable_users` on `bulk_actions-users` / `handle_bulk_actions-users`. |
| **Force logout** | `force_logout()` destroys all sessions when a user is disabled, hooked to `disable_user_login.user_disabled`. |
| **App passwords** | `maybe_disable_application_passwords_for_user()` blocks application passwords for disabled users. |
| **Settings** | Settings > Disable User Login — custom disabled message with live preview. Stored in `disable_user_login_settings` option. |

### User Meta

- Key: `_is_disabled` (accessed via `SS_Disable_User_Login_Plugin::user_meta_key()`)
- Values: `1` = disabled, `0` or absent = enabled

### Action Hooks

- `disable_user_login.user_disabled` — fired when a user is disabled (int `$user_id`)
- `disable_user_login.user_enabled` — fired when a user is enabled (int `$user_id`)
- `disable_user_login.disabled_login_attempt` — fired when a disabled user attempts to login (WP_User `$user`)

### Filter Hooks

- `disable_user_login.disabled_message` — customize the error message shown to disabled users

### Admin JS

`assets/js/admin.js` — jQuery. Quick-link toggle (AJAX enable/disable), nonce cloning for bulk actions.

## Release

Automated via release-please. Conventional commits (`feat:`, `fix:`, `BREAKING CHANGE`).
- Workflow: `.github/workflows/release-please.yml`
- Deploys to GitHub Releases + WordPress.org SVN
- Version markers: PHP header uses `x-release-please-start-version`/`x-release-please-end` block annotation. Class file uses `// x-release-please-version` inline comment. `readme.txt` Stable tag/Version updated via `sed` in the workflow.

## Testing

```bash
composer install
WP_TESTS_PHPUNIT_POLYFILLS_PATH=vendor/yoast/phpunit-polyfills \
WP_TESTS_DIR=/tmp/wordpress-tests-lib \
vendor/bin/phpunit
```

Tests in `tests/`. Bootstrap loads WordPress + this plugin. Covers login blocking, column display, bulk actions, application passwords, session logout, action hooks, and filters.

=== Disable User Login ===
Contributors: saintsystems, anderly
Donate link: https://ssms.us/donate
Tags: users, user, login, account, disable
Requires at least: 6.2
Tested up to: 6.8.2
Requires PHP: 7.4
Stable tag: 2.1.1
Version: 2.1.1
License: GPLv3

Provides the ability to disable user accounts and prevent them from logging in.

== Description ==

This plugin gives you the ability to disable specific user accounts via a profile setting.

Once installed and activated, a checkbox appears on the user profile settings (only for admins). When checked, the user's account will be disabled and they will be unable to login with the account. If they try to login, they are instantly logged out and redirected to the login page with a message that notifies them their account is disabled.

This can be useful in a few situations.

* You want freelance writers to still show up in the authors box, but you don't want them to be able to login.
* You have former employees who have authored posts and you don't want to delete them or reassign their posts to other users, but still need them to show up in the "Authors box."
* You are working on a site for a client who has an account, but do not want him to login and/or make changes during development.
* You have a client who has an unpaid invoice.

**[This plugin is on GitHub!](https://github.com/saintsystems/disable-user-login/)** Pull requests are welcome. If possible please report issues through Github.

= Upgrade to Pro =

[Disable User Login Pro](https://www.saintsystems.com/products/disable-user-login-pro/) adds powerful tools for managing user accounts at scale:

- **CSV Import & Export** — bulk enable or disable hundreds of users at once via spreadsheet
- **Email Notifications** — automatically notify users when their account is disabled or re-enabled, with customizable email templates, merge tags, and optional admin CC
- **Audit Log** — full history of every disable/enable action with timestamps, who performed it, and why
- **Filterable User List** — "Disabled" and "Enabled" filter views on the Users screen with count badges and a sortable Disabled column

Perfect for membership sites, nonprofits managing volunteers, agencies onboarding/offboarding clients, and universities managing student accounts.

[Learn more about Disable User Login Pro](https://www.saintsystems.com/products/disable-user-login-pro/)

== Installation ==

1. Upload or extract the `disable-user-login` folder to your site's `/wp-content/plugins/` directory. You can also use the *Add new* option found in the *Plugins* menu in WordPress.
2. Enable the plugin from the *Plugins* menu in WordPress.

= Usage =

1. Edit any user and then look for the "Disable User Account" checkbox.
2. Bulk Disable/Enable user accounts by selecting one or more user accounts and then choosing *Enable* or *Disable* from the bulk actions menu.

== Frequently Asked Questions ==

= Can I change the message a disabled user sees? =

Yes! You can customize the disabled user message in two ways:

1. **Admin Panel (Easy)**: Go to Settings > Disable User Login in your WordPress admin to customize the message via a user-friendly interface.
2. **Filter Hook (Advanced)**: Use the `disable_user_login.disabled_message` filter in your theme or plugin code for programmatic customization.

= Support =

Please visit the [Disable User Login support forum on WordPress.org](https://wordpress.org/support/plugin/disable-user-login) for basic support and help from other users. Since this is a free plugin, we respond to these as we have time.

Priority support is available for [Disable User Login Pro](https://www.saintsystems.com/products/disable-user-login-pro/) customers.

== Screenshots ==

1. User profile setting available to administrators.
2. Message when a disabled user tries to login.
3. Disable User Login Pro — Export users to CSV with enabled/disabled status.
4. Disable User Login Pro — Import CSV to bulk enable or disable users.

== Changelog ==

#### 2.1.1 - Apr 2, 2026
- Fix: bump column filter priority and add multisite column hook

#### 2.1.0 - Apr 2, 2026
- Feature: add pro upsell on plugin row, settings page, and readme
- Feature: add tabbed settings page with hooks for pro plugin

#### 2.0.0 - Apr 2, 2026
- Minimum requirements raised from PHP 5.6 to 7.4 and WordPress 4.7 to 6.2.
- Feature: add PHP 7.4+ and WP 6.2+ compatibility checks

= 1.3.12 =
* Fix inconsistent user meta retrieval #15
* Bump tested up to WP 6.8.2

= 1.3.11 =
* Add custom message settings page - users can now customize the disabled account message via the WordPress admin panel (Settings > Disable User Login).
* Maintain backward compatibility with existing filter hooks.

= 1.3.10 =
* Bump tested WP version.

= 1.3.9 =
* Fix bulk action nonce verification.

= 1.3.8 =
* Improved user-specific nonce validation.

= 1.3.7 =
* Add hooks for multisite.

= 1.3.6 =
* Permission fix on quick links.
* Translation updates.

= 1.3.5 =
* Fix to disable application passwords (fixes #7).

= 1.3.4 =
* Add user enable/disable quick links.

= 1.3.3 =
* Bump WP tested version.

= 1.3.2 =
* Static method to return user meta key
* Fix some comments

= 1.3.1 =
* Update to prevent disabling super admins on bulk disable.
* Update to check user edit permission on bulk disable.
* Updated to prevent current user from disabling his/her own account on bulk disable.

= 1.3.0 =
* Force disabled users to logout.
* Tested up to WP 5.7

= 1.2.1 =
* Tested up to WP 5.6

= 1.2.0 =
* Add action hooks when user accounts are enabled/disabled and when disabled user attempts to login.

= 1.1.0 =
* Switch to `authenticate` filter for user login.
* Multisite support.

= 1.0.1 =
* Version bump.

= 1.0.0 =
* Initial version.

=== Disable User Login ===
Contributors: saintsystems, anderly
Donate link: https://ssms.us/donate
Tags: users, user, login, account, disable
Requires at least: 4.7.0
Tested up to: 5.7
Requires PHP: 5.6
Stable tag: 1.3.2
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

== Installation ==

1. Upload or extract the `disable-user-login` folder to your site's `/wp-content/plugins/` directory. You can also use the *Add new* option found in the *Plugins* menu in WordPress.
2. Enable the plugin from the *Plugins* menu in WordPress.

= Usage =

1. Edit any user and then look for the "Disable User Account" checkbox.
2. Bulk Disable/Enable user accounts by selecting one or more user accounts and then choosing *Enable* or *Disable* from the bulk actions menu.

== Frequently Asked Questions ==

= Can I change the message a disabled user sees? =

Yes, there is a filter in place for that, `disable_user_login_notice`.

== Screenshots ==

1. User profile setting available to administrators.
2. Message when a disabled user tries to login.

== Changelog ==

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

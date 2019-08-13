=== Disable User Login ===
Contributors: saintsystems, anderly
Donate link: http://ssms.us/hVdk
Tags: users, login, account, disable
Requires at least: 4.7.0
Tested up to: 5.2.2
Requires PHP: 5.6
Stable tag: 1.0.0
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

1. Upload `disable-users` to your `/wp-content/plugins/` directory.
1. Edit any user and then look for the "Disable User Account" checkbox.

== Frequently Asked Questions ==

= Can I change the message a disabled user sees? =

Yes, there is a filter in place for that, `disable_user_login_notice`.

== Screenshots ==

1. User profile setting available to administrators.
2. Message when a disabled user tries to login.

== Changelog ==

= 1.0.0 =
* Initial version.

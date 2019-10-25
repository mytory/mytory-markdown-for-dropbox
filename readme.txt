=== Mytory Markdown for Dropbox ===
Contributors: mytory
Tags: markdown, dropbox
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=QUWVEWJ3N7M4W&lc=GA&item_name=Mytory%20Markdown&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted
Tested up to: 5.2.4
Stable tag: 1.0.4

Link with Dropbox, select markdown file. Then, post content will be updated. It's Cool.

== Description ==

**[See intro video.](https://youtu.be/fc-ROSH8Eng)**

[youtube https://www.youtube.com/watch?v=fc-ROSH8Eng]

한글 자막 있습니다.

* The plugin connect with Dropbox and link post with a markdown file on Dropbox.
* Edit the file on your computer and click update button. Then post will be updated.
* The plugin is compatible with other plugins like shortcode.
* Other default functions of Wordpress will work. This plugin do not disturb default work of Wordpress.
* Although your Dropbox file is losed by mistake, the content in Wordpress is not losed.
* Revoke Dropbox connection whenever you want. It will cause no problem.

= You can use your own Dropbox App instead of my app =

Define `MYTORY_MARKDOWN_APP_KEY`, `MYTORY_MARKDOWN_APP_SECRET` in `wp-config.php`. Then, your Dropbox App will control plugin.

[Create your Dropbox App on this page.](https://www.dropbox.com/developers/apps)

= You can use Multimarkdown 6 =

You can use the Muitimarkdown if you install it on your server. ((Website)[https://fletcher.github.io/MultiMarkdown-6/])

1. Multimarkdown option will be enabled if a multimarkdown execution is on OS PATH and web server can run it.

2. Otherwise, you can define `MYTORY_MARKDOWN_MULTIMARKDOWN_EXECUTION` constant on `wp-config.php`.
   ex) `define('MYTORY_MARKDOWN_MULTIMARKDOWN_EXECUTION', '/opt/multimarkdown/bin/multimarkdown');`

== Screenshots ==

1. Select a file in Dropbox.
2. File list of Dropbox.
3. Update on view page.
4. Select your favorite Markdown Engine.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/mytory-markdown-for-dropbox` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Go to 'Settings->Mytory Markdown for Dropbox Settings' screen and link with your Dropbox account.

== Changelog ==

= 1.0.4 =

Added the multimarkdown engine option.

= 1.0.3 =

Fixed revoking not work.

= 1.0.2 =

Disable on Gutenberg Editor, and show notice. Please rest assured. You can install Classic Editor Plugin
to use Mytory Markdown for Dropbox. And... I'll support Gutenberg Editor.

= 1.0.1 =

Apply Dropbox API change.

= 1.0 =

Initial version.
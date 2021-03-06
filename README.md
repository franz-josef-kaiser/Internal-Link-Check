# This plugin currently is under *heavy* development

As I'm currently reworking the whole core, here's the late *Alpha* or early *Beta*.
Please note that this release is moving towards a Beta and then RC candidate, but in the meantime
features might get added or dropped. Only *the core functionality* is somewhat *safe to use*.
Please use with caution and if you find a strange behavior, please take yourself a minute and *[report a bug](https://github.com/franz-josef-kaiser/Internal-Link-Check/issues/new)*.
Software development takes two sides: The devloper and the user. Want to become a user? Give it a test drive.

If you like it and want to tell me, drop a mail or just open an issue. Or [buy me a beer](http://unserkaiser.com/donate/)!

As you're already thinking about giving this a test run: If you find a feature, just add your request
as issue as well. I'll consider and discuss everything down the road.

---

## Plugin Name

* Plugin Name:        Internal Link Checker
* Plugin URI:         http://unserkaiser.com/plugins/internal-link-checker/
* Author:             Franz Josef Kaiser, Patrick Matsumura, Rodolfo Buaiz
* Author URI:         http://unserkaiser.com/
* Tags:               admin, link, links, meta, box, meta_box, missing, blogroll, broken, maintenance, posts, 404
* Requires at least:  3.0
* Tested up to:       3.8
* Stable tag:         0.6

## Description

### Internal Link Checker

Adds a meta box to the post edit screen that shows all internal links from other posts to the currently displayed post. The plugin works out of the box.


## Installation

No explanation needed - works out of the box. Just activate and be safe.


## Frequently Asked Questions

### How-to

The old meta box content filter from pre 1.0 versions won't work anymore. Updates & How-Tos will follow here.
Or on a dedicated wiki page on this repo.


## Languages

Translation ready.
If you want to help translating, please contact me on G+.

Included:
EN/DE (Patrick Matsumura)
EN/DE & EN/ES (Rodolfo Buiaz)

## Screenshots

1. meta box in post edit admin UI screen


## Changelog

 * v1.0.0

	> Start complete rework.

	> Everything as much OOP as possible with WP Italian food style code.

	> Switched License to MIT. Less headaches.

 * v0.6.2

	> Reformatted code styling to prepare rewrite

 * v0.6.1

	> Shortened admin table class

 * v0.6

	> Major improvements in code length

 * v0.5.4

	> Empty posts fix

 * v0.5.3
	Code styling

 * v0.5.2

	> Prepare SQL

 * v0.5.1

	> Separate textdomain function

 * v0.5

	> Now supports sorting by column

 * v0.4

	> Now supports pagination for the meta box to take less space in the UI in cases where there are more than just a few links

 * v0.3

	> Final stable release with new WP Admin Tables class API in use. Typo & Language fixes, fully translated

 * v0.2.9

	> Check for remote readme file availibility before get file contents on update

	> The meta box content filter disappeared and was replaced by an action to override the content

 * v0.2.8

	> Added better update messages for plugin list screen

 * v0.2.7.1

	> Moved to admin edit post permalinks

 * v0.2.7

	> Added native WP admin UI table to the meta box

 * v0.2.6.6

	> Moved Text Domain to plugin header comment for easier maintainance and transparency

 * v0.2.6.5

	> Added wrapper function to call plugin data

 * v0.2.6.4

	> Changed meta box title to "Internal Links" to not have long titles in screen options tab

 * v0.2.6.3

	> Added mu-plugins directory for l10n lang files loading

 * v0.2.6.2

	> Avoids loading on every $_REQUEST. Now only loads on post.php in admin (post/page/cpt new/edit) screens

 * v0.2.6.1

	> Reworked translation files

 * v0.2.6

	> Added singular/plural translation strings

	> Grouped results by post type column

 * v0.2.5

	> Added auto-correction for li elements & container

 * v0.2.4

	> Added MarkUp & MarkUp filter functions that work based on an initial settings array

 * v0.2.2

	> Added authors file

	> Made links unique and minor fixes

 * v0.2.1

	> Minor fixes

	> Added translation props Patrick Matsumura

 * v0.2

	> Moved to class to clean up global namespace

	> Added next step for translations

 * v0.1.3

	> Added screenshot, readme.txt for wp.org repo & fixed not echoing if there are no links

 * v0.1.2

	> Meta box content now displays inside an unordered list

 * v0.1.1

	> Should now work with post titles & post slugs/names

 * v0.1

	> First version

	> Can now be easier extended or used on front
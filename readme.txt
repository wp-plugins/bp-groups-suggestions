===  BP Groups Suggestions ===
Contributors: lenasterg
Tags: buddypress, groups, suggested groups, suggested groups widget
Requires at least:  WP 3.5, BuddyPress 1.7
Tested up to: 3.7.1, BuddyPress 2.0.1
Stable tag: 1.3
License: GNU General Public License 3.0 or newer (GPL) http://www.gnu.org/licenses/gpl.html
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=Q4VCLDW4BFW6L

Adds  Suggested groups functionality into Buddypress.

== Description ==
Adds "Suggested groups" functionality into Buddypress groups.
By default, the plugin uses the user's friends's groups in order to suggest groups of the login user, but this can be extended throught available filters.
It adds a "Suggested group" tab into the Groups Directory page, and also a widget "Suggested groups" is available.
The login user can hide groups from suggestion list, by pressing the "Remove group", either through the widget,
the "Suggested groups" tab or by the group's homepage. Also the login user can reset the hidden suggestion list.
The plugin uses various 'filters' so a developer can extend it, for example to include admins specified groups as suggested,
or to exclude groups from suggestion list.

Special thanks goes to Brajesh Singh, whoes 'BP Groups Suggest Widget' (http://buddydev.com/plugins/bp-group-suggest/) gave the idea for extending it to a full plugin.

PLEASE: If you have any issues or it doesn't work for you, please report in support forum. It doesn't help anyone to mark "broken" without asking around. Thanks!

== Installation ==

1. Upload folder `bp-group-suggestions` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==
= Which are the available filters =
1. add_excluded_groups_suggestions: Allows developer to exclude groups from displaying as suggested.
    For example, see function my_groups_to_exclude on bp-suggest-functions.php
2. get_possible_groups_suggestions_by_plugin: Allows developer to add groups into suggestion list
3. add_users_for_group_search: Add users which groups should be used for group search. From example, see function friends_for_group_suggestion_ls on bp-suggest-functions.php
4. bp_get_group_remove_suggestion_button: Allow developer to change the look and action of remove suggestion button
5. ls_group_suggestions_description: Change the message on group_loop head. From example, see function friends_grsug_ls_description which is used inside friends_for_group_suggestion_ls on bp-suggest-functions.php

= I think there should be more suggested groups =
Only recenlty active groups are displayed.
Recently active means 'last_activity' date of group is < BP_GROUP_SUGGESTIONS_GROUPS_LAST_ACTIVITY_INTERVAL . " " . BP_GROUP_SUGGESTIONS_GROUPS_LAST_ACTIVITY_INTERVAL_TYPE. .
Default setting is groups active the last 4 weeks, which means BP_GROUP_SUGGESTIONS_GROUPS_LAST_ACTIVITY_INTERVAL = '4' and
BP_GROUP_SUGGESTIONS_GROUPS_LAST_ACTIVITY_INTERVAL_TYPE='WEEK'.
Both consts are defined at the beginning of loader.php file.

= Can I extend/impove the plugin =
Sure. Actually, I would be happy if someone gives a hand to make the best of the suggested groups idea. Various things (brainstorming) which need to be fixed are available on /ideas.txt

== Screenshots ==

1. The "Suggested groups" tab on Groups Directory page.
2. Hide this Suggestion button on Group description (in case the group is on the suggested groups list).
3. Message on Group home page is the group, which is displayed in every visit after the group is removed from suggestion list.
4. Message on "Suggested groups" tab on Groups Directory page, in case none group is suggested
5. Widget settings

== Changelog ==

= 1.3 =
* Minor fix for BP_GROUP_SUGGESTIONS_LS_DIR notice

= 1.2 =
* Added two more option in widget: Show join group button and limit group's title charachers in widget (See screenshot 5).
* Fix Greek translation
* Other minor fixes

= 1.1 =
* Speed enhancement on the widget

= 1.0 =
Initial version


== Notes ==

ideas.txt - contains ideas (brainstorming) which can improve the plugin. Feel free to build some of them.
License.txt - contains the licensing details for this component.

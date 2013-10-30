<?php
/* * * GROUP SUGGESTION SPECIFIC FUNCTIONS* */
if (!(function_exists('ls_group_suggestions_title'))) {

    /**
     * Prints the title in groups page
     * @global type $wpdb
     * @global type $bp
     */
    function ls_group_suggestions_title() {
        global $wpdb, $bp;
        if (is_user_logged_in()) {
            ?><li id="groups-lssuggestions"><a href="<?php echo trailingslashit(bp_loggedin_user_domain() . bp_get_groups_slug() . '/my-group-suggestions'); ?>"><?php printf(__('Suggested Groups <span>%s</span>', 'bp_group_suggestions_ls'), BPGroupSuggest::count_possible_groups()); ?></a></li>
            <?php
        }
    }

    add_action('bp_groups_directory_group_filter', 'ls_group_suggestions_title');
}

function ls_group_suggestions_description($in) {
    return ls_group_suggestions_get_description() . $in;
}

/**
 * 
 * @global type $wpdb
 * @global type $bp
 * @uses apply_filter (Calls 'ls_group_suggestions_description' in order to change the message on group_loop head)
 */
function ls_group_suggestions_get_description() {
    global $wpdb, $bp;
    $message_div_start = '';
    $messageDefault = __("The suggested groups are based on: ", 'bp_group_suggestions_ls');
    $messageDefault = apply_filters("ls_group_suggestions_description", $messageDefault);
    $message_div_end = '. ';
    $message = $message_div_start . $messageDefault . $message_div_end;
    return $message;
}

/**
 * Creates the query for the loop and adds the hide suggestion button 
 * Prints an info message when we can't suggest groups
 * @global type $wpdb
 * @global type $bp
 * @param string $qs
 * @param type $object
 * @param type $object_filter
 * @param type $object_scope
 * @param type $object_page
 * @param type $object_search_terms
 * @param type $object_extras
 * @return string
 * 
 */
function add_suggestions_search_to_query($qs, $object, $object_filter, $object_scope, $object_page, $object_search_terms, $object_extras) {
    global $wpdb, $bp;
    if (is_user_logged_in() && bp_is_directory()) {
        if ($object_scope == 'lssuggestions') {
            if (BPGroupSuggest::get_suggestions_groups_id()) {
                $qs .= "&include=" . implode(',', BPGroupSuggest::get_suggestions_groups_id());
            } else {
                $qs .= "&include=0,0";
                echo '<script> jQuery(document).ready(function() {
                var j = jQuery;j(".info").html("<p>' . __("We don't have enough details to suggest a group yet.", 'bp_group_suggestions_ls') . '<br>' . __("Try make some more friends, first.", "bp_group_suggestions_ls") . '</p>");});</script>';
                echo ls_bp_unhide_button();
            }

            add_filter('bp_get_groups_pagination_count', 'ls_group_suggestions_description');
            add_action('bp_directory_groups_actions', 'ls_bp_group_suggestion_hide');
            add_action('bp_before_directory_groups_list', 'ls_group_suggestions_description');
            add_action('bp_before_directory_groups_list', 'ls_bp_unhide_button');
        }
    }
    return $qs;
}

add_filter('bp_dtheme_ajax_querystring', 'add_suggestions_search_to_query', 10, 7); //tweak query string to account for the search term.
add_filter('bp_legacy_theme_ajax_querystring', 'add_suggestions_search_to_query', 10, 7); //tweak query string to account for the search term. In case of non buddypress theme

/**
 * 
 * @global type $groups_template
 * @param type $group_id
 */
function ls_bp_group_suggestion_hide($group_id = false) {
    global $groups_template;
    if (empty($group_id)) {
        $group_id = $groups_template->group->id;
    }
    echo '<br/>';
    BPGroupSuggest::get_hide_suggestion_link($group_id);
}

add_action('bp_group_header_actions', 'bp_group_remove_suggestion_button');

/**
 * Calls the reset hidden groups suggestion button
 */
function ls_bp_unhide_button() {
    BPGroupSuggest::get_unhide_button();
}

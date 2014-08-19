<?php
if ( ! class_exists( 'BPGroupSuggest' ) ) :

    class BPGroupSuggest {

        static $instance ;

        private function __construct() {
            //load script
            add_action( 'wp_print_scripts' , array ( &$this , 'load_js' ) ) ;
//            add_action('wp_enqueue_scripts', array(&$this, 'load_js'));
            //ajax handling of hiding the suggestion
            add_action( 'wp_ajax_group_suggest_remove_suggestion' , array ( &$this , 'hide_suggestion' ) ) ;
            //ajax handling of resetting the suggestions
            add_action( 'wp_ajax_group_reset_suggestions' , array ( &$this , 'reset_suggestions' ) ) ;

            //load text domain
            add_action( 'bp_loaded' , array ( &$this , 'load_textdomain' ) , 2 ) ;
            include_once 'widgets/bp-group-suggest-widget.php' ;
            add_action( 'bp_loaded' , 'group_suggest_register_widget_ls' ) ;
            include_once 'bp-suggest-functions.php' ;
            include_once 'groups/suggested_groups.php' ;
        }

//public method for getting the instance/initializing
        function get_instance() {

            if ( ! isset( self::$instance ) )
                self::$instance = new self() ;
            return self::$instance ;
        }

        //
        /**
         * Ajax helper for hiding,it keeps the hidden group in usermeta
         * @version 2, 23/10/2013
         * @global type $bp
         * @return type
         */
        function hide_suggestion() {
            global $bp ;
            $suggestion_id = $_POST[ 'suggestion_id' ] ;
            check_ajax_referer( 'group-suggestion-remove-' . $suggestion_id ) ;

            if ( empty( $suggestion_id ) || ! is_user_logged_in() )
                return ;
            $user_id = bp_loggedin_user_id() ;

            $have_hidden = get_user_meta( $user_id , "hidden_group_suggestions" , true ) ;
            echo count( $have_hidden ) ;
            if ( $have_hidden != '' ) {
                $excluded = ( array ) ($have_hidden) ;
                $excluded[] = $suggestion_id ;
                update_user_meta( $user_id , "hidden_group_suggestions" , $excluded ) ;
            } else {
                $excluded[] = $suggestion_id ;
                add_user_meta( $user_id , "hidden_group_suggestions" , $excluded ) ;
            }
            wp_cache_delete( 'get_users_groups_for_user' . $user_id ) ;
            exit( 0 ) ;
        }

        /**
         * Get the hidden group ids as an array
         * @param type $user_id
         * @return array
         */
        function get_hidden( $user_id = null ) {
            if ( ! $user_id )
                $user_id = bp_loggedin_user_id() ;
            $have_hidden = get_user_meta( $user_id , "hidden_group_suggestions" , true ) ;
            if ( $have_hidden != '' ) {
                return $have_hidden ;
            }
        }

        /**
         * @param type $user_id
         * @return type
         * @author stergatu
         * @since
         * @version 1, 8/10/2013
         */
        function count_get_hidden( $user_id = null ) {
            if ( ! $user_id )
                $user_id = bp_loggedin_user_id() ;
            return count( self::get_hidden( $user_id ) ) ;
        }

        function print_count_hidden( $user_id = null ) {
            if ( ! $user_id )
                $user_id = bp_loggedin_user_id() ;
            return '<span id="num_hidden_groups">' . self::count_get_hidden( $user_id ) . '</span>' ;
        }

        /**
         * Ajax helper for reseting the suggestions list,removes the hidden groups from usermeta
         * @global type $bp
         * @return type
         */
        function reset_suggestions( $url ) {
            global $bp , $wpdb ;
            check_ajax_referer( 'reset-group-suggestions' ) ;
            if ( ! is_user_logged_in() )
                return false ;
            $user_id = bp_loggedin_user_id() ;
            $resetted = self::count_get_hidden() ;
            if ( delete_user_meta( $user_id , "hidden_group_suggestions" ) ) {
                wp_cache_delete( 'get_users_groups_for_user' . $user_id ) ;
                echo $resetted ;
            }
            exit( 0 ) ;
        }

        /**
         * Get possible groups
         * @uses apply_filter (Calls 'add_excluded_groups_suggestions').
         * @uses apply_filter (Calls 'get_possible_groups_suggestions_by_plugin').
         * @param type $user_id
         * @return type
         * @author stergatu
         * @todo add hook for other plugins to add users_ids
         */
        function get_possible_groups( $user_id = null ) {
            global $bp , $wpdb ;
            if ( ! $user_id )
                $user_id = bp_loggedin_user_id() ;
            //groups the user has hidden
            $my_excluded = ( array ) self::get_hidden( $user_id ) ;
            $my_external_excluded = array () ;
            $my_external_excluded = apply_filters( 'add_excluded_groups_suggestions' , $my_external_excluded ) ;

            //make an array of users group+groups hidden by user
            $excluded = array_merge( $my_external_excluded , $my_excluded ) ;
            $excluded = array_filter( array_unique( $excluded ) ) ;

            $users_for_group_search = self::get_users_for_group_search() ;
            $possible_groups = get_users_groups( $users_for_group_search , $excluded ) ;

            $have_possible_groups_plugin = array () ;

            $have_possible_groups_by_plugin = apply_filters( 'get_possible_groups_suggestions_by_plugin' , $have_possible_groups_plugin ) ;
            //remove from $have_possible_groups_by_plugin the user $excluded groups
            if ( ! empty( $have_possible_groups_by_plugin ) ) {
                $have_possible_groups_by_plugin = array_diff( $have_possible_groups_by_plugin , $excluded ) ;
                //merge the $possible_groups with $have_possible_groups_by_plugin
                $possible_groups = array_merge( $possible_groups , $have_possible_groups_by_plugin ) ;
                $possible_groups = array_filter( array_unique( $possible_groups ) ) ;
            }
            return $possible_groups ;
        }

        /**
         * Get users in order for use their group as possible suggested groups
         * @uses apply_filter (Calls 'add_users_for_group_search').
         * @global type $bp
         * @global type $wpdb
         * @return array
         */
        function get_users_for_group_search() {
            global $bp , $wpdb ;
            $users_for_group_search = array () ;
            $users_for_group_search = apply_filters( 'add_users_for_group_search' , $users_for_group_search ) ;
            return $users_for_group_search ;
        }

        /**
         * @param type $user_id
         * @return type
         * @author stergatu
         * @since
         * @version 1, 8/10/2013
         */
        function count_possible_groups( $user_id = null ) {
            global $bp , $wpdb ;
            if ( ! $user_id )
                $user_id = bp_loggedin_user_id() ;
            return count( self::get_possible_groups( $user_id ) ) ;
        }

        function get_suggestions_groups_id( $limit = null , $user_id = null ) {
            global $bp , $wpdb ;
            if ( ! $user_id )
                $user_id = bp_loggedin_user_id() ;

            $possible_groups = self::get_possible_groups( $user_id ) ;
            if ( ! empty( $possible_groups ) ) {
                shuffle( $possible_groups ) ; //randomize
                if ( $limit ) {
                    $groupsSug = array_slice( $possible_groups , 0 , $limit ) ;
                } else {
                    $groupsSug = $possible_groups ;
                }
                return $groupsSug ;
            }
            return false ;
        }

        /**
         * show the list here. Used for widget
         * @global type $bp
         * @global type $wpdb
         * @param integer $limit
         * @param integer $mikos
         * @param integer $show_join
         * @param integer $user_id
         * @version 2, 19/8/2014 added:  mikos for group title length, show_join for join group button
         */
        function suggestions_list( $limit = null , $mikos = null , $show_join = null , $user_id = null ) {
            global $bp , $wpdb ;

            if ( ! $user_id )
                $user_id = bp_loggedin_user_id() ;

            $groupsSug = self::get_suggestions_groups_id( null , $user_id ) ;

            if ( ! empty( $groupsSug ) ):
                if ( $limit ) {
                    $groupsSug = array_slice( $groupsSug , 0 , $limit ) ;
                }
                ?>
                                <ul id="suggested-groups-list" class="item-list suggested-group-item-list">
                    <?php
                    foreach ( $groupsSug as $group_id ):
                        $group_sug = groups_get_group( array ( 'group_id' => $group_id ) ) ;
                        ?>
                    <li><div class= "item-avatar"><a href = "<?php bp_group_permalink( $group_sug ) ?>"
                                                                         title = "<?php bp_group_name( $group_sug ) ?>"><?php echo bp_core_fetch_avatar( array ( 'type' => 'thumb' , 'width' => 50 , 'height' => 50 , 'object' => 'group' , 'item_id' => $group_id ) ) ; ?></a>
                                            </div>
                                            <div class="item">
                                                <div class="item-title"><a href="<?php bp_group_permalink( $group_sug ) ?>" title="<?php bp_group_name( $group_sug ) ?>">
                                                        <?php
                                                        if ( ($mikos) && ($mikos > 0) ) {
                                                            echo mb_substr( bp_get_group_name( $group_sug ) , 0 , $mikos ) ;
                                                            if ( mb_strlen( bp_get_group_name( $group_sug ) ) > $mikos ) {
                                                                echo '...' ;
                                                            }
                                                        } else {
                                                            bp_group_name( $group_sug ) ;
                                        }
                                        ?>
                                                                            </a></div>

                                                                        <div class="item-meta">
                                                        <span class="activity">
                                                            <?php
                                                            printf( __( 'active %s' , 'buddypress' ) , bp_get_group_last_active( $group_sug ) ) ;
                                                            ?>
                                                        </span>
                                                    </div>
                                                    <div id="item-buttons">
                                                        <?php self::get_hide_suggestion_link( $group_id ) ; ?>
                                                        <?php
                                                        if ( $show_join == '1' ) {
                                                            bp_group_join_button( $group_sug ) ;
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </li>
                                        <?php endforeach ; ?>
                                </ul>
                            <?php else: ?>
                                <div id="message" class="info">
                                    <p><?php _e( "We don't have enough details to suggest a group yet." , 'bp_group_suggestions_ls' ) ?></p>
                                </div>

            <?php
            endif ;
        }

        /**
         * get the link for hide (x) button
         * @param type $possible_group_id
         */
        function get_hide_suggestion_link( $possible_group_id ) {
            $url = bp_get_root_domain() . "/remove-group-suggestion/?suggest_id=" . $possible_group_id . "&_wpnonce=" . wp_create_nonce( 'group-suggestion-remove-' . $possible_group_id ) ;
            ?>
                        <div class="generic-button remove-group-suggestion" id="removegroupsuggestion-<?php echo $possible_group_id ; ?>" style="margin-top:10px"><a href="<?php echo $url ; ?>" title="<?php _e( 'Hide this suggestion' , 'bp_group_suggestions_ls' ) ; ?>"><?php _e( 'Hide this suggestion' , 'bp_group_suggestions_ls' ) ; ?></a></div>
            <?php
        }

        /**
         * get the link for unhide button
         * @author stergatu
          @since
          @version 1, 10/10/2013
         */
        function get_unhide_button() {
            $url = bp_get_root_domain() . '/' . bp_get_groups_root_slug() . '/?_wpnonce=' . wp_create_nonce( 'reset-group-suggestions' ) ;
            printf( __( 'You have %s removed groups from suggestions' , 'bp_group_suggestions_ls' ) , self::print_count_hidden() ) ;
            ?>
                                    <div class="generic-button reset-group-suggestions" style="margin-top: 10px;margin-bottom: 10px"><a href="<?php echo $url ; ?>" title="<?php _e( 'Reset groups suggestions' , 'bp_group_suggestions_ls' ) ; ?>"><?php _e( 'Reset groups suggestions' , 'bp_group_suggestions_ls' ) ; ?></a></div>
                        <?php
                    }

                    /**
                     *
                     * @return type
                     */
        function load_js() {
            if ( ! is_user_logged_in() || is_admin() )
                return ;
            $gsuggest_url = plugin_dir_url( __FILE__ ) ; //with a trailing slash
            wp_enqueue_script( "group-suggest-js" , $gsuggest_url . "group-suggest.js" , array ( "jquery" ) ) ;
            wp_localize_script( 'group-suggest-js' , 'l10nBpGrSug' , array (
                'remove_done' => __( 'The group has been removed from the suggested groups' , 'bp_group_suggestions_ls' ) ,
            ) ) ;
        }

        /**
         *
         */
        function load_textdomain() {
            $locale = get_locale() ;
            // First look in wp-content/languages, where custom language files will not be overwritten by upgrades. Then check the packaged language file directory.
            $mofile_custom = WP_CONTENT_DIR . "/languages/bp_group_suggestions_ls-$locale.mo" ;
            $mofile_packaged = BP_GROUP_SUGGESTIONS_LS_DIR . "languages/bp_group_suggestions_ls-$locale.mo" ;

            if ( file_exists( $mofile_custom ) ) {
                load_textdomain( 'bp_group_suggestions_ls' , $mofile_custom ) ;
                return ;
            } else if ( file_exists( $mofile_packaged ) ) {
                load_textdomain( 'bp_group_suggestions_ls' , $mofile_packaged ) ;
                return ;
            }
        }

    }

    endif ;
//end of helper class
//initialize
//add_action('bp_init', 'ls_get_bpgroupsuggest_instance');
//function ls_get_bpgroupsuggest_instance() {
//    if (is_user_logged_in()) {
BPGroupSuggest::get_instance() ;

//    }
//}


function my_suggestion( $have_suggestions ) {
    $have_suggestions = array ( '27' , '83' ) ;

    return $have_suggestions ;
}

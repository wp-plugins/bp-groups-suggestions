<?php
/**
 *
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

class BPGroupSuggestionWidgetLs extends WP_Widget {

    const TEXTDOMAIN = 'bp_group_suggestions_ls';

    public function __construct() {
        parent::__construct('bpgrsugls', __('Group Suggestion Widget', self::TEXTDOMAIN), array('classname' => __CLASS__,
            'description' => __('Suggest groups for logged in user', self::TEXTDOMAIN)
                )
        );
    }

    /**
     *
     * @global type $bp
     * @param type $args
     * @param type $instance
     * @return type
     * @version 2, 6/3/2014, performance enhancement
     */
    function widget($args, $instance) {
        global $bp;

        if (!is_user_logged_in())
            return; //do not show to non logged in user
        extract($args);
        $title = apply_filters('widget_title', $instance['title']);
        echo $args['before_widget'];
        if (!empty($title)) :
            echo $args['before_title'] . $title . $args['after_title'];
        endif ;
        $countpossible = BPGroupSuggest::count_possible_groups() ;
        if ( $countpossible == '0' ) :
?>
                        <div id="message" class="info"><?php _e("We don't have enough details to suggest a group yet.", self::TEXTDOMAIN); ?><br>
                <?php _e('Try make some more friends, first', self::TEXTDOMAIN); ?></div>
            <?php
        else :
            BPGroupSuggest::suggestions_list($instance['max']);
            if ( $countpossible > $instance[ 'max' ] ) :
            ?>
                                <div  role="navigation"><a href="<?php echo bp_get_groups_slug(); ?>/?scope=lssuggestions" ><?php _e('See more suggestions', self::TEXTDOMAIN); ?></a></div>
                <?php
            endif;
        endif;

        echo $args['after_widget'];
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['max'] = absint($new_instance['max']);

        return $instance;
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    function form($instance) {
        $instance = wp_parse_args((array) $instance, array('title' => __('Group Suggestions', self::TEXTDOMAIN), 'max' => 5));
        $title = strip_tags($instance['title']);
        $max = absint($instance['max']);
        ?>
                <p>
                            <label for="bp-groups-suggest-widget-title"><?php _e('Title', self::TEXTDOMAIN); ?>
                                <input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" class="widefat" value="<?php echo esc_attr($title); ?>" />
                            </label>
                        </p>
                        <p>
                            <label for="bp-show-groups-widget-per-page"><?php _e('Max Number of suggestions:', self::TEXTDOMAIN); ?>
                                <input class="widefat" id="<?php echo $this->get_field_id('max'); ?>" name="<?php echo $this->get_field_name('max'); ?>" type="text" value="<?php echo esc_attr($max); ?>" style="width: 30%" />
                            </label>
                        </p>
                        <?php
                    }

}

//register widget
function group_suggest_register_widget_ls() {
    add_action('widgets_init', create_function('', 'return register_widget("BPGroupSuggestionWidgetLs");'));
}
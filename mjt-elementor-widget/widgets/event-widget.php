<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Event_Widget extends Widget_Base {

    public function get_name() {
        return 'event_widget';
    }

    public function get_title() {
        return 'Event Widget';
    }

    public function get_icon() {
        return 'eicon-calendar';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Content', 'plugin-name' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'number_of_events',
            [
                'label'   => __( 'Number of Events', 'plugin-name' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => 5,
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $query = new \WP_Query( [
            'post_type'      => 'event',
            'posts_per_page' => $settings['number_of_events'],
        ] );

        if ( $query->have_posts() ) {
            echo '<div class="event-list">';
            while ( $query->have_posts() ) {
                $query->the_post();
                echo '<div class="event-item">';              
                /* echo '<p>' . get_the_excerpt() . '</p>'; */
                if ( has_post_thumbnail() ) {
                    echo get_the_post_thumbnail( get_the_ID(), 'medium' );
                } 
				echo '<h3>' . get_the_title() . '</h3>';
				 $postID = get_the_ID() ;
				 $datetime = get_post_meta( $postID, '_dt_picker_meta_value', true );
				 $location_address = get_post_meta( $postID , 'ichc_location_address', true );
				?>
				<div class="fl-events-meta">
                    <div class="fl-event-date"><img src ="<?php echo plugin_dir_url( __dir__ ).'assets/img/calendar-clock.svg'; ?>" style="width:15px; height:15px;" alt=""/><span class="fl-body-light-font-style">Date:</span><?php echo $datetime; ?></div>
					<div class="fl-event-address"><img src ="<?php echo plugin_dir_url( __dir__ ).'assets/img/marker.svg'; ?>" style="width:15px; height:15px;" alt=""/><span class="fl-body-light-font-style">At:</span><?php echo $location_address; ?></div>
                </div>
				<?php  
                echo '</div>';
            }
            echo '</div>';
            wp_reset_postdata();
        } else {
            echo '<p>No events found.</p>';
        }
    }

    protected function _content_template() {}
}

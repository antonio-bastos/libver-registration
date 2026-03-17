<?php

if (!defined('ABSPATH')) {
    exit;
}

class LVR_Plugin
{
    private static $instance = null;

    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function init()
    {
        add_action('init', array($this, 'register_shortcodes'));
        add_action('admin_menu', array($this, 'register_admin_menu'));
    }

    public function register_shortcodes()
    {
        add_shortcode('libver_upcoming_activities', array($this, 'render_upcoming_activities'));
    }

    public function register_admin_menu()
    {
        add_menu_page(
            __('LibVer Registration', LVR_TEXT_DOMAIN),
            __('LibVer Registration', LVR_TEXT_DOMAIN),
            'lvr_manage',
            'lvr-registration',
            array($this, 'render_admin_dashboard'),
            'dashicons-calendar-alt'
        );
    }

    public function render_upcoming_activities($atts)
    {
        $atts = shortcode_atts(
            array(
                'limit' => 10,
            ),
            $atts,
            'libver_upcoming_activities'
        );

        $limit = max(1, (int) $atts['limit']);

        return '<div class="lvr-upcoming-activities" data-limit="' . esc_attr($limit) . '">'
            . esc_html__('Upcoming activities will appear here.', LVR_TEXT_DOMAIN)
            . '</div>';
    }

    public function render_admin_dashboard()
    {
        if (!current_user_can('lvr_manage')) {
            wp_die(esc_html__('You do not have permission to access this page.', LVR_TEXT_DOMAIN));
        }

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('LibVer Registration', LVR_TEXT_DOMAIN) . '</h1>';
        echo '<p>' . esc_html__('Admin dashboard coming soon.', LVR_TEXT_DOMAIN) . '</p>';
        echo '</div>';
    }
}

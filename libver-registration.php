<?php
/**
 * Plugin Name: LibVer Registration
 * Description: Library registration system for workshops, events, and space bookings.
 * Version: 0.1.0
 * Author: LibVer
 * Text Domain: libver-registration
 */

if (!defined('ABSPATH')) {
    exit;
}

define('LVR_VERSION', '0.1.0');
define('LVR_PLUGIN_FILE', __FILE__);
define('LVR_PLUGIN_DIR', __DIR__);
define('LVR_TEXT_DOMAIN', 'libver-registration');

require_once LVR_PLUGIN_DIR . '/includes/class-lvr-activator.php';
require_once LVR_PLUGIN_DIR . '/includes/class-lvr-deactivator.php';
require_once LVR_PLUGIN_DIR . '/includes/class-lvr-roles.php';
require_once LVR_PLUGIN_DIR . '/includes/class-lvr-db.php';
require_once LVR_PLUGIN_DIR . '/includes/class-lvr-plugin.php';

register_activation_hook(__FILE__, array('LVR_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('LVR_Deactivator', 'deactivate'));

LVR_Plugin::get_instance()->init();

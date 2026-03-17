<?php

if (!defined('ABSPATH')) {
    exit;
}

class LVR_DB
{
    const DB_VERSION = '0.1.0';

    public static function install()
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();

        $activities = self::table('activities');
        $children = self::table('children');
        $registrations = self::table('registrations');
        $attendance = self::table('attendance');

        $sql_activities = "CREATE TABLE {$activities} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            description longtext NULL,
            activity_type varchar(64) NOT NULL DEFAULT 'workshop',
            start_at datetime NOT NULL,
            end_at datetime NULL,
            timezone varchar(64) NOT NULL DEFAULT 'Europe/Athens',
            capacity int(11) NOT NULL DEFAULT 0,
            waitlist_capacity int(11) NULL,
            registration_open_at datetime NULL,
            registration_close_at datetime NULL,
            location varchar(255) NULL,
            age_min int(11) NULL,
            age_max int(11) NULL,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NULL,
            PRIMARY KEY  (id),
            KEY activity_type (activity_type),
            KEY start_at (start_at)
        ) {$charset_collate};";

        $sql_children = "CREATE TABLE {$children} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            parent_user_id bigint(20) unsigned NOT NULL,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            birthdate date NULL,
            notes text NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NULL,
            PRIMARY KEY  (id),
            KEY parent_user_id (parent_user_id)
        ) {$charset_collate};";

        $sql_registrations = "CREATE TABLE {$registrations} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            activity_id bigint(20) unsigned NOT NULL,
            child_id bigint(20) unsigned NOT NULL,
            parent_user_id bigint(20) unsigned NOT NULL,
            status varchar(32) NOT NULL DEFAULT 'registered',
            waitlist_position int(11) NULL,
            seat_number int(11) NULL,
            fee_amount decimal(10,2) NULL,
            payment_status varchar(32) NOT NULL DEFAULT 'unpaid',
            registered_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NULL,
            PRIMARY KEY  (id),
            KEY activity_id (activity_id),
            KEY child_id (child_id),
            KEY parent_user_id (parent_user_id)
        ) {$charset_collate};";

        $sql_attendance = "CREATE TABLE {$attendance} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            registration_id bigint(20) unsigned NOT NULL,
            status varchar(32) NOT NULL DEFAULT 'pending',
            checked_in_at datetime NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY registration_id (registration_id)
        ) {$charset_collate};";

        dbDelta($sql_activities);
        dbDelta($sql_children);
        dbDelta($sql_registrations);
        dbDelta($sql_attendance);

        add_option('lvr_db_version', self::DB_VERSION);
        if (!get_option('lvr_timezone')) {
            add_option('lvr_timezone', 'Europe/Athens');
        }
    }

    public static function table($name)
    {
        global $wpdb;

        return $wpdb->prefix . 'lvr_' . $name;
    }
}

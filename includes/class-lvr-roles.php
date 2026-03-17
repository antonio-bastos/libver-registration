<?php

if (!defined('ABSPATH')) {
    exit;
}

class LVR_Roles
{
    const ROLE_ADMIN = 'lvr_admin';
    const ROLE_INSTRUCTOR = 'lvr_instructor';
    const ROLE_PARENT = 'lvr_parent';
    const ROLE_CHILD = 'lvr_child';

    const CAP_MANAGE = 'lvr_manage';
    const CAP_INSTRUCT = 'lvr_instruct';
    const CAP_PARENT = 'lvr_parent';
    const CAP_CHILD = 'lvr_child';

    public static function add_roles()
    {
        add_role(
            self::ROLE_ADMIN,
            __('LibVer Admin', LVR_TEXT_DOMAIN),
            array(
                'read' => true,
                self::CAP_MANAGE => true,
                self::CAP_INSTRUCT => true,
                self::CAP_PARENT => true,
                self::CAP_CHILD => true,
            )
        );

        add_role(
            self::ROLE_INSTRUCTOR,
            __('LibVer Instructor', LVR_TEXT_DOMAIN),
            array(
                'read' => true,
                self::CAP_INSTRUCT => true,
            )
        );

        add_role(
            self::ROLE_PARENT,
            __('LibVer Parent', LVR_TEXT_DOMAIN),
            array(
                'read' => true,
                self::CAP_PARENT => true,
            )
        );

        add_role(
            self::ROLE_CHILD,
            __('LibVer Child', LVR_TEXT_DOMAIN),
            array(
                'read' => true,
                self::CAP_CHILD => true,
            )
        );

        self::ensure_admin_capabilities();
    }

    public static function remove_roles()
    {
        remove_role(self::ROLE_ADMIN);
        remove_role(self::ROLE_INSTRUCTOR);
        remove_role(self::ROLE_PARENT);
        remove_role(self::ROLE_CHILD);

        self::remove_admin_capabilities();
    }

    public static function ensure_admin_capabilities()
    {
        $role = get_role('administrator');

        if (!$role) {
            return;
        }

        $role->add_cap(self::CAP_MANAGE);
        $role->add_cap(self::CAP_INSTRUCT);
        $role->add_cap(self::CAP_PARENT);
        $role->add_cap(self::CAP_CHILD);
    }

    public static function remove_admin_capabilities()
    {
        $role = get_role('administrator');

        if (!$role) {
            return;
        }

        $role->remove_cap(self::CAP_MANAGE);
        $role->remove_cap(self::CAP_INSTRUCT);
        $role->remove_cap(self::CAP_PARENT);
        $role->remove_cap(self::CAP_CHILD);
    }
}

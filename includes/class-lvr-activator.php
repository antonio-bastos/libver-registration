<?php

if (!defined('ABSPATH')) {
    exit;
}

class LVR_Activator
{
    public static function activate()
    {
        LVR_Roles::add_roles();
        LVR_DB::install();
    }
}

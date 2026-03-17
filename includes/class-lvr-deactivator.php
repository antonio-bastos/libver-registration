<?php

if (!defined('ABSPATH')) {
    exit;
}

class LVR_Deactivator
{
    public static function deactivate()
    {
        // Keep data by default; do not remove roles or tables on deactivation.
    }
}

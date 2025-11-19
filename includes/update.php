<?php

final class RY_WEZI_update
{
    public static function update()
    {
        $now_version = RY_WEZI::get_option('version');

        if (false === $now_version) {
            $now_version = '0.0.0';
        }
        if (RY_WEZI_VERSION === $now_version) {
            return;
        }

        if (version_compare($now_version, '2.0.1', '<')) {
            RY_WEZI::update_option('version', '2.0.1', true);
        }
    }
}

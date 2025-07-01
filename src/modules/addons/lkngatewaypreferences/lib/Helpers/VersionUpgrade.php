<?php

namespace WHMCS\Module\Addon\lkngatewaypreferences\Helpers;

use WHMCS\Database\Capsule;

abstract class VersionUpgrade
{
    final public static function setLatestVersion(string $version): void
    {
        Capsule::table('mod_lkngatewaypreferences_settings')
            ->updateOrInsert(
                ['setting' => 'latest_version'],
                ['value' => $version]
            );
    }

    final public static function setDismissOnAdminHome(bool $dismiss): void
    {
        Capsule::table('mod_lkngatewaypreferences_settings')
            ->updateOrInsert(
                ['setting' => 'new_version_dismiss_on_admin_home'],
                ['value' => $dismiss]
            );
    }

    final public static function getNewVersion(): ?string
    {
        return Capsule::table('mod_lkngatewaypreferences_settings')
        ->where('setting', 'latest_version')
        ->first('value')
        ->value;
    }

    final public static function getDismissNewVersionAlert(): ?bool
    {
        return Capsule::table('mod_lkngatewaypreferences_settings')
        ->where('setting', 'new_version_dismiss_on_admin_home')
        ->first('value')
        ->value;
    }
}

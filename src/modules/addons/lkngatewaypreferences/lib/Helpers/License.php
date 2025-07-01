<?php

namespace WHMCS\Module\Addon\lkngatewaypreferences\Helpers;

use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Controllers\PrefByClientController;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Controllers\PrefByCountryController;

final class License
{
    public static function isProUser(): bool
    {
        if (!is_string(lkngatewaypreferencescheck_license())) {
            return true;
        }

        return false;
    }

    public static function doesUserExceedPrefsOnFreePlan(string $prefType, bool $exclusive = true): bool
    {
        if ($prefType === 'client') {
            $controller = new PrefByClientController();
            $preferences = $controller->getClientPrefs();
            $maxSize = 10;
        } elseif ($prefType === 'country') {
            $controller = new PrefByCountryController();
            $preferences = $controller->getCountryPrefs();
            $maxSize = 1;
        } else {
            return false;
        }

        $conditional = $exclusive ? (bool)(sizeof($preferences) > $maxSize) : (bool)(sizeof($preferences) >= $maxSize);
        if ($conditional && !License::isProUser()) {
            return true;
        }

        return false;
    }
}

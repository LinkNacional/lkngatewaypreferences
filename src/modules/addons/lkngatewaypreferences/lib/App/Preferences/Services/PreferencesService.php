<?php

namespace WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Services;

use Throwable;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Repository\PrefByClientRepository;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Repository\PrefByCountryRepository;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\Client;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\License;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\View;

final class PreferencesService
{
    /**
     * Automatically gets the allowed gateways for the client and renders
     * the .js script responsible for removing them from the user view.
     *
     * @since 1.1.0
     *
     * @param int         $clientId
     * @param string|null $paymentMethodsSelectName
     * @param string|null $scriptName
     *
     * @return string
     */
    public static function renderPreferencesScript(
        int $clientId,
        ?string $paymentMethodsSelectName,
        ?string $scriptName = null
    ): string {
        $scriptName = $scriptName ? $scriptName : 'remove_unallowed_gateways';

        $unallowedGateways = self::getUnallowedGateways($clientId);

        return View::renderPreferencesScript($scriptName, $unallowedGateways, $paymentMethodsSelectName);
    }

    public static function getUnallowedGateways(int $clientId): array
    {
        $allowedGateways = self::getAllowed($clientId);
        $activeGateways = self::getActive();

        return self::getUnallowed($allowedGateways, $activeGateways);
    }

    /**
     * Checks with gateways a client can access.
     *
     * 1. Checks if the client has its own defined preferences.
     * 2. Checks if the client country has its preferences.
     * 3. Otherwise, returns the default global preference.
     *
     * @param int $cliendId
     * @since 1.0.0
     *
     * @return array ['gateway1', 'gateway2', ...]
     */
    public static function getAllowed(int $clientId): array
    {
        $clientPreferences = (new PrefByClientRepository())->getOne($clientId);

        if (count($clientPreferences) > 0 && !License::doesUserExceedPrefsOnFreePlan('client')) {
            return $clientPreferences;
        }

        $prefByCountryRepository = new PrefByCountryRepository();

        $clientCountry = Client::getCountry($clientId);
        $clientCountryGateways = $prefByCountryRepository->getOne($clientCountry);

        if (count($clientCountryGateways) > 0 && !License::doesUserExceedPrefsOnFreePlan('country')) {
            return $clientCountryGateways;
        }

        return $prefByCountryRepository->getGlobalPref();
    }

    /**
     * Returns the active gateways in WHMCS.
     *
     * @since 1.1.0
     *
     * @param bool $withDisplayName
     *
     * @return array [gatewaycode1, gatewaycode2, ...]. If withDisplayName is true
     *               then, return will be [[name => , code => ], ...].
     */
    public static function getActive(bool $withDisplayName = false): array
    {
        return array_map(
            function (array $method) use ($withDisplayName): string|array {
                if ($withDisplayName) {
                    return [
                        'name' => $method['displayname'],
                        'code' => $method['module']
                    ];
                }

                return $method['module'];
            },
            localAPI('GetPaymentMethods')['paymentmethods']['paymentmethod']
        );
    }

    public static function getUnallowed(array $allowedGateways, array $activeGateways): array
    {
        return array_values(array_diff($activeGateways, $allowedGateways));
    }

    public static function updateAllowed(int $clientId, array $gatewaysCodes): bool|string
    {
        try {
            $repository = new PrefByClientRepository();
            $repository->store($clientId, $gatewaysCodes);

            return true;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }
}

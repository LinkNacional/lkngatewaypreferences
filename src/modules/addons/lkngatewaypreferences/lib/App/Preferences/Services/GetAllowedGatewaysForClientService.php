<?php

namespace WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Services;

use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Repository\PrefByClientRepository;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Repository\PrefByCountryRepository;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\Client;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\License;

/**
 * Checks with gateways a client can access.
 *
 * @since 1.0.0
 */
final class GetAllowedGatewaysForClientService
{
    private readonly PrefByCountryRepository $prefByCountryRepo;
    private readonly PrefByClientRepository $prefByClientRepo;

    public function __construct(
        public readonly int $clientId
    ) {
        $this->prefByCountryRepo = new PrefByCountryRepository();
        $this->prefByClientRepo = new PrefByClientRepository();
    }

    /**
     * Checks with gateways a client can access.
     *
     * 1. Checks if the client has its own defined preferences.
     * 2. Checks if the client country has its preferences.
     * 3. Otherwise, returns the default global preference.
     *
     * @since 1.0.0
     *
     * @return array ['gateway1', 'gateway2', ...]
     */
    public function run(): array
    {
        $clientPreferences = $this->prefByClientRepo->getOne($this->clientId);

        if (count($clientPreferences) > 0 && !License::doesUserExceedPrefsOnFreePlan('client')) {
            return $clientPreferences;
        }

        $clientCountry = Client::getCountry($this->clientId);
        $clientCountryGateways = $this->prefByCountryRepo->getOne($clientCountry);

        if (count($clientCountryGateways) > 0 && !License::doesUserExceedPrefsOnFreePlan('country')) {
            return $clientCountryGateways;
        }

        return $this->prefByCountryRepo->getGlobalPref();
    }
}

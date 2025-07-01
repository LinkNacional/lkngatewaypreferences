<?php

namespace WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Controllers;

use Throwable;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Abstract\AbstractController;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Repository\PrefByCountryRepository;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Services\PreferencesService;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\Lang;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\Logger;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\View;

/**
 * @since 1.0.0
 */
final class PrefByCountryController extends AbstractController
{
    /**
     * @since 1.0.0
     * @var PrefByCountryRepository
     */
    private readonly PrefByCountryRepository $repository;

    /**
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->repository = new PrefByCountryRepository();
    }

    /**
     * @since 1.0.0
     *
     * @return string
     */
    public function create(): string
    {
        $countriesJson = json_decode(
            file_get_contents(__DIR__ . '/../../../../../../../resources/country/dist.countries.json'),
            true
        );
        $countriesList = [];

        foreach ($countriesJson as $countryCode => $country) {
            $countriesList[] = ['name' => $country['name'], 'code' => $countryCode];
        }

        $currentPrefsList = array_map(function ($pref) use ($countriesList): array {
            return [
                'countryCode' => $pref->country,
                'countryName' => current(array_filter(
                    $countriesList,
                    function ($country) use ($pref) {
                        return $country['code'] === $pref->country;
                    }
                ))['name'],
                'gateways' => json_decode($pref->gateways, true),
            ];
        }, $this->repository->index());

        $globalPrefGateways = $this->repository->getGlobalPref();

        $paymentMethodsList = PreferencesService::getActive(true);

        return View::render(
            'views.admin.pages.countries_pref.index',
            [
                'countriesList' => $countriesList,
                'paymentMethodsList' => $paymentMethodsList,
                'currentPrefsList' => $currentPrefsList,
                'globalPrefGateways' => $globalPrefGateways
            ]
        );
    }

    public function storeGlobalPref(array $request): void
    {
        try {
            $this->repository->storeGlobalPref($request['gateways']);

            self::response(true);
        } catch (Throwable $e) {
            Logger::log(
                Lang::getModuleLang()['log_add_update_global_preference'],
                $request,
                $e->getMessage()
            );

            self::response(false, ['reason' => $e->getMessage()]);
        }
    }

    /**
     * @since 1.0.0
     *
     * @param array $request
     *
     * @return void
     */
    public function store(array $request): void
    {
        try {
            $this->repository->store($request['countryCode'], $request['gateways']);

            self::response(true);
        } catch (Throwable $e) {
            Logger::log(
                Lang::getModuleLang()['log_add_update_country_preference'],
                $request,
                $e->getMessage()
            );

            self::response(false, ['reason' => $e->getMessage()]);
        }
    }

    /**
     * @since 1.0.0
     *
     * @param array $request
     *
     * @return void
     */
    public function destroy(array $request): void
    {
        try {
            $this->repository->delete($request['countryCode']);

            self::response(true);
        } catch (Throwable $e) {
            Logger::log(
                Lang::getModuleLang()['log_delete_country_preference'],
                $request,
                $e->getMessage()
            );

            self::response(false, ['reason' => $e->getMessage()]);
        }
    }

    public function getCountryPrefs(): array
    {
        $countriesJson = json_decode(
            file_get_contents(__DIR__ . '/../../../../../../../resources/country/dist.countries.json'),
            true
        );
        $countriesList = [];

        foreach ($countriesJson as $countryCode => $country) {
            $countriesList[] = ['name' => $country['name'], 'code' => $countryCode];
        }

        $currentPrefsList = array_map(function ($pref) use ($countriesList): array {
            return [
                'countryCode' => $pref->country,
                'countryName' => current(array_filter(
                    $countriesList,
                    function ($country) use ($pref) {
                        return $country['code'] === $pref->country;
                    }
                ))['name'],
                'gateways' => json_decode($pref->gateways, true),
            ];
        }, $this->repository->index());
        return $currentPrefsList;
    }
}

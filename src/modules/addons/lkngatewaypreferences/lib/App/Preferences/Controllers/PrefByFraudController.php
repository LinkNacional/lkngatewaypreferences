<?php

namespace WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Controllers;

use Throwable;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Abstract\AbstractController;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Repository\PrefByFraudRepository;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Services\PreferencesService;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\Lang;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\Logger;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\View;

/**
 * @since 1.0.0
 */
final class PrefByFraudController extends AbstractController
{
    /**
     * @since 1.1.0
     * @var PrefByFraudRepository
     */
    private readonly PrefByFraudRepository $repository;

    /**
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->repository = new PrefByFraudRepository();
    }

    /**
     * @since 1.0.0
     *
     * @return string
     */
    public function create(): string
    {
        $viewParams = [];

        if (isset($_POST['update-fraud-settings'])) {
            $enableFraudChange = strip_tags($_POST['enable-fraud-gateways']);
            $this->repository->storeOrUpdate('enable_fraud_gateways', $enableFraudChange);

            if (is_array($_POST['order-fraud-gateway'])) {
                $orderFraudGatewayCodes = array_map('strip_tags', $_POST['order-fraud-gateway']);
                $this->repository->storeOrUpdate('order_fraud_gateway', $orderFraudGatewayCodes);
            }

            $viewParams['settingsSaved'] = true;
        }

        $orderFraudGatewayCodes = $this->repository->getOne('order_fraud_gateway') ?? '{}';
        $orderFraudGatewayCodes = json_decode($orderFraudGatewayCodes, true);

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

        $paymentMethodsList = PreferencesService::getActive(true);
        $enableFraudChange = (bool) $this->repository->getOne('enable_fraud_gateways') ?? '';

        $viewParams = array_merge($viewParams, [
            'orderFraudGatewayCodes' => $orderFraudGatewayCodes,
            'enableFraudGateways' => $enableFraudChange,
            'countriesList' => $countriesList,
            'paymentMethodsList' => $paymentMethodsList,
            'currentPrefsList' => $currentPrefsList,
        ]);

        return View::render('views.admin.pages.fraud_pref.index', $viewParams);
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
                Lang::getModuleLang()['log_delete_fraud_preference'],
                $request,
                $e->getMessage()
            );

            self::response(false, ['reason' => $e->getMessage()]);
        }
    }
}

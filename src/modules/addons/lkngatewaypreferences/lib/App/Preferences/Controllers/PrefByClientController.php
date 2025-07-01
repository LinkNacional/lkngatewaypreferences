<?php

namespace WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Controllers;

use Throwable;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Abstract\AbstractController;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Events\UpdateClientPendingInvoicesGatewayEvent;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Repository\PrefByClientRepository;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Services\PreferencesService;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\Client;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\Lang;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\Logger;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\View;

final class PrefByClientController extends AbstractController
{
    /**
     * @since 1.0.0
     * @var PrefByClientRepository
     */
    private readonly PrefByClientRepository $repository;

    /**
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->repository = new PrefByClientRepository();
    }

    /**
     * @since 1.0.0
     *
     * @return void
     */
    public function storeOrUpdate(array $request): void
    {
        try {
            $this->repository->store($request['clientId'], $request['gateways']);

            UpdateClientPendingInvoicesGatewayEvent::run($request['clientId']);

            self::response(true);
        } catch (Throwable $e) {
            Logger::log(
                Lang::getModuleLang()['log_add_update_client_preference'],
                $request,
                $e->getMessage()
            );

            self::response(false, ['reason' => $e->getMessage()]);
        }
    }

    public function createOrEditSummaryPage(): string
    {
        $paymentMethodsList = PreferencesService::getActive(true);
        $currentClientPrefs = $this->repository->getOne(strip_tags($_GET['userid'])) ?? [];

        $prefsPerPage = isset($_GET['per-page']) ? strip_tags($_GET['per-page']) : 15;
        $currentPage = isset($_GET['page']) ? strip_tags($_GET['page']) : 1;

        $prefs = $this->repository->index($prefsPerPage, $currentPage);

        return View::render(
            'hooks.adm_client_summary_links.index',
            [
                'prefs' => $prefs['prefs'],
                'paymentMethodsList' => $paymentMethodsList,
                'currentClientPrefs' => $currentClientPrefs
            ]
        );
    }

    public function create(): string
    {
        $viewParams = [];

        $prefsPerPage = isset($_GET['per-page']) ? strip_tags($_GET['per-page']) : 15;
        $currentPage = isset($_GET['page']) ? strip_tags($_GET['page']) : 1;
        $searchType = '1';
        $searchString = '';

        $prefs = $this->repository->index($prefsPerPage, $currentPage);
        $clientsEmail = [];

        foreach ($prefs['prefs'] as $pref) {
            $clients = json_decode(Client::getClientsByType($pref->client_id, 'id'));

            foreach ($clients as $client) {
                if ($pref->client_id === $client->id) {
                    $clientsEmail += [$client->id => $client->email];
                }
            }
        }

        if (isset($_POST['search-params'])) {
            $searchType = strip_tags($_POST['search-type']);
            $searchString = strip_tags($_POST['search-string']);

            switch($searchType) {
                case '1':
                    $prefs = $this->repository->indexByType($searchString, 'id', $prefsPerPage, $currentPage);

                    break;

                case '2':
                    $prefs = $this->repository->indexByType($searchString, 'email', $prefsPerPage, $currentPage);

                    break;

                case '3':
                    $prefs = $this->repository->indexByType($searchString, 'domain', $prefsPerPage, $currentPage);

                    break;

                case '4':
                    $prefs = $this->repository->indexByType($searchString, 'name', $prefsPerPage, $currentPage);

                    break;
                default:
                    $prefs = $this->repository->index($prefsPerPage, $currentPage);
                    break;
            }
            if ($searchString === '') {
                $prefs = $this->repository->index($prefsPerPage, $currentPage);
            }

            $viewParams['settingsSaved'] = true;
        } else {
            $prefs = $this->repository->index($prefsPerPage, $currentPage);
        }

        $paymentMethodsList = PreferencesService::getActive(true);

        $viewParams = array_merge($viewParams, [
            'prefs' => $prefs['prefs'],
            'searchType' => $searchType,
            'searchString' => $searchString,
            'clientsEmail' => $clientsEmail,
            'totalPrefs' => $prefs['totalPrefs'],
            'prefsPerPage' => $prefsPerPage,
            'currentPage' => $currentPage,
            'paymentMethodsList' => $paymentMethodsList
        ]);

        return View::render(
            'views.admin.pages.by_client.index',
            $viewParams
        );
    }

    public function delete(array $request): void
    {
        try {
            $this->repository->delete($request['clientId']);

            self::response(true);
        } catch (Throwable $e) {
            Logger::log(
                Lang::getModuleLang()['log_delete_client_preference'],
                $request,
                $e->getMessage()
            );

            self::response(false, ['reason' => $e->getMessage()]);
        }
    }

    public function getClientPrefs(): array
    {
        $prefsPerPage = isset($_GET['per-page']) ? strip_tags($_GET['per-page']) : 15;
        $currentPage = isset($_GET['page']) ? strip_tags($_GET['page']) : 1;

        $prefs = $this->repository->index($prefsPerPage, $currentPage);
        return $prefs;
    }
}

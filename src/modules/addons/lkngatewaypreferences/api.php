<?php

/**
 * Handles JS fetch requests coming from the module pages.
 * Every request has an "a" ("a"ction) that tells which controller should be
 * called.
 *
 * @since 1.0.0
 */

require_once __DIR__ . '/../../../init.php';

use WHMCS\Authentication\CurrentUser;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Controllers\PrefByClientController;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Controllers\PrefByCountryController;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Controllers\PrefByFraudController;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\Client;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\Diagnostic;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\License;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\VersionUpgrade;

$currentUser = new CurrentUser();
$clientLogged = $currentUser->admin();

if (!$clientLogged) {
    exit;
}

$request = json_decode(file_get_contents('php://input'), true) ?? $_POST;

if (!isset($request['a'])) {
    exit;
}

require_once __DIR__ . '/license.php';
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/lib/utils.php';

$license = new License();

switch ($request['a']) {
    case 'save-country-pref':
        $controller = new PrefByCountryController();
        if ($license->doesUserExceedPrefsOnFreePlan('country')) {
            http_response_code(402);
            exit;
        }
        $controller->store($request);

        break;

    case 'delete-country-pref':
        $controller = new PrefByCountryController();

        $controller->destroy($request);

        break;

    case 'save-global-pref':
        $controller = new PrefByCountryController();

        $controller->storeGlobalPref($request);

        break;

    case 'save-fraud-pref':
        $controller = new PrefByFraudController();
        if (!$license->isProUser()) {
            http_response_code(402);
            exit;
        }
        $controller->store($request);

        break;

    case 'delete-fraud-pref':
        $controller = new PrefByFraudController();

        $controller->destroy($request);

        break;

    case 'store-client-pref':
        $controller = new PrefByClientController();
        if ($license->doesUserExceedPrefsOnFreePlan('client')) {
            http_response_code(402);
            exit;
        }
        $controller->storeOrUpdate($request);

        break;

    case 'delete-client-pref':
        $controller = new PrefByClientController();

        $controller->delete($request);

        break;

    case 'get-all-clients':
        echo Client::getClientsByType('', '', 0);

        break;

    case 'get-clients-by-id':
        echo Client::getClientsByType($request['searchText'], 'id', 0);

        break;

    case 'get-clients-by-email':
        echo Client::getClientsByType($request['searchText'], 'email', 0);

        break;

    case 'get-clients-by-domain':
        echo Client::getClientsByType($request['searchText'], 'domain', 0);

        break;

    case 'get-clients-by-name':
        echo Client::getClientsByType($request['searchText'], 'name', 0);

        break;

    case 'new-version-dismiss-on-admin-home':
        VersionUpgrade::setDismissOnAdminHome(true);
        break;

    case 'diagnostic-module-status':
        header('Content-Type: application/json');
        echo json_encode(Diagnostic::checkModuleStatus());
        break;

    case 'diagnostic-fraud-orders':
        header('Content-Type: application/json');
        echo json_encode(Diagnostic::getFraudOrdersDetail());
        break;

    case 'diagnostic-test-cron':
        header('Content-Type: application/json');
        echo json_encode(Diagnostic::testCronHook());
        break;

    default:
        lkngatewaypreferences_json_response(false);

        break;
}

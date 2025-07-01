<?php

/**
 * @since 1.2.1
 */

use WHMCS\Authentication\CurrentUser;
use WHMCS\Database\Capsule;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Controllers\PrefByClientController;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Services\GetAllowedGatewaysForClientService;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Services\PreferencesService;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\Client;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\Config;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\Lang;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\Logger;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\VersionUpgrade;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\View;

require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/../license.php';

add_hook('AdminAreaClientSummaryActionLinks', 1, function ($vars): array {
    $return = [];

    $return[] = (new PrefByClientController())->createOrEditSummaryPage();

    return $return;
});

add_hook('AdminClientServicesTabFields', 1, function ($vars): void {
    if (lkngatewaypreferencescheck_license() !== true) {
        return;
    }

    $clientId = strip_tags($_GET['userid']);

    echo PreferencesService::renderPreferencesScript($clientId, 'paymentmethod');
});

add_hook('AdminClientDomainsTabFields', 1, function ($vars): void {
    if (lkngatewaypreferencescheck_license() !== true) {
        return;
    }

    $clientId = strip_tags($_GET['userid']);

    echo PreferencesService::renderPreferencesScript($clientId, 'paymentmethod');
});

add_hook('AdminAreaHeadOutput', 1, function ($vars): string {
    if (str_contains($_SERVER['PHP_SELF'], 'clientsinvoices.php')) {
        if (lkngatewaypreferencescheck_license() !== true) {
            return '';
        }

        $clientId = strip_tags($_GET['userid']);

        return PreferencesService::renderPreferencesScript($clientId, 'paymentmethod');
    }

    return '';
});

add_hook('ClientAreaPageViewInvoice', 1, function ($vars): void {
    if (lkngatewaypreferencescheck_license() !== true) {
        return;
    }

    $currentGateway = $vars['paymentmodule'];
    $clientId = $vars['clientsdetails']['client_id'];
    $invoiceId = $vars['invoiceid'];

    $allowedGateways = PreferencesService::getAllowed($clientId);

    if (!in_array($currentGateway, $allowedGateways, true)) {
        localAPI('UpdateInvoice', [
            'invoiceid' => $invoiceId,
            'paymentmethod' => $allowedGateways[0]
        ]);

        header('Refresh:0');

        exit;
    }

    echo PreferencesService::renderPreferencesScript($clientId, 'gateway');
});

add_hook('ClientAreaHeadOutput', 1, function ($vars): string {
    if (isset($_GET['action']) && $_GET['action'] === 'details') {
        if (lkngatewaypreferencescheck_license() !== true) {
            return '';
        }

        $clientId = $vars['clientsdetails']['client_id'];

        return PreferencesService::renderPreferencesScript($clientId, 'paymentmethod');
    }

    return '';
});

add_hook('AdminInvoicesControlsOutput', 1, function ($vars): string {
    if (lkngatewaypreferencescheck_license() !== true) {
        return '';
    }

    $clientId = $vars['userid'];

    return PreferencesService::renderPreferencesScript($clientId, 'paymentmethod');
});

add_hook('AdminClientProfileTabFields', 1, function ($vars): void {
    if (lkngatewaypreferencescheck_license() !== true) {
        return;
    }

    $clientId = $vars['client_id'];

    echo PreferencesService::renderPreferencesScript($clientId, 'paymentmethod');
});

add_hook('AdminHomepage', 1, function ($vars) {
    $mustDismissAlert = VersionUpgrade::getDismissNewVersionAlert();

    if ($mustDismissAlert) {
        return;
    }

    $currentAdminDetails = localAPI('GetAdminDetails');
    $adminPermissons = $currentAdminDetails['allowedpermissions'];

    if (!str_contains($adminPermissons, 'Configure Addon Modules')) {
        return;
    }

    $newVersion = VersionUpgrade::getNewVersion();

    $currentVersion = Config::constant('version');

    if (version_compare($newVersion, $currentVersion, '>')) {
        return View::render(
            'views.hooks.admin_homepage.index',
            [
                'newVersion' => $newVersion,
                'lang' => Lang::getModuleLang(),
            ]
        );
    }
});

add_hook('ShoppingCartCheckoutOutput', 1, function ($vars): string {
    if (lkngatewaypreferencescheck_license() !== true) {
        return '';
    }

    $currentUser = new CurrentUser();
    $clientId = $currentUser->client()->id;

    if (!$clientId) {
        return '';
    }

    $allowedGateways = PreferencesService::getAllowed($clientId);
    $activeGateways = PreferencesService::getActive(true);

    $unallowedGateways = array_filter(
        $activeGateways,
        function (array $activeGateway) use ($allowedGateways): bool {
            return !in_array($activeGateway['code'], $allowedGateways, true);
        }
    );

    $unallowedGateways = array_column($unallowedGateways, 'name');

    return View::renderPreferencesScript('shopping_card_checkout_output', $unallowedGateways);
});

add_hook('InvoiceCreation', 1, function ($vars): void {
    if (lkngatewaypreferencescheck_license() !== true) {
        return;
    }

    $invoiceId = $vars['invoiceid'];
    $clientId = Client::getClientIdByInvoiceId($invoiceId);

    $allowedGatewaysForClient = (new GetAllowedGatewaysForClientService($clientId))->run();
    $invoiceGatewayCode = Client::getClientInvoiceGatewayCode($invoiceId);

    if (!in_array($invoiceGatewayCode, $allowedGatewaysForClient, true)) {
        localAPI('UpdateInvoice', [
            'invoiceid' => $invoiceId,
            'paymentmethod' => $allowedGatewaysForClient[0]
        ]);
    }
});

add_hook('AfterCronJob', 1, function ($vars): void {
    if (lkngatewaypreferencescheck_license() === false || !Config::setting('enable_fraudchange')) {
        return;
    }

    $lang = Lang::getModuleLang();

    $order = Capsule::table('mod_lkngatewaypreferences_fraud_orders')->where('status', 'Fraud')->first();
    if ($order <= 0) {
        return;
    }

    $orderFrauded = localAPI('GetOrders', ['id' => $order->order_id, 'status' => 'Fraud']);

    $isStillFraud = (int) ($orderFrauded['totalresults']) > 0;
    if (!$isStillFraud) {
        $queryRes = Capsule::table('mod_lkngatewaypreferences_fraud_orders')->where('id', $order->id)->update(['status' => 'Pending']);
        if ($queryRes <= 0) {
            Logger::log($lang['log_update_fraud_table_e'], [($lang['log_order_num'] . $order->order_id)], [$queryRes]);
            return;
        }
        Logger::log($lang['log_update_fraud_table_success'], [($lang['log_order_num'] . $order->order_id)], [$queryRes]);
        return;
    }

    $res = localAPI(
        'PendingOrder',
        [
            'orderid' => $order->order_id,
        ]
    );

    if ($res['result'] !== 'success') {
        Logger::log($lang['log_update_fraud_order_e'], [($lang['log_order_num'] . $order->order_id)], [$res]);
        return;
    }

    $queryRes = Capsule::table('mod_lkngatewaypreferences_fraud_orders')->where('id', $order->id)->update(['status' => 'Pending']);
    if ($queryRes <= 0) {
        Logger::log($lang['log_update_fraud_table_e'], [($lang['log_order_num'] . $order->order_id)], [$queryRes]);
        return;
    }

    Logger::log($lang['log_update_fraud_table_success'], [($lang['log_order_num'] . $order->order_id)], [$queryRes]);

    $userid = $whmcsOrder['orders']['order']['userid'] ?? $orderFrauded['orders']['order'][0]['userid'];
    if (!Config::setting('enable_notes')) {
        return;
    }

    $res = localAPI('AddClientNote', [
        'userid' => $userid,
        'notes' => $lang['log_order_num'] . $order->order_id . $lang['log_altered_order_note'],
        'sticky' => true,
    ]);
    if ($res['result'] !== 'success') {
        Logger::log($lang['log_add_note_e'], [($lang['log_client_num'] . $userid)], [$res]);
        return;
    }
    Logger::log($lang['log_add_note_success'], [($lang['log_order_num'] . $order->order_id . $lang['log_altered_order_note']), ($lang['log_client_num'] . $userid)], [$res]);
});

add_hook('FraudOrder', 1, function ($vars): void {
    if (lkngatewaypreferencescheck_license() !== true) {
        return;
    }

    $lang = Lang::getModuleLang();

    $orderInfo = Capsule::table('tblorders')
        ->where('id', $vars['orderid'])
        ->first(['invoiceid', 'userid']);

    $clientId = $orderInfo->userid;
    $invoiceId = $orderInfo->invoiceid;

    if (Config::setting('enable_fraudchange')) {
        Capsule::table('mod_lkngatewaypreferences_fraud_orders')->insert(['order_id' => $vars['orderid'], 'status' => 'Fraud']);
    }

    $gatewaysForFraudOrder = Config::setting('order_fraud_gateway');
    $newGateway = $gatewaysForFraudOrder[0];

    if (!((bool) Config::setting('enable_fraud_gateways') ?? '')) {
        $gatewaysForFraudOrder = PreferencesService::getAllowed($clientId);
        $newGateway = $gatewaysForFraudOrder[0];
    } else {
        $countryGateways = json_decode(Capsule::table('mod_lkngatewaypreferences_for_fraud')->where('country', 'LIKE', Client::getCountry($clientId))->first('gateways')->gateways);

        if (!empty($countryGateways)) {
            $gatewaysForFraudOrder = $countryGateways;
        }

        $response = PreferencesService::updateAllowed($clientId, $gatewaysForFraudOrder);
        Logger::log(
            $lang['log_update_gateways'],
            [
                'response' => $response,
                'clientId' => $clientId,
                'gatewaysForFraudOrder' => $gatewaysForFraudOrder
            ]
        );
    }

    $updateClientResponse = localAPI('UpdateClient', ['clientid' => $clientId, 'paymentmethod' => $newGateway]);
    $updateInvoiceResponse = localAPI('UpdateInvoice', ['invoiceid' => $invoiceId, 'paymentmethod' => $newGateway]);

    Logger::log(
        $lang['log_fraud_event'],
        [
            'order' => ['id' => $vars['orderid'], 'invoiceId' => $invoiceId],
            'updateClientResponse' => $updateClientResponse,
            'updateInvoiceResponse' => $updateInvoiceResponse
        ]
    );
});

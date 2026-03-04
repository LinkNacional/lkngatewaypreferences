<?php

/**
 * @since 1.2.1
 */

use WHMCS\Authentication\CurrentUser;
use WHMCS\Database\Capsule;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Controllers\PrefByClientController;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Services\GetAllowedGatewaysForClientService;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Services\PreferencesService;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\AutoCancel;
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
    if (lkngatewaypreferencescheck_license() !== true) return;
    $clientId = strip_tags($_GET['userid']);
    echo PreferencesService::renderPreferencesScript($clientId, 'paymentmethod');
});

add_hook('AdminClientDomainsTabFields', 1, function ($vars): void {
    if (lkngatewaypreferencescheck_license() !== true) return;
    $clientId = strip_tags($_GET['userid']);
    echo PreferencesService::renderPreferencesScript($clientId, 'paymentmethod');
});

add_hook('AdminAreaHeadOutput', 1, function ($vars): string {
    if (str_contains($_SERVER['PHP_SELF'], 'clientsinvoices.php')) {
        if (lkngatewaypreferencescheck_license() !== true) return '';
        $clientId = strip_tags($_GET['userid']);
        return PreferencesService::renderPreferencesScript($clientId, 'paymentmethod');
    }
    return '';
});

add_hook('ClientAreaPageViewInvoice', 1, function ($vars): void {
    if (lkngatewaypreferencescheck_license() !== true) return;

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
        if (lkngatewaypreferencescheck_license() !== true) return '';
        $clientId = $vars['clientsdetails']['client_id'];
        return PreferencesService::renderPreferencesScript($clientId, 'paymentmethod');
    }
    return '';
});

add_hook('AdminInvoicesControlsOutput', 1, function ($vars): string {
    if (lkngatewaypreferencescheck_license() !== true) return '';
    $clientId = $vars['userid'];
    return PreferencesService::renderPreferencesScript($clientId, 'paymentmethod');
});

add_hook('AdminClientProfileTabFields', 1, function ($vars): void {
    if (lkngatewaypreferencescheck_license() !== true) return;
    $clientId = $vars['client_id'];
    echo PreferencesService::renderPreferencesScript($clientId, 'paymentmethod');
});

add_hook('AdminHomepage', 1, function ($vars) {
    $mustDismissAlert = VersionUpgrade::getDismissNewVersionAlert();
    if ($mustDismissAlert) return;

    $currentAdminDetails = localAPI('GetAdminDetails');
    $adminPermissons = $currentAdminDetails['allowedpermissions'];

    if (!str_contains($adminPermissons, 'Configure Addon Modules')) return;

    $newVersion = VersionUpgrade::getNewVersion();
    $currentVersion = Config::constant('version');

    if (version_compare($newVersion, $currentVersion, '>')) {
        return View::render('views.hooks.admin_homepage.index', [
            'newVersion' => $newVersion,
            'lang' => Lang::getModuleLang(),
        ]);
    }
});

add_hook('ShoppingCartCheckoutOutput', 1, function ($vars): string {
    if (lkngatewaypreferencescheck_license() !== true) return '';

    $currentUser = new CurrentUser();
    $clientId = $currentUser->client()->id;

    if (!$clientId) return '';

    $allowedGateways = PreferencesService::getAllowed($clientId);
    $activeGateways = PreferencesService::getActive(true);

    $unallowedGateways = array_filter($activeGateways, function (array $activeGateway) use ($allowedGateways): bool {
        return !in_array($activeGateway['code'], $allowedGateways, true);
    });

    $unallowedGateways = array_column($unallowedGateways, 'name');

    return View::renderPreferencesScript('shopping_card_checkout_output', $unallowedGateways);
});

add_hook('InvoiceCreation', 1, function ($vars): void {
    if (lkngatewaypreferencescheck_license() !== true) return;

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


// ==========================================================================================
// MÓDULO DE PROCESSAMENTO DE FRAUDE (CÓDIGO ISOLADO PARA EVITAR ERROS DE ESCOPO NO WHMCS)
// ==========================================================================================

add_hook('FraudOrder', 1, function ($vars): void {
    if (lkngatewaypreferencescheck_license() !== true) return;

    $fraudCronHook = Config::setting('fraud_cron_hook') ?? 'AfterCronJob';
    $enableFraudChange = Config::setting('enable_fraudchange');
    $enableFraudGateways = Config::setting('enable_fraud_gateways');
    $skipZeroAmount = Config::setting('skip_zero_amount');
    $lang = Lang::getModuleLang();
    
    $orderId = $vars['orderid'];

    $orderInfo = Capsule::table('tblorders')
        ->where('id', $orderId)
        ->first(['invoiceid', 'userid', 'amount']);

    if (!$orderInfo) return;
    if ($skipZeroAmount && (float) $orderInfo->amount <= 0) return;

    $clientId = $orderInfo->userid;
    $invoiceId = $orderInfo->invoiceid;

    // REGISTRO OBRIGATÓRIO: Se a mudança estiver ativa, registra na tabela para o cron encontrar depois.
    // Isso resolve o problema do "order_id não foi cadastrado".
    if ($enableFraudChange) {
        $tracked = Capsule::table('mod_lkngatewaypreferences_fraud_orders')->where('order_id', $orderId)->first();
        if (!$tracked) {
            Capsule::table('mod_lkngatewaypreferences_fraud_orders')->insert([
                'order_id' => $orderId, 
                'status' => 'Fraud'
            ]);
        }
    }

    // Se o sistema estiver configurado para aguardar o Cron Job, paramos por aqui.
    if ($fraudCronHook !== 'FraudOrder') {
        return;
    }

    // --- EXECUÇÃO IMEDIATA (Se o hook configurado for FraudOrder) ---

    // 1. Atualização de Gateways
    if ($enableFraudGateways) {
        $gatewaysForFraudOrder = Config::setting('order_fraud_gateway') ?? [];
        $newGateway = $gatewaysForFraudOrder[0] ?? null;

        $fraudCountryRow = Capsule::table('mod_lkngatewaypreferences_for_fraud')
            ->where('country', 'LIKE', Client::getCountry($clientId))
            ->first('gateways');

        if ($fraudCountryRow) {
            $countryGateways = json_decode($fraudCountryRow->gateways, true);
            if (!empty($countryGateways)) {
                $gatewaysForFraudOrder = $countryGateways;
                $newGateway = $countryGateways[0] ?? null;
            }
        }

        if ($newGateway) {
            PreferencesService::updateAllowed($clientId, $gatewaysForFraudOrder);
            localAPI('UpdateClient', ['clientid' => $clientId, 'paymentmethod' => $newGateway]);
            
            // Validação de segurança para pedidos que não geram fatura
            if (!empty($invoiceId) && $invoiceId > 0) {
                localAPI('UpdateInvoice', ['invoiceid' => $invoiceId, 'paymentmethod' => $newGateway]);
            }
        }
    }

    // 2. Mudança de Status (Fraude -> Pendente)
    if ($enableFraudChange) {
        $res = localAPI('PendingOrder', ['orderid' => $orderId]);

        if ($res['result'] === 'success') {
            Capsule::table('mod_lkngatewaypreferences_fraud_orders')
                ->where('order_id', $orderId)
                ->update(['status' => 'Pending']);

            if (Config::setting('enable_notes')) {
                localAPI('AddClientNote', [
                    'userid' => $clientId,
                    'notes' => $lang['log_order_num'] . $orderId . $lang['log_altered_order_note'],
                    'sticky' => true,
                ]);
            }
        }
    }
});


add_hook('AfterCronJob', 1, function ($vars): void {
    $fraudCronHook = Config::setting('fraud_cron_hook') ?? 'AfterCronJob';
    if ($fraudCronHook !== 'AfterCronJob') return;
    if (lkngatewaypreferencescheck_license() === false) return;

    $enableFraudChange = Config::setting('enable_fraudchange');
    $enableFraudGateways = Config::setting('enable_fraud_gateways');

    // Se ambas as opções estiverem desmarcadas no painel, economiza recursos
    if (!$enableFraudChange && !$enableFraudGateways) return;

    $lang = Lang::getModuleLang();
    $skipZeroAmount = Config::setting('skip_zero_amount');

    $query = Capsule::table('tblorders')->where('status', 'Fraud');
    if ($skipZeroAmount) {
        $query->where('amount', '>', 0);
    }
    
    // Assegura que invoiceid seja retornado da query
    $fraudOrders = $query->get(['id', 'userid', 'amount', 'invoiceid']);
    
    if (count($fraudOrders) === 0) return;

    foreach ($fraudOrders as $fraudOrder) {
        $orderId = $fraudOrder->id;
        $clientId = $fraudOrder->userid;
        $invoiceId = $fraudOrder->invoiceid;

        // 1. Atualização de Gateways
        if ($enableFraudGateways) {
            $gatewaysForFraudOrder = Config::setting('order_fraud_gateway') ?? [];
            $newGateway = $gatewaysForFraudOrder[0] ?? null;

            $fraudCountryRow = Capsule::table('mod_lkngatewaypreferences_for_fraud')
                ->where('country', 'LIKE', Client::getCountry($clientId))
                ->first('gateways');

            if ($fraudCountryRow) {
                $countryGateways = json_decode($fraudCountryRow->gateways, true);
                if (!empty($countryGateways)) {
                    $gatewaysForFraudOrder = $countryGateways;
                    $newGateway = $countryGateways[0] ?? null;
                }
            }

            if ($newGateway) {
                PreferencesService::updateAllowed($clientId, $gatewaysForFraudOrder);
                localAPI('UpdateClient', ['clientid' => $clientId, 'paymentmethod' => $newGateway]);
                
                if (!empty($invoiceId) && $invoiceId > 0) {
                    localAPI('UpdateInvoice', ['invoiceid' => $invoiceId, 'paymentmethod' => $newGateway]);
                }
            }
        }

        // 2. Mudança de Status
        if ($enableFraudChange) {
            $trackedOrder = Capsule::table('mod_lkngatewaypreferences_fraud_orders')
                ->where('order_id', $orderId)
                ->first();

            // Pula status change se já processamos antes
            if ($trackedOrder && $trackedOrder->status === 'Pending') {
                continue;
            }

            if (!$trackedOrder) {
                Capsule::table('mod_lkngatewaypreferences_fraud_orders')->insert([
                    'order_id' => $orderId,
                    'status' => 'Fraud'
                ]);
            }

            $res = localAPI('PendingOrder', ['orderid' => $orderId]);

            if ($res['result'] === 'success') {
                Capsule::table('mod_lkngatewaypreferences_fraud_orders')
                    ->where('order_id', $orderId)
                    ->update(['status' => 'Pending']);

                if (Config::setting('enable_notes')) {
                    localAPI('AddClientNote', [
                        'userid' => $clientId,
                        'notes' => $lang['log_order_num'] . $orderId . $lang['log_altered_order_note'],
                        'sticky' => true,
                    ]);
                }
            }
        }
    }
});


add_hook('AfterFraudCheck', 1, function ($vars): void {
    $fraudCronHook = Config::setting('fraud_cron_hook') ?? 'AfterCronJob';
    if ($fraudCronHook !== 'AfterFraudCheck') return;
    if (lkngatewaypreferencescheck_license() === false) return;

    $enableFraudChange = Config::setting('enable_fraudchange');
    $enableFraudGateways = Config::setting('enable_fraud_gateways');

    if (!$enableFraudChange && !$enableFraudGateways) return;

    $lang = Lang::getModuleLang();
    $skipZeroAmount = Config::setting('skip_zero_amount');

    $query = Capsule::table('tblorders')->where('status', 'Fraud');
    if ($skipZeroAmount) {
        $query->where('amount', '>', 0);
    }
    
    $fraudOrders = $query->get(['id', 'userid', 'amount', 'invoiceid']);
    
    if (count($fraudOrders) === 0) return;

    foreach ($fraudOrders as $fraudOrder) {
        $orderId = $fraudOrder->id;
        $clientId = $fraudOrder->userid;
        $invoiceId = $fraudOrder->invoiceid;

        // 1. Atualização de Gateways
        if ($enableFraudGateways) {
            $gatewaysForFraudOrder = Config::setting('order_fraud_gateway') ?? [];
            $newGateway = $gatewaysForFraudOrder[0] ?? null;

            $fraudCountryRow = Capsule::table('mod_lkngatewaypreferences_for_fraud')
                ->where('country', 'LIKE', Client::getCountry($clientId))
                ->first('gateways');

            if ($fraudCountryRow) {
                $countryGateways = json_decode($fraudCountryRow->gateways, true);
                if (!empty($countryGateways)) {
                    $gatewaysForFraudOrder = $countryGateways;
                    $newGateway = $countryGateways[0] ?? null;
                }
            }

            if ($newGateway) {
                PreferencesService::updateAllowed($clientId, $gatewaysForFraudOrder);
                localAPI('UpdateClient', ['clientid' => $clientId, 'paymentmethod' => $newGateway]);
                
                if (!empty($invoiceId) && $invoiceId > 0) {
                    localAPI('UpdateInvoice', ['invoiceid' => $invoiceId, 'paymentmethod' => $newGateway]);
                }
            }
        }

        // 2. Mudança de Status
        if ($enableFraudChange) {
            $trackedOrder = Capsule::table('mod_lkngatewaypreferences_fraud_orders')
                ->where('order_id', $orderId)
                ->first();

            if ($trackedOrder && $trackedOrder->status === 'Pending') {
                continue;
            }

            if (!$trackedOrder) {
                Capsule::table('mod_lkngatewaypreferences_fraud_orders')->insert([
                    'order_id' => $orderId,
                    'status' => 'Fraud'
                ]);
            }

            $res = localAPI('PendingOrder', ['orderid' => $orderId]);

            if ($res['result'] === 'success') {
                Capsule::table('mod_lkngatewaypreferences_fraud_orders')
                    ->where('order_id', $orderId)
                    ->update(['status' => 'Pending']);

                if (Config::setting('enable_notes')) {
                    localAPI('AddClientNote', [
                        'userid' => $clientId,
                        'notes' => $lang['log_order_num'] . $orderId . $lang['log_altered_order_note'],
                        'sticky' => true,
                    ]);
                }
            }
        }
    }
});


add_hook('DailyCronJob', 1, function ($vars): void {
    if (lkngatewaypreferencescheck_license() !== true) return;
    if (!Config::setting('enable_auto_cancel')) return;

    $skipZeroAmount = Config::setting('skip_zero_amount');
    $query = Capsule::table('tblorders')->where('status', 'Pending');
    
    if ($skipZeroAmount) {
        $query->where('amount', '>', 0);
    }
    
    $pendingOrders = $query->get(['id', 'userid', 'status', 'date', 'total', 'amount', 'paymentmethod']);

    if (count($pendingOrders) === 0) return;

    foreach ($pendingOrders as $orderData) {
        if (AutoCancel::shouldCancel($orderData->id, (array) $orderData)) {
            AutoCancel::cancelOrder($orderData->id);
        }
    }
});

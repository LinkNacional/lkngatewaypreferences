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
    // Log que o hook foi chamado
    if (Config::setting('enable_log')) {
        Logger::log('[AfterCronJob Hook] Hook disparado pelo WHMCS', ['timestamp' => date('Y-m-d H:i:s')]);
    }

    $fraudCronHook = Config::setting('fraud_cron_hook') ?? 'AfterCronJob';

    if (Config::setting('enable_log')) {
        Logger::log('[AfterCronJob Hook] Configuração fraud_cron_hook', ['valor' => $fraudCronHook]);
    }

    if ($fraudCronHook !== 'AfterCronJob') {
        if (Config::setting('enable_log')) {
            Logger::log('[AfterCronJob Hook] Hook não configurado para AfterCronJob, abortando', ['esperado' => 'AfterCronJob', 'configurado' => $fraudCronHook]);
        }
        return;
    }

    $licenseValid = lkngatewaypreferencescheck_license();
    $enableFraudChange = Config::setting('enable_fraudchange');

    if (Config::setting('enable_log')) {
        Logger::log('[AfterCronJob Hook] Verificação de licença e configuração', [
            'licenseValid' => $licenseValid ? 'true' : 'false',
            'enableFraudChange' => $enableFraudChange ? 'true' : 'false'
        ]);
    }

    if ($licenseValid === false || !$enableFraudChange) {
        if (Config::setting('enable_log')) {
            Logger::log('[AfterCronJob Hook] Condições não atendidas, abortando', [
                'motivo' => $licenseValid === false ? 'Licença inválida' : 'enable_fraudchange desativado'
            ]);
        }
        return;
    }

    $lang = Lang::getModuleLang();
    $skipZeroAmount = Config::setting('skip_zero_amount');

    // Get ALL fraud orders from WHMCS tblorders
    $query = Capsule::table('tblorders')
        ->where('status', 'Fraud');
    
    // Apply skip zero amount filter if enabled
    if ($skipZeroAmount) {
        $query->where('amount', '>', 0);
        
        if (Config::setting('enable_log')) {
            Logger::log('[AfterCronJob Hook] Filtro de zero amount ativado', [
                'skip_zero_amount' => 'true'
            ]);
        }
    }
    
    $fraudOrders = $query->get(['id', 'userid', 'amount']);
    
    if (Config::setting('enable_log')) {
        Logger::log('[AfterCronJob Hook] Busca por pedidos em fraude', [
            'encontrado' => count($fraudOrders) > 0 ? 'sim' : 'não',
            'totalOrders' => count($fraudOrders)
        ]);
    }

    if (count($fraudOrders) === 0) {
        if (Config::setting('enable_log')) {
            Logger::log('[AfterCronJob Hook] Nenhum pedido em fraude encontrado, abortando');
        }
        return;
    }

    // Process each fraud order
    foreach ($fraudOrders as $fraudOrder) {
        // Check if already tracked
        $trackedOrder = Capsule::table('mod_lkngatewaypreferences_fraud_orders')
            ->where('order_id', $fraudOrder->id)
            ->first();

        if ($trackedOrder && $trackedOrder->status === 'Pending') {
            // Already processed, skip
            continue;
        }

        // Track or update this fraud order
        if (!$trackedOrder) {
            Capsule::table('mod_lkngatewaypreferences_fraud_orders')->insert([
                'order_id' => $fraudOrder->id,
                'status' => 'Fraud'
            ]);

            if (Config::setting('enable_log')) {
                Logger::log('[AfterCronJob Hook] Novo pedido em fraude rastreado', [
                    'orderId' => $fraudOrder->id,
                    'amount' => $fraudOrder->amount,
                    'action' => 'inserted'
                ]);
            }
        }

        // Process the fraud order (convert to Pending)
        $res = localAPI(
            'PendingOrder',
            [
                'orderid' => $fraudOrder->id,
            ]
        );

        if (Config::setting('enable_log')) {
            Logger::log('[AfterCronJob Hook] Resultado da conversão para Pending', [
                'orderId' => $fraudOrder->id,
                'result' => $res['result'] ?? 'N/A'
            ]);
        }

        if ($res['result'] !== 'success') {
            Logger::log($lang['log_update_fraud_order_e'], [($lang['log_order_num'] . $fraudOrder->id)], [$res]);
            continue;
        }

        // Update status to Pending in tracking table
        $queryRes = Capsule::table('mod_lkngatewaypreferences_fraud_orders')
            ->where('order_id', $fraudOrder->id)
            ->update(['status' => 'Pending']);

        if (Config::setting('enable_log')) {
            Logger::log('[AfterCronJob Hook] Atualizando tabela de fraudes', [
                'orderId' => $fraudOrder->id,
                'updateResult' => $queryRes
            ]);
        }

        if ($queryRes <= 0) {
            Logger::log($lang['log_update_fraud_table_e'], [($lang['log_order_num'] . $fraudOrder->id)], [$queryRes]);
            continue;
        }

        Logger::log($lang['log_update_fraud_table_success'], [($lang['log_order_num'] . $fraudOrder->id)], [$queryRes]);

        // Add client note if enabled
        $userid = $fraudOrder->userid;
        if ($userid && Config::setting('enable_notes')) {
            $res = localAPI('AddClientNote', [
                'userid' => $userid,
                'notes' => $lang['log_order_num'] . $fraudOrder->id . $lang['log_altered_order_note'],
                'sticky' => true,
            ]);
            if ($res['result'] !== 'success') {
                Logger::log($lang['log_add_note_e'], [($lang['log_client_num'] . $userid)], [$res]);
            } else {
                Logger::log($lang['log_add_note_success'], [($lang['log_order_num'] . $fraudOrder->id . $lang['log_altered_order_note']), ($lang['log_client_num'] . $userid)], [$res]);
            }
        }
    }
});

add_hook('DailyCronJob', 1, function ($vars): void {
    if (lkngatewaypreferencescheck_license() !== true) {
        return;
    }

    // Só executa se o recurso de auto-cancel estiver ativado nas configurações do módulo
    if (!Config::setting('enable_auto_cancel')) {
        return;
    }

    if (Config::setting('enable_log')) {
        Logger::log('[DailyCronJob Hook] Iniciando verificação de cancelamento automático para TODOS os pedidos Pendentes');
    }

    $skipZeroAmount = Config::setting('skip_zero_amount');

    // Busca TODOS os pedidos com status puramente 'Pending' no WHMCS (Ignora os 'Fraud')
    $query = Capsule::table('tblorders')
        ->where('status', 'Pending');
    
    // Respeita a configuração de ignorar pedidos com valor zero
    if ($skipZeroAmount) {
        $query->where('amount', '>', 0);
    }
    
    $pendingOrders = $query->get([
        'id', 'userid', 'status', 'date', 'total', 'amount', 'paymentmethod'
    ]);

    if (count($pendingOrders) === 0) {
        if (Config::setting('enable_log')) {
            Logger::log('[DailyCronJob Hook] Nenhum pedido pendente encontrado para cancelamento.');
        }
        return;
    }

    foreach ($pendingOrders as $orderData) {
        // A classe AutoCancel::shouldCancel usa a inteligência do seu módulo para checar
        // os dias configurados e se o pedido tem faturas pagas de acordo com a sua configuração
        if (AutoCancel::shouldCancel($orderData->id, (array) $orderData)) {
            
            if (Config::setting('enable_log')) {
                Logger::log('[AutoCancel Geral] Cancelando pedido pendente', [
                    'orderId' => $orderData->id,
                    'orderStatus' => $orderData->status,
                    'daysOld' => (new \DateTime())->diff(new \DateTime($orderData->date))->days
                ]);
            }

            // Cancela o pedido via API oficial do WHMCS de forma segura
            $cancelResult = AutoCancel::cancelOrder($orderData->id);

            if ($cancelResult['result'] !== 'success') {
                if (Config::setting('enable_log')) {
                    Logger::log('[AutoCancel Geral] Erro ao cancelar pedido', [
                        'orderId' => $orderData->id,
                        'error' => $cancelResult['message'] ?? 'Erro desconhecido'
                    ]);
                }
            }
        }
    }
});

add_hook('FraudOrder', 1, function ($vars): void {
    if (lkngatewaypreferencescheck_license() !== true) {
        return;
    }

    $fraudCronHook = Config::setting('fraud_cron_hook') ?? 'AfterCronJob';
    $enableFraudChange = Config::setting('enable_fraudchange');
    $skipZeroAmount = Config::setting('skip_zero_amount');

    $lang = Lang::getModuleLang();

    $orderInfo = Capsule::table('tblorders')
        ->where('id', $vars['orderid'])
        ->first(['invoiceid', 'userid', 'amount']);

    if (!$orderInfo) {
        return;
    }

    // Skip if zero amount and skip_zero_amount is enabled
    if ($skipZeroAmount && (float) $orderInfo->amount <= 0) {
        if (Config::setting('enable_log')) {
            Logger::log('[FraudOrder Hook] Pedido ignorado - Amount = 0', [
                'orderId' => $vars['orderid'],
                'amount' => $orderInfo->amount,
                'motivo' => 'skip_zero_amount enabled'
            ]);
        }
        return;
    }

    $clientId = $orderInfo->userid;
    $invoiceId = $orderInfo->invoiceid;

    // Record fraud order if enabled
    if ($enableFraudChange) {
        Capsule::table('mod_lkngatewaypreferences_fraud_orders')->insert(['order_id' => $vars['orderid'], 'status' => 'Fraud']);
    }

    // Only process fraud preferences if configured to use FraudOrder hook
    if ($fraudCronHook !== 'FraudOrder') {
        return;
    }

    $enableFraudGateways = (bool) (Config::setting('enable_fraud_gateways') ?? false);

    if (!$enableFraudGateways) {
        $gatewaysForFraudOrder = PreferencesService::getAllowed($clientId);
        $newGateway = $gatewaysForFraudOrder[0] ?? null;
    } else {
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

    if (!$newGateway) {
        Logger::log($lang['log_fraud_event'], ['error' => 'No gateway found for fraud order ' . $vars['orderid']]);
        return;
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

add_hook('AfterFraudCheck', 1, function ($vars): void {
    $fraudCronHook = Config::setting('fraud_cron_hook') ?? 'AfterCronJob';

    if ($fraudCronHook !== 'AfterFraudCheck') {
        return;
    }

    if (lkngatewaypreferencescheck_license() === false || !Config::setting('enable_fraudchange')) {
        return;
    }

    $lang = Lang::getModuleLang();
    $skipZeroAmount = Config::setting('skip_zero_amount');

    // Get ALL fraud orders from WHMCS tblorders
    $query = Capsule::table('tblorders')
        ->where('status', 'Fraud');
    
    // Apply skip zero amount filter if enabled
    if ($skipZeroAmount) {
        $query->where('amount', '>', 0);
        
        if (Config::setting('enable_log')) {
            Logger::log('[AfterFraudCheck Hook] Filtro de zero amount ativado', [
                'skip_zero_amount' => 'true'
            ]);
        }
    }
    
    $fraudOrders = $query->get(['id', 'userid', 'amount']);

    if (count($fraudOrders) === 0) {
        if (Config::setting('enable_log')) {
            Logger::log('[AfterFraudCheck Hook] Nenhum pedido em fraude encontrado, abortando');
        }
        return;
    }

    // Process each fraud order
    foreach ($fraudOrders as $fraudOrder) {
        // Check if already tracked
        $trackedOrder = Capsule::table('mod_lkngatewaypreferences_fraud_orders')
            ->where('order_id', $fraudOrder->id)
            ->first();

        if ($trackedOrder && $trackedOrder->status === 'Pending') {
            // Already processed, skip
            continue;
        }

        // Track or update this fraud order
        if (!$trackedOrder) {
            Capsule::table('mod_lkngatewaypreferences_fraud_orders')->insert([
                'order_id' => $fraudOrder->id,
                'status' => 'Fraud'
            ]);

            if (Config::setting('enable_log')) {
                Logger::log('[AfterFraudCheck Hook] Novo pedido em fraude rastreado', [
                    'orderId' => $fraudOrder->id,
                    'amount' => $fraudOrder->amount,
                    'action' => 'inserted'
                ]);
            }
        }

        // Process the fraud order (convert to Pending)
        $res = localAPI(
            'PendingOrder',
            [
                'orderid' => $fraudOrder->id,
            ]
        );

        if (Config::setting('enable_log')) {
            Logger::log('[AfterFraudCheck Hook] Resultado da conversão para Pending', [
                'orderId' => $fraudOrder->id,
                'result' => $res['result'] ?? 'N/A'
            ]);
        }

        if ($res['result'] !== 'success') {
            Logger::log($lang['log_update_fraud_order_e'], [($lang['log_order_num'] . $fraudOrder->id)], [$res]);
            continue;
        }

        // Update status to Pending in tracking table
        $queryRes = Capsule::table('mod_lkngatewaypreferences_fraud_orders')
            ->where('order_id', $fraudOrder->id)
            ->update(['status' => 'Pending']);

        if (Config::setting('enable_log')) {
            Logger::log('[AfterFraudCheck Hook] Atualizando tabela de fraudes', [
                'orderId' => $fraudOrder->id,
                'updateResult' => $queryRes
            ]);
        }

        if ($queryRes <= 0) {
            Logger::log($lang['log_update_fraud_table_e'], [($lang['log_order_num'] . $fraudOrder->id)], [$queryRes]);
            continue;
        }

        Logger::log($lang['log_update_fraud_table_success'], [($lang['log_order_num'] . $fraudOrder->id)], [$queryRes]);

        // Add client note if enabled
        $userid = $fraudOrder->userid;
        if ($userid && Config::setting('enable_notes')) {
            $res = localAPI('AddClientNote', [
                'userid' => $userid,
                'notes' => $lang['log_order_num'] . $fraudOrder->id . $lang['log_altered_order_note'],
                'sticky' => true,
            ]);
            if ($res['result'] !== 'success') {
                Logger::log($lang['log_add_note_e'], [($lang['log_client_num'] . $userid)], [$res]);
            } else {
                Logger::log($lang['log_add_note_success'], [($lang['log_order_num'] . $fraudOrder->id . $lang['log_altered_order_note']), ($lang['log_client_num'] . $userid)], [$res]);
            }
        }
    }
});

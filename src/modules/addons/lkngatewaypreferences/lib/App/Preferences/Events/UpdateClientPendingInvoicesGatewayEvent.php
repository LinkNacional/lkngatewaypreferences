<?php

namespace WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Events;

use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Services\PreferencesService;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\Lang;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\Logger;

final class UpdateClientPendingInvoicesGatewayEvent
{
    public static function run(int $clientId): void
    {
        $allowedGateways = PreferencesService::getAllowed($clientId);

        $invoices = localAPI(
            'GetInvoices',
            [
                'userid' => $clientId,
                'status' => 'Unpaid',
                'limitnum' => 200
            ]
        );

        $log = $invoices;

        $invoices = $invoices['invoices']['invoice'];

        foreach ($invoices as $invoice) {
            if (!in_array($invoice['paymentmethod'], $allowedGateways, true)) {
                $response = localAPI(
                    'UpdateInvoice',
                    [
                        'invoiceid' => $invoice['id'],
                        'paymentmethod' => $allowedGateways[0]
                    ]
                );

                $log[] = [
                    'id' => $invoice['id'],
                    'currentGateway' => $invoice['paymentmethod'],
                    'newGateway' => $allowedGateways[0],
                    'updateInvoiceResponse' => $response
                ];
            }
        }

        Logger::log(Lang::getModuleLang()['log_update_invoice_allowed_gateways'], [$log]);
    }
}

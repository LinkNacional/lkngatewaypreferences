<?php

namespace WHMCS\Module\Addon\lkngatewaypreferences\Helpers;

use WHMCS\Database\Capsule;

/**
 * Diagnostic helper to troubleshoot module issues
 *
 * @since 1.6.3
 */
final class Diagnostic
{
    /**
     * Check if addon module is properly installed and configured
     *
     * @return array Diagnostic information
     */
    public static function checkModuleStatus(): array
    {
        $diagnostic = [
            'timestamp' => date('Y-m-d H:i:s'),
            'module_version' => Config::constant('version'),
            'license' => [
                'valid' => lkngatewaypreferencescheck_license() === true ? 'Yes' : 'No'
            ],
            'configuration' => [],
            'database' => [],
            'errors' => []
        ];

        // Check all configuration settings
        try {
            $diagnostic['configuration']['enable_log'] = Config::setting('enable_log') ? 'Enabled' : 'Disabled';
            $diagnostic['configuration']['enable_fraudchange'] = Config::setting('enable_fraudchange') ? 'Enabled' : 'Disabled';
            $diagnostic['configuration']['enable_notes'] = Config::setting('enable_notes') ? 'Enabled' : 'Disabled';
            $diagnostic['configuration']['enable_fraud_gateways'] = Config::setting('enable_fraud_gateways') ? 'Enabled' : 'Disabled';
            $diagnostic['configuration']['fraud_cron_hook'] = Config::setting('fraud_cron_hook') ?? 'AfterCronJob (default)';
            $diagnostic['configuration']['skip_zero_amount'] = Config::setting('skip_zero_amount') ? 'Enabled' : 'Disabled';
            $diagnostic['configuration']['enable_auto_cancel'] = Config::setting('enable_auto_cancel') ? 'Enabled' : 'Disabled';
            if (Config::setting('enable_auto_cancel')) {
                $diagnostic['configuration']['auto_cancel_days'] = (int) (Config::setting('auto_cancel_days') ?? 0) . ' days';
                $diagnostic['configuration']['cancel_paid_orders'] = Config::setting('cancel_paid_orders') ? 'Enabled' : 'Disabled';
            }
        } catch (\Throwable $e) {
            $diagnostic['errors'][] = 'Error reading configuration: ' . $e->getMessage();
        }

        // Check database tables
        try {
            $tables = [
                'mod_lkngatewaypreferences_settings' => 'Settings',
                'mod_lkngatewaypreferences_by_country' => 'Country Preferences',
                'mod_lkngatewaypreferences_by_client' => 'Client Preferences',
                'mod_lkngatewaypreferences_fraud_settings' => 'Fraud Settings',
                'mod_lkngatewaypreferences_fraud_orders' => 'Fraud Orders (Module Tracking)',
                'mod_lkngatewaypreferences_for_fraud' => 'Fraud Gateways'
            ];

            foreach ($tables as $table => $name) {
                $exists = Capsule::schema()->hasTable($table);
                $diagnostic['database'][$name] = $exists ? 'Exists' : 'Missing';
            }

            // Count fraud orders in WHMCS (actual fraudulent orders)
            $fraudOrdersCount = Capsule::table('tblorders')->where('status', 'Fraud')->count();
            $diagnostic['database']['Total Fraud Orders in WHMCS'] = $fraudOrdersCount;

            // Count tracked fraud orders in module
            if (Capsule::schema()->hasTable('mod_lkngatewaypreferences_fraud_orders')) {
                $trackedFraudCount = Capsule::table('mod_lkngatewaypreferences_fraud_orders')->count();
                $diagnostic['database']['Tracked by Module'] = $trackedFraudCount;
            }
        } catch (\Throwable $e) {
            $diagnostic['errors'][] = 'Error checking database tables: ' . $e->getMessage();
        }

        // Check if logs are being written
        try {
            Logger::log('[Diagnostic] Module diagnostic check performed', $diagnostic);
            $diagnostic['logging'] = 'Working - Log entry created';
        } catch (\Throwable $e) {
            $diagnostic['errors'][] = 'Error writing to logs: ' . $e->getMessage();
        }

        return $diagnostic;
    }

    /**
     * Get detailed fraud orders information
     *
     * @return array Fraud orders details
     */
    public static function getFraudOrdersDetail(): array
    {
        try {
            // Get fraud orders from tblorders (actual WHMCS orders marked as Fraud)
            $fraudOrders = Capsule::table('tblorders')
                ->where('status', 'Fraud')
                ->orderBy('date', 'desc')
                ->limit(10)
                ->select('id', 'userid', 'status', 'date', 'amount', 'paymentmethod')
                ->get();

            $detail = [];
            foreach ($fraudOrders as $order) {
                // Check if this order is also tracked in our module's fraud table
                $isTracked = Capsule::table('mod_lkngatewaypreferences_fraud_orders')
                    ->where('order_id', $order->id)
                    ->exists();

                $detail[] = [
                    'order_id' => $order->id,
                    'client_id' => $order->userid,
                    'status' => $order->status,
                    'created_date' => $order->date,
                    'amount' => $order->amount,
                    'payment_method' => $order->paymentmethod,
                    'tracked_by_module' => $isTracked ? 'Yes' : 'No (Hook not executed)'
                ];
            }

            return $detail;
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Check if AfterCronJob hook is being called
     *
     * @return array Hook test result
     */
    public static function testCronHook(): array
    {
        $testMarker = 'lkn_cron_test_' . time();
        
        try {
            Capsule::table('mod_lkngatewaypreferences_settings')
                ->updateOrInsert(
                    ['setting' => 'cron_test_marker'],
                    ['value' => $testMarker]
                );

            Logger::log('[Diagnostic] Cron hook test marker set', ['marker' => $testMarker]);

            return [
                'test_initiated' => true,
                'test_marker' => $testMarker,
                'instructions' => 'Run WHMCS cron and check if this marker changes to indicate hook execution',
                'next_step' => 'Call this function again after cron runs to see if marker changed'
            ];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }
}

#!/usr/bin/env php
<?php

/**
 * Diagnostic script for lkngatewaypreferences module
 * Run from WHMCS root directory: php modules/addons/lkngatewaypreferences/diagnostic.php
 */

// Determine WHMCS root
$whmcsRoot = dirname(__FILE__);
while ($whmcsRoot !== '/' && !file_exists($whmcsRoot . '/init.php')) {
    $whmcsRoot = dirname($whmcsRoot);
}

if (!file_exists($whmcsRoot . '/init.php')) {
    echo "Error: Could not find WHMCS init.php\n";
    exit(1);
}

require_once $whmcsRoot . '/init.php';

use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\Diagnostic;

echo "\n";
echo "==========================================\n";
echo "LKNGATEWAYPREFERENCES MODULE DIAGNOSTIC\n";
echo "==========================================\n\n";

// Check module status
echo "1. MODULE STATUS\n";
echo "-------------------------------------------\n";
$status = Diagnostic::checkModuleStatus();

foreach ($status as $category => $data) {
    if (is_array($data)) {
        echo "  {$category}:\n";
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                echo "    {$key}: " . json_encode($value) . "\n";
            } else {
                echo "    {$key}: {$value}\n";
            }
        }
    } else {
        echo "  {$category}: {$data}\n";
    }
}

// Check fraud orders
echo "\n2. FRAUD ORDERS\n";
echo "-------------------------------------------\n";
$fraudOrders = Diagnostic::getFraudOrdersDetail();

if (empty($fraudOrders)) {
    echo "  No fraud orders found.\n";
} elseif (isset($fraudOrders['error'])) {
    echo "  Error: " . $fraudOrders['error'] . "\n";
} else {
    echo "  Found " . count($fraudOrders) . " fraud order(s):\n";
    foreach ($fraudOrders as $order) {
        echo "    Order ID: {$order['order_id']}\n";
        echo "      Status: {$order['status']}\n";
        echo "      Created: {$order['creation_date']}\n";
        echo "      Updated: {$order['update_date']}\n";
    }
}

// Test cron hook
echo "\n3. CRON HOOK TEST\n";
echo "-------------------------------------------\n";
$testResult = Diagnostic::testCronHook();

if (isset($testResult['error'])) {
    echo "  Error: " . $testResult['error'] . "\n";
} else {
    foreach ($testResult as $key => $value) {
        echo "  {$key}: {$value}\n";
    }
}

echo "\n==========================================\n";
echo "RECOMMENDATIONS:\n";
echo "==========================================\n\n";

$recommendations = [];

if (isset($status['configuration']['enable_log']) && $status['configuration']['enable_log'] === 'Disabled') {
    $recommendations[] = "1. Enable 'Enable debug logs' in module settings to get detailed logs";
}

if (isset($status['database']['Fraud Orders Count']) && $status['database']['Fraud Orders Count'] == 0) {
    $recommendations[] = "2. No fraud orders found in module tracking. Logs will only show when fraudulent orders exist.";
    $recommendations[] = "   Check 'Total Fraud Orders in WHMCS' - if it's > 0, the hook may not be executing.";
}

if (isset($status['license']['valid']) && $status['license']['valid'] === 'No') {
    $recommendations[] = "3. License is not valid. Some features may be disabled.";
}

if (empty($recommendations)) {
    $recommendations[] = "All checks passed. Module appears to be properly configured.";
    $recommendations[] = "After next WHMCS cron execution, check logs for 'AfterCronJob Hook' entries.";
}

foreach ($recommendations as $rec) {
    echo "  {$rec}\n";
}

echo "\n";

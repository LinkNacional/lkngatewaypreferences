<?php

namespace WHMCS\Module\Addon\lkngatewaypreferences\Helpers;

use WHMCS\Database\Capsule;

/**
 * Helper for automatic fraud order cancellation
 *
 * @since 1.6.4
 */
final class AutoCancel
{
    /**
     * Check if an order should be automatically canceled
     *
     * @param int $orderId Order ID
     * @param array $orderData Order data from tblorders
     * @return bool True if order should be canceled
     */
    public static function shouldCancel(int $orderId, array $orderData): bool
    {
        $enableAutoCancel = Config::setting('enable_auto_cancel');
        $autoCancelDays = (int) (Config::setting('auto_cancel_days') ?? 0);
        $cancelPaidOrders = Config::setting('cancel_paid_orders');

        if (!$enableAutoCancel || $autoCancelDays <= 0) {
            return false;
        }

        $orderDate = $orderData['date'] ?? null;
        if (!$orderDate) {
            return false;
        }

        // Calculate days since order creation
        $orderDateTime = new \DateTime($orderDate);
        $now = new \DateTime();
        $interval = $now->diff($orderDateTime);
        $daysPassed = $interval->days;

        // Check if enough days have passed
        if ($daysPassed < $autoCancelDays) {
            return false;
        }

        // Check if order is paid
        $status = $orderData['status'] ?? null;
        $isPaid = in_array($status, ['Paid', 'Active']);

        if ($isPaid && !$cancelPaidOrders) {
            return false;
        }

        return true;
    }

    /**
     * Cancel an order using WHMCS API
     *
     * @param int $orderId Order ID
     * @return array API response
     */
    public static function cancelOrder(int $orderId): array
    {
        try {
            $response = localAPI('CancelOrder', ['orderid' => $orderId]);

            if (Config::setting('enable_log')) {
                Logger::log('[AutoCancel] Order canceled via API', [
                    'orderId' => $orderId,
                    'result' => $response['result'] ?? 'N/A'
                ]);
            }

            return $response;
        } catch (\Throwable $e) {
            if (Config::setting('enable_log')) {
                Logger::log('[AutoCancel] Error canceling order', [
                    'orderId' => $orderId,
                    'error' => $e->getMessage()
                ]);
            }

            return ['result' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Update fraud order status after cancellation
     *
     * @param int $orderId Order ID
     * @return void
     */
    public static function updateFraudOrderStatus(int $orderId): void
    {
        try {
            Capsule::table('mod_lkngatewaypreferences_fraud_orders')
                ->where('order_id', $orderId)
                ->update(['status' => 'Canceled']);

            if (Config::setting('enable_log')) {
                Logger::log('[AutoCancel] Fraud order status updated to Canceled', [
                    'orderId' => $orderId
                ]);
            }
        } catch (\Throwable $e) {
            if (Config::setting('enable_log')) {
                Logger::log('[AutoCancel] Error updating fraud order status', [
                    'orderId' => $orderId,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}

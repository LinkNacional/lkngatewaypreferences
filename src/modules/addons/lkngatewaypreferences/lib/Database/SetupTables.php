<?php

namespace WHMCS\Module\Addon\lkngatewaypreferences\Database;

use Throwable;
use WHMCS\Database\Capsule;

/**
 * @since 1.0.0
 */
final class SetupTables
{
    /**
     * @since 1.0.0
     */
    public static function activate(): array
    {
        try {
            if (!Capsule::schema()->hasTable('mod_lkngatewaypreferences_by_country')) {
                Capsule::schema()->create(
                    'mod_lkngatewaypreferences_by_country',
                    function (\Illuminate\Database\Schema\Blueprint $table): void {
                        $table->char('country', 2)->unique();
                        $table->longText('gateways');
                    }
                );
            }

            if (!Capsule::schema()->hasTable('mod_lkngatewaypreferences_by_client')) {
                Capsule::schema()->create(
                    'mod_lkngatewaypreferences_by_client',
                    function (\Illuminate\Database\Schema\Blueprint $table): void {
                        $table->unsignedBigInteger('client_id')->unique();
                        $table->longText('gateways');
                    }
                );
            }

            if (!Capsule::schema()->hasTable('mod_lkngatewaypreferences_settings')) {
                Capsule::schema()->create(
                    'mod_lkngatewaypreferences_settings',
                    function (\Illuminate\Database\Schema\Blueprint $table): void {
                        $table->increments('id');
                        $table->string('setting');
                        $table->longText('value')->nullable();
                    }
                );
            }

            if (!Capsule::schema()->hasTable('mod_lkngatewaypreferences_fraud_settings')) {
                Capsule::schema()->create(
                    'mod_lkngatewaypreferences_fraud_settings',
                    function (\Illuminate\Database\Schema\Blueprint $table): void {
                        $table->increments('id');
                        $table->string('setting');
                        $table->longText('value')->nullable();
                    }
                );
            }

            if (!Capsule::schema()->hasTable('mod_lkngatewaypreferences_fraud_orders')) {
                Capsule::schema()->create(
                    'mod_lkngatewaypreferences_fraud_orders',
                    function (\Illuminate\Database\Schema\Blueprint $table): void {
                        $table->increments('id');
                        $table->unsignedBigInteger('order_id');
                        $table->string('status');
                        $table->timestamp('creation_date')->default(Capsule::raw('CURRENT_TIMESTAMP'));
                        $table->timestamp('update_date')->default(Capsule::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
                    }
                );
            }

            if (!Capsule::schema()->hasTable('mod_lkngatewaypreferences_for_fraud')) {
                Capsule::schema()->create(
                    'mod_lkngatewaypreferences_for_fraud',
                    function (\Illuminate\Database\Schema\Blueprint $table): void {
                        $table->char('country', 2)->unique();
                        $table->longText('gateways');
                    }
                );
            }

            return ['status' => 'success'];
        } catch (Throwable $e) {
            return [
                'status' => 'error',
                'description' => "Unable to complete database tables setup: {$e->getMessage()}"
            ];
        }
    }
}

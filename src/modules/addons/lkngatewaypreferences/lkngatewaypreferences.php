<?php

use WHMCS\Database\Capsule;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Controllers\GeneralSettingsController;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Controllers\PrefByClientController;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Controllers\PrefByCountryController;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Controllers\PrefByFraudController;
use WHMCS\Module\Addon\lkngatewaypreferences\Database\SetupTables;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\Config;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\Lang;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\Logger;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\View;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/lib/utils.php';

/**
 * Define addon module configuration parameters.
 *
 * Also allows you to define any configuration parameters that should be
 * presented to the user when activating and configuring the module. These
 * values are then made available in all module function calls.
 *
 * @see https://developers.whmcs.com/addon-modules/configuration/
 *
 * @return array
 */
function lkngatewaypreferences_config()
{
    $moduleUrl = lkngatewaypreferences_module_url();

    $language = $GLOBALS['CONFIG']['Language'];

    if (!in_array($language, ['english', 'portuguese-pt', 'portuguese-br'], true)) {
        $language = 'english';
    }

    require_once __DIR__ . "/lang/$language.php";

    return [
        'name' => $_ADDONLANG['Gateway Preferences'],
        'description' => <<<EOT
        <div style="margin-bottom: 10px">{$_ADDONLANG['Define payment gateways individually for each client and country.']}</div>
        <div>{$_ADDONLANG['By']} <a href="https://www.linknacional.com.br/" target="_blank"><strong>{$_ADDONLANG['Link Nacional']}</strong></a></div>
EOT,
        'author' => <<<EOT
        <a href="https://www.linknacional.com.br/" target="_blank">
            <img src="{$moduleUrl}/lib/assets/logo_vertical.png" width="70px" style="margin: 5px;">
        </a>
EOT,
        'version' => Config::constant('version'),
        'language' => $language,
        'fields' => [
            'header' => [
                'Description' => <<<HTML
                <div style="margin: 30px;">
                    <p style="margin-top: 12px">
                        <i class="fas fa-exclamation-triangle fa-sm"></i>
                        {$_ADDONLANG['Grant Access Control to your group to access the module settings page.']}
                    </p>
                    <div>
                        <a href="addonmodules.php?module=lkngatewaypreferences">
                            <strong>{$_ADDONLANG['Access Module Settings']}</strong>
                        </a> &#x2022
                        <a href="logs/module-log">
                            <strong>{$_ADDONLANG['Access Module Logs']}</strong>
                        </a>
                    </div>
                </div>
HTML
            ]
        ]
    ];
}

/**
 * Activate.
 *
 * Called upon activation of the module for the first time.
 * Use this function to perform any database and schema modifications
 * required by your module.
 *
 * @see https://developers.whmcs.com/advanced/db-interaction/
 *
 * @return array Optional success/failure message
 */
function lkngatewaypreferences_activate()
{
    return SetupTables::activate();
}

/**
 * Admin Area Output.
 *
 * Called when the addon module is accessed via the admin area.
 * Should return HTML output for display to the admin user.
 *
 * @see https://developers.whmcs.com/addon-modules/admin-area-output/
 *
 * @return void
 */
function lkngatewaypreferences_output($vars): void
{
    $controller = isset($_REQUEST['c']) ? strip_tags($_REQUEST['c']) : 'general';
    $method = isset($_REQUEST['r']) ? strip_tags($_REQUEST['r']) : 'create';

    // Special handling for diagnostic page
    if ($controller === 'diagnostic') {
        echo View::render('views.admin.pages.diagnostic', [
            'lang' => Lang::getModuleLang(),
        ]);
        return;
    }

    $controllerInstance = match ($controller) {
        'by-country' => new PrefByCountryController(),
        'by-client' => new PrefByClientController(),
        'by-fraud' => new PrefByFraudController(),
        'general' => new GeneralSettingsController()
    };

    echo $controllerInstance->$method();
}

function lkngatewaypreferences_upgrade($vars): void
{
    $currentlyInstalledVersion = $vars['version'];

    try {
        if ($currentlyInstalledVersion < 1.1) {
            Capsule::schema()
                ->create(
                    'mod_lkngatewaypreferences_settings',
                    function (Illuminate\Database\Schema\Blueprint $table): void {
                        $table->increments('id');
                        $table->string('setting');
                        $table->longText('value')->nullable();
                    }
                );
        }

        if ($currentlyInstalledVersion < 1.4) {
            Capsule::schema()
            ->create(
                'mod_lkngatewaypreferences_fraud_orders',
                function (Illuminate\Database\Schema\Blueprint $table): void {
                    $table->increments('id');
                    $table->unsignedBigInteger('order_id');
                    $table->string('status');
                    $table->timestamp('creation_date')->default(Capsule::raw('CURRENT_TIMESTAMP'));
                    $table->timestamp('update_date')->default(Capsule::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
                }
            );
        }

        if ($currentlyInstalledVersion < 1.6) {
            Capsule::schema()
                ->create(
                    'mod_lkngatewaypreferences_for_fraud',
                    function (Illuminate\Database\Schema\Blueprint $table): void {
                        $table->char('country', 2)->unique();
                        $table->longText('gateways');
                    }
                );
        }
    } catch (Throwable $e) {
        Logger::log(
            Lang::getModuleLang()['log_update_database_e'],
            ['error' => $e->getMessage()]
        );
    }
}

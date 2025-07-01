<?php

/**
 * Provides general functions for better organization of the project.
 *
 * @since 1.0.0
 */

use WHMCS\Database\Capsule;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\Config;

/**
 * @since 1.0.0
 *
 * @return string
 */
function lkngatewaypreferences_system_url(): string
{
    $url = Capsule::table('tblconfiguration')
            ->where('setting', 'SystemURL')
            ->first(['value'])
            ->value;

    return rtrim($url, '/');
}

/**
 * @since 1.0.0
 *
 * @return string
 */
function lkngatewaypreferences_module_url(): string
{
    return lkngatewaypreferences_system_url() . '/modules/addons/' . Config::constant('name');
}

/**
 * @since 1.0.0
 *
 * @return array
 */
function lkngatewaypreferences_create_custom_field_select(): array
{
    $fields = Capsule::table('tblcustomfields')
        ->where('type', 'client')
        ->get(['id', 'fieldname']);

    $selectData = ['' => 'Selecionar opção'];

    foreach ($fields as $field) {
        $selectData[$field->id] = $field->fieldname;
    }

    return $selectData;
}

/**
 * @since 1.0.0
 *
 * @param bool  $success
 * @param array $value
 *
 * @return void
 */
function lkngatewaypreferences_json_response(bool $success, array $value = []): void
{
    $response = [];

    $response['success'] = $success;
    $response['data'] = $value;

    header('Content-Type: application/json');

    echo json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

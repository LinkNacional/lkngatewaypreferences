<?php

namespace WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Abstract;

/**
 * @since 1.0.0
 */
abstract class AbstractController
{
    /**
     * @since 1.0.0
     *
     * @param bool  $success
     * @param array $value
     *
     * @return void
     */
    final public static function response(bool $success, array $value = []): void
    {
        $response = [];

        $response['success'] = $success;
        $response['data'] = $value;

        header('Content-Type: application/json');

        echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}

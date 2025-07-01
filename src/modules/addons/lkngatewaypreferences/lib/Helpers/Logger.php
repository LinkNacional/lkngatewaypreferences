<?php

namespace WHMCS\Module\Addon\lkngatewaypreferences\Helpers;

final class Logger
{
    /**
     * @since 1.0.0
     *
     * @param string       $action
     * @param array        $request
     * @param array|string $response
     *
     * @return void
     */
    public static function log(string $action, array $request, string|array $response = []): void
    {
        $isDebugEnabled = Config::setting('enable_log');

        if ($isDebugEnabled) {
            logModuleCall(
                Config::constant('name'),
                $action,
                json_encode($request, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            );
        }
    }
}

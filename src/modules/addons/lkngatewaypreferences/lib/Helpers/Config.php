<?php

namespace WHMCS\Module\Addon\lkngatewaypreferences\Helpers;

use WHMCS\Database\Capsule;

/**
 * Provides helper methods to access the modules settings.
 *
 * @since 1.0.0
 */
final class Config
{
    /**
     * Returns a constant defined in configs.php.
     *
     * @since 1.0.0
     *
     * @param string $constant
     *
     * @return mixed
     */
    final public static function constant(string $constant): mixed
    {
        $constants = require __DIR__ . '/../configs.php';

        return self::getArrayKeyValue($constants, $constant);
    }

    /**
     * Returns a module's constants according to the current env, defined in config.php.
     *
     * @since 1.0.0
     *
     * @param string $name
     *
     * @return mixed
     */
    final public static function env(string $varName): mixed
    {
        $constants = require __DIR__ . '/../configs.php';

        $env = $constants['env'];

        return self::getArrayKeyValue($constants, "$env.$varName");
    }

    /**
     * Returns a module's setting, defined in _config().
     *
     * @since 1.0.0
     *
     * @param string $name
     *
     * @return mixed
     */
    final public static function setting(string $name): mixed
    {
        $settingRow = Capsule::table('mod_lkngatewaypreferences_settings')
            ->where('setting', $name)
            ->first(['value']);

        if (!$settingRow) {
            return null;
        }

        return self::parseConfig($name, $settingRow->value);
    }

    /**
     * @since 1.0.0
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return mixed
     */
    private static function parseConfig(string $name, mixed $value): mixed
    {
        return match ($name) {
            'enable_log' => $value === 'on',
            'enable_fraudchange' => $value === 'on',
            'enable_notes' => $value === 'on',
            'order_fraud_gateway' => json_decode($value, true),
            'fraud_cron_hook' => trim($value) ?: 'AfterCronJob',
            default => trim($value)
        };
    }

    /**
     * @since 1.0.0
     *
     * @param array  $array
     * @param string $keys  can be a key1.subkey1.subkey2.
     *
     * @return mixed
     */
    private static function getArrayKeyValue(array $array, string $keys): mixed
    {
        $keys = explode('.', $keys);

        foreach ($keys as $key) {
            if (is_array($array) && array_key_exists($key, $array)) {
                $array = $array[$key];
            }
        }

        return $array;
    }
}

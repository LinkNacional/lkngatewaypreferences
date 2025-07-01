<?php

namespace WHMCS\Module\Addon\lkngatewaypreferences\Helpers;

use Exception;
use Smarty;

final class View
{
    public static function render(string $view, array $vars = []): string
    {
        $viewPath = str_replace('.', '/', $view);
        $viewPath = Config::constant('resources_path') . "/$viewPath.tpl";

        if (!file_exists($viewPath)) {
            throw new Exception('Smarty template not found.');
        }

        $smarty = new Smarty();
        $smarty = self::assignVars($smarty, $vars);

        return $smarty->fetch($viewPath);
    }

    /**
     * Renders the script tags necessary for removing the gateway <select> tag
     * on front end.
     *
     * @since 1.1.0
     *
     * @param string $scriptName               The .js file under resources/hooks that will
     *                                         handle the current page.
     * @param array  $unallowedGateways        an array of the gateways codes.
     * @param string $paymentMethodsSelectName the name="" attribute for
     *                                         the select alement that has the
     *                                         gateway options.
     *
     * @return string
     */
    public static function renderPreferencesScript(
        string $scriptName,
        array $unallowedGateways,
        string $paymentMethodsSelectName = ''
    ): string {
        $unallowedGateways = json_encode($unallowedGateways);
        $systemURL = lkngatewaypreferences_system_url();
        return <<<HTML
        <script type="text/javascript">
            const unallowedGateways = {$unallowedGateways}
            const paymentMethodsSelectName = '{$paymentMethodsSelectName}'
        </script>

        <script
            src="{$systemURL}/modules/addons/lkngatewaypreferences/lib/resources/hooks/{$scriptName}.js"
            defer
        ></script>
HTML;
    }

    private static function assignVars(Smarty $smartyInstance, array $vars): Smarty
    {
        foreach ($vars as $key => $value) {
            $smartyInstance->assign($key, $value);
        }

        $smartyInstance->assign('moduleVersion', Config::constant('version'));

        $lang = Lang::getModuleLang();

        $smartyInstance->assign('lang', $lang);
        $smartyInstance->assign('jsonLang', json_encode($lang, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        require_once __DIR__ . '/../../license.php';
        require_once __DIR__ . '/../utils.php';
        $systemURL = lkngatewaypreferences_system_url();

        $license = lkngatewaypreferencescheck_license();

        if (is_string($license)) {
            $smartyInstance->assign('license_msg', $license);
        }

        $smartyInstance->assign('license', new License());
        $smartyInstance->assign('systemURL', $systemURL);
        return $smartyInstance;
    }
}

<?php

namespace WHMCS\Module\Addon\lkngatewaypreferences\Helpers;

final class Lang
{
    public static function getModuleLang(): array
    {
        $language = $GLOBALS['CONFIG']['Language'];

        if (!in_array($language, ['english', 'portuguese-pt', 'portuguese-br'], true)) {
            $language = 'english';
        }

        require __DIR__ . "/../../lang/$language.php";

        return $_ADDONLANG;
    }

    public static function text(string $text): string
    {
        return self::getModuleLang()[$text];
    }
}

<?php

namespace WHMCS\Module\Addon\lkngatewaypreferences\Helpers;

final class Lang
{
    /**
     * Idiomas com arquivo de tradução no módulo.
     */
    private const SUPPORTED = ['english', 'portuguese-pt', 'portuguese-br'];

    /**
     * Fallback final quando nem o idioma atual nem o inglês têm a chave.
     */
    private const FALLBACK = 'english';

    /**
     * Cache por idioma para não reler o arquivo a cada chamada.
     *
     * @var array<string, array<string, string>>
     */
    private static array $cache = [];

    /**
     * Retorna as traduções do idioma atual do WHMCS (ou inglês, se o idioma não tiver arquivo).
     *
     * @since 1.0.0
     *
     * @return array<string, string>
     */
    public static function getModuleLang(): array
    {
        $language = (string) ($GLOBALS['CONFIG']['Language'] ?? '');

        if (!in_array($language, self::SUPPORTED, true)) {
            $language = self::FALLBACK;
        }

        return self::load($language);
    }

    /**
     * Traduz uma string do módulo. Nunca retorna null (assinatura mantida em string):
     * idioma atual -> inglês -> a própria chave.
     *
     * @since 1.0.0
     *
     * @param string $text
     *
     * @return string
     */
    public static function text(string $text): string
    {
        $current = self::getModuleLang();

        if (isset($current[$text]) && is_string($current[$text])) {
            return $current[$text];
        }

        $english = self::load(self::FALLBACK);

        if (isset($english[$text]) && is_string($english[$text])) {
            return $english[$text];
        }

        // Último recurso: devolve a própria chave (nunca null, nunca 500 no hook).
        return $text;
    }

    /**
     * Carrega (com cache) o arquivo de tradução de um idioma.
     *
     * @param string $language
     *
     * @return array<string, string>
     */
    private static function load(string $language): array
    {
        if (isset(self::$cache[$language])) {
            return self::$cache[$language];
        }

        $file = __DIR__ . "/../../lang/$language.php";

        if (!is_file($file)) {
            $file = __DIR__ . '/../../lang/' . self::FALLBACK . '.php';
        }

        $_ADDONLANG = [];

        if (is_file($file)) {
            require $file;
        }

        return self::$cache[$language] = is_array($_ADDONLANG) ? $_ADDONLANG : [];
    }
}

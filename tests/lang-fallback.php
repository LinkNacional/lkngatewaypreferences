<?php

/**
 * Teste simples do helper Lang (sem framework de testes).
 *
 * Uso: php tests/lang-fallback.php
 * Sai com código 0 quando todos os casos passam e 1 quando algum falha.
 */

require __DIR__ . '/../src/modules/addons/lkngatewaypreferences/lib/Helpers/Lang.php';

use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\Lang;

$key = 'Payment method unavailable for the selected country.';
$failures = 0;

function check(string $label, $value, string $expectedType = 'string'): void
{
    global $failures;

    if (get_debug_type($value) !== $expectedType) {
        echo "FALHOU  $label -> " . get_debug_type($value) . ' (esperado ' . $expectedType . ')' . PHP_EOL;
        $failures++;
        return;
    }

    if ($expectedType === 'string' && trim($value) === '') {
        echo "FALHOU  $label -> string vazia" . PHP_EOL;
        $failures++;
        return;
    }

    echo 'OK      ' . $label . ' -> ' . (is_string($value) ? $value : (string) $value) . PHP_EOL;
}

$cases = [
    'pt-BR' => 'portuguese-br',
    'pt-PT' => 'portuguese-pt',
    'en'    => 'english',
    'es'    => 'spanish',
];

foreach ($cases as $label => $language) {
    $GLOBALS['CONFIG']['Language'] = $language;
    check("texto do hook em $label", Lang::text($key));
}

// Chave inexistente: precisa devolver a própria chave (string), nunca null.
$GLOBALS['CONFIG']['Language'] = 'portuguese-br';
$missing = Lang::text('chave.que.nao.existe.de.proposito');
check('chave inexistente devolve a própria chave', $missing);
if ($missing !== 'chave.que.nao.existe.de.proposito') {
    echo "FALHOU  chave inexistente não devolveu a chave" . PHP_EOL;
    $failures++;
}

// Sem idioma definido no WHMCS: cai no inglês e continua retornando string.
unset($GLOBALS['CONFIG']['Language']);
check('sem CONFIG[Language]', Lang::text($key));

// getModuleLang continua devolvendo array.
$GLOBALS['CONFIG']['Language'] = 'portuguese-br';
check('getModuleLang devolve array', Lang::getModuleLang(), 'array');

echo PHP_EOL . ($failures === 0 ? 'TODOS OS CASOS PASSARAM' : "$failures caso(s) falharam") . PHP_EOL;

exit($failures === 0 ? 0 : 1);

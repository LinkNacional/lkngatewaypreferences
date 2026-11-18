<?php

/**
 * Public endpoint used by the checkout to discover which gateways are allowed
 * for a given country (guest checkout included).
 *
 * Action: gateways-by-country
 * Input : {"country":"BR"} as JSON body (same-origin request)
 * Output: {"success":true,"data":{"gateways":["code1","code2"]}}
 * Errors: 400 invalid_action|invalid_country, 403 forbidden, 429 rate_limited,
 *         500 unavailable
 *
 * The list comes from the same source used by the module preferences:
 * PrefByCountryRepository::getOne() with fallback to getGlobalPref().
 *
 * @since 1.7.0
 */

require_once __DIR__ . '/../../../init.php';

use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Repository\PrefByCountryRepository;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

/**
 * Sends a JSON response and stops the request.
 */
function lkngatewaypreferences_public_json(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Simple per-IP rate limit (30 requests per minute).
 */
function lkngatewaypreferences_public_rate_limit(string $key, int $limit = 30, int $window = 60): bool
{
    $dir = sys_get_temp_dir() . '/lkngatewaypreferences_rl';

    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }

    $file = $dir . '/' . sha1($key) . '.json';
    $now = time();
    $data = ['start' => $now, 'hits' => 0];

    if (is_file($file)) {
        $raw = @file_get_contents($file);
        $parsed = $raw ? json_decode($raw, true) : null;

        if (is_array($parsed) && isset($parsed['start'], $parsed['hits'])) {
            $data = $parsed;
        }
    }

    if ($now - (int) $data['start'] >= $window) {
        $data = ['start' => $now, 'hits' => 0];
    }

    $data['hits'] = (int) $data['hits'] + 1;
    @file_put_contents($file, json_encode($data), LOCK_EX);

    return $data['hits'] <= $limit;
}

$body = json_decode(file_get_contents('php://input'), true);

if (!is_array($body)) {
    $body = $_POST;
}

$action = isset($body['a']) ? (string) $body['a'] : '';

if ($action !== 'gateways-by-country') {
    lkngatewaypreferences_public_json(400, ['success' => false, 'error' => 'invalid_action']);
}

$ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '0.0.0.0';

if (!lkngatewaypreferences_public_rate_limit('gw:' . $ip)) {
    lkngatewaypreferences_public_json(429, ['success' => false, 'error' => 'rate_limited']);
}

/*
 * CSRF: this WHMCS build does not expose a token validation helper to addon
 * endpoints, so the protection is "same-origin only" (Origin/Referer/Sec-Fetch-Site)
 * plus the rate limit above. The endpoint is read-only and returns gateway codes only.
 */
$host = isset($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : '';
$origin = isset($_SERVER['HTTP_ORIGIN']) ? (string) $_SERVER['HTTP_ORIGIN'] : '';
$referer = isset($_SERVER['HTTP_REFERER']) ? (string) $_SERVER['HTTP_REFERER'] : '';
$fetchSite = isset($_SERVER['HTTP_SEC_FETCH_SITE']) ? (string) $_SERVER['HTTP_SEC_FETCH_SITE'] : '';

$sameOrigin = ($fetchSite === 'same-origin');

foreach ([$origin, $referer] as $url) {
    if ($url === '') {
        continue;
    }

    $parsedHost = parse_url($url, PHP_URL_HOST);

    if ($parsedHost && $host && strcasecmp((string) $parsedHost, preg_replace('/:\d+$/', '', $host)) === 0) {
        $sameOrigin = true;
    }
}

if (!$sameOrigin) {
    lkngatewaypreferences_public_json(403, ['success' => false, 'error' => 'forbidden']);
}

$country = isset($body['country']) ? strtoupper(trim((string) $body['country'])) : '';

if (!preg_match('/^[A-Z]{2}$/', $country)) {
    lkngatewaypreferences_public_json(400, ['success' => false, 'error' => 'invalid_country']);
}

try {
    $repository = new PrefByCountryRepository();
    $gateways = $repository->getOne($country);

    if (!is_array($gateways) || count($gateways) === 0) {
        $gateways = $repository->getGlobalPref();
    }

    $gateways = array_values(array_filter(array_map('strval', (array) $gateways)));
} catch (\Throwable $e) {
    lkngatewaypreferences_public_json(500, ['success' => false, 'error' => 'unavailable']);
}

lkngatewaypreferences_public_json(200, ['success' => true, 'data' => ['gateways' => $gateways]]);

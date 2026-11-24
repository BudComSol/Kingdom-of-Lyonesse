<?php

function checking($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function fch($sql, $params = []) { return $GLOBALS['analyticsSetting'] ?? false; }
function cnt($sql, $params = []) { return 0; }
function check($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}

define('APP_NAME', 'Kingdom of Lyonesse');
define('BASE_URL', 'https://example.test');
$analyticsSetting = false;
$sourceStylesheet = file_get_contents(__DIR__ . '/../style/style.css');
$minifiedStylesheet = file_get_contents(__DIR__ . '/../style/style.min.css');
$cookieNoticeStylesheet = file_get_contents(__DIR__ . '/../style/cookie-notice.css');
check($sourceStylesheet !== false, 'Readable stylesheet missing');
check($minifiedStylesheet !== false, 'Minified stylesheet missing');
check($cookieNoticeStylesheet !== false, 'Cookie notice stylesheet missing');
check(strlen($minifiedStylesheet) < strlen($sourceStylesheet), 'Minified stylesheet is not smaller than its source');
check(!str_contains($minifiedStylesheet, "\n"), 'Minified stylesheet contains line breaks');

$source = file_get_contents(__DIR__ . '/../core/func.php');
$start = strpos($source, 'function head(');
$end = strpos($source, 'function foot(', $start);
check($start !== false && $end !== false, 'Header function missing');
$header = substr($source, $start, $end - $start);
$header = str_replace('__DIR__', var_export(__DIR__ . '/../core', true), $header);
eval($header);

foreach ([0, 1] as $uid) {
    $user = $uid > 0 ? ['start' => 0] : false;
    ob_start();
    head('Style test', $uid);
    $html = ob_get_clean();

    check(!str_contains($html, 'application/ld+json'), 'Structured data should not appear on ordinary pages');
    check(!str_contains($html, 'property="og:'), 'Social metadata should not appear on ordinary pages');
    check(!str_contains($html, 'name="twitter:'), 'Twitter metadata should not appear on ordinary pages');
    check(preg_match('/<style>(.*?)<\/style>/s', $html, $matches) === 1, 'Inline stylesheet missing');
    check($matches[1] === $minifiedStylesheet, 'Inline stylesheet differs from minified source');
    check(
        $uid <= 0
            ? str_contains($html, '<style>' . $cookieNoticeStylesheet . '</style>')
            : !str_contains($html, '<style>' . $cookieNoticeStylesheet . '</style>'),
        'Cookie notice styles are not limited to guest pages'
    );
    check(!str_contains($html, 'href="/style/style.css'), 'External game stylesheet still requested');
    check(str_contains($html, "url('/images/start/bg.webp')"), 'Background URL must work on nested routes');
    check(preg_match('/url\(\s*[\'"]?(?!\/|https?:|data:)[a-z.]/i', $matches[1]) === 0, 'Inline CSS has a relative asset URL');

    check(preg_match('/<noscript>(.*?)<\/noscript>/s', $html, $fallback) === 1, 'No-JavaScript font fallback missing');
    check(str_contains($fallback[1], 'fonts.googleapis.com/css2?'), 'Fallback font stylesheet missing');
    $withScripts = str_replace($fallback[0], '', $html);
    check(preg_match_all('/<link\b[^>]*rel="stylesheet"[^>]*>/', $withScripts, $links) === 1, 'Unexpected stylesheet request');
    check(str_contains($links[0][0], 'media="print"'), 'Fonts block initial rendering');
    check(str_contains($links[0][0], "this.onload=null;this.media='all'"), 'Fonts do not apply after loading');
    check(str_contains($links[0][0], 'display=swap'), 'Font swap behavior missing');
}

$analyticsSetting = ['setting_value' => 'G-ABCDEF1234'];
$_COOKIE = [];
ob_start();
head('Cookie consent test', 0);
$analyticsBeforeConsent = ob_get_clean();
check(!str_contains($analyticsBeforeConsent, 'googletagmanager.com'), 'Analytics loaded before cookie consent');

$_COOKIE['lyonesse_cookie_consent'] = 'accepted';
ob_start();
head('Cookie consent test', 0);
$analyticsAfterConsent = ob_get_clean();
check(str_contains($analyticsAfterConsent, 'googletagmanager.com'), 'Analytics did not load after cookie consent');

$user = false;
ob_start();
head('A MMORPG Browser Game', 0, true);
$landingHtml = ob_get_clean();
check(str_contains($landingHtml, '<title>A MMORPG Browser Game - ' . APP_NAME . '</title>'), 'Landing page title missing');
check(str_contains($landingHtml, '<meta property="og:title" content="A MMORPG Browser Game - ' . APP_NAME . '">'), 'Open Graph title missing');
check(str_contains($landingHtml, '<meta property="og:description" content="Kingdom of Lyonesse modern MMORPG for mobile and desktop.'), 'Open Graph description missing');
check(str_contains($landingHtml, '<meta property="og:url" content="' . BASE_URL . '/">'), 'Open Graph URL missing');
check(str_contains($landingHtml, '<meta property="og:image" content="' . BASE_URL . '/images/logo.webp">'), 'Open Graph image missing');
check(str_contains($landingHtml, '<meta name="twitter:card" content="summary_large_image">'), 'Twitter card type missing');
check(str_contains($landingHtml, '<meta name="twitter:title" content="A MMORPG Browser Game - ' . APP_NAME . '">'), 'Twitter title missing');
check(str_contains($landingHtml, '<meta name="twitter:image" content="' . BASE_URL . '/images/logo.webp">'), 'Twitter image missing');
check(preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $landingHtml, $schemaMatch) === 1, 'Landing page structured data missing');
$schema = json_decode($schemaMatch[1], true, 512, JSON_THROW_ON_ERROR);
check($schema['@context'] === 'https://schema.org' && $schema['@type'] === 'VideoGame', 'Incorrect structured data type');
check($schema['name'] === APP_NAME && $schema['url'] === BASE_URL . '/', 'Incorrect game identity or URL');
check($schema['image'] === BASE_URL . '/images/logo.webp', 'Incorrect game image URL');
check(!empty($schema['description']) && $schema['genre'] === 'MMORPG' && $schema['gamePlatform'] === 'Web browser', 'Game details missing');

ob_start();
head('In-game page', 1, true);
check(!str_contains(ob_get_clean(), 'application/ld+json'), 'Structured data should not appear inside the game');

$footerStart = strpos($source, 'function foot(');
$footerEnd = strpos($source, '// ---------------------------------------------------------------------', $footerStart);
check($footerStart !== false && $footerEnd !== false, 'Footer function missing');
eval(substr($source, $footerStart, $footerEnd - $footerStart));
$cookieConsentToken = 'test-consent-token';
$ref = 0;
$user = [];
ob_start();
foot(1);
$footerHtml = ob_get_clean();
check(str_contains($footerHtml, '<a href="/index.php?help=1">Help</a>'), 'Footer help link missing');
check(!str_contains($footerHtml, 'id="cookie-notice"'), 'Cookie notice should not appear inside the game');

$user = false;
$_SERVER['PHP_SELF'] = '/index.php';
$_GET = [];
$_COOKIE = [];
ob_start();
foot(0);
$guestFooterHtml = ob_get_clean();
check(str_contains($guestFooterHtml, 'id="cookie-notice"'), 'Cookie notice missing from guest pages');
check(str_contains($guestFooterHtml, 'Accept cookies to continue to the game'), 'Cookie notice does not require consent before entry');
check(str_contains($guestFooterHtml, 'name="csrf_token" value="test-consent-token"'), 'Cookie consent form is missing its CSRF token');

$_SERVER['PHP_SELF'] = '/privacy.php';
ob_start();
foot(0);
$otherGuestFooterHtml = ob_get_clean();
check(!str_contains($otherGuestFooterHtml, 'id="cookie-notice"'), 'Cookie notice should only appear on the landing page');

$user = [];
ob_start();
foot(1);
$loggedInFooterHtml = ob_get_clean();
check(!str_contains($loggedInFooterHtml, 'id="cookie-notice"'), 'Cookie notice should not appear inside the game');

$_SERVER['PHP_SELF'] = '/index.php';
$_COOKIE['lyonesse_cookie_consent'] = 'accepted';
ob_start();
foot(0);
$acceptedFooterHtml = ob_get_clean();
check(!str_contains($acceptedFooterHtml, 'id="cookie-notice"'), 'Cookie notice should be hidden after consent');

echo "Header stylesheet tests passed\n";

<?php

function checking($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function fch($sql, $params = []) { return false; }
function check($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}

define('APP_NAME', 'Kingdom of Lyonesse');
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

    check(preg_match('/<style>(.*?)<\/style>/s', $html, $matches) === 1, 'Inline stylesheet missing');
    check($matches[1] === file_get_contents(__DIR__ . '/../style/style.css'), 'Inline stylesheet differs from source');
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

echo "Header stylesheet tests passed\n";

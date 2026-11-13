<?php

$testAppKey = 'paypal-settings-test-key-with-at-least-32-characters';

function env($key, $default = null) {
    global $testAppKey;
    return $key === 'APP_KEY' ? $testAppKey : $default;
}

function check($condition, $message) {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

require_once __DIR__ . '/../core/paypal.php';

$secret = 'paypal-test-secret';
$encrypted = paypalEncryptSecret($secret);
check($encrypted !== $secret, 'PayPal Secret was not encrypted');
check(paypalDecryptSecret($encrypted) === $secret, 'PayPal Secret round trip failed');

$testAppKey = 'different-test-key-that-is-at-least-32-characters';
try {
    paypalDecryptSecret($encrypted);
    throw new RuntimeException('Secret decrypted with the wrong APP_KEY');
} catch (RuntimeException $e) {
    check(str_contains($e->getMessage(), 'APP_KEY'), 'Wrong-key failure was not reported');
}
$testAppKey = 'paypal-settings-test-key-with-at-least-32-characters';

$packs = paypalNormalizePacks(paypalDefaultPacks());
check(count($packs) === 4 && $packs[2]['price'] === '14.99', 'Default Ruby packs did not normalize');

$packs[0]['price'] = '-1';
try {
    paypalNormalizePacks($packs);
    throw new RuntimeException('Invalid pack price was accepted');
} catch (InvalidArgumentException $e) {
    check(str_contains($e->getMessage(), 'pack'), 'Invalid pack failure was not reported');
}

echo "PayPal settings tests passed\n";

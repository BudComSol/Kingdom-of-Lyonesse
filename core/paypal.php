<?php

function paypalSetting($key, $default = '') {
    $setting = fch(
        "SELECT `setting_value` FROM `site_settings` WHERE `setting_key` = ? LIMIT 1",
        [$key]
    );
    return $setting ? $setting['setting_value'] : $default;
}

function paypalSaveSetting($key, $value) {
    qry(
        "INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)",
        [$key, $value]
    );
}

function paypalDefaultPacks() {
    return [
        ['name' => 'Pouch of Rubies', 'rubies' => 50, 'price' => '1.99', 'vip_days' => 0],
        ['name' => 'Chest of Rubies', 'rubies' => 150, 'price' => '4.99', 'vip_days' => 0],
        ['name' => 'Treasury Vault', 'rubies' => 500, 'price' => '14.99', 'vip_days' => 1],
        ['name' => "Dragon's Hoard", 'rubies' => 1200, 'price' => '29.99', 'vip_days' => 3],
    ];
}

function paypalLoadPacks() {
    $json = paypalSetting('paypal_ruby_packs');
    $packs = json_decode($json, true);
    if (!is_array($packs) || count($packs) !== 4) {
        return paypalDefaultPacks();
    }

    foreach ($packs as $pack) {
        if (
            !is_array($pack)
            || !isset($pack['name'], $pack['rubies'], $pack['price'], $pack['vip_days'])
            || !is_numeric($pack['rubies'])
            || !is_numeric($pack['price'])
            || !is_numeric($pack['vip_days'])
        ) {
            return paypalDefaultPacks();
        }
    }

    return array_values($packs);
}

function paypalNormalizePacks($input) {
    if (!is_array($input) || count($input) !== 4) {
        throw new InvalidArgumentException('All four Ruby packs are required.');
    }

    $packs = [];
    for ($i = 0; $i < 4; $i++) {
        $pack = $input[$i] ?? null;
        if (!is_array($pack)) {
            throw new InvalidArgumentException('Invalid Ruby pack settings.');
        }

        $name = trim(strip_tags((string)($pack['name'] ?? '')));
        $rubies = filter_var($pack['rubies'] ?? null, FILTER_VALIDATE_INT);
        $price = trim((string)($pack['price'] ?? ''));
        $vipDays = filter_var($pack['vip_days'] ?? null, FILTER_VALIDATE_INT);
        if (
            $name === ''
            || mb_strlen($name) > 50
            || $rubies === false
            || $rubies < 1
            || $rubies > 1000000
            || !preg_match('/^\d{1,5}(?:\.\d{1,2})?$/', $price)
            || (float)$price <= 0
            || $vipDays === false
            || $vipDays < 0
            || $vipDays > 3650
        ) {
            throw new InvalidArgumentException('Check each pack name, Ruby amount, price, and VIP days.');
        }

        $packs[] = [
            'name' => $name,
            'rubies' => $rubies,
            'price' => number_format((float)$price, 2, '.', ''),
            'vip_days' => $vipDays,
        ];
    }
    return $packs;
}

function paypalEncryptionKey() {
    $appKey = (string)env('APP_KEY', '');
    if (strlen($appKey) < 32) {
        throw new RuntimeException('Set a random APP_KEY of at least 32 characters in .env before using PayPal credentials.');
    }
    return hash('sha256', $appKey, true);
}

function paypalEncryptSecret($secret) {
    $key = paypalEncryptionKey();
    $iv = random_bytes(12);
    $tag = '';
    $ciphertext = openssl_encrypt($secret, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    if ($ciphertext === false) {
        throw new RuntimeException('Could not encrypt the PayPal secret.');
    }

    return 'enc:v1:' . base64_encode($iv . $tag . $ciphertext);
}

function paypalDecryptSecret($encrypted) {
    if ($encrypted === '') {
        return '';
    }
    if (!str_starts_with($encrypted, 'enc:v1:')) {
        throw new RuntimeException('The stored PayPal secret is not encrypted. Save it again in the admin panel.');
    }

    $payload = base64_decode(substr($encrypted, 7), true);
    if ($payload === false || strlen($payload) < 29) {
        throw new RuntimeException('The stored PayPal secret is invalid.');
    }

    $key = paypalEncryptionKey();
    $secret = openssl_decrypt(
        substr($payload, 28),
        'aes-256-gcm',
        $key,
        OPENSSL_RAW_DATA,
        substr($payload, 0, 12),
        substr($payload, 12, 16)
    );
    if ($secret === false) {
        throw new RuntimeException('Could not decrypt PayPal credentials. Check APP_KEY in .env.');
    }
    return $secret;
}

function paypalCsrfToken() {
    if (empty($_SESSION['paypal_csrf'])) {
        $_SESSION['paypal_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['paypal_csrf'];
}

function paypalVerifyCsrfToken($token) {
    return isset($_SESSION['paypal_csrf'])
        && is_string($token)
        && hash_equals($_SESSION['paypal_csrf'], $token);
}

function paypalApiRequest($method, $path, $payload = null, $requestId = null) {
    $clientId = paypalSetting('paypal_client_id');
    $secret = paypalDecryptSecret(paypalSetting('paypal_client_secret'));
    if ($clientId === '' || $secret === '') {
        throw new RuntimeException('PayPal is not configured. Contact the game administrator.');
    }
    if (!function_exists('curl_init')) {
        throw new RuntimeException('PayPal payments require the PHP cURL extension.');
    }

    $mode = paypalSetting('paypal_mode', 'sandbox');
    $baseUrl = $mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
    $curl = curl_init($baseUrl . '/v1/oauth2/token');
    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
        CURLOPT_USERPWD => $clientId . ':' . $secret,
        CURLOPT_HTTPHEADER => ['Accept: application/json', 'Accept-Language: en_US'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    $tokenResponse = curl_exec($curl);
    $tokenStatus = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    if ($tokenResponse === false) {
        $error = curl_error($curl);
        curl_close($curl);
        error_log('PayPal OAuth request failed: ' . $error);
        throw new RuntimeException('Could not connect to PayPal. Please try again later.');
    }
    curl_close($curl);

    $tokenData = json_decode($tokenResponse, true);
    if ($tokenStatus < 200 || $tokenStatus >= 300 || empty($tokenData['access_token'])) {
        error_log('PayPal OAuth rejected credentials (HTTP ' . $tokenStatus . ').');
        throw new RuntimeException('PayPal credentials were rejected. Check the admin settings.');
    }

    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $tokenData['access_token'],
    ];
    if ($requestId !== null) {
        $headers[] = 'PayPal-Request-Id: ' . $requestId;
    }

    $curl = curl_init($baseUrl . $path);
    curl_setopt_array($curl, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    if ($payload !== null) {
        $body = json_encode($payload);
        if ($body === false) {
            curl_close($curl);
            throw new RuntimeException('Could not prepare the PayPal request.');
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
    }
    $response = curl_exec($curl);
    $status = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    if ($response === false) {
        $error = curl_error($curl);
        curl_close($curl);
        error_log('PayPal API request failed: ' . $error);
        throw new RuntimeException('Could not connect to PayPal. Please try again later.');
    }
    curl_close($curl);

    $data = json_decode($response, true);
    if ($status < 200 || $status >= 300 || !is_array($data)) {
        $debugId = is_array($data) ? (string)($data['debug_id'] ?? '') : '';
        error_log('PayPal API returned HTTP ' . $status . ($debugId !== '' ? ' (debug ' . $debugId . ')' : '') . '.');
        throw new RuntimeException('PayPal could not complete this request. Please try again or contact support.');
    }
    return $data;
}

function paypalCreateCheckoutOrder($orderId, $pack) {
    $order = paypalApiRequest('POST', '/v2/checkout/orders', [
        'intent' => 'CAPTURE',
        'purchase_units' => [[
            'reference_id' => (string)$orderId,
            'custom_id' => (string)$orderId,
            'description' => $pack['name'],
            'amount' => [
                'currency_code' => 'USD',
                'value' => $pack['price'],
            ],
        ]],
        'application_context' => [
            'brand_name' => APP_NAME,
            'user_action' => 'PAY_NOW',
            'return_url' => BASE_URL . '/paywk.php?paypal=return&order=' . $orderId,
            'cancel_url' => BASE_URL . '/paywk.php?paypal=cancel&order=' . $orderId,
        ],
    ], 'create-' . $orderId);

    $approvalUrl = '';
    foreach (($order['links'] ?? []) as $link) {
        if (($link['rel'] ?? '') === 'approve' && !empty($link['href'])) {
            $approvalUrl = $link['href'];
            break;
        }
    }
    $approvalHost = parse_url($approvalUrl, PHP_URL_HOST);
    if (
        empty($order['id'])
        || parse_url($approvalUrl, PHP_URL_SCHEME) !== 'https'
        || !in_array($approvalHost, ['www.paypal.com', 'www.sandbox.paypal.com'], true)
    ) {
        throw new RuntimeException('PayPal did not provide an approval link.');
    }
    return [$order['id'], $approvalUrl];
}

function paypalCaptureAndCredit($orderId, $userId, $paypalOrderId) {
    global $BaseConnect;

    $stmt = $BaseConnect->prepare(
        "SELECT * FROM `paypal_orders` WHERE `id` = ? AND `user_id` = ? AND `paypal_order_id` = ? LIMIT 1"
    );
    $stmt->execute([$orderId, $userId, $paypalOrderId]);
    $order = $stmt->fetch();
    if (!$order) {
        throw new RuntimeException('This PayPal order could not be found for your account.');
    }
    if ($order['status'] === 'captured') {
        return ['rubies' => (int)$order['rubies'], 'vip_days' => (int)$order['vip_days']];
    }
    if ($order['status'] !== 'pending') {
        throw new RuntimeException('This PayPal order is no longer available.');
    }

    $capture = paypalApiRequest(
        'POST',
        '/v2/checkout/orders/' . rawurlencode($paypalOrderId) . '/capture',
        new stdClass(),
        'capture-' . $orderId
    );
    $purchaseUnit = $capture['purchase_units'][0] ?? [];
    $amount = $purchaseUnit['payments']['captures'][0]['amount'] ?? [];
    if (
        ($capture['status'] ?? '') !== 'COMPLETED'
        || (string)($purchaseUnit['custom_id'] ?? '') !== (string)$orderId
        || (string)($purchaseUnit['reference_id'] ?? '') !== (string)$orderId
        || ($amount['currency_code'] ?? '') !== $order['currency']
        || ($amount['value'] ?? '') !== number_format((float)$order['amount'], 2, '.', '')
    ) {
        error_log('PayPal capture did not match local order ' . (int)$orderId . '.');
        throw new RuntimeException('The PayPal payment could not be verified. Contact support before trying again.');
    }

    $captureId = (string)($purchaseUnit['payments']['captures'][0]['id'] ?? '');
    if ($captureId === '') {
        throw new RuntimeException('PayPal did not return a capture reference.');
    }

    $BaseConnect->beginTransaction();
    try {
        $lock = $BaseConnect->prepare("SELECT `status`, `rubies`, `vip_days` FROM `paypal_orders` WHERE `id` = ? FOR UPDATE");
        $lock->execute([$orderId]);
        $lockedOrder = $lock->fetch();
        if (!$lockedOrder) {
            throw new RuntimeException('The PayPal order record is missing.');
        }

        if ($lockedOrder['status'] === 'captured') {
            $BaseConnect->commit();
            return ['rubies' => (int)$lockedOrder['rubies'], 'vip_days' => (int)$lockedOrder['vip_days']];
        }
        if ($lockedOrder['status'] !== 'pending') {
            throw new RuntimeException('This PayPal order is no longer available.');
        }

        $userLock = $BaseConnect->prepare("SELECT `premium_time` FROM `users` WHERE `id` = ? FOR UPDATE");
        $userLock->execute([$userId]);
        $account = $userLock->fetch();
        if (!$account) {
            throw new RuntimeException('Could not credit Rubies to this account.');
        }

        $credit = $BaseConnect->prepare("UPDATE `users` SET `gold` = `gold` + ? WHERE `id` = ? LIMIT 1");
        $credit->execute([(int)$lockedOrder['rubies'], $userId]);
        if ($credit->rowCount() !== 1) {
            throw new RuntimeException('Could not credit Rubies to this account.');
        }
        if ((int)$lockedOrder['vip_days'] > 0) {
            $vipUntil = max(time(), (int)$account['premium_time']) + ((int)$lockedOrder['vip_days'] * 86400);
            $vip = $BaseConnect->prepare("UPDATE `users` SET `premium` = 2, `premium_time` = ? WHERE `id` = ? LIMIT 1");
            $vip->execute([$vipUntil, $userId]);
        }

        $finish = $BaseConnect->prepare(
            "UPDATE `paypal_orders` SET `status` = 'captured', `paypal_capture_id` = ?, `captured_at` = ? WHERE `id` = ? AND `status` = 'pending'"
        );
        $finish->execute([$captureId, time(), $orderId]);
        if ($finish->rowCount() !== 1) {
            throw new RuntimeException('Could not finalize the PayPal order.');
        }
        $BaseConnect->commit();
    } catch (Throwable $e) {
        if ($BaseConnect->inTransaction()) {
            $BaseConnect->rollBack();
        }
        throw $e;
    }

    return ['rubies' => (int)$lockedOrder['rubies'], 'vip_days' => (int)$lockedOrder['vip_days']];
}

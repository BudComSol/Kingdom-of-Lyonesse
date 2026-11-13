<?php
$location = true;
require_once __DIR__ . "/core/func.php";
require_once __DIR__ . "/core/paypal.php";

$packs = paypalLoadPacks();
$paypalAction = (string)($_GET['paypal'] ?? '');

if ($paypalAction === 'return') {
    try {
        $orderId = number($_GET['order'] ?? 0);
        $paypalOrderId = trim((string)($_GET['token'] ?? ''));
        if ($orderId < 1 || $paypalOrderId === '') {
            throw new RuntimeException('PayPal did not return a valid order reference.');
        }
        $reward = paypalCaptureAndCredit($orderId, $uid, $paypalOrderId);
        $message = 'Payment complete! Received +' . $reward['rubies'] . ' Rubies';
        if ($reward['vip_days'] > 0) {
            $message .= ' and ' . $reward['vip_days'] . ' VIP day(s)';
        }
        msg($message . '.');
    } catch (RuntimeException $e) {
        err($e->getMessage());
        error_log('PayPal checkout return failed: ' . $e->getMessage());
    }
    header('Location: /paywk.php');
    exit();
}

if ($paypalAction === 'cancel') {
    qry(
        "UPDATE `paypal_orders` SET `status` = 'cancelled' WHERE `id` = ? AND `user_id` = ? AND `status` = 'pending'",
        [number($_GET['order'] ?? 0), $uid]
    );
    msg('PayPal checkout was cancelled. No payment was taken.');
    header('Location: /paywk.php');
    exit();
}

if (isset($_POST['buy_pack'])) {
    if (!paypalVerifyCsrfToken($_POST['csrf_token'] ?? null)) {
        err('Your session expired. Reload the page and try again.');
        header('Location: /paywk.php');
        exit();
    }

    $packId = filter_var($_POST['buy_pack'], FILTER_VALIDATE_INT);
    if ($packId === false || $packId < 1 || $packId > count($packs)) {
        err('Select a valid Ruby pack.');
        header('Location: /paywk.php');
        exit();
    }

    $pack = $packs[$packId - 1];
    qry(
        "INSERT INTO `paypal_orders` (`user_id`, `package_id`, `rubies`, `vip_days`, `amount`, `currency`, `status`, `created_at`) VALUES (?, ?, ?, ?, ?, 'USD', 'pending', ?)",
        [$uid, $packId, (int)$pack['rubies'], (int)$pack['vip_days'], $pack['price'], time()]
    );
    $orderId = (int)$BaseConnect->lastInsertId();

    try {
        [$paypalOrderId, $approvalUrl] = paypalCreateCheckoutOrder($orderId, $pack);
        qry(
            "UPDATE `paypal_orders` SET `paypal_order_id` = ? WHERE `id` = ? AND `status` = 'pending'",
            [$paypalOrderId, $orderId]
        );
        header('Location: ' . $approvalUrl);
        exit();
    } catch (RuntimeException $e) {
        qry("UPDATE `paypal_orders` SET `status` = 'failed' WHERE `id` = ? AND `status` = 'pending'", [$orderId]);
        err($e->getMessage());
        error_log('PayPal order creation failed: ' . $e->getMessage());
        header('Location: /paywk.php');
        exit();
    }
}

// Action: Redeem Promo Code
if (isset($_POST['promo_code'])) {
    if (!paypalVerifyCsrfToken($_POST['csrf_token'] ?? null)) {
        err('Your session expired. Reload the page and try again.');
        header('Location: /paywk.php');
        exit();
    }
    $code = strtoupper(trim(checking($_POST['promo_code'] ?? '')));
    if ($code === 'FREE100' || $code === 'LYONESSE' || $code === 'VIP2026') {
        qry("UPDATE `users` SET `gold` = `gold` + 100 WHERE `id` = ? LIMIT 1", array($uid));
        msg("Promo code redeemed successfully! Received +100 Rubies!");
    } else {
        err("Invalid or expired promo code!");
    }
    header('Location: /paywk.php');
    exit();
}

head("Royal Treasury - Buy Rubies", $uid);
?>

<div class="tophead cntr">
    <div class="title cntr"><span class="color">Royal Treasury & Ruby Vault</span></div>
    <div class="hrs"></div>
    <small><font color="#a4b0be">Rubies allow you to acquire legendary gear, socket powerful runes, and activate VIP perks.</font></small>
    <br><br>
    Your Current Balance: <img src="/images/ruby.png" width="17px" height="17px" alt="Ruby"> <span class="yellow"><b><?php echo (int)($user['gold'] ?? 0); ?> Rubies</b></span>
</div>

<div class="tophead2 cntr">
    <table style="width: 100%; border-collapse: separate; border-spacing: 0 10px;">
        <?php foreach ($packs as $index => $pack) { ?>
            <tr>
                <td style="width: 15%; text-align: center; vertical-align: middle;">
                    <img src="/images/ruby.png" width="28" height="28" alt="Rubies">
                </td>
                <td style="width: 55%; text-align: left; vertical-align: middle;">
                    <span class="yellow"><b><?php echo htmlspecialchars($pack['name']); ?></b></span><br>
                    <small>
                        <span class="lime">+<?php echo $pack['rubies']; ?> Rubies</span>
                        <?php if ((int)$pack['vip_days'] > 0) { ?>
                            <span class="orange">(+<?php echo (int)$pack['vip_days']; ?>d VIP)</span>
                        <?php } ?>
                    </small>
                </td>
                <td style="width: 30%; text-align: right; vertical-align: middle;">
                    <form action="/paywk.php" method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(paypalCsrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
                        <button class="button" style="padding: 5px 12px; font-size: 0.85rem;" type="submit" name="buy_pack" value="<?php echo $index + 1; ?>">Pay $<?php echo htmlspecialchars($pack['price'], ENT_QUOTES, 'UTF-8'); ?></button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>

<div class="tophead cntr">
    <div class="title cntr"><span class="color">Redeem Promo Code</span></div>
    <div class="hrs"></div>
    <form action="/paywk.php" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(paypalCsrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
        <input type="text" name="promo_code" placeholder="Enter promo code (e.g. LYONESSE)" required style="width: 70%; max-width: 250px; text-transform: uppercase;">
        <br><br>
        <input type="submit" value="Redeem Code">
    </form>
</div>

<a href="/shop.php" class="tophead cntr">← Back to Town / Shops</a>

<?php
foot($uid);
?>

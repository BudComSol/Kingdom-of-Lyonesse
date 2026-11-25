<?php
$location = true;
require_once __DIR__ . '/core/func.php';
require_once __DIR__ . '/core/bordello.php';

if (empty($_SESSION['bordello_csrf'])) {
    $_SESSION['bordello_csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $action = $_POST['action'] ?? '';
    $secretId = $_POST['secret'] ?? '';
    try {
        if (!is_string($token) || !hash_equals($_SESSION['bordello_csrf'], $token)) {
            throw new RuntimeException('Your session has expired. Reload the page and try again.');
        }
        if ($action === 'company' && is_string($secretId) && isset(bordelloSecrets()[$secretId])) {
            bordelloBuyCompany($BaseConnect, $uid, time());
            msg('You enjoy some welcome company with ' . bordelloSecrets()[$secretId]['companion'] . ' and leave with renewed morale.');
        } elseif ($action === 'secret' && is_string($secretId)) {
            bordelloBuySecret($BaseConnect, $uid, $secretId, time());
            msg('Your companion shares her secret.');
        } else {
            throw new RuntimeException('That choice is not available.');
        }
    } catch (Throwable $e) {
        if ($e instanceof RuntimeException && !($e instanceof PDOException)) {
            err(htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
        } else {
            error_log('Bordello purchase failed: ' . $e->getMessage());
            err('Your purchase could not be completed. Please try again.');
        }
    }
    header('Location: /bordello.php');
    exit();
}

$stmt = $BaseConnect->prepare('SELECT `secret_id` FROM `bordello_secrets` WHERE `user_id` = ?');
$stmt->execute([$uid]);
$unlocked = array_fill_keys($stmt->fetchAll(PDO::FETCH_COLUMN), true);
$moraleUntil = (int)($user['morale_until'] ?? 0);
$moraleRemaining = max(0, $moraleUntil - time());
$csrf = htmlspecialchars($_SESSION['bordello_csrf'], ENT_QUOTES, 'UTF-8');
head('The Bordello', $uid);
?>
<img style="top: 0; border-radius: 8px;" width="100%" src="/images/bordello.svg" alt="The Bordello, a warmly lit house in the town">
<div class="tophead">
    <span class="lime">Madam Elspeth</span>
    <p>Welcome, traveler. Leave your worries at the door; good company and a little discretion await within.</p>
    <p>Our companions have a gift for listening, too. They may share a secret for the right price.</p>
</div>
<div class="tophead2 cntr">
    <h2>Seek Company</h2>
    <p>Choose a companion below and enjoy a quiet interlude. Private moments are yours to imagine.</p>
    <p>Cost: <?php echo BORDELLO_COMPANY_COST; ?> Coins. Gain 5% Power for one hour; available again when the bonus expires.</p>
    <p>The bonus applies when a forest or Tavern fight starts, an Arena challenge is sent, or an Arena challenge is accepted. Saved battle stats do not change when it expires.</p>
    <?php if ($moraleRemaining > 0) { ?>
        <p class="lime">Morale active: <?php echo (int)ceil($moraleRemaining / 60); ?> minutes remaining.</p>
    <?php } ?>
</div>
<div class="tophead2">
    <h2>Whispers and Secrets</h2>
    <p>Each secret costs <?php echo BORDELLO_SECRET_COST; ?> Coins and can be reread whenever you return.</p>
</div>
<?php foreach (bordelloSecrets() as $id => $secret) { ?>
    <div class="tophead">
        <span class="lime"><?php echo htmlspecialchars($secret['companion'], ENT_QUOTES, 'UTF-8'); ?></span><br>
        <p><i>&ldquo;<?php echo htmlspecialchars($secret['greeting'], ENT_QUOTES, 'UTF-8'); ?>&rdquo;</i></p>
        <?php if ($moraleRemaining === 0) { ?>
            <form method="post" action="/bordello.php">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
                <input type="hidden" name="action" value="company">
                <input type="hidden" name="secret" value="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="button">Seek <?php echo htmlspecialchars($secret['companion'], ENT_QUOTES, 'UTF-8'); ?>'s Company (<?php echo BORDELLO_COMPANY_COST; ?> Coins)</button>
            </form>
        <?php } ?>
        <b><?php echo htmlspecialchars($secret['title'], ENT_QUOTES, 'UTF-8'); ?></b>
        <?php if (isset($unlocked[$id])) { ?>
            <p><?php echo htmlspecialchars($secret['story'], ENT_QUOTES, 'UTF-8'); ?></p>
        <?php } else { ?>
            <form method="post" action="/bordello.php">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
                <input type="hidden" name="action" value="secret">
                <input type="hidden" name="secret" value="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="button">Hear the Secret (<?php echo BORDELLO_SECRET_COST; ?> Coins)</button>
            </form>
        <?php } ?>
    </div>
<?php } ?>
<div class="tophead2 cntr"><a href="/index.php">Return to the Town Square</a></div>
<?php foot($uid); ?>

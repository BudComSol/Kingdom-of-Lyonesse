<?php
$location = true;
require_once __DIR__ . '/core/func.php';

if (empty($_SESSION['faction_csrf'])) {
    $_SESSION['faction_csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    $faction = factionId($_POST['faction'] ?? null);
    $currentFaction = factionId($user['faction'] ?? 0);
    $factionChangeCost = 100;
    if (!is_string($token) || !hash_equals($_SESSION['faction_csrf'], $token)) {
        err('Your session has expired. Please try again.');
    } elseif (!$faction) {
        err('Please choose a valid faction.');
    } elseif ($faction === $currentFaction) {
        err('You are already in that faction.');
    } elseif (fch('SELECT `id` FROM `lec_fight` WHERE `id_user` = ? LIMIT 1', [$uid])
        || fch('SELECT `id` FROM `tavern_fight` WHERE `id_user` = ? LIMIT 1', [$uid])) {
        err('Finish your current forest or tavern fight before changing factions.');
    } elseif ((int)($user['gold'] ?? 0) < $factionChangeCost) {
        err('Changing factions costs 100 Rubies.');
    } else {
        $change = qry(
            'UPDATE `users` SET `faction` = ?, `gold` = `gold` - ? WHERE `id` = ? AND `gold` >= ? LIMIT 1',
            [$faction, $factionChangeCost, $uid, $factionChangeCost]
        );
        if ($change->rowCount() === 1) {
            msg('Faction updated for 100 Rubies!');
        } else {
            err('Changing factions costs 100 Rubies.');
        }
    }
    header('Location: /factions.php');
    exit();
}

head('Choose Faction', $uid);
$selectedFaction = factionId($user['faction'] ?? 0);
?>
<div class="tophead cntr">
    <p>Current faction: <b style="color: #FFFF33;"><?php echo factionName($user); ?></b></p>
    <p>Changing faction costs <b>100 Rubies</b>. Bonuses apply to your combat stats, including equipped items, before equipment set and morale bonuses. Finish any ongoing forest or tavern fight before changing.</p>
    <p>Arena challenges already sent keep the combat stats recorded when they were sent.</p>
    <form method="post" action="/factions.php">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['faction_csrf'], ENT_QUOTES, 'UTF-8'); ?>">
        <?php require __DIR__ . '/templates/faction_choices.php'; ?>
        <button type="submit" class="button">Choose Faction</button>
    </form>
</div>
<a href="/profile.php" class="tophead cntr">← Back to Profile</a>
<?php foot($uid); ?>

<?php

const BORDELLO_COMPANY_COST = 100;
const BORDELLO_SECRET_COST = 50;
const BORDELLO_MORALE_SECONDS = 3600;

function bordelloSecrets() {
    return [
        'gate' => [
            'companion' => 'Mara',
            'title' => 'The Gatekeeper\'s Debt',
            'greeting' => 'A friendly ear hears more than a watchman ever will.',
            'story' => 'A merchant boasting over wine confessed that the old gatekeeper once paid a smuggler to bring medicine through the forest. The debt was repaid years ago, but the gatekeeper still leaves a lantern burning for travelers caught outside after dark.',
        ],
        'bell' => [
            'companion' => 'Isolde',
            'title' => 'The Silent Bell',
            'greeting' => 'Sit with me a while. I know a tale worth keeping.',
            'story' => 'A weary mason revealed that the cracked bell in the abandoned chapel was never broken by raiders. The villagers muffled it themselves to shelter refugees from a passing army. On quiet evenings, someone still leaves flowers beneath it.',
        ],
        'ribbon' => [
            'companion' => 'Rosamund',
            'title' => 'The Blue Ribbon',
            'greeting' => 'Some secrets are dangerous. Others are simply beautiful.',
            'story' => 'A knight let slip that the blue ribbon tied to his sword belonged to a rival who saved his life. They meet at the tournament each year, trading insults for the crowd and sharing a quiet toast afterward.',
        ],
    ];
}

function bordelloBuyCompany(PDO $db, int $userId, int $now) {
    $stmt = $db->prepare('UPDATE `users` SET `silver` = `silver` - ?, `morale_until` = ? WHERE `id` = ? AND `silver` >= ? AND `morale_until` <= ?');
    $stmt->execute([BORDELLO_COMPANY_COST, $now + BORDELLO_MORALE_SECONDS, $userId, BORDELLO_COMPANY_COST, $now]);
    if ($stmt->rowCount() !== 1) {
        throw new RuntimeException('You need 100 Coins and no active morale bonus to seek company.');
    }
}

function bordelloBuySecret(PDO $db, int $userId, string $secretId, int $now) {
    if (!isset(bordelloSecrets()[$secretId])) {
        throw new RuntimeException('That secret is not available.');
    }
    $db->beginTransaction();
    try {
        $charge = $db->prepare('UPDATE `users` SET `silver` = `silver` - ? WHERE `id` = ? AND `silver` >= ?');
        $charge->execute([BORDELLO_SECRET_COST, $userId, BORDELLO_SECRET_COST]);
        if ($charge->rowCount() !== 1) {
            throw new RuntimeException('You need 50 Coins to hear this secret.');
        }
        $owned = $db->prepare('SELECT `secret_id` FROM `bordello_secrets` WHERE `user_id` = ? AND `secret_id` = ?');
        $owned->execute([$userId, $secretId]);
        if ($owned->fetch()) {
            throw new RuntimeException('You already know this secret.');
        }
        $insert = $db->prepare('INSERT INTO `bordello_secrets` (`user_id`, `secret_id`, `unlocked_at`) VALUES (?, ?, ?)');
        $insert->execute([$userId, $secretId, $now]);
        $db->commit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}

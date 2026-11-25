<?php

function explorationNodes() {
    return [
        1 => ['name' => 'The Old Gate', 'monster' => 1, 'x' => 100, 'y' => 310,
            'story' => 'A weathered gate marks the old road into the forest. Beyond it, hoofprints lead into the mist.'],
        2 => ['name' => 'Eagle Watch', 'monster' => 2, 'x' => 230, 'y' => 250,
            'story' => 'A ruined watchtower rises above the trees. An abandoned signal fire still overlooks the kingdom.'],
        3 => ['name' => 'Whispering Stones', 'monster' => 3, 'x' => 360, 'y' => 310,
            'story' => 'Moss-covered stones bear names worn away by centuries, and the wind carries voices between them.'],
        4 => ['name' => 'Moonlit Crossing', 'monster' => 4, 'x' => 490, 'y' => 205,
            'story' => 'An ancient bridge spans a silver stream. Claw marks score the stones on its far side.'],
        5 => ['name' => 'The Forgotten Shrine', 'monster' => 5, 'x' => 625, 'y' => 115,
            'story' => 'At the heart of the outskirts stands a forgotten shrine, reclaimed by roots and guarded by a fierce beast.'],
    ];
}

function explorationProgress($uid) {
    $rows = qry("SELECT `node_id` FROM `exploration_progress` WHERE `user_id` = ?", [$uid])->fetchAll(PDO::FETCH_COLUMN);
    return array_fill_keys(array_map('intval', $rows), true);
}

function explorationAvailable($nodeId, $progress) {
    $nodes = explorationNodes();
    return isset($nodes[$nodeId]) && !isset($progress[$nodeId])
        && ($nodeId === 1 || isset($progress[$nodeId - 1]));
}

function explorationRecordVictory($uid, $fight) {
    $nodeId = (int)($fight['exploration_node'] ?? 0);
    if ($nodeId === 0) {
        return;
    }
    $nodes = explorationNodes();
    if (!isset($nodes[$nodeId]) || $nodes[$nodeId]['monster'] !== (int)$fight['id_bot']
        || !explorationAvailable($nodeId, explorationProgress($uid))) {
        throw new RuntimeException('Invalid exploration victory');
    }
    qry("INSERT INTO `exploration_progress` (`user_id`, `node_id`, `discovered_at`) VALUES (?, ?, ?)",
        [$uid, $nodeId, time()]);
}

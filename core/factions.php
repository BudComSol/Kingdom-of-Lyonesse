<?php

function factions() {
    return [
        1 => ['name' => 'Barbarian', 'power' => 10, 'block' => 0, 'health' => 5],
        2 => ['name' => 'Wizard', 'power' => 15, 'block' => 0, 'health' => 0],
        3 => ['name' => 'Warlock', 'power' => 10, 'block' => 5, 'health' => 0],
        4 => ['name' => 'Sorcerer', 'power' => 5, 'block' => 0, 'health' => 10],
        5 => ['name' => 'Rogue', 'power' => 5, 'block' => 5, 'health' => 5],
        6 => ['name' => 'Ranger', 'power' => 5, 'block' => 10, 'health' => 0],
        7 => ['name' => 'Paladin', 'power' => 0, 'block' => 5, 'health' => 10],
    ];
}

function factionId($value) {
    if (!is_int($value) && (!is_string($value) || !preg_match('/^[1-7]$/', $value))) {
        return 0;
    }
    $id = (int)$value;
    return isset(factions()[$id]) ? $id : 0;
}

function factionName($player) {
    $id = factionId($player['faction'] ?? 0);
    return $id ? factions()[$id]['name'] : 'Unaligned';
}

function factionImageUrl($player) {
    $id = factionId($player['faction'] ?? 0);
    return $id ? '/images/factions/' . $id . '.png' : '/images/arena.svg';
}

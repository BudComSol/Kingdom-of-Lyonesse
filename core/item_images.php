<?php

function itemImageUrl(array $item): string {
    $set = (int)($item['set'] ?? 0);
    $type = (int)($item['type'] ?? 0);
    $imageKey = $item['image_key'] ?? null;

    if (
        is_string($imageKey)
        && preg_match('/\A[a-f0-9]{32}\z/', $imageKey) === 1
        && $type >= 1
        && $type <= 8
        && is_file(__DIR__ . '/../images/items/custom/' . $imageKey . '/' . $type . '.png')
    ) {
        return '/images/items/custom/' . $imageKey . '/' . $type . '.png';
    }

    return '/images/items/' . $set . '/' . $type . '.png';
}

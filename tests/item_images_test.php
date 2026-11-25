<?php

require_once __DIR__ . '/../core/item_images.php';

function check($condition, $message) {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$customRoot = __DIR__ . '/../images/items/custom';
$customRootExisted = is_dir($customRoot);
$imageKey = bin2hex(random_bytes(16));
$imageDirectory = $customRoot . '/' . $imageKey;
if (!mkdir($imageDirectory, 0755, true)) {
    throw new RuntimeException('Could not create temporary equipment image directory');
}

try {
    file_put_contents($imageDirectory . '/5.png', 'test');
    check(
        itemImageUrl(['set' => 2, 'type' => 5, 'image_key' => $imageKey])
            === '/images/items/custom/' . $imageKey . '/5.png',
        'Custom equipment image was not selected'
    );
    check(
        itemImageUrl(['set' => 2, 'type' => 4, 'image_key' => $imageKey]) === '/images/items/2/4.png',
        'Missing custom slot did not fall back to the tier image'
    );
    check(
        itemImageUrl(['set' => 2, 'type' => 5, 'image_key' => '../../invalid']) === '/images/items/2/5.png',
        'Invalid image key did not fall back to the tier image'
    );
} finally {
    unlink($imageDirectory . '/5.png');
    rmdir($imageDirectory);
    if (!$customRootExisted) {
        rmdir($customRoot);
    }
}

echo "Equipment image tests passed\n";

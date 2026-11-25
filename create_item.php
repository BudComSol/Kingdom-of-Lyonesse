<?php
$location = true;
require_once __DIR__ . "/core/func.php";

if (($user['adm'] ?? 0) != 3) {
    header('Location: /');
    exit();
}

if (isset($_REQUEST['ok'])) {
    $name   = checking($_POST['name'] ?? '');
    $case   = abs(intval($_POST['case'] ?? 0));
    $param  = abs(intval($_POST['param'] ?? 0));
    $set    = abs(intval($_POST['set'] ?? 1));
    $level  = abs(intval($_POST['level'] ?? 1));
    $smitch = abs(intval($_POST['smitch'] ?? 10));
    $res    = abs(intval($_POST['res'] ?? 0));

    if (!empty($name)) {
        $type = array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7', '8' => '8');
        $n = array(
            '1' => $name . ' Helmet',
            '2' => $name . ' Shoulders',
            '3' => $name . ' Armor',
            '4' => $name . ' Gloves',
            '5' => $name . ' Sword',
            '6' => $name . ' Shield',
            '7' => $name . ' Pants',
            '8' => $name . ' Boots'
        );

        $imageFiles = [];
        for ($slot = 1; $slot <= 8; $slot++) {
            $file = $_FILES['image_' . $slot] ?? null;
            if ($file === null) {
                continue;
            }

            if (!is_array($file)) {
                err('Equipment images must be PNG files no larger than 2 MB.');
                header('Location: /create_item.php');
                exit();
            }
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if (
                ($file['error'] ?? null) !== UPLOAD_ERR_OK
                || !isset($file['tmp_name'])
                || !is_uploaded_file($file['tmp_name'])
                || filesize($file['tmp_name']) > 2 * 1024 * 1024
            ) {
                err('Equipment images must be PNG files no larger than 2 MB.');
                header('Location: /create_item.php');
                exit();
            }

            $imageInfo = @getimagesize($file['tmp_name']);
            if (
                $imageInfo === false
                || ($imageInfo['mime'] ?? '') !== 'image/png'
                || $imageInfo[0] > 1024
                || $imageInfo[1] > 1024
            ) {
                err('Equipment images must be PNG files no larger than 1024 × 1024 pixels.');
                header('Location: /create_item.php');
                exit();
            }

            $imageFiles[$slot] = $file['tmp_name'];
        }

        $imageKey = $imageFiles ? bin2hex(random_bytes(16)) : null;
        $imageDirectory = $imageKey === null
            ? null
            : __DIR__ . '/images/items/custom/' . $imageKey;
        $savedImages = [];

        if ($imageDirectory !== null && !is_dir($imageDirectory) && !mkdir($imageDirectory, 0755, true) && !is_dir($imageDirectory)) {
            err('Could not create the equipment image directory.');
            header('Location: /create_item.php');
            exit();
        }

        foreach ($imageFiles as $slot => $temporaryPath) {
            $destination = $imageDirectory . '/' . $slot . '.png';
            if (!move_uploaded_file($temporaryPath, $destination)) {
                foreach ($savedImages as $savedImage) {
                    unlink($savedImage);
                }
                if ($imageDirectory !== null) {
                    rmdir($imageDirectory);
                }
                err('Could not save an equipment image.');
                header('Location: /create_item.php');
                exit();
            }
            $savedImages[] = $destination;
        }

        try {
            $BaseConnect->beginTransaction();
            for ($i = 1; $i <= 8; $i++) {
                qry(
                    "INSERT INTO `items` SET `name` = ?, `type` = ?, `gold` = ?, `power` = ?, `health` = ?, `block` = ?, `set` = ?, `level` = ?, `max_smitch` = ?, `res` = ?, `image_key` = ?",
                    array($n[$i], $type[$i], $case, $param, $param, $param, $set, $level, $smitch, $res, $imageKey)
                );
            }
            $BaseConnect->commit();
        } catch (Throwable $exception) {
            if ($BaseConnect->inTransaction()) {
                $BaseConnect->rollBack();
            }
            foreach ($savedImages as $savedImage) {
                unlink($savedImage);
            }
            if ($imageDirectory !== null) {
                rmdir($imageDirectory);
            }
            throw $exception;
        }

        msg("Equipment set created successfully!");
    }

    header('Location: /create_item.php');
    exit();
}

head("Admin - Add Equipment Set", $uid);
?>

<div class="tophead cntr">
    <form action="" method="post" enctype="multipart/form-data">
        Set Base Name:<br /><input type="text" name="name" maxlength="100" required placeholder="e.g. Dragon Slayer" /><br /><br />
        Ruby Cost:<br><input type="number" name="case" value="0" /><br /><br />
        Resource Cost (Iron & Cloth):<br><input type="number" name="res" value="10" /><br /><br />
        Attributes (Power/Def/HP):<br><input type="number" name="param" value="30" /><br /><br />
        Set Tier (1-5):<br><input type="number" name="set" value="1" min="1" max="5" /><br /><br />
        Required Level:<br><input type="number" name="level" value="1" min="1" /><br /><br />
        Max Forge Level:<br><input type="number" name="smitch" value="10" /><br /><br />
        Optional PNG Images (max 2 MB and 1024 × 1024 each):<br>
        Helmet: <input type="file" name="image_1" accept="image/png"><br>
        Shoulders: <input type="file" name="image_2" accept="image/png"><br>
        Armor: <input type="file" name="image_3" accept="image/png"><br>
        Gloves: <input type="file" name="image_4" accept="image/png"><br>
        Sword: <input type="file" name="image_5" accept="image/png"><br>
        Shield: <input type="file" name="image_6" accept="image/png"><br>
        Pants: <input type="file" name="image_7" accept="image/png"><br>
        Boots: <input type="file" name="image_8" accept="image/png"><br><br>
        <input type="submit" name="ok" value="Create Set" />
    </form>
</div>

<a href="/update.php" class="tophead cntr">← Back to Admin Panel</a>

<?php
foot($uid);
?>
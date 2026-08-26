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

        for ($i = 1; $i <= 8; $i++) {
            qry("INSERT INTO `items` SET `name` = ?, `type` = ?, `gold` = ?, `power` = ?, `health` = ?, `block` = ?, `set` = ?, `level` = ?, `max_smitch` = ?, `res` = ?",
                array($n[$i], $type[$i], $case, $param, $param, $param, $set, $level, $smitch, $res));
        }

        msg("Equipment set created successfully!");
    }

    header('Location: /create_item.php');
    exit();
}

head("Admin - Add Equipment Set", $uid);
?>

<div class="tophead cntr">
    <form action="" method="post">
        Set Base Name:<br /><input type="text" name="name" maxlength="100" required placeholder="e.g. Dragon Slayer" /><br /><br />
        Ruby Cost:<br><input type="number" name="case" value="0" /><br /><br />
        Resource Cost (Iron & Cloth):<br><input type="number" name="res" value="10" /><br /><br />
        Attributes (Power/Def/HP):<br><input type="number" name="param" value="30" /><br /><br />
        Set Tier (1-5):<br><input type="number" name="set" value="1" min="1" max="5" /><br /><br />
        Required Level:<br><input type="number" name="level" value="1" min="1" /><br /><br />
        Max Forge Level:<br><input type="number" name="smitch" value="10" /><br /><br />
        <input type="submit" name="ok" value="Create Set" />
    </form>
</div>

<a href="/update.php" class="tophead cntr">← Back to Admin Panel</a>

<?php
foot($uid);
?>
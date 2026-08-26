<?php
$name = array(
    '',
    'Chapter <b>I</b>: A New Home',
    'Chapter <b>II</b>: In Search of Truth',
    'Chapter <b>III</b>: On the Wolf\'s Trail',
    'Chapter <b>IV</b>: An Unexpected Obstacle',
    'Chapter <b>V</b>: The Holy Knight'
);

$glava = (int)($tavern['glava'] ?? 1);
?>
<div class="tophead2 cntr">
    <span class="orange"><?php echo $name[$glava] ?? 'Tavern Missions'; ?></span>
</div>

<?php
if ($glava == 1) {
    require_once __DIR__ . "/quest1.php";
} elseif ($glava == 2) {
    require_once __DIR__ . "/quest2.php";
} elseif ($glava == 3) {
    require_once __DIR__ . "/quest3.php";
} elseif ($glava == 4) {
    require_once __DIR__ . "/quest4.php";
} elseif ($glava == 5) {
    require_once __DIR__ . "/quest5.php";
} else {
    require_once __DIR__ . "/quest6.php";
}
?>
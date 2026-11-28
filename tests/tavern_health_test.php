<?php
require_once __DIR__ . '/../core/factions.php';

function number($value) { return abs((int)$value); }
function effectiveCombatStats($user) { return ['power' => 100, 'block' => 10, 'health' => 100, 'critical_chance' => 0]; }
function fch($sql, $params = []) {
    if (str_contains($sql, 'FROM `tavern_fight`')) {
        return ['id' => 1, 'hp' => 35, 'power' => 100, 'block' => 10, 'bot_power' => 35, 'bot_block' => 35, 'bot_hp' => 35];
    }
    if (str_contains($sql, 'FROM `tavern`')) return ['level' => 4];
    throw new RuntimeException('Unexpected query');
}
function head($title, $uid) { echo '<header>Tavern</header>'; }
function foot($uid) {}

$uid = 1;
$user = ['login' => 'Knight', 'faction' => 1];
$_GET = [];
$source = file_get_contents(__DIR__ . '/../tavern.php');
$source = str_replace('require_once __DIR__ . "/core/func.php";', '', $source);
$source = str_replace('__DIR__ . "/templates/tavern/tavern" . $tavernLevel . ".php"', var_export(__DIR__ . '/../templates/tavern/tavern4.php', true), $source);
ob_start();
eval('?>' . $source);
$html = ob_get_clean();
if (!str_contains($html, '<header>Tavern</header>') || !str_contains($html, 'width:35%;')) {
    throw new RuntimeException('Tavern combat health bar missing');
}
echo "Tavern health test passed\n";

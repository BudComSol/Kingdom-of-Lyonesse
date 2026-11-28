<?php
$location = true;
require_once __DIR__ . '/core/func.php';
require_once __DIR__ . '/core/exploration.php';

$nodes = explorationNodes();
if (empty($_SESSION['exploration_csrf'])) {
    $_SESSION['exploration_csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ajax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    $message = '';
    $status = 400;
    $token = $_POST['csrf_token'] ?? '';
    $nodeId = filter_var($_POST['node'] ?? null, FILTER_VALIDATE_INT);

    if (!is_string($token) || !hash_equals($_SESSION['exploration_csrf'], $token)) {
        $message = 'Your session has expired. Reload the map and try again.';
    } elseif ($nodeId === false || $nodeId === null || !isset($nodes[$nodeId])) {
        $message = 'That expedition does not exist.';
    } else {
        try {
            $BaseConnect->beginTransaction();
            $lockedUser = fch("SELECT * FROM `users` WHERE `id` = ? FOR UPDATE", [$uid]);
            if (!$lockedUser) {
                throw new RuntimeException('Your account could not be found.');
            }
            if (fch("SELECT `id` FROM `lec_fight` WHERE `id_user` = ? LIMIT 1", [$uid])) {
                throw new RuntimeException('Finish your current forest battle first.');
            }
            if (!explorationAvailable($nodeId, explorationProgress($uid))) {
                throw new RuntimeException('This location is already discovered or not yet available.');
            }
            if ((int)$lockedUser['lec'] < 1) {
                throw new RuntimeException('You need forest energy to start an expedition.');
            }
            $monster = fch("SELECT * FROM `lec_boss` WHERE `id` = ? LIMIT 1", [$nodes[$nodeId]['monster']]);
            if (!$monster) {
                throw new RuntimeException('The expedition encounter is unavailable.');
            }
            if (!fch("SELECT `id` FROM `lec_user` WHERE `id_user` = ? LIMIT 1", [$uid])) {
                qry("INSERT INTO `lec_user` (`id_user`, `level`) VALUES (?, 1)", [$uid]);
            }
            $stats = effectiveCombatStats($lockedUser);
            qry("UPDATE `users` SET `lec` = `lec` - 1 WHERE `id` = ?", [$uid]);
            qry("UPDATE `skills` SET `time` = 0, `reset` = 0 WHERE `id_user` = ?", [$uid]);
            qry("INSERT INTO `lec_fight`
                (`id_user`, `name`, `id_bot`, `exploration_node`, `bot_power`, `bot_block`, `bot_hp`, `power`, `block`, `hp`, `default`, `default2`, `type`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [$uid, $monster['name'], $monster['id'], $nodeId, $monster['param'], $monster['param'],
                 $monster['param'], $stats['power'], $stats['block'], $stats['health'], $monster['param'],
                 $stats['power'], $monster['type']]);
            $BaseConnect->commit();
            $status = 200;
        } catch (Throwable $e) {
            if ($BaseConnect->inTransaction()) {
                $BaseConnect->rollBack();
            }
            if ($e instanceof RuntimeException && !($e instanceof PDOException)) {
                $message = $e->getMessage();
            } else {
                error_log('Expedition start failed: ' . $e->getMessage());
                $message = 'Could not start the expedition. Please try again.';
                $status = 500;
            }
        }
    }

    if ($ajax) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => $status === 200, 'message' => $message, 'redirect' => $status === 200 ? '/lec.php' : null]);
    } else {
        if ($message !== '') {
            err(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
        }
        header('Location: ' . ($status === 200 ? '/lec.php' : '/exploration.php'));
    }
    exit();
}

$progress = explorationProgress($uid);
$activeFight = fch("SELECT `id` FROM `lec_fight` WHERE `id_user` = ? LIMIT 1", [$uid]);
head('Expedition Map', $uid);
?>
<link rel="stylesheet" href="/style/exploration.css">
<div class="tophead2 exploration">
    <h1>Expedition Map</h1>
    <p>Explore the Dark Forest outskirts, one landmark at a time. Win each expedition to clear the fog and uncover the next location.</p>
    <p><b><?php echo count($progress); ?> / <?php echo count($nodes); ?> discovered</b> &middot;
        Forest energy: <b><?php echo (int)$user['lec']; ?> / 100</b> &middot; 1 energy per expedition</p>
    <?php if ($activeFight) { ?>
        <p><a class="button" href="/lec.php">Return to your current battle</a></p>
    <?php } elseif ((int)$user['lec'] < 1) { ?>
        <p><a href="/lec.php">Visit the Dark Forest to refill energy.</a></p>
    <?php } ?>
    <svg class="exploration-map" viewBox="0 0 720 400" role="img" aria-labelledby="map-title map-desc">
        <title id="map-title">Dark Forest outskirts</title>
        <desc id="map-desc">A trail through the forest connects five landmarks. Fog covers undiscovered places. Select a marker or use the location list below.</desc>
        <rect width="720" height="400" fill="#263e30"/>
        <path d="M0 300 Q120 200 215 270 T400 250 T720 70 V400 H0Z" fill="#39543a"/>
        <path d="M0 110 Q150 15 270 125 T510 100 T720 20 V0 H0Z" fill="#344b38"/>
        <defs>
            <g id="tree"><path d="M-5 24 V38 H5 V24" fill="#5b4933"/><path d="M0 -25 L-22 8 H-12 L-27 26 H27 L12 8 H22Z" fill="#172f25" stroke="#71936c" stroke-width="2"/></g>
        </defs>
        <g opacity=".8">
            <use href="#tree" x="45" y="130"/><use href="#tree" x="135" y="90"/><use href="#tree" x="190" y="175"/>
            <use href="#tree" x="300" y="105"/><use href="#tree" x="390" y="155"/><use href="#tree" x="465" y="70"/>
            <use href="#tree" x="560" y="310"/><use href="#tree" x="665" y="255"/><use href="#tree" x="290" y="355"/>
        </g>
        <g fill="#a99c77" stroke="#d3c59a" stroke-width="3">
            <path d="M75 310 V270 H88 V285 H112 V270 H125 V310 H112 V296 H88 V310Z"/>
            <path d="M214 250 V208 L230 194 L246 208 V250 H236 V229 H224 V250Z"/>
            <path d="M340 310 L345 277 L354 272 L358 310 M365 310 L369 270 L378 278 L383 310"/>
            <path d="M465 213 Q490 180 515 213 L515 220 Q490 199 465 220Z"/>
            <path d="M600 115 V90 L625 70 L650 90 V115 H640 V96 H610 V115Z"/>
        </g>
        <path d="M100 310 Q170 300 230 250 T360 310 T490 205 T625 115" fill="none" stroke="#ad9870" stroke-width="10" stroke-dasharray="10 10" opacity=".8"/>
        <path d="M0 370 Q180 330 320 380 T720 345" fill="none" stroke="#648c88" stroke-width="12" opacity=".6"/>
        <?php foreach ($nodes as $id => $node) {
            $discovered = isset($progress[$id]);
            if (!$discovered) { ?>
                <g class="map-fog" transform="translate(<?php echo $node['x']; ?> <?php echo $node['y']; ?>)">
                    <ellipse rx="95" ry="62"/><circle cx="-45" cy="-25" r="42"/><circle cx="25" cy="-32" r="48"/><circle cx="65" cy="0" r="35"/>
                </g>
            <?php }
        } ?>
        <?php foreach ($nodes as $id => $node) {
            $discovered = isset($progress[$id]);
            $available = explorationAvailable($id, $progress);
            if ($discovered || $available) { ?>
                <a href="#location-<?php echo $id; ?>" aria-label="<?php echo htmlspecialchars($node['name'], ENT_QUOTES, 'UTF-8'); ?>">
                    <circle cx="<?php echo $node['x']; ?>" cy="<?php echo $node['y']; ?>" r="19" class="<?php echo $discovered ? 'map-pin discovered' : 'map-pin available'; ?>"/>
                    <text x="<?php echo $node['x']; ?>" y="<?php echo $node['y'] + 6; ?>" text-anchor="middle" class="map-pin-label"><?php echo $discovered ? '&#10003;' : $id; ?></text>
                </a>
            <?php }
        } ?>
    </svg>
    <p class="exploration-legend">Gold marker: available &middot; Green marker: discovered &middot; Clouds: unexplored</p>
    <div id="exploration-error" class="exploration-error" role="alert" hidden></div>
    <div class="exploration-locations">
        <?php foreach ($nodes as $id => $node) {
            $discovered = isset($progress[$id]);
            $available = explorationAvailable($id, $progress); ?>
            <section class="exploration-location" id="location-<?php echo $id; ?>">
                <?php if ($discovered || $available) { ?>
                    <h2><?php echo htmlspecialchars($node['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                    <?php if ($discovered) { ?>
                        <p class="lime">Discovered</p>
                        <p><?php echo htmlspecialchars($node['story'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php } else { ?>
                        <p>Available to explore. Defeat the guardian to reveal this landmark.</p>
                        <form method="post" action="/exploration.php" class="expedition-form">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['exploration_csrf'], ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="node" value="<?php echo $id; ?>">
                            <button class="button button_red" type="submit" <?php echo $activeFight || (int)$user['lec'] < 1 ? 'disabled' : ''; ?>>Begin expedition</button>
                        </form>
                    <?php } ?>
                <?php } else { ?>
                    <h2>Unknown location</h2>
                    <p>Discover the previous landmark to reveal the way.</p>
                <?php } ?>
            </section>
        <?php } ?>
    </div>
    <p class="cntr"><a href="/lec.php">Visit the Dark Forest</a></p>
</div>
<script>
document.querySelectorAll('.expedition-form').forEach(form => {
    form.addEventListener('submit', async event => {
        event.preventDefault();
        const button = form.querySelector('button');
        const error = document.getElementById('exploration-error');
        button.disabled = true;
        error.hidden = true;
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                body: new FormData(form)
            });
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Could not start the expedition.');
            window.location.assign(result.redirect);
        } catch (err) {
            error.textContent = err.message || 'Could not start the expedition. Please try again.';
            error.hidden = false;
            button.disabled = false;
        }
    });
});
</script>
<?php foot($uid); ?>

<?php
$location = true;
require_once __DIR__ . '/core/func.php';
require_once __DIR__ . '/core/wiki.php';

if (empty($_SESSION['wiki_csrf'])) {
    $_SESSION['wiki_csrf'] = bin2hex(random_bytes(32));
}

$title = is_string($_GET['title'] ?? null) ? trim($_GET['title']) : '';
$action = is_string($_GET['action'] ?? null) ? $_GET['action'] : '';
$error = '';
$form = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = [
        'title' => is_string($_POST['title'] ?? null) ? trim($_POST['title']) : '',
        'category' => is_string($_POST['category'] ?? null) ? trim($_POST['category']) : '',
        'content' => is_string($_POST['content'] ?? null) ? $_POST['content'] : '',
        'version' => is_string($_POST['version'] ?? null) && ctype_digit($_POST['version']) && strlen($_POST['version']) <= 9
            ? (int)$_POST['version'] : -1,
    ];
    $title = $form['title'];
    $action = 'edit';
    try {
        $token = $_POST['csrf_token'] ?? '';
        if (!is_string($token) || !hash_equals($_SESSION['wiki_csrf'], $token)) {
            http_response_code(403);
            throw new RuntimeException('Your session has expired. Reload the page and try again.');
        }
        wikiSave($BaseConnect, $uid, $form['title'], $form['category'], $form['content'], $form['version']);
        header('Location: ' . wikiUrl($form['title']));
        exit();
    } catch (RuntimeException $e) {
        if ($e instanceof PDOException) {
            error_log('Wiki save failed: ' . $e->getMessage());
            http_response_code(500);
            $error = 'The page could not be saved. Please try again.';
        } else {
            $error = $e->getMessage();
        }
    }
}

$page = false;
if ($title !== '' && wikiValidTitle($title)) {
    $stmt = $BaseConnect->prepare('SELECT * FROM wiki_pages WHERE title = ?');
    $stmt->execute([$title]);
    $page = $stmt->fetch(PDO::FETCH_ASSOC);
}

head('Hero Wiki', $uid);
?>
<div class="tophead2">
    <h2>Hero Wiki</h2>
    <p>Share advice, tips and strategies with other Heroes.</p>
    <a href="/wiki.php">All pages</a> | <a href="/wiki.php?action=edit">Create a page</a>
</div>
<?php if ($action === 'edit') {
    if ($form === null) {
        $form = [
            'title' => $page ? $page['title'] : $title,
            'category' => $page ? $page['category'] : '',
            'content' => $page ? $page['content'] : '',
            'version' => $page ? (int)$page['version'] : 0,
        ];
    }
    ?>
    <div class="tophead">
        <h3><?php echo $form['version'] > 0 ? 'Edit page' : 'Create page'; ?></h3>
        <?php if ($error !== '') { ?><p class="red" role="alert"><?php echo wikiEscape($error); ?></p><?php } ?>
        <?php if ($page) { ?><p><a href="<?php echo wikiEscape(wikiUrl($page['title'])); ?>" target="_blank" rel="noopener">Open current page in a new tab</a></p><?php } ?>
        <p>Use <code>[[Page title]]</code> to link to another wiki page. Follow a link to create a missing page. Titles cannot be changed after creation.</p>
        <form method="post" action="/wiki.php">
            <input type="hidden" name="csrf_token" value="<?php echo wikiEscape($_SESSION['wiki_csrf']); ?>">
            <input type="hidden" name="version" value="<?php echo (int)$form['version']; ?>">
            <label for="wiki-title">Title</label><br>
            <input id="wiki-title" name="title" type="text" maxlength="120" required value="<?php echo wikiEscape($form['title']); ?>"<?php echo $form['version'] > 0 ? ' readonly' : ''; ?>><br>
            <label for="wiki-category">Category (optional)</label><br>
            <input id="wiki-category" name="category" type="text" maxlength="80" value="<?php echo wikiEscape($form['category']); ?>"><br>
            <label for="wiki-content">Page content</label><br>
            <textarea id="wiki-content" name="content" rows="16" maxlength="60000" required style="width: 100%; max-width: 100%; box-sizing: border-box;"><?php echo wikiEscape($form['content']); ?></textarea><br>
            <button type="submit" class="button">Save page</button>
        </form>
    </div>
<?php } elseif ($title !== '' && !wikiValidTitle($title)) { ?>
    <div class="tophead">Invalid page title.</div>
<?php } elseif ($title !== '' && !$page) { ?>
    <div class="tophead">
        <p>This page does not exist yet.</p>
        <a href="<?php echo wikiEscape(wikiUrl($title) . '&action=edit'); ?>">Create <?php echo wikiEscape($title); ?></a>
    </div>
<?php } elseif ($page) {
    $history = $action === 'history';
    $revision = isset($_GET['revision']) && is_string($_GET['revision']) && ctype_digit($_GET['revision']) ? (int)$_GET['revision'] : 0;
    $display = $page;
    if ($revision > 0 && !$history) {
        $stmt = $BaseConnect->prepare('SELECT category, content, version, edited_by AS updated_by, edited_at AS updated_at FROM wiki_revisions WHERE page_id = ? AND version = ?');
        $stmt->execute([(int)$page['id'], $revision]);
        $display = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    ?>
    <div class="tophead">
        <h3><?php echo wikiEscape($page['title']); ?></h3>
        <p><a href="<?php echo wikiEscape(wikiUrl($page['title']) . '&action=edit'); ?>">Edit page</a> |
            <a href="<?php echo wikiEscape(wikiUrl($page['title']) . '&action=history'); ?>">History</a></p>
        <?php if ($history) {
            $stmt = $BaseConnect->prepare('SELECT r.version, r.edited_at, u.login FROM wiki_revisions r LEFT JOIN users u ON u.id = r.edited_by WHERE r.page_id = ? ORDER BY r.version DESC');
            $stmt->execute([(int)$page['id']]);
            ?>
            <h4>Revision history</h4>
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                <p><a href="<?php echo wikiEscape(wikiUrl($page['title']) . '&revision=' . (int)$row['version']); ?>">Version <?php echo (int)$row['version']; ?></a>
                    &mdash; <?php echo wikiEscape($row['login'] ?? 'Unknown Hero'); ?>, <?php echo gmdate('Y-m-d H:i', (int)$row['edited_at']); ?> UTC</p>
            <?php } ?>
        <?php } elseif (!$display) { ?>
            <p>That revision was not found.</p>
        <?php } else { ?>
            <p>Category: <?php echo wikiEscape($display['category'] !== '' ? $display['category'] : 'Uncategorized'); ?> |
                Version <?php echo (int)$display['version']; ?><?php if ($revision > 0) { ?> (historical)<?php } ?></p>
            <div><?php echo wikiRender($display['content']); ?></div>
        <?php } ?>
    </div>
<?php } else {
    $stmt = $BaseConnect->query('SELECT title, category FROM wiki_pages ORDER BY category, title');
    $lastCategory = null;
    $found = false;
    ?>
    <div class="tophead">
        <h3>Pages by category</h3>
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $found = true;
            if ($lastCategory !== $row['category']) {
                $lastCategory = $row['category'];
                ?><h4><?php echo wikiEscape($lastCategory !== '' ? $lastCategory : 'Uncategorized'); ?></h4><?php
            }
            ?><div><a href="<?php echo wikiEscape(wikiUrl($row['title'])); ?>"><?php echo wikiEscape($row['title']); ?></a></div><?php
        }
        if (!$found) { ?><p>No wiki pages yet. Be the first Hero to create one.</p><?php } ?>
    </div>
<?php }
foot($uid);

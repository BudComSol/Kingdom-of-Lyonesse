<?php
require_once __DIR__ . '/../core/wiki.php';

function check($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}

if (($argv[1] ?? '') === '--request') {
    $BaseConnect = new PDO('sqlite:' . $argv[2], null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $uid = 1;
    $_SESSION = ['wiki_csrf' => 'valid'];
    $request = json_decode($argv[3], true);
    $_GET = $request['get'] ?? [];
    $_POST = $request['post'] ?? [];
    $_SERVER['REQUEST_METHOD'] = isset($request['post']) ? 'POST' : 'GET';
    function head($title, $uid) { echo '<main>'; }
    function foot($uid) { echo '</main>'; }
    $source = file_get_contents(__DIR__ . '/../wiki.php');
    $source = str_replace("require_once __DIR__ . '/core/func.php';", '', $source);
    $source = str_replace("require_once __DIR__ . '/core/wiki.php';", '', $source);
    eval('?>' . $source);
    exit;
}

function request($path, $request) {
    $process = proc_open([PHP_BINARY, '-d', 'xdebug.mode=off', __FILE__, '--request', $path, json_encode($request)],
        [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    check(is_resource($process), 'Could not start wiki request');
    $output = stream_get_contents($pipes[1]);
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    check(proc_close($process) === 0 && $errors === '', "Wiki request failed: $errors");
    return $output;
}

$path = tempnam(sys_get_temp_dir(), 'wiki-test-');
try {
    $db = new PDO('sqlite:' . $path, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $db->exec("CREATE TABLE users (id INTEGER PRIMARY KEY, login TEXT);
        INSERT INTO users VALUES (1, 'Hero'), (2, 'Other Hero');
        CREATE TABLE wiki_pages (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL UNIQUE COLLATE NOCASE, category TEXT NOT NULL, content TEXT NOT NULL, version INTEGER NOT NULL, updated_by INTEGER NOT NULL, updated_at INTEGER NOT NULL);
        CREATE TABLE wiki_revisions (page_id INTEGER NOT NULL, version INTEGER NOT NULL, category TEXT NOT NULL, content TEXT NOT NULL, edited_by INTEGER NOT NULL, edited_at INTEGER NOT NULL, PRIMARY KEY (page_id, version))");

    check(str_contains(request($path, []), 'No wiki pages yet'), 'Empty directory missing');
    $post = ['csrf_token' => 'wrong', 'title' => 'Forest', 'category' => 'Guides', 'content' => 'Beware [[Bosses & loot]]', 'version' => '0'];
    check(str_contains(request($path, ['post' => $post]), 'session has expired'), 'CSRF was not rejected');
    check((int)$db->query('SELECT COUNT(*) FROM wiki_pages')->fetchColumn() === 0, 'Invalid CSRF created a page');
    $post['csrf_token'] = 'valid';
    request($path, ['post' => $post]);
    check((int)$db->query('SELECT COUNT(*) FROM wiki_revisions')->fetchColumn() === 1, 'Creation revision missing');
    $directory = request($path, []);
    check(str_contains($directory, 'Guides') && str_contains($directory, 'Forest'), 'Page not listed under category');
    $view = request($path, ['get' => ['title' => 'Forest']]);
    check(str_contains($view, '/wiki.php?title=Bosses%20%26%20loot') && str_contains($view, 'Bosses &amp; loot'), 'Wiki link not rendered');
    $missing = request($path, ['get' => ['title' => 'Bosses & loot']]);
    check(str_contains($missing, 'does not exist') && str_contains($missing, '&amp;action=edit'), 'Missing link cannot be created');
    check(str_contains(request($path, ['post' => $post]), 'already exists'), 'Duplicate title accepted');

    wikiSave($db, 2, 'Forest', 'Strategy', '<script>alert(1)</script>', 1);
    $post['content'] = 'My unsaved draft';
    $post['version'] = '1';
    $conflict = request($path, ['post' => $post]);
    check(str_contains($conflict, 'changed since you opened it') && str_contains($conflict, 'My unsaved draft'), 'Concurrent edit was lost');
    $view = request($path, ['get' => ['title' => 'Forest']]);
    check(str_contains($view, '&lt;script&gt;') && !str_contains($view, '<script>'), 'Content HTML was not escaped');
    check(str_contains(request($path, ['get' => ['title' => 'Forest', 'action' => 'history']]), 'Other Hero'), 'History editor missing');
    check(str_contains(request($path, ['get' => ['title' => 'Forest', 'revision' => '1']]), 'Beware'), 'Historical revision missing');
    check(str_contains(request($path, ['get' => ['title' => 'Forest', 'revision' => '9']]), 'not found'), 'Missing revision displayed current content');
    check((int)$db->query('SELECT COUNT(*) FROM wiki_revisions')->fetchColumn() === 2, 'Conflict created a revision');
    $post['version'] = '2';
    $post['category'] = 'Combat';
    request($path, ['post' => $post]);
    $view = request($path, ['get' => ['title' => 'Forest']]);
    check(str_contains($view, 'My unsaved draft') && str_contains($view, 'Combat'), 'Edit did not save content and category');
    check((int)$db->query('SELECT COUNT(*) FROM wiki_revisions')->fetchColumn() === 3, 'Edit revision missing');

    $db->exec("CREATE TRIGGER reject_revision BEFORE INSERT ON wiki_revisions BEGIN SELECT RAISE(ABORT, 'revision failed'); END");
    try {
        wikiSave($db, 1, 'Forest', '', 'Failed update', 3);
        throw new RuntimeException('Failed revision was accepted');
    } catch (PDOException $e) {
        check(str_contains($e->getMessage(), 'revision failed'), 'Unexpected database error');
    }
    check((int)$db->query("SELECT version FROM wiki_pages WHERE title = 'Forest'")->fetchColumn() === 3, 'Failed revision did not roll back');
    check(!wikiValidTitle("bad\nname") && !wikiValidTitle('bad[title]'), 'Invalid titles accepted');
    check(str_contains(wikiRender('[[<img src=x onerror=alert(1)>]]'), '&lt;img'), 'Link title HTML was not escaped');
    echo "Wiki tests passed\n";
} finally {
    unlink($path);
}

<?php

function check($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}
function qry($sql, $params = []) {
    $stmt = $GLOBALS['BaseConnect']->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}
function fch($sql, $params = []) { return qry($sql, $params)->fetch(PDO::FETCH_ASSOC); }
function cnt($sql, $params = []) { return count(qry($sql, $params)->fetchAll(PDO::FETCH_ASSOC)); }
function head($title, $uid) {}
function page($num, $size, $count) { return ($num - 1) * $size; }
function ppage($url, $num, $size, $count) {
    if ($GLOBALS['publishDuringRead']) {
        $GLOBALS['BaseConnect']->exec("INSERT INTO news VALUES (10, 'Concurrent', 'New', 0)");
    }
}
function clock($time) { return (string)$time; }
function bbcode($text) { return $text; }

$BaseConnect = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$BaseConnect->exec("CREATE TABLE users (id INTEGER PRIMARY KEY, time_on INTEGER, last_news_read_id INTEGER NOT NULL DEFAULT 0);
    CREATE TABLE news (id INTEGER PRIMARY KEY, name TEXT, text TEXT, time INTEGER);
    INSERT INTO users (id, time_on) VALUES (1, 0), (2, 0)");
$source = file_get_contents(__DIR__ . '/../core/func.php');
$start = strpos($source, 'function foot(');
$end = strpos($source, '// Initialize User Quests', $start);
eval(substr($source, $start, $end - $start));
$newsPage = file_get_contents(__DIR__ . '/../news.php');
$newsPage = str_replace('require_once __DIR__ . "/core/func.php";', '', $newsPage);
$uid = 1;
$user = ['id' => 1, 'last_news_read_id' => 0];
$publishDuringRead = false;
$_SERVER['PHP_SELF'] = '/news.php';

function footerHtml() {
    ob_start();
    foot($GLOBALS['uid']);
    return ob_get_clean();
}
function visitNews() {
    global $newsPage, $uid, $user, $BaseConnect;
    ob_start();
    eval('?>' . $newsPage);
    return ob_get_clean();
}

check(!str_contains(footerHtml(), 'news-badge'), 'No badge should appear without news');
$BaseConnect->exec("INSERT INTO news VALUES (1, 'First', 'Text', 0), (3, 'Second', 'Text', 0), (7, 'Third', 'Text', 0)");
$html = footerHtml();
check(str_contains($html, 'News, 3 unread items') && str_contains($html, 'class="news-badge" aria-hidden="true">3</span>'), 'Badge should count actual news items');
$html = visitNews();
check(!str_contains($html, 'news-badge'), 'Opening News should clear the badge immediately');
check((int)$BaseConnect->query('SELECT last_news_read_id FROM users WHERE id = 1')->fetchColumn() === 7, 'Read position was not saved');
check((int)$BaseConnect->query('SELECT last_news_read_id FROM users WHERE id = 2')->fetchColumn() === 0, 'Reading news changed another user');
$BaseConnect->exec("INSERT INTO news VALUES (8, 'Fourth', 'Text', 0)");
check(str_contains(footerHtml(), 'News, 1 unread item'), 'New news should show a singular unread count');
$publishDuringRead = true;
$html = visitNews();
check(str_contains($html, 'News, 1 unread item'), 'News published during a visit should remain unread');
check((int)$BaseConnect->query('SELECT last_news_read_id FROM users WHERE id = 1')->fetchColumn() === 8, 'Read position advanced past the visit snapshot');

foreach (['style.css', 'style.min.css'] as $stylesheet) {
    $css = file_get_contents(__DIR__ . '/../style/' . $stylesheet);
    check(str_contains($css, '.news-badge') && preg_match('/\.footer-nav-buttons a\.footer-news\s*\{\s*position:\s*relative/', $css), 'Badge positioning missing');
}
$config = file_get_contents(__DIR__ . '/../config.php');
check(preg_match('/\$BaseConnect->exec\("\s*(UPDATE `users` SET `last_news_read_id` = CASE.*?)"\);/s', $config, $migration) === 1, 'News read-position initialization missing');
$BaseConnect->exec('ALTER TABLE users ADD COLUMN news INTEGER DEFAULT 0');
$BaseConnect->exec('UPDATE users SET news = CASE WHEN id = 2 THEN 1 ELSE 0 END');
$BaseConnect->exec($migration[1]);
check((int)$BaseConnect->query('SELECT last_news_read_id FROM users WHERE id = 1')->fetchColumn() === 10, 'Previously read news became unread');
check((int)$BaseConnect->query('SELECT last_news_read_id FROM users WHERE id = 2')->fetchColumn() === 8, 'Legacy unread flag should preserve one unread item');

$BaseConnect = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$BaseConnect->exec("CREATE TABLE users (id INTEGER PRIMARY KEY, time_on INTEGER, news INTEGER DEFAULT 0);
    CREATE TABLE news (id INTEGER PRIMARY KEY, name TEXT, text TEXT, time INTEGER);
    INSERT INTO users VALUES (1, 0, 0), (2, 0, 1);
    INSERT INTO news VALUES (3, 'First', 'Text', 0), (7, 'Latest', 'Text', 0)");
$publishDuringRead = false;
$user = fch('SELECT * FROM users WHERE id = ?', [$uid]);
check(!str_contains(footerHtml(), 'news-badge'), 'Legacy read news should not all become unread');
$BaseConnect->exec('UPDATE users SET news = 1 WHERE id = 1');
$user = fch('SELECT * FROM users WHERE id = ?', [$uid]);
check(str_contains(footerHtml(), 'News, 1 unread item'), 'Legacy unread flag should show one notification');
$html = visitNews();
check(str_contains($html, 'Latest') && !str_contains($html, 'news-badge'), 'Legacy News should render and clear the badge without the new column');
check((int)$BaseConnect->query('SELECT news FROM users WHERE id = 1')->fetchColumn() === 0, 'Legacy read flag was not saved');
check((int)$BaseConnect->query('SELECT news FROM users WHERE id = 2')->fetchColumn() === 1, 'Legacy News visit changed another user');
$BaseConnect->exec('ALTER TABLE users ADD COLUMN last_news_read_id INTEGER NOT NULL DEFAULT 0');
$BaseConnect->exec($migration[1]);
$user = fch('SELECT * FROM users WHERE id = ?', [$uid]);
check(!str_contains(footerHtml(), 'news-badge'), 'Migration should preserve a legacy News visit as read');
$BaseConnect->exec("INSERT INTO news VALUES (8, 'After upgrade', 'Text', 0)");
check(str_contains(footerHtml(), 'News, 1 unread item'), 'Upgraded legacy user should receive counted notifications');
visitNews();
check((int)$BaseConnect->query('SELECT last_news_read_id FROM users WHERE id = 1')->fetchColumn() === 8, 'Upgraded legacy user should save the new read position');
echo "News notification tests passed\n";

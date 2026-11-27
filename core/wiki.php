<?php

function wikiEscape(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function wikiValidTitle(string $title): bool {
    return $title !== '' && mb_check_encoding($title, 'UTF-8')
        && mb_strlen($title) <= 120 && !preg_match('/[\[\]\x00-\x1f\x7f]/u', $title);
}

function wikiUrl(string $title): string {
    return '/wiki.php?title=' . rawurlencode($title);
}

function wikiRender(string $content): string {
    $parts = preg_split('/(\[\[[^\[\]\r\n]{1,120}\]\])/u', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
    $html = '';
    foreach ($parts as $part) {
        if (str_starts_with($part, '[[') && str_ends_with($part, ']]')) {
            $title = trim(substr($part, 2, -2));
            if (wikiValidTitle($title)) {
                $html .= '<a href="' . wikiEscape(wikiUrl($title)) . '">' . wikiEscape($title) . '</a>';
                continue;
            }
        }
        $html .= nl2br(wikiEscape($part));
    }
    return $html;
}

function wikiSave(PDO $db, int $userId, string $title, string $category, string $content, int $version): void {
    if (!wikiValidTitle($title)) {
        throw new RuntimeException('Enter a page title of 1 to 120 characters without brackets or control characters.');
    }
    if (!mb_check_encoding($category, 'UTF-8') || mb_strlen($category) > 80 || preg_match('/[\x00-\x1f\x7f]/u', $category)) {
        throw new RuntimeException('Category must be at most 80 characters without control characters.');
    }
    if (!mb_check_encoding($content, 'UTF-8') || trim($content) === '' || strlen($content) > 60000) {
        throw new RuntimeException('Enter page content of up to 60,000 bytes.');
    }
    if ($version < 0) {
        throw new RuntimeException('Invalid page version.');
    }

    $db->beginTransaction();
    try {
        $now = time();
        if ($version === 0) {
            try {
                $stmt = $db->prepare('INSERT INTO wiki_pages (title, category, content, version, updated_by, updated_at) VALUES (?, ?, ?, 1, ?, ?)');
                $stmt->execute([$title, $category, $content, $userId, $now]);
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    throw new RuntimeException('A page with this title already exists. Open it to edit instead.');
                }
                throw $e;
            }
            $pageId = (int)$db->lastInsertId();
            $newVersion = 1;
        } else {
            $stmt = $db->prepare('UPDATE wiki_pages SET category = ?, content = ?, version = version + 1, updated_by = ?, updated_at = ? WHERE title = ? AND version = ?');
            $stmt->execute([$category, $content, $userId, $now, $title, $version]);
            if ($stmt->rowCount() !== 1) {
                throw new RuntimeException('This page changed since you opened it. Your text is preserved below; copy it and open the current page before editing again.');
            }
            $stmt = $db->prepare('SELECT id FROM wiki_pages WHERE title = ?');
            $stmt->execute([$title]);
            $pageId = (int)$stmt->fetchColumn();
            $newVersion = $version + 1;
        }
        $stmt = $db->prepare('INSERT INTO wiki_revisions (page_id, version, category, content, edited_by, edited_at) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$pageId, $newVersion, $category, $content, $userId, $now]);
        $db->commit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}

<?php
declare(strict_types=1);

// محافظ: این فایل فقط از طریق نقاط ورود معتبر (index.php هسته یا request-center.php) بارگذاری می‌شود؛ دسترسی مستقیم قطعاً ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

/**
 * Request Center دانش‌نامه — کتابچهٔ راهنمای حل مشکل داخل ماژول.
 * پیش‌تر صفحهٔ legacy «index.php?page=knowledge» بود؛ با تصمیم مالک ۲۰۲۶-۱۱-۱۴ به ماژول منتقل شد.
 * ثبت مقاله فقط از مسیر «add_knowledge» (Actions/service-actions.php) انجام می‌شود که مجوز «knowledge.manage» می‌خواهد.
 */

if ($page === 'knowledge') {
    $knowledgeUser = require_login();
    $articleId = (int) ($_GET['id'] ?? 0);
    $article = null;
    if ($articleId > 0) {
        $articleQuery = db()->prepare('SELECT k.*, u.full_name AS author_name FROM knowledge_articles k LEFT JOIN users u ON u.id = k.created_by WHERE k.id = ? AND (k.is_published = 1 OR ? = 1) LIMIT 1');
        $articleQuery->execute([$articleId, is_staff_role($knowledgeUser['role']) ? 1 : 0]);
        $article = $articleQuery->fetch();
    }
    $knowledgeSearch = trim((string) ($_GET['q'] ?? ''));
    $knowledgeSql = 'SELECT id, title, category, updated_at FROM knowledge_articles WHERE ' . (is_staff_role($knowledgeUser['role']) ? '1 = 1' : 'is_published = 1');
    $knowledgeParams = [];
    if ($knowledgeSearch !== '') {
        $knowledgeSql .= ' AND (title LIKE ? OR body LIKE ? OR category LIKE ?)';
        $knowledgeParams = array_fill(0, 3, '%' . $knowledgeSearch . '%');
    }
    $knowledgeSql .= ' ORDER BY updated_at DESC LIMIT 100';
    $knowledgeQuery = db()->prepare($knowledgeSql);
    $knowledgeQuery->execute($knowledgeParams);
    $articles = $knowledgeQuery->fetchAll();
    render_header('دانش‌نامه', $knowledgeUser);
    echo '<section class="page-heading"><div><span class="eyebrow">Knowledge Base</span><h1>دانش‌نامه و راهنمای حل مشکل</h1><p>قبل از ثبت درخواست، راه‌حل‌های تاییدشده سازمان را جست‌وجو کنید.</p></div><form class="inline-search" method="get"><input type="hidden" name="page" value="knowledge"><input name="q" value="' . e($knowledgeSearch) . '" placeholder="جست‌وجو در دانش‌نامه"><button class="button" type="submit">جست‌وجو</button></form></section>';
    if ($article) {
        echo '<article class="card knowledge-article"><span class="eyebrow">' . e($article['category'] ?: 'راهنما') . '</span><h2>' . e($article['title']) . '</h2><p class="muted">نویسنده: ' . e($article['author_name'] ?: 'سامانه') . ' • ' . e(jalali_date($article['updated_at'])) . '</p><div class="article-body">' . nl2br(e($article['body'])) . '</div><a class="button secondary" href="request-center.php?page=knowledge">بازگشت به دانش‌نامه</a></article>';
    } else {
        echo '<section class="knowledge-grid">';
        foreach ($articles as $item) { echo '<a class="card knowledge-card" href="request-center.php?page=knowledge&id=' . (int) $item['id'] . '"><span class="eyebrow">' . e($item['category'] ?: 'راهنما') . '</span><h2>' . e($item['title']) . '</h2><small>به‌روزرسانی: ' . e(jalali_date($item['updated_at'], false)) . '</small></a>'; }
        if (!$articles) { echo '<div class="card empty-state"><h2>مقاله‌ای پیدا نشد.</h2><p>عبارت دیگری جست‌وجو کنید یا از کارشناس انفورماتیک کمک بگیرید.</p></div>'; }
        echo '</section>';
    }
    if (is_staff_role($knowledgeUser['role'])) {
        echo '<section class="card form-card"><h2>افزودن مقاله دانش‌نامه</h2><form method="post" action="request-center.php?page=knowledge">' . csrf_field() . '<input type="hidden" name="action" value="add_knowledge"><div class="form-grid"><label>عنوان مقاله<input name="article_title" required></label><label>دسته‌بندی<input name="article_category" placeholder="شبکه، پرینتر، نرم‌افزار"></label><label class="full">متن راهنما<textarea name="article_body" rows="7" required></textarea></label><label class="check-label"><input type="checkbox" name="is_published" value="1" checked> منتشر شود</label></div><button class="button" type="submit">ذخیره مقاله</button></form></section>';
    }
    render_footer();
    exit;
}

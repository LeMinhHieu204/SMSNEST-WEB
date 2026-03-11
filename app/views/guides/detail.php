<?php
$config = require __DIR__ . '/../../../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');
?>
<div class="guide-detail-grid">
    <div class="card">
        <div class="card-title"><?php echo !empty($guide['section']) ? htmlspecialchars($guide['section']) : 'Section'; ?></div>
        <div class="list">
            <?php if (!empty($sectionGuides)) : ?>
                <?php foreach ($sectionGuides as $item) : ?>
                    <div class="news-item">
                        <div class="news-title">
                            <a class="link" href="<?php echo $baseUrl; ?>/guides/detail?id=<?php echo htmlspecialchars($item['id']); ?>">
                                <?php echo htmlspecialchars($item['title']); ?>
                            </a>
                        </div>
                        <?php
                        $plain = trim(strip_tags($item['content'] ?? ''));
                        if (strlen($plain) > 120) {
                            $plain = substr($plain, 0, 117) . '...';
                        }
                        ?>
                        <div class="news-meta"><?php echo htmlspecialchars($plain); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="news-item">
                    <div class="news-title">No guides in this section.</div>
                    <div class="news-meta">Add more items to populate this list.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="card guide-detail-card">
        <?php if (empty($guide)) : ?>
            <div class="card-title">Guide not found</div>
            <div class="muted">The requested guide does not exist.</div>
        <?php else : ?>
            <div class="card-title"><?php echo htmlspecialchars($guide['title']); ?></div>
            <?php
            $contentHtml = $guide['content'];
            $contentHtml = preg_replace('/src=(["\'])\\/uploads\\//i', 'src=$1' . $baseUrl . '/uploads/', $contentHtml);
            $imageUrl = $guide['image_path'] ?? '';
            if ($imageUrl !== '' && !preg_match('/^https?:\\/\\//i', $imageUrl)) {
                $imageUrl = $baseUrl . $imageUrl;
            }
            ?>
            <div class="muted guide-detail-content">
                <?php if (!empty($imageUrl)) : ?>
                    <div style="margin-bottom: 12px;">
                        <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="Guide image" style="max-width:100%; border-radius:12px;">
                    </div>
                <?php endif; ?>
                <?php echo $contentHtml; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="card">
        <div class="card-title">Other interesting articles</div>
        <div class="list">
            <?php if (!empty($sectionGuides)) : ?>
                <?php foreach ($sectionGuides as $item) : ?>
                    <div class="news-item">
                        <div class="news-title">
                            <a class="link" href="<?php echo $baseUrl; ?>/guides/detail?id=<?php echo htmlspecialchars($item['id']); ?>">
                                <?php echo htmlspecialchars($item['title']); ?>
                            </a>
                        </div>
                        <?php
                        $plain = trim(strip_tags($item['content'] ?? ''));
                        if (strlen($plain) > 120) {
                            $plain = substr($plain, 0, 117) . '...';
                        }
                        ?>
                        <div class="news-meta"><?php echo htmlspecialchars($plain); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="news-item">
                    <div class="news-title">No guides in this section.</div>
                    <div class="news-meta">Add more items to populate this list.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

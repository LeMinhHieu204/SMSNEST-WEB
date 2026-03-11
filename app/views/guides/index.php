<?php
$sections = [
    'FAQ' => [],
    'How to Order SMS' => [],
    'How to Deposit' => [],
];
if (!empty($guides)) {
    foreach ($guides as $row) {
        $sectionName = $row['section'] ?? 'FAQ';
        if (!array_key_exists($sectionName, $sections)) {
            $sections[$sectionName] = [];
        }
        $sections[$sectionName][] = $row;
    }
}
?>
<div class="grid two">
    <?php foreach ($sections as $sectionName => $items) : ?>
        <div class="card">
            <div class="card-title"><?php echo htmlspecialchars($sectionName); ?></div>
            <div class="list">
                <?php if (!empty($items)) : ?>
                    <?php foreach ($items as $item) : ?>
                        <div class="news-item">
                            <div class="news-title">
                                <a class="link" href="<?php echo $baseUrl; ?>/guides/detail?id=<?php echo htmlspecialchars($item['id']); ?>">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </a>
                            </div>
                            <?php
                            $plain = trim(strip_tags($item['content']));
                            if (strlen($plain) > 160) {
                                $plain = substr($plain, 0, 157) . '...';
                            }
                            ?>
                            <div class="news-meta"><?php echo htmlspecialchars($plain); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="news-item">
                        <div class="news-title">No guides yet.</div>
                        <div class="news-meta">Ask admin to add content.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

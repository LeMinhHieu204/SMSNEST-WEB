<div class="grid two">
    <div class="card">
        <div class="card-title">Contact Support</div>
        <p class="muted">Reach out if you need help with orders, payments, or account access.</p>
        <?php if (!empty($_GET['success'])) : ?>
            <div class="alert">Your support request has been sent.</div>
        <?php elseif (!empty($_GET['error'])) : ?>
            <div class="alert">Please fill in the required fields.</div>
        <?php endif; ?>
        <div class="table contact-table">
            <div class="table-row">
                <div>Email</div>
                <div>smsnesthub@gmail.com</div>
            </div>
            <div class="table-row">
                <div>Telegram</div>
                <div>@Jimmy3212</div>
            </div>
            <div class="table-row">
                <div>Hours</div>
                <div>Mon - Fri, 09:00 - 22:00 (UTC+7)</div>
            </div>
        </div>
        <div style="margin-top:16px;">
            <button class="btn primary" type="button" id="support-open">Support</button>
        </div>
    </div>
    <div class="card">
        <div class="card-title">Support History</div>
        <div class="table">
            <div class="table-row table-head">
                <div>#</div>
                <div>Title</div>
                <div>Content</div>
                <div>Image</div>
                <div>Date</div>
            </div>
            <?php if (!empty($tickets)) : ?>
                <?php foreach ($tickets as $ticket) : ?>
                    <?php
                    $imageUrl = $ticket['image_path'] ?? '';
                    if ($imageUrl !== '' && !preg_match('/^https?:\\/\\//i', $imageUrl)) {
                        $imageUrl = $baseUrl . $imageUrl;
                    }
                    ?>
                    <div class="table-row">
                        <div><?php echo htmlspecialchars($ticket['id']); ?></div>
                        <div><?php echo htmlspecialchars($ticket['title']); ?></div>
                        <div><?php echo htmlspecialchars($ticket['content']); ?></div>
                        <div>
                            <?php if (!empty($imageUrl)) : ?>
                                <a class="link" href="<?php echo htmlspecialchars($imageUrl); ?>" target="_blank">View</a>
                            <?php else : ?>
                                -
                            <?php endif; ?>
                        </div>
                        <div><?php echo htmlspecialchars($ticket['created_at']); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="table-row">
                    <div>-</div>
                    <div>No support requests yet.</div>
                    <div>-</div>
                    <div>-</div>
                    <div>-</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="support-modal" style="display:none; position:fixed; inset:0; background:rgba(10,12,16,0.6); z-index:50;">
    <div class="card" style="max-width:520px; margin:6vh auto; padding:20px;">
        <div class="card-title">Support Request</div>
        <?php $currentUser = Auth::user(); ?>
        <form class="form" id="support-form" method="post" action="<?php echo $baseUrl; ?>/contact/support" enctype="multipart/form-data">
            <label>Username</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($currentUser['username'] ?? ''); ?>" readonly>

            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($currentUser['email'] ?? ''); ?>" readonly>

            <label>Title</label>
            <input type="text" name="title" placeholder="Issue title" required>

            <label>Content</label>
            <textarea name="content" rows="5" placeholder="Describe your issue" required></textarea>

            <label>Upload image</label>
            <input type="file" name="attachment" accept="image/*">

            <div style="display:flex; gap:8px; margin-top:12px;">
                <button class="btn primary" type="submit" id="support-submit">Submit</button>
                <button class="btn" type="button" id="support-close">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        var openBtn = document.getElementById('support-open');
        var closeBtn = document.getElementById('support-close');
        var modal = document.getElementById('support-modal');
        var form = document.getElementById('support-form');
        var submitBtn = document.getElementById('support-submit');

        function openModal() {
            if (modal) modal.style.display = 'block';
        }

        function closeModal() {
            if (modal) modal.style.display = 'none';
        }

        if (openBtn) openBtn.addEventListener('click', openModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });
        }
        if (form) {
            form.addEventListener('submit', function () {
                form.classList.add('is-loading');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Sending...';
                }
            });
        }
    })();
</script>

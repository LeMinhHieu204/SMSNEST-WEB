<div class="card">
    <div class="card-title">Support Requests</div>
    <form class="table-toolbar" method="get" action="<?php echo $baseUrl; ?>/admin/support">
        <input class="input-sm" type="text" name="email" placeholder="Search by email" value="<?php echo htmlspecialchars($emailQuery ?? ''); ?>">
        <button class="btn" type="submit">Search</button>
        <?php if (!empty($emailQuery)) : ?>
            <a class="btn" href="<?php echo $baseUrl; ?>/admin/support">Clear</a>
        <?php endif; ?>
    </form>
    <div class="table">
            <div class="table-row table-head admin-table">
                <div>#</div>
                <div>User</div>
                <div>Email</div>
                <div>Title</div>
                <div>Status</div>
                <div>Attachment</div>
                <div>Date</div>
                <div>Detail</div>
            </div>
            <?php if (!empty($tickets)) : ?>
                <?php foreach ($tickets as $ticket) : ?>
                    <div class="table-row admin-table">
                    <div><?php echo htmlspecialchars($ticket['id']); ?></div>
                    <div><?php echo htmlspecialchars($ticket['account_username'] ?? $ticket['username']); ?></div>
                    <div><?php echo htmlspecialchars($ticket['email']); ?></div>
                    <div><?php echo htmlspecialchars($ticket['title']); ?></div>
                    <div><?php echo htmlspecialchars($ticket['status']); ?></div>
                    <div>
                        <?php if (!empty($ticket['image_path'])) : ?>
                            <a class="link" href="<?php echo htmlspecialchars($baseUrl . $ticket['image_path']); ?>" target="_blank">View</a>
                        <?php else : ?>
                            -
                        <?php endif; ?>
                    </div>
                    <div><?php echo htmlspecialchars($ticket['created_at']); ?></div>
                    <div>
                        <button
                            class="btn"
                            type="button"
                            data-detail="1"
                            data-id="<?php echo htmlspecialchars($ticket['id']); ?>"
                            data-user="<?php echo htmlspecialchars($ticket['account_username'] ?? $ticket['username']); ?>"
                            data-email="<?php echo htmlspecialchars($ticket['email']); ?>"
                            data-title="<?php echo htmlspecialchars($ticket['title']); ?>"
                            data-status="<?php echo htmlspecialchars($ticket['status']); ?>"
                            data-content="<?php echo htmlspecialchars($ticket['content']); ?>"
                            data-image="<?php echo htmlspecialchars($ticket['image_path'] ?? ''); ?>"
                            data-date="<?php echo htmlspecialchars($ticket['created_at']); ?>"
                        >Detail</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="table-row admin-table">
                <div>-</div>
                <div>No tickets</div>
                <div>-</div>
                <div>-</div>
                <div>-</div>
                <div>-</div>
                <div>-</div>
                <div>-</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="support-detail-modal" style="display:none; position:fixed; inset:0; background:rgba(10,12,16,0.6); z-index:50;">
    <div class="card" style="max-width:640px; margin:6vh auto; padding:20px;">
        <div class="card-title">Support Detail</div>
        <div class="table">
            <div class="table-row">
                <div>ID</div>
                <div id="detail-id">-</div>
            </div>
            <div class="table-row">
                <div>User</div>
                <div id="detail-user">-</div>
            </div>
            <div class="table-row">
                <div>Email</div>
                <div id="detail-email" style="white-space:normal; overflow:visible; text-overflow:initial;">-</div>
            </div>
            <div class="table-row">
                <div>Title</div>
                <div id="detail-title" style="white-space:normal; overflow:visible; text-overflow:initial;">-</div>
            </div>
            <div class="table-row">
                <div>Status</div>
                <div id="detail-status">-</div>
            </div>
            <div class="table-row">
                <div>Content</div>
                <div id="detail-content" style="white-space:normal; overflow:visible; text-overflow:initial;">-</div>
            </div>
            <div class="table-row">
                <div>Attachment</div>
                <div id="detail-image">-</div>
            </div>
            <div class="table-row">
                <div>Date</div>
                <div id="detail-date">-</div>
            </div>
        </div>
        <div style="margin-top:12px; text-align:right;">
            <button class="btn" type="button" id="detail-close">Close</button>
        </div>
    </div>
</div>

<script>
    (function () {
        var modal = document.getElementById('support-detail-modal');
        var closeBtn = document.getElementById('detail-close');
        var triggers = document.querySelectorAll('[data-detail="1"]');

        function setText(id, value) {
            var el = document.getElementById(id);
            if (el) {
                el.textContent = value || '-';
            }
        }

        function openModal(data) {
            setText('detail-id', data.id);
            setText('detail-user', data.user);
            setText('detail-email', data.email);
            setText('detail-title', data.title);
            setText('detail-status', data.status);
            setText('detail-content', data.content);
            setText('detail-date', data.date);
            var imageWrap = document.getElementById('detail-image');
            if (imageWrap) {
                if (data.image) {
                    imageWrap.innerHTML = '<a class="link" href="<?php echo $baseUrl; ?>' + data.image + '" target="_blank">View</a>';
                } else {
                    imageWrap.textContent = '-';
                }
            }
            if (modal) modal.style.display = 'block';
        }

        function closeModal() {
            if (modal) modal.style.display = 'none';
        }

        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });
        }

        for (var i = 0; i < triggers.length; i++) {
            triggers[i].addEventListener('click', function () {
                openModal({
                    id: this.getAttribute('data-id'),
                    user: this.getAttribute('data-user'),
                    email: this.getAttribute('data-email'),
                    title: this.getAttribute('data-title'),
                    status: this.getAttribute('data-status'),
                    content: this.getAttribute('data-content'),
                    image: this.getAttribute('data-image'),
                    date: this.getAttribute('data-date')
                });
            });
        }
    })();
</script>

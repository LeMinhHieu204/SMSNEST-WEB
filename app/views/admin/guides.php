<?php
$config = require __DIR__ . '/../../../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');
$sections = ['FAQ', 'How to Order SMS', 'How to Deposit'];
?>
<div class="guides-admin">
    <div class="card">
        <div class="card-title">Create Guide</div>
        <?php if (!empty($_GET['success'])) : ?>
            <div class="alert">Saved successfully.</div>
        <?php elseif (!empty($_GET['error'])) : ?>
            <div class="alert">Please fill all required fields.</div>
        <?php endif; ?>
        <form class="form" method="post" action="<?php echo $baseUrl; ?>/admin/guides/create" enctype="multipart/form-data">
            <label>Section</label>
            <select name="section" required>
                <?php foreach ($sections as $section) : ?>
                    <option value="<?php echo htmlspecialchars($section); ?>"><?php echo htmlspecialchars($section); ?></option>
                <?php endforeach; ?>
            </select>

            <label>Title</label>
            <input type="text" name="title" required>

            <label>Content</label>
            <div class="editor">
                <div class="editor-toolbar" data-editor-toolbar>
                    <button class="btn icon" type="button" data-cmd="bold">B</button>
                    <button class="btn icon" type="button" data-cmd="italic">I</button>
                    <button class="btn icon" type="button" data-cmd="underline">U</button>
                    <button class="btn icon" type="button" data-cmd="insertUnorderedList">• List</button>
                    <button class="btn icon" type="button" data-cmd="justifyLeft">L</button>
                    <button class="btn icon" type="button" data-cmd="justifyCenter">C</button>
                    <button class="btn icon" type="button" data-cmd="justifyRight">R</button>
                    <select class="input-sm" data-cmd="formatBlock">
                        <option value="p">Text</option>
                        <option value="h2">Heading</option>
                        <option value="h3">Subheading</option>
                    </select>
                    <select class="input-sm" data-cmd="fontSize">
                        <option value="3">Size 14</option>
                        <option value="4">Size 16</option>
                        <option value="5">Size 18</option>
                    </select>
                    <select class="input-sm" data-img-size>
                        <option value="">Image size</option>
                        <option value="40">Small</option>
                        <option value="60">Medium</option>
                        <option value="80">Large</option>
                        <option value="100">Full</option>
                    </select>
                    <button class="btn icon" type="button" data-img-remove>Remove image</button>
                </div>
                <div class="editor-body" contenteditable="true" data-editor></div>
                <textarea name="content" class="editor-input" required></textarea>
            </div>

            <label>Images (optional)</label>
            <input type="file" name="image_files[]" accept="image/png,image/jpeg,image/webp" multiple data-editor-upload>
            <div class="image-previews" data-editor-previews></div>
            <div class="image-names" data-editor-names></div>

            <button class="btn primary" type="submit">Create</button>
        </form>
    </div>

    <div class="card">
        <div class="card-title">Guides</div>
        <?php if (!empty($guides)) : ?>
            <div class="guides-list">
                <?php foreach ($guides as $row) : ?>
                    <div class="guide-item" data-guide-id="<?php echo htmlspecialchars($row['id']); ?>">
                        <div class="guide-meta">
                            <div class="guide-title"><?php echo htmlspecialchars($row['title']); ?></div>
                        </div>
                        <div class="guide-actions">
                            <div class="card-actions">
                                <button class="btn icon" type="button" data-guide-toggle>Edit</button>
                                <button class="btn warning" type="button" data-preview-url="<?php echo $baseUrl; ?>/guides/detail?id=<?php echo htmlspecialchars($row['id']); ?>">Preview</button>
                                <form method="post" action="<?php echo $baseUrl; ?>/admin/guides/delete" class="guide-delete-form">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                    <button class="btn danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </div>
                        <div class="guide-edit" hidden>
                            <form class="form" method="post" action="<?php echo $baseUrl; ?>/admin/guides/update" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                <label>Section</label>
                                <select name="section" required>
                                    <?php foreach ($sections as $section) : ?>
                                        <option value="<?php echo htmlspecialchars($section); ?>" <?php echo $section === $row['section'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($section); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label>Title</label>
                                <input type="text" name="title" value="<?php echo htmlspecialchars($row['title']); ?>" required>
                                <label>Content</label>
                                <div class="editor">
                                    <div class="editor-toolbar" data-editor-toolbar>
                                        <button class="btn icon" type="button" data-cmd="bold">B</button>
                                        <button class="btn icon" type="button" data-cmd="italic">I</button>
                                        <button class="btn icon" type="button" data-cmd="underline">U</button>
                                        <button class="btn icon" type="button" data-cmd="insertUnorderedList">• List</button>
                                        <button class="btn icon" type="button" data-cmd="justifyLeft">L</button>
                                        <button class="btn icon" type="button" data-cmd="justifyCenter">C</button>
                                        <button class="btn icon" type="button" data-cmd="justifyRight">R</button>
                                        <select class="input-sm" data-cmd="formatBlock">
                                            <option value="p">Text</option>
                                            <option value="h2">Heading</option>
                                            <option value="h3">Subheading</option>
                                        </select>
                                        <select class="input-sm" data-cmd="fontSize">
                                            <option value="3">Size 14</option>
                                            <option value="4">Size 16</option>
                                            <option value="5">Size 18</option>
                                        </select>
                                        <select class="input-sm" data-img-size>
                                            <option value="">Image size</option>
                                            <option value="40">Small</option>
                                            <option value="60">Medium</option>
                                            <option value="80">Large</option>
                                            <option value="100">Full</option>
                                        </select>
                                        <button class="btn icon" type="button" data-img-remove>Remove image</button>
                                    </div>
                                    <?php
                                    $editorContent = $row['content'];
                                    $editorContent = preg_replace('/src=(["\'])\\/uploads\\//i', 'src=$1' . $baseUrl . '/uploads/', $editorContent);
                                    ?>
                                    <div class="editor-body" contenteditable="true" data-editor><?php echo $editorContent; ?></div>
                                    <textarea name="content" class="editor-input" required><?php echo htmlspecialchars($row['content']); ?></textarea>
                                </div>
                                <label>Images (optional)</label>
                                <input type="file" name="image_files[]" accept="image/png,image/jpeg,image/webp" multiple data-editor-upload>
                                <div class="image-previews" data-editor-previews></div>
                                <div class="image-names" data-editor-names></div>
                                <div class="card-actions">
                                    <button class="btn success" type="submit">Update</button>
                                    <button class="btn" type="button" data-guide-toggle>Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="muted">No guides found.</div>
        <?php endif; ?>
    </div>
</div>
<div class="preview-modal" id="guide-preview-modal" aria-hidden="true">
    <div class="preview-backdrop" data-preview-close></div>
    <div class="preview-panel">
        <div class="preview-header">
            <div class="preview-title">Guide Preview</div>
            <button class="btn icon" type="button" data-preview-close>✕</button>
        </div>
        <iframe class="preview-frame" title="Guide preview"></iframe>
    </div>
</div>

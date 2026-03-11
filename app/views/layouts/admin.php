<?php
$config = require __DIR__ . '/../../../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');
$cssPath = __DIR__ . '/../../../public/assets/css/app.css';
$jsPath = __DIR__ . '/../../../public/assets/js/app.js';
$cssVersion = file_exists($cssPath) ? filemtime($cssPath) : time();
$jsVersion = file_exists($jsPath) ? filemtime($jsPath) : time();
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="cryptomus" content="b223f689">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Admin Panel'; ?></title>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/app.css?v=<?php echo $cssVersion; ?>">
    <link rel="icon" href="<?php echo $baseUrl; ?>/assets/logo.png">
</head>
<body>
    <div class="app">
        <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>
        <div class="sidebar-overlay" data-sidebar-overlay aria-hidden="true"></div>
        <main class="main">
            <?php require __DIR__ . '/../partials/admin_topbar.php'; ?>
            <section class="content">
                <?php require $viewPath; ?>
            </section>
        </main>
    </div>
    <script>
        window.APP_BASE_URL = "<?php echo $baseUrl; ?>";
    </script>
    <script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>
    <script src="<?php echo $baseUrl; ?>/assets/js/app.js?v=<?php echo $jsVersion; ?>"></script>
</body>
</html>

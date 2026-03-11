<?php
$config = require __DIR__ . '/../../../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');
$jsPath = __DIR__ . '/../../../public/assets/js/app.js';
$jsVersion = file_exists($jsPath) ? filemtime($jsPath) : time();
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="cryptomus" content="b223f689">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Auth'; ?></title>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/app.css">
    <link rel="icon" href="<?php echo $baseUrl; ?>/assets/logo.png">
</head>
<body class="auth-body">
    <div class="auth-wrapper">
        <?php require $viewPath; ?>
    </div>
    <script>
        window.APP_BASE_URL = "<?php echo $baseUrl; ?>";
    </script>
    <script src="<?php echo $baseUrl; ?>/assets/js/app.js?v=<?php echo $jsVersion; ?>"></script>
</body>
</html>

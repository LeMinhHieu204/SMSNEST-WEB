<?php
$config = require __DIR__ . '/../../../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');
$jsPath = __DIR__ . '/../../../public/assets/js/app.js';
$jsVersion = file_exists($jsPath) ? filemtime($jsPath) : time();
$homeCssPath = __DIR__ . '/../../../public/assets/css/home.css';
$homeCssVersion = file_exists($homeCssPath) ? filemtime($homeCssPath) : time();
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="cryptomus" content="b223f689">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'SMSNest'; ?></title>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/app.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/home.css?v=<?php echo $homeCssVersion; ?>">
    <link rel="icon" href="<?php echo $baseUrl; ?>/assets/logo.png">
</head>
<body class="landing-body">
    <?php require $viewPath; ?>
    <script>
        window.APP_BASE_URL = "<?php echo $baseUrl; ?>";
    </script>
    <script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>
    <script src="<?php echo $baseUrl; ?>/assets/js/app.js?v=<?php echo $jsVersion; ?>"></script>
</body>
</html>

<?php
require __DIR__ . '/../app/core/Router.php';
require __DIR__ . '/../app/core/Controller.php';
require __DIR__ . '/../app/core/View.php';
require __DIR__ . '/../app/core/Database.php';
require __DIR__ . '/../app/core/Model.php';
require __DIR__ . '/../app/core/Auth.php';
require __DIR__ . '/../app/core/Mailer.php';

session_start();

spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../app/controllers/' . $class . '.php',
        __DIR__ . '/../app/models/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require $path;
            return;
        }
    }
});

$router = new Router();
$router->get('/', [HomeController::class, 'index']);
$router->get('/home', [HomeController::class, 'index']);
$router->get('/dashboard', [DashboardController::class, 'user']);
$router->get('/admin', [DashboardController::class, 'admin']);
$router->get('/admin/pricing', [AdminController::class, 'pricing']);
$router->get('/admin/users', [AdminController::class, 'users']);
$router->get('/admin/order-logs', [AdminController::class, 'orderLogs']);
$router->get('/admin/wallet-logs', [AdminController::class, 'walletLogs']);
$router->get('/admin/support', [AdminController::class, 'supportTickets']);
$router->post('/admin/pricing', [AdminController::class, 'updatePricing']);
$router->get('/order', [OrderController::class, 'sms']);
$router->get('/order/quick', [OrderController::class, 'quick']);
$router->get('/order/history', [OrderController::class, 'history']);
$router->get('/deposit', [DepositController::class, 'index']);
$router->post('/deposit', [DepositController::class, 'store']);
$router->post('/deposit/cryptomus-invoice', [DepositController::class, 'cryptomusInvoice']);
$router->post('/deposit/cryptomus-webhook', [DepositController::class, 'cryptomusWebhook']);
$router->get('/deposit/cryptomus-services', [DepositController::class, 'cryptomusServices']);
$router->get('/affiliate', [AffiliateController::class, 'index']);
$router->get('/contact', [ContactController::class, 'index']);
$router->post('/affiliate/withdraw', [AffiliateController::class, 'withdraw']);
$router->get('/settings', [SettingsController::class, 'index']);
$router->post('/settings', [SettingsController::class, 'update']);
$router->post('/contact/support', [ContactController::class, 'store']);
$router->get('/guides', [GuidesController::class, 'index']);
$router->get('/guides/detail', [GuidesController::class, 'detail']);
$router->get('/admin/guides', [GuidesAdminController::class, 'index']);
$router->post('/admin/guides/create', [GuidesAdminController::class, 'store']);
$router->post('/admin/guides/update', [GuidesAdminController::class, 'update']);
$router->post('/admin/guides/delete', [GuidesAdminController::class, 'delete']);
$router->get('/api/countries', [ApiController::class, 'countries']);
$router->get('/api/service-countries', [ApiController::class, 'serviceCountries']);
$router->get('/api/smspool-pricing', [ApiController::class, 'smspoolPricing']);
$router->get('/api/smspool-stock', [ApiController::class, 'smspoolStock']);
$router->post('/api/smspool-order-sms', [ApiController::class, 'smspoolOrderSms']);
$router->post('/api/smspool-check-sms', [ApiController::class, 'smspoolCheckSms']);
$router->post('/api/smspool-cancel-sms', [ApiController::class, 'smspoolCancelSms']);
$router->post('/api/smspool-cancel-local', [ApiController::class, 'smspoolCancelLocal']);
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'authenticate']);
$router->get('/forgot-password', [AuthController::class, 'forgotPassword']);
$router->post('/forgot-password', [AuthController::class, 'sendPasswordReset']);
$router->get('/reset-password', [AuthController::class, 'resetPassword']);
$router->post('/reset-password', [AuthController::class, 'updatePassword']);
$router->get('/register', [AuthController::class, 'register']);
$router->post('/register', [AuthController::class, 'store']);
$router->get('/verify-email', [AuthController::class, 'verifyEmail']);
$router->get('/logout', [AuthController::class, 'logout']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

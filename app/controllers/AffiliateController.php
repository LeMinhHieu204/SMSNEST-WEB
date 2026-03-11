<?php
class AffiliateController extends Controller
{
    public function index()
    {
        Auth::requireLogin();
        $user = Auth::user();
        $affiliateModel = new Affiliate();
        $affiliate = $affiliateModel->getByUserId($user['id']);
        if (!$affiliate) {
            $config = require __DIR__ . '/../../config/config.php';
            $baseUrl = rtrim($config['app']['base_url'] ?? '', '/');
            $promoCode = strtoupper(bin2hex(random_bytes(4)));
            $referralLink = $baseUrl . '/register?ref=' . $promoCode;
            $affiliateModel->createForUser($user['id'], $promoCode, $referralLink);
            $affiliate = $affiliateModel->getByUserId($user['id']);
        }
        $registrations = $affiliate ? $affiliateModel->getRegistrations($affiliate['id']) : [];
        $this->view->render('affiliate/index', [
            'pageTitle' => 'Affiliate',
            'affiliate' => $affiliate,
            'registrations' => $registrations,
        ]);
    }

    public function withdraw()
    {
        Auth::requireLogin();
        $user = Auth::user();
        $affiliateModel = new Affiliate();
        $amountInput = trim($_POST['amount'] ?? '');
        $amount = $amountInput !== '' ? (float) $amountInput : null;
        $amount = $affiliateModel->withdrawToBalance($user['id'], $amount);
        if ($amount > 0) {
            Auth::redirect('/affiliate?success=1');
        }
        Auth::redirect('/affiliate?error=empty');
    }
}

<?php
class DashboardController extends Controller
{
    public function user()
    {
        Auth::requireLogin();
        $user = Auth::user();
        if ($user && $user['role'] === 'admin') {
            Auth::redirect('/admin');
        }
        $balanceTotal = (new WalletTransaction())->getNetByUserId($user['id']);
        $stats = (new Order())->getStatsByUserId($user['id']);
        $this->view->render('dashboard/user', [
            'pageTitle' => 'User Dashboard',
            'user' => $user,
            'balanceTotal' => $balanceTotal,
            'stats' => $stats,
        ]);
    }

    public function admin()
    {
        Auth::requireLogin();
        Auth::requireAdmin();
        $admin = Auth::user();
        $balanceTotal = (new WalletTransaction())->getNetByUserId($admin['id']);
        $orderModel = new Order();
        $stats = $orderModel->getAdminStats();
        $totalDeposits = (new WalletTransaction())->getTotalDepositsAll();
        $totalCompletedOrders = $orderModel->getTotalCompletedSpent();
        $recentOrders = $orderModel->getRecentForAdmin(5);
        $lowStockCount = (new ServiceCountry())->countLowStock(5);
        $alerts = [];
        if ($lowStockCount > 0) {
            $alerts[] = $lowStockCount . ' services low on stock';
        }
        if (!empty($stats['pending_orders'])) {
            $alerts[] = $stats['pending_orders'] . ' pending orders need attention';
        }
        $this->view->render('dashboard/admin', [
            'pageTitle' => 'Admin Dashboard',
            'layout' => 'admin',
            'user' => $admin,
            'balanceTotal' => $balanceTotal,
            'stats' => $stats,
            'totalDeposits' => $totalDeposits,
            'totalCompletedOrders' => $totalCompletedOrders,
            'recentOrders' => $recentOrders,
            'alerts' => $alerts,
        ]);
    }
}

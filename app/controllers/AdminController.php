<?php
class AdminController extends Controller
{
    public function pricing()
    {
        Auth::requireLogin();
        Auth::requireAdmin();

        $serviceQuery = trim($_GET['service_query'] ?? '');
        $services = $serviceQuery !== ''
            ? (new Service())->searchByName($serviceQuery)
            : (new Service())->all();
        $serviceId = isset($_GET['service_id']) ? (int) $_GET['service_id'] : 0;
        $countryQuery = trim($_GET['country_query'] ?? '');
        $rows = [];

        if ($serviceId > 0) {
            $rows = $countryQuery !== ''
                ? (new ServiceCountry())->getByServiceIdRawFiltered($serviceId, $countryQuery)
                : (new ServiceCountry())->getByServiceIdRaw($serviceId);
        }

        $this->view->render('admin/pricing', [
            'pageTitle' => 'Admin Pricing',
            'layout' => 'admin',
            'services' => $services,
            'serviceId' => $serviceId,
            'serviceQuery' => $serviceQuery,
            'countryQuery' => $countryQuery,
            'rows' => $rows,
        ]);
    }

    public function updatePricing()
    {
        Auth::requireLogin();
        Auth::requireAdmin();

        $serviceId = isset($_POST['service_id']) ? (int) $_POST['service_id'] : 0;
        $countryId = isset($_POST['country_id']) ? (int) $_POST['country_id'] : 0;
        $min = trim($_POST['custom_min_price'] ?? '');
        $max = trim($_POST['custom_max_price'] ?? '');

        if ($serviceId > 0 && $countryId > 0) {
            (new ServiceCountry())->updateCustomPrices($serviceId, $countryId, $min, $max);
        }

        Auth::redirect('/admin/pricing?service_id=' . $serviceId . '&saved=1');
    }

    public function users()
    {
        Auth::requireLogin();
        Auth::requireAdmin();

        $users = (new User())->getAllWithBalances(200);
        $this->view->render('admin/users', [
            'pageTitle' => 'User Management',
            'layout' => 'admin',
            'users' => $users,
        ]);
    }

    public function walletLogs()
    {
        Auth::requireLogin();
        Auth::requireAdmin();

        $logs = (new WalletTransaction())->getDepositLogs(300);
        $this->view->render('admin/wallet_logs', [
            'pageTitle' => 'Wallet Deposit Logs',
            'layout' => 'admin',
            'logs' => $logs,
        ]);
    }

    public function orderLogs()
    {
        Auth::requireLogin();
        Auth::requireAdmin();

        $filters = [
            'order_id' => trim($_GET['order_id'] ?? ''),
            'username' => trim($_GET['username'] ?? ''),
            'service' => trim($_GET['service'] ?? ''),
            'country' => trim($_GET['country'] ?? ''),
            'status' => trim($_GET['status'] ?? ''),
        ];
        $orders = (new Order())->getAllForAdminFiltered($filters, 300);
        $this->view->render('admin/order_logs', [
            'pageTitle' => 'Order Logs',
            'layout' => 'admin',
            'orders' => $orders,
            'filters' => $filters,
        ]);
    }

    public function supportTickets()
    {
        Auth::requireLogin();
        Auth::requireAdmin();

        $emailQuery = trim($_GET['email'] ?? '');
        $tickets = (new SupportTicket())->getAll(300, $emailQuery);
        $this->view->render('admin/support_tickets', [
            'pageTitle' => 'Support Requests',
            'layout' => 'admin',
            'tickets' => $tickets,
            'emailQuery' => $emailQuery,
        ]);
    }
}

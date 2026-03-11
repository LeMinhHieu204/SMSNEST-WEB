<?php
class OrderController extends Controller
{
    public function sms()
    {
        Auth::requireLogin();
        $user = Auth::user();
        $orderModel = new Order();
        $pending = $orderModel->getPendingByUserId($user['id']);
        $services = (new Service())->all();
        $countries = (new Country())->all();
        $this->view->render('order/sms', [
            'pageTitle' => 'Order SMS',
            'pending' => $pending,
            'services' => $services,
            'countries' => $countries,
        ]);
    }

    public function quick()
    {
        Auth::requireLogin();
        $user = Auth::user();
        $pending = (new Order())->getPendingByUserId($user['id']);
        $services = (new Service())->all();
        $this->view->render('order/quick', [
            'pageTitle' => 'Quick Order',
            'pending' => $pending,
            'services' => $services,
        ]);
    }

    public function history()
    {
        Auth::requireLogin();
        $user = Auth::user();
        $orders = (new Order())->getHistoryByUserId($user['id']);
        $this->view->render('order/history', [
            'pageTitle' => 'Order History',
            'orders' => $orders,
        ]);
    }
}

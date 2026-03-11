<?php
class ApiController extends Controller
{
    public function countries()
    {
        Auth::requireLogin();
        $rows = (new Country())->all();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['data' => $rows]);
    }

    public function serviceCountries()
    {
        if (!Auth::check() || !Auth::user()) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        $serviceId = isset($_GET['service_id']) ? (int) $_GET['service_id'] : 0;
        if ($serviceId <= 0) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Invalid service_id']);
            return;
        }
        $rows = (new ServiceCountry())->getByServiceId($serviceId);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['data' => $rows]);
    }

    public function smspoolPricing()
    {
        Auth::requireLogin();
        $serviceId = isset($_GET['service_id']) ? (int) $_GET['service_id'] : 0;
        $countryId = isset($_GET['country_id']) ? (int) $_GET['country_id'] : 0;
        $maxPrice = isset($_GET['max_price']) ? (float) $_GET['max_price'] : 0;

        if ($serviceId <= 0) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Invalid service_id']);
            return;
        }

        $config = require __DIR__ . '/../../config/config.php';
        $apiKey = $config['smspool']['api_key'] ?? '';
        $baseUrl = rtrim($config['smspool']['base_url'] ?? '', '/');

        if ($apiKey === '' || $baseUrl === '') {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'API key not configured']);
            return;
        }

        $payload = [
            'key' => $apiKey,
            'service' => $serviceId,
        ];
        if ($countryId > 0) {
            $payload['country'] = $countryId;
        }
        if ($maxPrice > 0) {
            $payload['max_price'] = $maxPrice;
        }

        $ch = curl_init($baseUrl . '/request/pricing');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
            ],
        ]);
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $status >= 400) {
            http_response_code(502);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Upstream API error']);
            return;
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            http_response_code(502);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Invalid API response']);
            return;
        }

        $rows = $data;
        if (isset($data['data']) && is_array($data['data'])) {
            $rows = $data['data'];
        }

        $serviceCountry = new ServiceCountry();
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $serviceId = $row['service'] ?? $row['service_id'] ?? null;
            $countryId = $row['country'] ?? $row['country_id'] ?? null;
            $price = $row['price'] ?? $row['cost'] ?? null;
            if ($serviceId === null || $countryId === null || $price === null) {
                continue;
            }
            $stock = null;
            if (isset($row['stock'])) {
                $stock = (int) $row['stock'];
            } elseif (isset($row['available'])) {
                $stock = (int) $row['available'];
            } elseif (isset($row['qty'])) {
                $stock = (int) $row['qty'];
            } elseif (isset($row['count'])) {
                $stock = (int) $row['count'];
            }
            $serviceCountry->upsertPricing(
                (int) $serviceId,
                (int) $countryId,
                (float) $price,
                $stock
            );
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['data' => $data]);
    }

    public function smspoolStock()
    {
        Auth::requireLogin();
        $serviceId = isset($_GET['service_id']) ? (int) $_GET['service_id'] : 0;
        $countryId = isset($_GET['country_id']) ? (int) $_GET['country_id'] : 0;

        if ($serviceId <= 0 || $countryId <= 0) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Invalid service_id or country_id']);
            return;
        }

        $config = require __DIR__ . '/../../config/config.php';
        $apiKey = $config['smspool']['api_key'] ?? '';
        $baseUrl = rtrim($config['smspool']['base_url'] ?? '', '/');

        if ($apiKey === '' || $baseUrl === '') {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'API key not configured']);
            return;
        }

        $payload = [
            'key' => $apiKey,
            'service' => $serviceId,
            'country' => $countryId,
        ];

        $ch = curl_init($baseUrl . '/sms/stock');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
            ],
        ]);
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $status >= 400) {
            http_response_code(502);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Upstream API error']);
            return;
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            http_response_code(502);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Invalid API response']);
            return;
        }

        if (isset($data['success']) && (int) $data['success'] === 0) {
            http_response_code(502);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'error' => $data['message'] ?? 'Stock lookup failed',
                'raw' => $data,
            ]);
            return;
        }

        $stock = null;
        if (isset($data['amount'])) {
            $stock = (int) $data['amount'];
        } else {
            $rows = $data;
            if (isset($data['data']) && is_array($data['data'])) {
                $rows = $data['data'];
            }
            foreach ($rows as $row) {
                if (!is_array($row)) {
                    continue;
                }
                if ((int) ($row['service'] ?? 0) !== $serviceId || (int) ($row['country'] ?? 0) !== $countryId) {
                    continue;
                }
                if (isset($row['stock'])) {
                    $stock = (int) $row['stock'];
                    break;
                }
                if (isset($row['available'])) {
                    $stock = (int) $row['available'];
                    break;
                }
                if (isset($row['qty'])) {
                    $stock = (int) $row['qty'];
                    break;
                }
                if (isset($row['count'])) {
                    $stock = (int) $row['count'];
                    break;
                }
            }
        }

        if ($stock !== null) {
            (new ServiceCountry())->updateStock($serviceId, $countryId, $stock);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['stock' => $stock, 'raw' => $data]);
    }

    public function smspoolOrderSms()
    {
        Auth::requireLogin();
        $serviceId = isset($_POST['service_id']) ? (int) $_POST['service_id'] : 0;
        $countryId = isset($_POST['country_id']) ? (int) $_POST['country_id'] : 0;
        $pricingOption = isset($_POST['pricing_option']) ? (int) $_POST['pricing_option'] : 1;
        $maxPrice = trim($_POST['max_price'] ?? '');
        $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

        if ($serviceId <= 0 || $countryId <= 0) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Invalid service_id or country_id']);
            return;
        }

        $config = require __DIR__ . '/../../config/config.php';
        $apiKey = $config['smspool']['api_key'] ?? '';
        $baseUrl = rtrim($config['smspool']['base_url'] ?? '', '/');

        if ($apiKey === '' || $baseUrl === '') {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'API key not configured']);
            return;
        }

        $serviceCountry = new ServiceCountry();
        $customPrice = $serviceCountry->getEffectivePrice($serviceId, $countryId);
        if ($customPrice === null) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Pricing unavailable']);
            return;
        }
        $walletModel = new WalletTransaction();
        $currentBalance = $walletModel->getNetByUserId(Auth::user()['id']);
        $quantityValue = $quantity > 0 ? $quantity : 1;
        $expectedTotal = $customPrice * $quantityValue;
        if ($expectedTotal > $currentBalance) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'error' => 'Insufficient balance',
                'balance' => $currentBalance,
                'required' => $expectedTotal,
            ]);
            return;
        }

        $payload = [
            'key' => $apiKey,
            'country' => $countryId,
            'service' => $serviceId,
            'pricing_option' => $pricingOption,
            'quantity' => $quantity > 0 ? $quantity : 1,
        ];
        if ($maxPrice !== '') {
            $payload['max_price'] = $maxPrice;
        }

        $ch = curl_init($baseUrl . '/purchase/sms');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
            ],
        ]);
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            http_response_code(502);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Upstream API error']);
            return;
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            http_response_code(502);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Invalid API response']);
            return;
        }

        if (isset($data['success']) && (int) $data['success'] === 0) {
            $message = 'Purchase failed';
            $poolErrors = [];
            if (!empty($data['pools']) && is_array($data['pools'])) {
                foreach ($data['pools'] as $poolName => $poolData) {
                    if (!is_array($poolData)) {
                        continue;
                    }
                    $poolMessage = '';
                    if (!empty($poolData['errors']) && is_array($poolData['errors'])) {
                        $first = $poolData['errors'][0] ?? null;
                        if (is_array($first) && !empty($first['message'])) {
                            $poolMessage = $first['message'];
                        }
                    }
                    if ($poolMessage === '' && !empty($poolData['message'])) {
                        $poolMessage = strip_tags($poolData['message']);
                    }
                    if ($poolMessage !== '') {
                        $poolErrors[] = [
                            'pool' => (string) $poolName,
                            'message' => $poolMessage,
                        ];
                    }
                }
                $poolNames = array_keys($data['pools']);
                if (!empty($poolNames)) {
                    $message = 'Out of stock across pools (' . implode(', ', $poolNames) . '). Please try again later.';
                }
            } elseif (!empty($data['message'])) {
                $message = strip_tags($data['message']);
            }
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'error' => $message,
                'pools' => $poolErrors,
                'raw' => $data,
                'status' => $status,
            ]);
            return;
        }

        if ($status >= 400) {
            http_response_code(502);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Upstream API error', 'status' => $status]);
            return;
        }

        $orders = $this->extractOrdersFromResponse($data);
        if (empty($orders)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'No phone number returned', 'raw' => $data]);
            return;
        }

        $user = Auth::user();
        $walletModel = new WalletTransaction();
        $currentBalance = $walletModel->getNetByUserId($user['id']);
        $totalCost = 0;
        foreach ($orders as $order) {
            $price = $customPrice !== null ? $customPrice : (float) ($order['price'] ?? 0);
            $totalCost += (float) $price;
        }
        if ($totalCost > $currentBalance) {
            foreach ($orders as $order) {
                $providerOrderId = $order['order_id'];
                $this->cancelProviderOrder((string) $providerOrderId);
            }
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'error' => 'Insufficient balance',
                'balance' => $currentBalance,
                'required' => $totalCost,
            ]);
            return;
        }
        $country = (new Country())->getById($countryId);
        $countryName = $country ? $country['country_name'] : (string) $countryId;
        $orderModel = new Order();
        $created = [];

        foreach ($orders as $order) {
            $phoneNumber = $order['phone'];
            $providerOrderId = $order['order_id'];
            $cost = $customPrice !== null ? $customPrice : $order['price'];
            $localOrderId = $orderModel->create(
                $user['id'],
                $serviceId,
                $countryName,
                $phoneNumber,
                $cost,
                $providerOrderId,
                $quantity
            );
            $walletModel->create(
                $user['id'],
                'withdraw',
                (float) $cost,
                'completed',
                'SMS order #' . $localOrderId
            );
            $created[] = [
                'id' => $localOrderId,
                'provider_order_id' => $providerOrderId,
                'phone_number' => $phoneNumber,
                'price' => $cost,
                'quantity' => $quantity,
            ];
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['data' => $created]);
    }

    public function smspoolCheckSms()
    {
        Auth::requireLogin();
        $providerOrderId = trim($_POST['order_id'] ?? '');

        if ($providerOrderId === '') {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Missing order_id']);
            return;
        }

        $config = require __DIR__ . '/../../config/config.php';
        $apiKey = $config['smspool']['api_key'] ?? '';
        $baseUrl = rtrim($config['smspool']['base_url'] ?? '', '/');

        if ($apiKey === '' || $baseUrl === '') {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'API key not configured']);
            return;
        }

        $payload = [
            'key' => $apiKey,
            'orderid' => $providerOrderId,
        ];

        $ch = curl_init($baseUrl . '/sms/check');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
            ],
        ]);
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $status >= 400) {
            http_response_code(502);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Upstream API error']);
            return;
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            http_response_code(502);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Invalid API response']);
            return;
        }

        $code = $this->extractSmsCode($data);
        if ($code !== null) {
            $orderModel = new Order();
            $order = $orderModel->getByProviderOrderId($providerOrderId);
            if ($order) {
                $smsModel = new Sms();
                if (!$smsModel->existsForOrderId($order['id'], $code)) {
                    $smsModel->create($order['id'], $code, $data['message'] ?? 'SMS received');
                }
                if (($order['status'] ?? '') !== 'completed') {
                    $orderModel->updateStatusByProviderOrderId($providerOrderId, 'completed');
                    $userModel = new User();
                    $orderUser = $userModel->getById($order['user_id']);
                    if ($orderUser) {
                        $affiliateModel = new Affiliate();
                        $registration = $affiliateModel->getRegistrationByUsername($orderUser['username']);
                        if ($registration) {
                            $commission = round(((float) $order['cost']) * 0.05, 2);
                            if ($commission > 0) {
                                $affiliateModel->addRegistrationEarnings((int) $registration['id'], $commission);
                                $affiliateModel->addAffiliateEarnings((int) $registration['affiliate_id'], $commission);
                            }
                        }
                    }
                }
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['data' => $data, 'code' => $code]);
    }

    public function smspoolCancelSms()
    {
        Auth::requireLogin();
        $providerOrderId = trim($_POST['order_id'] ?? '');
        $localOrderId = isset($_POST['local_id']) ? (int) $_POST['local_id'] : 0;

        if ($providerOrderId === '') {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Missing order_id']);
            return;
        }

        $this->handleSmspoolCancel($providerOrderId, $localOrderId);
    }

    public function smspoolCancelLocal()
    {
        Auth::requireLogin();
        $localOrderId = isset($_POST['local_id']) ? (int) $_POST['local_id'] : 0;

        if ($localOrderId <= 0) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Missing local_id']);
            return;
        }

        $orderModel = new Order();
        $order = $orderModel->getById($localOrderId);
        $providerOrderId = $order && !empty($order['provider_order_id']) ? (string) $order['provider_order_id'] : '';
        if ($providerOrderId === '') {
            $this->refundOrderIfNeeded($order);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'data' => [
                    'success' => true,
                    'message' => 'Cancelled locally. Provider order id not available.',
                ],
            ]);
            return;
        }

        $this->handleSmspoolCancel($providerOrderId, $localOrderId);
    }

    private function handleSmspoolCancel($providerOrderId, $localOrderId = 0)
    {
        $config = require __DIR__ . '/../../config/config.php';
        $apiKey = $config['smspool']['api_key'] ?? '';
        $baseUrl = rtrim($config['smspool']['base_url'] ?? '', '/');

        if ($apiKey === '' || $baseUrl === '') {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'API key not configured']);
            return;
        }

        $payload = [
            'key' => $apiKey,
            'orderid' => $providerOrderId,
        ];

        $ch = curl_init($baseUrl . '/sms/cancel');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
            ],
        ]);
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $status >= 400) {
            http_response_code(502);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Upstream API error']);
            return;
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            http_response_code(502);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Invalid API response']);
            return;
        }

        if (!empty($data['success'])) {
            $orderModel = new Order();
            $order = null;
            if ($localOrderId > 0) {
                $order = $orderModel->getById($localOrderId);
            }
            if (!$order) {
                $order = $orderModel->getByProviderOrderId($providerOrderId);
            }
            $this->refundOrderIfNeeded($order);
            $orderModel->updateStatusByProviderOrderId($providerOrderId, 'cancelled');
            if ($localOrderId > 0) {
                $orderModel->updateStatusById($localOrderId, 'cancelled');
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['data' => $data]);
    }

    private function cancelProviderOrder($providerOrderId)
    {
        $config = require __DIR__ . '/../../config/config.php';
        $apiKey = $config['smspool']['api_key'] ?? '';
        $baseUrl = rtrim($config['smspool']['base_url'] ?? '', '/');

        if ($apiKey === '' || $baseUrl === '') {
            return;
        }

        $payload = [
            'key' => $apiKey,
            'orderid' => $providerOrderId,
        ];

        $ch = curl_init($baseUrl . '/sms/cancel');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
            ],
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    private function refundOrderIfNeeded($order)
    {
        if (!$order) {
            return;
        }
        if (($order['status'] ?? '') === 'cancelled') {
            return;
        }
        $amount = isset($order['cost']) ? (float) $order['cost'] : 0;
        if ($amount <= 0) {
            return;
        }
        $walletModel = new WalletTransaction();
        $walletModel->create(
            $order['user_id'],
            'deposit',
            $amount,
            'completed',
            'Refund SMS order #' . $order['id']
        );
        $orderModel = new Order();
        $orderModel->updateStatusById($order['id'], 'cancelled');
    }

    private function extractOrdersFromResponse($data)
    {
        $orders = [];
        $items = $data;
        if (isset($data['data']) && is_array($data['data'])) {
            $items = $data['data'];
        }
        if (isset($items['order_id']) || isset($items['orderid']) || isset($items['phone'])) {
            $items = [$items];
        }
        if (!is_array($items)) {
            return $orders;
        }
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $phone = $item['phone'] ?? $item['number'] ?? $item['phone_number'] ?? $item['phonenumber'] ?? null;
            $orderId = $item['order_id']
                ?? $item['orderid']
                ?? $item['orderId']
                ?? $item['order']
                ?? $item['order_code']
                ?? $item['id']
                ?? null;
            $price = $item['price'] ?? $item['cost'] ?? 0;
            if ($phone === null || $orderId === null) {
                continue;
            }
            $orders[] = [
                'phone' => $phone,
                'order_id' => $orderId,
                'price' => (float) $price,
            ];
        }
        return $orders;
    }

    private function extractSmsCode($data)
    {
        $candidates = ['code', 'sms', 'otp', 'text', 'message'];
        foreach ($candidates as $key) {
            if (isset($data[$key]) && is_string($data[$key])) {
                if ($key === 'message') {
                    if (preg_match('/\\b(\\d{4,8})\\b/', $data[$key], $matches)) {
                        return $matches[1];
                    }
                    continue;
                }
                if (preg_match('/\\b(\\d{4,8})\\b/', $data[$key], $matches)) {
                    return $matches[1];
                }
            }
        }
        return null;
    }
}

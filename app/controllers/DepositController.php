<?php
class DepositController extends Controller
{
    private function respondJson($payload, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }

    private function cryptomusRequest($payload, $config, $url = null)
    {
        $body = json_encode($payload);
        $signature = md5(base64_encode($body) . $config['cryptomus']['api_key']);

        $endpoint = $url ?: $config['cryptomus']['payment_url'];
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'merchant: ' . $config['cryptomus']['merchant_id'],
                'sign: ' . $signature,
            ],
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_TIMEOUT => 20,
        ]);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'message' => $error];
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            return ['success' => false, 'message' => 'Invalid response from Cryptomus.'];
        }

        return $data;
    }

    public function index()
    {
        Auth::requireLogin();
        $user = Auth::user();
        $deposits = (new Deposit())->getByUserId($user['id']);
        $transactions = (new WalletTransaction())->getByUserIdAndType($user['id'], 'deposit');
        $this->view->render('deposit/index', [
            'pageTitle' => 'Deposit',
            'deposits' => $deposits,
            'transactions' => $transactions,
        ]);
    }

    public function store()
    {
        Auth::requireLogin();
        $user = Auth::user();

        $amount = isset($_POST['amount']) ? (float) $_POST['amount'] : 0;
        $method = trim($_POST['method'] ?? '');
        $allowedMethods = ['Cryptomus', 'Stripe', 'Bank Transfer'];

        if ($amount <= 0 || !in_array($method, $allowedMethods, true)) {
            Auth::redirect('/deposit?error=1');
        }

        (new Deposit())->create($user['id'], $amount, $method);
        (new WalletTransaction())->create($user['id'], 'deposit', $amount, 'completed', $method);
        Auth::redirect('/deposit?success=1');
    }

    public function cryptomusInvoice()
    {
        Auth::requireLogin();
        $user = Auth::user();
        $config = require __DIR__ . '/../../config/config.php';

        $input = json_decode(file_get_contents('php://input'), true);
        $amount = isset($input['amount']) ? (float) $input['amount'] : 0;
        $crypto = trim($input['crypto'] ?? '');
        $network = trim($input['network'] ?? '');

        if ($amount <= 0) {
            $this->respondJson(['success' => false, 'message' => 'Invalid amount.'], 422);
        }

        if ($crypto === '' || $network === '') {
            $this->respondJson(['success' => false, 'message' => 'Invalid crypto selection.'], 422);
        }

        if (empty($config['cryptomus']['merchant_id']) || empty($config['cryptomus']['api_key'])) {
            $this->respondJson(['success' => false, 'message' => 'Cryptomus is not configured.'], 500);
        }

        $orderId = 'dep-' . $user['id'] . '-' . time() . '-' . mt_rand(1000, 9999);
        $payload = [
            'amount' => number_format($amount, 2, '.', ''),
            'currency' => strtoupper($crypto),
            'network' => strtoupper($network),
            'order_id' => $orderId,
            'url_callback' => rtrim($config['app']['base_url_full'], '/') . '/deposit/cryptomus-webhook',
            'url_return' => rtrim($config['app']['base_url_full'], '/') . '/deposit?success=1',
        ];

        $response = $this->cryptomusRequest($payload, $config);
        if (empty($response['result'])) {
            $message = $response['message'] ?? 'Cryptomus error.';
            $this->respondJson(['success' => false, 'message' => $message], 500);
        }

        (new Deposit())->create($user['id'], $amount, 'Cryptomus');
        (new WalletTransaction())->create(
            $user['id'],
            'deposit',
            $amount,
            'pending',
            'cryptomus_order:' . $orderId
        );

        $result = $response['result'];
        $qrCode = $result['qr_code'] ?? '';
        $this->respondJson([
            'success' => true,
            'qr_code' => $qrCode,
            'invoice_id' => $result['uuid'] ?? '',
            'pay_url' => $result['url'] ?? '',
        ]);
    }

    public function cryptomusWebhook()
    {
        $config = require __DIR__ . '/../../config/config.php';
        $rawBody = file_get_contents('php://input');
        $payload = json_decode($rawBody, true);
        if (!is_array($payload)) {
            http_response_code(400);
            echo 'Invalid payload';
            return;
        }
        $signature = $payload['sign'] ?? '';
        unset($payload['sign']);
        $expected = md5(
            base64_encode(json_encode($payload))
            . $config['cryptomus']['api_key']
        );

        if (!hash_equals($expected, $signature)) {
            http_response_code(403);
            echo 'Invalid signature';
            return;
        }

        $orderId = $payload['order_id'] ?? '';
        $status = strtolower($payload['status'] ?? '');
        if ($orderId === '') {
            http_response_code(400);
            echo 'Missing order_id';
            return;
        }

        $note = 'cryptomus_order:' . $orderId;
        $transaction = (new WalletTransaction())->getByNote($note);
        if (!$transaction) {
            http_response_code(404);
            echo 'Transaction not found';
            return;
        }

        if (in_array($status, ['paid', 'paid_over'], true)) {
            if (($transaction['status'] ?? '') === 'completed') {
                echo 'OK';
                return;
            }
            $paidAmount = 0;
            if (isset($payload['payment_amount']) && is_numeric($payload['payment_amount'])) {
                $paidAmount = (float) $payload['payment_amount'];
            } elseif (isset($payload['merchant_amount']) && is_numeric($payload['merchant_amount'])) {
                $paidAmount = (float) $payload['merchant_amount'];
            } elseif (isset($payload['amount']) && is_numeric($payload['amount'])) {
                $paidAmount = (float) $payload['amount'];
            }
            if ($paidAmount > 0) {
                (new WalletTransaction())->updateAmountStatusByNote($note, $paidAmount, 'completed');
            } else {
                (new WalletTransaction())->updateStatusByNote($note, 'completed');
            }

            $user = (new User())->getById($transaction['user_id']);
            if (!empty($user['email'])) {
                $amountValue = $paidAmount > 0 ? $paidAmount : (float) $transaction['amount'];
                $subject = 'Deposit successful';
                $html = '<p>Your deposit was successful.</p>'
                    . '<p>Order: <strong>' . htmlspecialchars($orderId, ENT_QUOTES, 'UTF-8') . '</strong></p>'
                    . '<p>Amount: <strong>$' . number_format($amountValue, 2) . '</strong></p>';
                $text = "Your deposit was successful.\nOrder: {$orderId}\nAmount: $" . number_format($amountValue, 2);
                Mailer::send($user['email'], $subject, $html, $text);
            }
        } elseif (in_array($status, ['cancel', 'failed', 'expired'], true)) {
            (new WalletTransaction())->updateStatusByNote($note, 'failed');
        } elseif (in_array($status, ['fail', 'wrong_amount', 'system_fail'], true)) {
            (new WalletTransaction())->updateStatusByNote($note, 'failed');
        }

        echo 'OK';
    }

    public function cryptomusServices()
    {
        Auth::requireLogin();
        $config = require __DIR__ . '/../../config/config.php';

        if (empty($config['cryptomus']['merchant_id']) || empty($config['cryptomus']['api_key'])) {
            $this->respondJson(['success' => false, 'message' => 'Cryptomus is not configured.'], 500);
        }

        $servicesUrl = $config['cryptomus']['services_url'] ?? 'https://api.cryptomus.com/v1/payment/services';
        $response = $this->cryptomusRequest([], $config, $servicesUrl);
        if (empty($response['result']) || !is_array($response['result'])) {
            $message = $response['message'] ?? 'Cryptomus error.';
            $this->respondJson(['success' => false, 'message' => $message], 500);
        }

        $items = $response['result'];
        $coins = [];
        $networksByCoin = [];
        foreach ($items as $item) {
            $currency = $item['currency'] ?? $item['code'] ?? '';
            $network = $item['network'] ?? $item['chain'] ?? '';
            if ($currency === '' || $network === '') {
                continue;
            }
            $currencyKey = strtolower($currency);
            $networkKey = strtolower($network);
            if (!isset($coins[$currencyKey])) {
                $coins[$currencyKey] = [
                    'value' => $currencyKey,
                    'label' => strtoupper($currency),
                ];
            }
            if (!isset($networksByCoin[$currencyKey])) {
                $networksByCoin[$currencyKey] = [];
            }
            if (!isset($networksByCoin[$currencyKey][$networkKey])) {
                $networksByCoin[$currencyKey][$networkKey] = [
                    'value' => $networkKey,
                    'label' => strtoupper($network),
                ];
            }
        }

        foreach ($networksByCoin as $coinKey => $networks) {
            $networksByCoin[$coinKey] = array_values($networks);
        }

        $this->respondJson([
            'success' => true,
            'coins' => array_values($coins),
            'networks' => $networksByCoin,
        ]);
    }
}

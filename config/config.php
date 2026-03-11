<?php
return [
    'db' => [
        'host' => '127.0.0.1',
        'name' => 'smsallword',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'base_url' => '/smsallword/public/',
        'base_url_full' => 'http://localhost/smsallword/public/',
    ],
    'mail' => [
        'from' => 'no-reply@smsnest.local',
        'from_name' => 'SMSNest',
        'smtp' => [
            'enabled' => true,
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'username' => 'smsnesthub@gmail.com',
            'password' => 'yasy yuhv fkes luex',
            'secure' => 'tls',
        ],
    ],
    'cloudinary' => [
        'enabled' => true,
        'cloud_name' => 'dcakohdin',
        'api_key' => '711635811365674',
        'api_secret' => 'K9EoeOl_XIDU5ite9W_qzNarbmg',
        'folder' => 'smsallword',
    ],
    'smspool' => [
        'base_url' => 'https://api.smspool.net',
        'api_key' => 'awOdAeOvxx9Kfvs0QCewH1dXnvWUM7Mi',
    ],
    'support' => [
        'admin_email' => 'smsnesthub@gmail.com',
        'admin_name' => 'SMSNest Support',
    ],
    'cryptomus' => [
        'merchant_id' => '50d677e6-6184-43be-a72f-752eb017d6e4',
        'api_key' => 'aTCe1bbmYPuBPezAQwsrpgqZ1GJDYMZqDG5H0k9r8RjHxZllJWw80z7vTCXYCaRmtwfdmqKxShFIRgkzIq5uH4hYZShfYQrUMeHAuaf7xMS7a7QuNkYVrHU7PjZVKUFs',
        'payment_url' => 'https://api.cryptomus.com/v1/payment',
        'services_url' => 'https://api.cryptomus.com/v1/payment/services',
    ],
];

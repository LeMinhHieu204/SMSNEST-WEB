<?php
class AuthController extends Controller
{
    public function login()
    {
        if (Auth::check()) {
            Auth::redirectByRole();
        }
        $this->view->render('auth/login', [
            'pageTitle' => 'Login',
            'layout' => 'auth',
        ]);
    }

    public function authenticate()
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $user = (new User())->getByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->view->render('auth/login', [
                'pageTitle' => 'Login',
                'layout' => 'auth',
                'error' => 'Invalid email or password.',
                'email' => $email,
            ]);
            return;
        }

        if (empty($user['email_verified_at'])) {
            $this->view->render('auth/login', [
                'pageTitle' => 'Login',
                'layout' => 'auth',
                'error' => 'Please verify your email before logging in.',
                'email' => $email,
            ]);
            return;
        }

        Auth::login($user);
        Auth::redirectByRole($user);
    }

    public function register()
    {
        if (Auth::check()) {
            Auth::redirectByRole();
        }
        $ref = trim($_GET['ref'] ?? '');
        if ($ref !== '') {
            $_SESSION['affiliate_ref'] = $ref;
        }
        $this->view->render('auth/register', [
            'pageTitle' => 'Register',
            'layout' => 'auth',
        ]);
    }

    public function store()
    {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $email === '' || $password === '') {
            $this->view->render('auth/register', [
                'pageTitle' => 'Register',
                'layout' => 'auth',
                'error' => 'Please fill in all required fields.',
                'username' => $username,
                'email' => $email,
            ]);
            return;
        }

        $userModel = new User();
        if ($userModel->existsByEmail($email)) {
            $this->view->render('auth/register', [
                'pageTitle' => 'Register',
                'layout' => 'auth',
                'error' => 'Email already exists.',
                'username' => $username,
                'email' => $email,
            ]);
            return;
        }
        $userId = $userModel->create($username, $email, $password);
        (new Balance())->createForUser($userId);
        $token = bin2hex(random_bytes(32));
        $userModel->setVerificationToken($userId, $token);
        $config = require __DIR__ . '/../../config/config.php';
        $baseUrl = rtrim($config['app']['base_url_full'] ?? ($config['app']['base_url'] ?? ''), '/');
        $verifyLink = $baseUrl . '/verify-email?token=' . $token;
        $subject = 'Verify your SMSNest account';
        $body = '<!doctype html>'
            . '<html><head><meta charset="utf-8"><title>Verify your email</title></head>'
            . '<body style="margin:0;padding:0;background:#0f1117;font-family:Segoe UI,Arial,sans-serif;color:#e8e8ea;">'
            . '<div style="max-width:520px;margin:24px auto;background:#1b1f27;border:1px solid #2a2f3a;border-radius:12px;padding:20px;">'
            . '<div style="font-size:18px;font-weight:700;margin-bottom:6px;">SMSNest</div>'
            . '<div style="font-size:16px;font-weight:600;margin-bottom:10px;">Verify your email</div>'
            . '<div style="color:#9aa0aa;margin-bottom:14px;">Hello ' . htmlspecialchars($username) . ', please confirm your email to activate your account.</div>'
            . '<div style="margin-bottom:14px;"><a href="' . htmlspecialchars($verifyLink) . '" style="display:inline-block;background:#3b82f6;color:#fff;text-decoration:none;padding:10px 14px;border-radius:8px;font-weight:600;">Verify Email</a></div>'
            . '<div style="color:#9aa0aa;font-size:12px;">If you did not request this, you can safely ignore this email.</div>'
            . '</div></body></html>';
        $textBody = "Verify your email\n\nHello {$username},\nPlease verify your email by visiting:\n{$verifyLink}\n\nIf you did not request this, you can ignore this email.";
        Mailer::send($email, $subject, $body, $textBody);
        $ref = $_SESSION['affiliate_ref'] ?? '';
        if ($ref !== '') {
            $affiliateModel = new Affiliate();
            $affiliate = $affiliateModel->getByPromoCode($ref);
            if ($affiliate && (int) $affiliate['user_id'] !== (int) $userId) {
                $affiliateModel->addRegistration((int) $affiliate['id'], $username, 0);
                $affiliateModel->incrementTotals((int) $affiliate['id'], 1, 0);
            }
            unset($_SESSION['affiliate_ref']);
        }
        $this->view->render('auth/verify_notice', [
            'pageTitle' => 'Verify Email',
            'layout' => 'auth',
            'email' => $email,
        ]);
    }

    public function verifyEmail()
    {
        $token = trim($_GET['token'] ?? '');
        if ($token === '') {
            $this->view->render('auth/verify_result', [
                'pageTitle' => 'Verify Email',
                'layout' => 'auth',
                'error' => 'Invalid verification link.',
            ]);
            return;
        }
        $userModel = new User();
        $user = $userModel->getByVerificationToken($token);
        if (!$user) {
            $this->view->render('auth/verify_result', [
                'pageTitle' => 'Verify Email',
                'layout' => 'auth',
                'error' => 'Verification link expired or invalid.',
            ]);
            return;
        }
        $userModel->markEmailVerified($user['id']);
        $this->view->render('auth/verify_result', [
            'pageTitle' => 'Verify Email',
            'layout' => 'auth',
            'success' => 'Email verified successfully. You can now log in.',
        ]);
    }

    public function logout()
    {
        Auth::logout();
        Auth::redirect('/login');
    }

    public function forgotPassword()
    {
        if (Auth::check()) {
            Auth::redirectByRole();
        }
        $this->view->render('auth/forgot_password', [
            'pageTitle' => 'Forgot Password',
            'layout' => 'auth',
        ]);
    }

    public function sendPasswordReset()
    {
        if (Auth::check()) {
            Auth::redirectByRole();
        }
        $email = trim($_POST['email'] ?? '');
        if ($email === '') {
            $this->view->render('auth/forgot_password', [
                'pageTitle' => 'Forgot Password',
                'layout' => 'auth',
                'error' => 'Please enter your email address.',
            ]);
            return;
        }

        $userModel = new User();
        $user = $userModel->getByEmail($email);
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 3600);
            $userModel->setPasswordResetToken((int) $user['id'], $token, $expiresAt);
            $config = require __DIR__ . '/../../config/config.php';
            $baseUrl = rtrim($config['app']['base_url_full'] ?? ($config['app']['base_url'] ?? ''), '/');
            $resetLink = $baseUrl . '/reset-password?token=' . $token;
            $subject = 'Reset your SMSNest password';
            $body = '<!doctype html>'
                . '<html><head><meta charset="utf-8"><title>Password reset</title></head>'
                . '<body style="margin:0;padding:0;background:#0f1117;font-family:Segoe UI,Arial,sans-serif;color:#e8e8ea;">'
                . '<div style="max-width:520px;margin:24px auto;background:#1b1f27;border:1px solid #2a2f3a;border-radius:12px;padding:20px;">'
                . '<div style="font-size:18px;font-weight:700;margin-bottom:6px;">SMSNest</div>'
                . '<div style="font-size:16px;font-weight:600;margin-bottom:10px;">Reset your password</div>'
                . '<div style="color:#9aa0aa;margin-bottom:14px;">Hello ' . htmlspecialchars($user['username']) . ', click the button below to reset your password. This link expires in 60 minutes.</div>'
                . '<div style="margin-bottom:14px;"><a href="' . htmlspecialchars($resetLink) . '" style="display:inline-block;background:#3b82f6;color:#fff;text-decoration:none;padding:10px 14px;border-radius:8px;font-weight:600;">Reset Password</a></div>'
                . '<div style="color:#9aa0aa;font-size:12px;">If you did not request this, you can safely ignore this email.</div>'
                . '</div></body></html>';
            $textBody = "Reset your password\n\nHello {$user['username']},\nReset your password using this link (valid for 60 minutes):\n{$resetLink}\n\nIf you did not request this, you can ignore this email.";
            Mailer::send($email, $subject, $body, $textBody);
        }

        $this->view->render('auth/forgot_password', [
            'pageTitle' => 'Forgot Password',
            'layout' => 'auth',
            'success' => 'If that email exists, a reset link has been sent.',
            'email' => $email,
        ]);
    }

    public function resetPassword()
    {
        if (Auth::check()) {
            Auth::redirectByRole();
        }
        $token = trim($_GET['token'] ?? '');
        if ($token === '') {
            $this->view->render('auth/reset_password', [
                'pageTitle' => 'Reset Password',
                'layout' => 'auth',
                'error' => 'Invalid reset link.',
            ]);
            return;
        }
        $userModel = new User();
        $user = $userModel->getByPasswordResetToken($token);
        $expiresAt = $user['password_reset_expires'] ?? null;
        $isExpired = !$user || !$expiresAt || (strtotime($expiresAt) !== false && strtotime($expiresAt) < time());
        if ($isExpired) {
            if ($user) {
                $userModel->clearPasswordResetToken((int) $user['id']);
            }
            $this->view->render('auth/reset_password', [
                'pageTitle' => 'Reset Password',
                'layout' => 'auth',
                'error' => 'Reset link expired or invalid.',
            ]);
            return;
        }
        $this->view->render('auth/reset_password', [
            'pageTitle' => 'Reset Password',
            'layout' => 'auth',
            'token' => $token,
        ]);
    }

    public function updatePassword()
    {
        if (Auth::check()) {
            Auth::redirectByRole();
        }
        $token = trim($_POST['token'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if ($token === '') {
            $this->view->render('auth/reset_password', [
                'pageTitle' => 'Reset Password',
                'layout' => 'auth',
                'error' => 'Invalid reset request.',
            ]);
            return;
        }
        if ($password === '' || $confirm === '') {
            $this->view->render('auth/reset_password', [
                'pageTitle' => 'Reset Password',
                'layout' => 'auth',
                'error' => 'Please fill out all password fields.',
                'token' => $token,
            ]);
            return;
        }
        if ($password !== $confirm) {
            $this->view->render('auth/reset_password', [
                'pageTitle' => 'Reset Password',
                'layout' => 'auth',
                'error' => 'Password confirmation does not match.',
                'token' => $token,
            ]);
            return;
        }
        if (strlen($password) < 6) {
            $this->view->render('auth/reset_password', [
                'pageTitle' => 'Reset Password',
                'layout' => 'auth',
                'error' => 'Password must be at least 6 characters.',
                'token' => $token,
            ]);
            return;
        }
        $userModel = new User();
        $user = $userModel->getByPasswordResetToken($token);
        $expiresAt = $user['password_reset_expires'] ?? null;
        $isExpired = !$user || !$expiresAt || (strtotime($expiresAt) !== false && strtotime($expiresAt) < time());
        if ($isExpired) {
            if ($user) {
                $userModel->clearPasswordResetToken((int) $user['id']);
            }
            $this->view->render('auth/reset_password', [
                'pageTitle' => 'Reset Password',
                'layout' => 'auth',
                'error' => 'Reset link expired or invalid.',
            ]);
            return;
        }
        $userModel->updatePassword((int) $user['id'], $password);
        $userModel->clearPasswordResetToken((int) $user['id']);
        $this->view->render('auth/reset_password', [
            'pageTitle' => 'Reset Password',
            'layout' => 'auth',
            'success' => 'Password updated. You can now sign in.',
        ]);
    }
}

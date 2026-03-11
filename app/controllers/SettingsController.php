<?php
class SettingsController extends Controller
{
    public function index()
    {
        Auth::requireLogin();
        $user = Auth::user();
        $this->view->render('settings/index', [
            'pageTitle' => 'Settings',
            'user' => $user,
        ]);
    }

    public function update()
    {
        Auth::requireLogin();
        $user = Auth::user();
        $userModel = new User();
        $action = trim($_POST['action'] ?? '');

        if ($action === 'profile') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            if ($username === '' || $email === '') {
                $this->view->render('settings/index', [
                    'pageTitle' => 'Settings',
                    'user' => $user,
                    'error' => 'Please fill out username and email.',
                ]);
                return;
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->view->render('settings/index', [
                    'pageTitle' => 'Settings',
                    'user' => $user,
                    'error' => 'Please enter a valid email address.',
                ]);
                return;
            }
            $existing = $userModel->getByEmail($email);
            if ($existing && (int) $existing['id'] !== (int) $user['id']) {
                $this->view->render('settings/index', [
                    'pageTitle' => 'Settings',
                    'user' => $user,
                    'error' => 'Email already in use.',
                ]);
                return;
            }
            $userModel->updateProfile($user['id'], $username, $email);
            $user = $userModel->getById($user['id']);
            $this->view->render('settings/index', [
                'pageTitle' => 'Settings',
                'user' => $user,
                'success' => 'Profile updated.',
            ]);
            return;
        }

        if ($action === 'password') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
                $this->view->render('settings/index', [
                    'pageTitle' => 'Settings',
                    'user' => $user,
                    'error' => 'Please fill out all password fields.',
                ]);
                return;
            }
            if (!password_verify($currentPassword, $user['password_hash'])) {
                $this->view->render('settings/index', [
                    'pageTitle' => 'Settings',
                    'user' => $user,
                    'error' => 'Current password is incorrect.',
                ]);
                return;
            }
            if ($newPassword !== $confirmPassword) {
                $this->view->render('settings/index', [
                    'pageTitle' => 'Settings',
                    'user' => $user,
                    'error' => 'New password confirmation does not match.',
                ]);
                return;
            }
            if (strlen($newPassword) < 6) {
                $this->view->render('settings/index', [
                    'pageTitle' => 'Settings',
                    'user' => $user,
                    'error' => 'New password must be at least 6 characters.',
                ]);
                return;
            }
            $userModel->updatePassword($user['id'], $newPassword);
            $user = $userModel->getById($user['id']);
            $this->view->render('settings/index', [
                'pageTitle' => 'Settings',
                'user' => $user,
                'success' => 'Password updated.',
            ]);
            return;
        }

        if ($action === 'avatar') {
            if (empty($_FILES['avatar']) || !isset($_FILES['avatar']['tmp_name'])) {
                $this->view->render('settings/index', [
                    'pageTitle' => 'Settings',
                    'user' => $user,
                    'error' => 'Please select an image to upload.',
                ]);
                return;
            }
            $file = $_FILES['avatar'];
            if (!empty($file['error'])) {
                $this->view->render('settings/index', [
                    'pageTitle' => 'Settings',
                    'user' => $user,
                    'error' => 'Upload failed. Please try again.',
                ]);
                return;
            }
            $maxBytes = 2 * 1024 * 1024;
            if ($file['size'] > $maxBytes) {
                $this->view->render('settings/index', [
                    'pageTitle' => 'Settings',
                    'user' => $user,
                    'error' => 'Image must be 2MB or less.',
                ]);
                return;
            }
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
            $allowed = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
            ];
            if (!isset($allowed[$mime])) {
                $this->view->render('settings/index', [
                    'pageTitle' => 'Settings',
                    'user' => $user,
                    'error' => 'Only JPG, PNG, or WEBP images are allowed.',
                ]);
                return;
            }
            require_once __DIR__ . '/../core/Cloudinary.php';
            $avatarPath = null;
            if (Cloudinary::isEnabled()) {
                $avatarPath = Cloudinary::uploadFile($file['tmp_name'], 'avatars');
                if (!$avatarPath) {
                    $this->view->render('settings/index', [
                        'pageTitle' => 'Settings',
                        'user' => $user,
                        'error' => 'Unable to upload the image.',
                    ]);
                    return;
                }
            } else {
                $uploadsRoot = __DIR__ . '/../../public/uploads/avatars';
                if (!is_dir($uploadsRoot)) {
                    mkdir($uploadsRoot, 0755, true);
                }
                $fileName = 'user_' . $user['id'] . '_' . time() . '.' . $allowed[$mime];
                $destPath = $uploadsRoot . '/' . $fileName;
                if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                    $this->view->render('settings/index', [
                        'pageTitle' => 'Settings',
                        'user' => $user,
                        'error' => 'Unable to save the uploaded image.',
                    ]);
                    return;
                }
                $avatarPath = '/uploads/avatars/' . $fileName;
                if (!empty($user['avatar']) && strpos($user['avatar'], '/uploads/avatars/') === 0) {
                    $oldPath = __DIR__ . '/../../public' . $user['avatar'];
                    if (is_file($oldPath)) {
                        unlink($oldPath);
                    }
                }
            }
            $userModel->updateAvatar($user['id'], $avatarPath);
            $user = $userModel->getById($user['id']);
            $this->view->render('settings/index', [
                'pageTitle' => 'Settings',
                'user' => $user,
                'success' => 'Avatar updated.',
            ]);
            return;
        }

        $this->view->render('settings/index', [
            'pageTitle' => 'Settings',
            'user' => $user,
            'error' => 'Invalid action.',
        ]);
    }
}

<?php
class ContactController extends Controller
{
    public function index()
    {
        Auth::requireLogin();
        $user = Auth::user();
        $tickets = (new SupportTicket())->getByUserId($user['id']);
        $this->view->render('contact/index', [
            'pageTitle' => 'Contact',
            'tickets' => $tickets,
        ]);
    }

    public function store()
    {
        Auth::requireLogin();
        $user = Auth::user();
        $config = require __DIR__ . '/../../config/config.php';
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if ($title === '' || $content === '') {
            Auth::redirect('/contact?error=1');
        }

        $imagePath = null;
        if (!empty($_FILES['attachment']) && is_array($_FILES['attachment'])) {
            $file = $_FILES['attachment'];
            if ($file['error'] === UPLOAD_ERR_OK) {
                $tmpName = $file['tmp_name'];
                $maxSize = 3 * 1024 * 1024;
                if ($file['size'] <= $maxSize && @getimagesize($tmpName)) {
                    require_once __DIR__ . '/../core/Cloudinary.php';
                    if (Cloudinary::isEnabled()) {
                        $uploadedUrl = Cloudinary::uploadFile($tmpName, 'support');
                        if ($uploadedUrl) {
                            $imagePath = $uploadedUrl;
                        }
                    } else {
                        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $extension = $extension ? strtolower($extension) : 'png';
                        $safeName = uniqid('support_', true) . '.' . preg_replace('/[^a-z0-9]+/i', '', $extension);
                        $targetDir = __DIR__ . '/../../public/uploads/support';
                        if (!is_dir($targetDir)) {
                            mkdir($targetDir, 0755, true);
                        }
                        $targetPath = $targetDir . '/' . $safeName;
                        if (move_uploaded_file($tmpName, $targetPath)) {
                            $imagePath = '/uploads/support/' . $safeName;
                        }
                    }
                }
            }
        }

        (new SupportTicket())->create(
            (int) $user['id'],
            $user['username'] ?? '',
            $user['email'] ?? '',
            $title,
            $content,
            $imagePath
        );

        $supportEmail = $config['support']['admin_email'] ?? ($config['mail']['from'] ?? '');
        $supportName = $config['support']['admin_name'] ?? 'Support';
        $userEmail = $user['email'] ?? '';
        $username = $user['username'] ?? 'User';
        $subjectUser = 'Support request received';
        $bodyUser = '<!doctype html><html><head><meta charset="utf-8"><title>Support received</title></head><body>'
            . '<p>Hello ' . htmlspecialchars($username) . ',</p>'
            . '<p>We received your support request and will reply shortly.</p>'
            . '<p><strong>Title:</strong> ' . htmlspecialchars($title) . '</p>'
            . '<p><strong>Message:</strong><br>' . nl2br(htmlspecialchars($content)) . '</p>'
            . '<p>Thank you,<br>' . htmlspecialchars($supportName) . '</p>'
            . '</body></html>';
        $textUser = "Hello {$username},\n\nWe received your support request and will reply shortly.\n\nTitle: {$title}\nMessage:\n{$content}\n\nThank you,\n{$supportName}";
        if ($userEmail !== '') {
            Mailer::send($userEmail, $subjectUser, $bodyUser, $textUser);
        }

        if ($supportEmail !== '') {
            $subjectAdmin = 'New support request: ' . $title;
            $bodyAdmin = '<!doctype html><html><head><meta charset="utf-8"><title>New support request</title></head><body>'
                . '<p>New support request submitted.</p>'
                . '<p><strong>User:</strong> ' . htmlspecialchars($username) . '</p>'
                . '<p><strong>Email:</strong> ' . htmlspecialchars($userEmail) . '</p>'
                . '<p><strong>Title:</strong> ' . htmlspecialchars($title) . '</p>'
                . '<p><strong>Message:</strong><br>' . nl2br(htmlspecialchars($content)) . '</p>'
                . '</body></html>';
            $textAdmin = "New support request submitted.\n\nUser: {$username}\nEmail: {$userEmail}\nTitle: {$title}\nMessage:\n{$content}";
            Mailer::send($supportEmail, $subjectAdmin, $bodyAdmin, $textAdmin);
        }

        Auth::redirect('/contact?success=1');
    }
}

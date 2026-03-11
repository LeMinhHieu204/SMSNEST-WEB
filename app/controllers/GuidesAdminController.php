<?php
class GuidesAdminController extends Controller
{
    public function index()
    {
        Auth::requireLogin();
        Auth::requireAdmin();
        $guides = (new Guide())->getAll();
        $this->view->render('admin/guides', [
            'pageTitle' => 'Guides',
            'layout' => 'admin',
            'guides' => $guides,
        ]);
    }

    public function store()
    {
        Auth::requireLogin();
        Auth::requireAdmin();
        $section = trim($_POST['section'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        if ($section === '' || $title === '' || $content === '') {
            Auth::redirect('/admin/guides?error=missing');
        }
        [$content, $imagePath] = $this->processContentImages($content);
        (new Guide())->create($section, $title, $content, $imagePath);
        Auth::redirect('/admin/guides?success=1');
    }

    public function update()
    {
        Auth::requireLogin();
        Auth::requireAdmin();
        $id = (int) ($_POST['id'] ?? 0);
        $section = trim($_POST['section'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        if ($id <= 0 || $section === '' || $title === '' || $content === '') {
            Auth::redirect('/admin/guides?error=missing');
        }
        $guideModel = new Guide();
        $existing = $guideModel->getById($id);
        if (!$existing) {
            Auth::redirect('/admin/guides?error=missing');
        }
        [$content, $imagePath] = $this->processContentImages($content);
        $guideModel->update($id, $section, $title, $content, $imagePath);
        Auth::redirect('/admin/guides?success=1');
    }

    public function delete()
    {
        Auth::requireLogin();
        Auth::requireAdmin();
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            Auth::redirect('/admin/guides?error=missing');
        }
        $guideModel = new Guide();
        $existing = $guideModel->getById($id);
        if ($existing) {
            $this->deleteGuideImages($existing['content'] ?? '', $existing['image_path'] ?? null);
            $guideModel->delete($id);
        }
        Auth::redirect('/admin/guides?success=1');
    }

    private function processContentImages($content)
    {
        require_once __DIR__ . '/../core/Cloudinary.php';
        $cloudEnabled = Cloudinary::isEnabled();
        $uploadsRoot = __DIR__ . '/../../public/uploads/guides';
        if (!$cloudEnabled && !is_dir($uploadsRoot)) {
            mkdir($uploadsRoot, 0755, true);
        }
        $uploaded = $this->collectUploadedImages($uploadsRoot);
        $fallbackIndex = 0;
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        $images = $dom->getElementsByTagName('img');
        $savedFirst = null;
        foreach ($images as $img) {
            $src = $img->getAttribute('src');
            $uploadIndex = $img->getAttribute('data-upload-index');
            if ($uploadIndex !== '' && isset($uploaded[(int) $uploadIndex])) {
                $publicPath = $uploaded[(int) $uploadIndex];
                $img->setAttribute('src', $publicPath);
                $img->removeAttribute('data-upload-index');
                if (!$savedFirst) {
                    $savedFirst = $publicPath;
                }
                continue;
            }
            if (strpos($src, 'blob:') === 0) {
                if (isset($uploaded[$fallbackIndex])) {
                    $publicPath = $uploaded[$fallbackIndex];
                    $img->setAttribute('src', $publicPath);
                    if (!$savedFirst) {
                        $savedFirst = $publicPath;
                    }
                    $fallbackIndex += 1;
                    continue;
                }
                $img->parentNode->removeChild($img);
                continue;
            }
            if (strpos($src, 'data:image/') !== 0) {
                if (!$savedFirst && (strpos($src, '/uploads/guides/') === 0 || preg_match('/^https?:\\/\\//i', $src))) {
                    $savedFirst = $src;
                }
                continue;
            }
            if (!preg_match('/^data:(image\/(png|jpeg|webp));base64,(.+)$/', $src, $match)) {
                $img->parentNode->removeChild($img);
                continue;
            }
            $ext = $match[2] === 'jpeg' ? 'jpg' : $match[2];
            $raw = str_replace(' ', '+', $match[3]);
            $data = base64_decode($raw, true);
            if ($data === false) {
                $img->parentNode->removeChild($img);
                continue;
            }
            if ($cloudEnabled) {
                $publicPath = Cloudinary::uploadBytes($data, 'guides', $ext);
                if (!$publicPath) {
                    $img->parentNode->removeChild($img);
                    continue;
                }
            } else {
                $fileName = 'guide_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $destPath = $uploadsRoot . '/' . $fileName;
                $bytes = file_put_contents($destPath, $data);
                if ($bytes === false) {
                    $img->parentNode->removeChild($img);
                    continue;
                }
                $publicPath = '/uploads/guides/' . $fileName;
            }
            $img->setAttribute('src', $publicPath);
            if (!$savedFirst) {
                $savedFirst = $publicPath;
            }
        }
        $content = $dom->saveHTML();
        $firstImage = $savedFirst ?: $this->extractFirstImagePath($content);
        return [$content, $firstImage];
    }

    private function collectUploadedImages($uploadsRoot)
    {
        if (empty($_FILES['image_files']) || !is_array($_FILES['image_files']['name'])) {
            return [];
        }
        $files = $_FILES['image_files'];
        $result = [];
        foreach ($files['name'] as $index => $name) {
            if (!isset($files['tmp_name'][$index]) || !isset($files['error'][$index])) {
                continue;
            }
            if ($files['error'][$index] !== UPLOAD_ERR_OK) {
                continue;
            }
            $tmpName = $files['tmp_name'][$index];
            $publicPath = $this->saveUploadedImage($tmpName, $uploadsRoot);
            if ($publicPath) {
                $result[(int) $index] = $publicPath;
            }
        }
        return $result;
    }

    private function saveUploadedImage($tmpName, $uploadsRoot)
    {
        $maxBytes = 5 * 1024 * 1024;
        if (!is_file($tmpName)) {
            return null;
        }
        if (filesize($tmpName) > $maxBytes) {
            return null;
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmpName);
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        if (!isset($allowed[$mime])) {
            return null;
        }
        require_once __DIR__ . '/../core/Cloudinary.php';
        if (Cloudinary::isEnabled()) {
            return Cloudinary::uploadFile($tmpName, 'guides');
        }
        $fileName = 'guide_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
        $destPath = $uploadsRoot . '/' . $fileName;
        if (!move_uploaded_file($tmpName, $destPath)) {
            return null;
        }
        return '/uploads/guides/' . $fileName;
    }

    private function extractFirstImagePath($content)
    {
        if (preg_match('/<img[^>]+src=[\"\'](\\/uploads\\/guides\\/[^\"\']+)[\"\']/i', $content, $match)) {
            return $match[1];
        }
        if (preg_match('/<img[^>]+src=[\"\']([^\"\']+)[\"\']/i', $content, $match)) {
            return $match[1];
        }
        return null;
    }

    private function deleteGuideImages($content, $imagePath)
    {
        require_once __DIR__ . '/../core/Cloudinary.php';
        if (Cloudinary::isEnabled()) {
            return;
        }
        $paths = [];
        if ($imagePath && strpos($imagePath, '/uploads/guides/') === 0) {
            $paths[] = $imagePath;
        }
        if ($content) {
            if (preg_match_all('/<img[^>]+src=[\"\'](\\/uploads\\/guides\\/[^\"\']+)[\"\']/i', $content, $matches)) {
                foreach ($matches[1] as $path) {
                    if (strpos($path, '/uploads/guides/') === 0) {
                        $paths[] = $path;
                    }
                }
            }
        }
        $paths = array_unique($paths);
        foreach ($paths as $path) {
            $fullPath = __DIR__ . '/../../public' . $path;
            if (is_file($fullPath)) {
                unlink($fullPath);
            }
        }
    }
}

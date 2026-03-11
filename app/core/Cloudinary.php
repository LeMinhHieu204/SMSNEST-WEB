<?php
class Cloudinary
{
    private static function getConfig()
    {
        static $config = null;
        if ($config === null) {
            $appConfig = require __DIR__ . '/../../config/config.php';
            $config = $appConfig['cloudinary'] ?? [];
        }
        return $config;
    }

    public static function isEnabled()
    {
        $config = self::getConfig();
        return !empty($config['enabled'])
            && !empty($config['cloud_name'])
            && !empty($config['api_key'])
            && !empty($config['api_secret']);
    }

    public static function uploadFile($tmpPath, $subFolder)
    {
        if (!self::isEnabled() || !is_file($tmpPath)) {
            return null;
        }

        $config = self::getConfig();
        $folder = trim(($config['folder'] ?? '') . '/' . trim($subFolder, '/'), '/');
        $timestamp = time();
        $params = [
            'folder' => $folder,
            'timestamp' => $timestamp,
        ];
        $signature = self::signParams($params, $config['api_secret']);

        $post = [
            'file' => new CURLFile($tmpPath),
            'api_key' => $config['api_key'],
            'timestamp' => $timestamp,
            'signature' => $signature,
            'folder' => $folder,
        ];

        $url = 'https://api.cloudinary.com/v1_1/' . $config['cloud_name'] . '/image/upload';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        if ($response === false) {
            curl_close($ch);
            return null;
        }
        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        if ($status < 200 || $status >= 300) {
            return null;
        }
        $data = json_decode($response, true);
        return $data['secure_url'] ?? null;
    }

    public static function uploadBytes($bytes, $subFolder, $extension)
    {
        if (!self::isEnabled()) {
            return null;
        }
        $temp = tempnam(sys_get_temp_dir(), 'cld_');
        if ($temp === false) {
            return null;
        }
        $tempPath = $temp . '.' . $extension;
        rename($temp, $tempPath);
        $written = file_put_contents($tempPath, $bytes);
        if ($written === false) {
            @unlink($tempPath);
            return null;
        }
        $url = self::uploadFile($tempPath, $subFolder);
        @unlink($tempPath);
        return $url;
    }

    private static function signParams(array $params, $apiSecret)
    {
        ksort($params);
        $pairs = [];
        foreach ($params as $key => $value) {
            $pairs[] = $key . '=' . $value;
        }
        $base = implode('&', $pairs) . $apiSecret;
        return sha1($base);
    }
}

<?php
class Mailer
{
    public static function send($to, $subject, $html, $text = null)
    {
        $config = require __DIR__ . '/../../config/config.php';
        $from = $config['mail']['from'] ?? 'no-reply@example.com';
        $fromName = $config['mail']['from_name'] ?? 'App';
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'From: ' . $fromName . ' <' . $from . '>';
        $smtp = $config['mail']['smtp'] ?? [];
        if (!empty($smtp['enabled'])) {
            return self::sendSmtp($smtp, $from, $to, $subject, $html, $text, $headers);
        }
        return mail($to, $subject, $html, implode("\r\n", $headers));
    }

    private static function sendSmtp($smtp, $from, $to, $subject, $html, $text, $headers)
    {
        $host = $smtp['host'] ?? '';
        $port = (int) ($smtp['port'] ?? 587);
        $username = $smtp['username'] ?? '';
        $password = $smtp['password'] ?? '';
        $secure = $smtp['secure'] ?? 'tls';
        if ($host === '' || $username === '' || $password === '') {
            return false;
        }
        $fp = stream_socket_client(
            sprintf('tcp://%s:%d', $host, $port),
            $errno,
            $errstr,
            10,
            STREAM_CLIENT_CONNECT
        );
        if (!$fp) {
            return false;
        }
        $read = self::readResponse($fp);
        if (!$read) {
            fclose($fp);
            return false;
        }
        self::writeCommand($fp, 'EHLO smsnest');
        $read = self::readResponse($fp);
        if (!$read) {
            fclose($fp);
            return false;
        }
        if ($secure === 'tls') {
            self::writeCommand($fp, 'STARTTLS');
            $read = self::readResponse($fp);
            if (!$read) {
                fclose($fp);
                return false;
            }
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($fp);
                return false;
            }
            self::writeCommand($fp, 'EHLO smsnest');
            $read = self::readResponse($fp);
            if (!$read) {
                fclose($fp);
                return false;
            }
        }
        self::writeCommand($fp, 'AUTH LOGIN');
        $read = self::readResponse($fp);
        if (!$read) {
            fclose($fp);
            return false;
        }
        self::writeCommand($fp, base64_encode($username));
        $read = self::readResponse($fp);
        if (!$read) {
            fclose($fp);
            return false;
        }
        self::writeCommand($fp, base64_encode($password));
        $read = self::readResponse($fp);
        if (!$read) {
            fclose($fp);
            return false;
        }
        self::writeCommand($fp, 'MAIL FROM:<' . $from . '>');
        if (!self::readResponse($fp)) {
            fclose($fp);
            return false;
        }
        self::writeCommand($fp, 'RCPT TO:<' . $to . '>');
        if (!self::readResponse($fp)) {
            fclose($fp);
            return false;
        }
        self::writeCommand($fp, 'DATA');
        if (!self::readResponse($fp)) {
            fclose($fp);
            return false;
        }
        $boundary = 'smsnest_' . bin2hex(random_bytes(8));
        $headers[] = 'Content-Type: multipart/alternative; boundary=' . $boundary;
        $message = 'Subject: ' . $subject . "\r\n" . implode("\r\n", $headers) . "\r\n\r\n";
        $message .= '--' . $boundary . "\r\n";
        $message .= "Content-Type: text/plain; charset=utf-8\r\n\r\n";
        $message .= ($text ?? strip_tags($html)) . "\r\n";
        $message .= '--' . $boundary . "\r\n";
        $message .= "Content-Type: text/html; charset=utf-8\r\n\r\n";
        $message .= $html . "\r\n";
        $message .= '--' . $boundary . "--\r\n.";
        self::writeCommand($fp, $message);
        $result = self::readResponse($fp);
        self::writeCommand($fp, 'QUIT');
        fclose($fp);
        return (bool) $result;
    }

    private static function writeCommand($fp, $command)
    {
        fwrite($fp, $command . "\r\n");
    }

    private static function readResponse($fp)
    {
        $data = '';
        while (!feof($fp)) {
            $line = fgets($fp, 515);
            if ($line === false) {
                break;
            }
            $data .= $line;
            if (preg_match('/^\\d{3}\\s/', $line)) {
                break;
            }
        }
        return $data !== '' && preg_match('/^2\\d{2}|^3\\d{2}/', $data);
    }
}

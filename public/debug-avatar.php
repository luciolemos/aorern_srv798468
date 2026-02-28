<?php
require_once __DIR__ . '/../config/config.php';

$allowedIps = ['127.0.0.1', '::1'];
$remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';

if (!in_array($remoteAddr, $allowedIps, true)) {
    http_response_code(404);
    exit;
}

header('Content-Type: text/plain; charset=utf-8');

error_log('[DEBUG] ========== TESTANDO UPLOAD DE AVATAR ==========');
error_log('[DEBUG] Arquivo recebido');
error_log('[DEBUG] $_FILES: ' . json_encode($_FILES));

if (isset($_FILES['avatar'])) {
    $file = $_FILES['avatar'];
    error_log('[DEBUG] Nome: ' . $file['name']);
    error_log('[DEBUG] Size: ' . $file['size']);
    error_log('[DEBUG] Error: ' . $file['error']);
    error_log('[DEBUG] Type: ' . $file['type']);
    error_log('[DEBUG] Tmp: ' . $file['tmp_name']);

    // Testar finfo
    if (file_exists($file['tmp_name'])) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        error_log('[DEBUG] MIME (finfo): ' . $mime);
    }
} else {
    error_log('[DEBUG] Nenhum arquivo recebido');
}

error_log('[DEBUG] ==========================================');

echo "Check /var/log/apache2/error.log for [DEBUG] messages\n";
?>

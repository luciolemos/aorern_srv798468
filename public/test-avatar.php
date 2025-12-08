<?php
// Script para testar upload de avatar
session_start();

require 'vendor/autoload.php';
require 'config/config.php';

// Simular um arquivo de teste
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_avatar'])) {
    $file = $_FILES['test_avatar'];
    
    echo "=== TESTE DE UPLOAD ===\n\n";
    echo "Nome: " . $file['name'] . "\n";
    echo "Tamanho: " . ($file['size'] / 1024) . "KB\n";
    echo "MIME (type field): " . $file['type'] . "\n";
    echo "Error code: " . $file['error'] . "\n";
    echo "Tmp name: " . $file['tmp_name'] . "\n\n";
    
    // Testar finfo
    if (is_uploaded_file($file['tmp_name'])) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_finfo = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        echo "MIME (finfo): " . $mime_finfo . "\n";
        
        // Tipos permitidos
        $tipos = ['image/jpeg', 'image/png', 'image/webp'];
        echo "Permitido? " . (in_array($mime_finfo, $tipos) ? "SIM ✓" : "NÃO ✗") . "\n";
    }
} else {
    echo "<form method='POST' enctype='multipart/form-data'>";
    echo "<input type='file' name='test_avatar' accept='image/*'>";
    echo "<button>Testar Upload</button>";
    echo "</form>";
}
?>

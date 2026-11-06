<?php
require_once __DIR__ . '/../../core/Security.php';
Security::sendHeaders(true);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Csrf.php';
require_once __DIR__ . '/../../core/Logger.php';

Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
    exit;
}

if (!Csrf::validate()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token de segurança inválido ou expirado.']);
    exit;
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nenhum arquivo enviado ou erro no upload.']);
    exit;
}

$file = $_FILES['file'];

// Limite: 5MB
$maxSize = 5 * 1024 * 1024;
if ($file['size'] <= 0 || $file['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'O arquivo ultrapassa o tamanho máximo permitido de 5MB.']);
    exit;
}

// Validação MIME estrita (SVG removido para prevenir Stored XSS)
$allowedMimes = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp'
];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$imgInfo = @getimagesize($file['tmp_name']);
if (!array_key_exists($mime, $allowedMimes) || $imgInfo === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Formato de arquivo inválido. Permitido apenas JPG, PNG e WEBP válidos.']);
    exit;
}

$ext = $allowedMimes[$mime];
$targetDir = __DIR__ . '/../../uploads/blog';
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

$fileName = 'cover_' . bin2hex(random_bytes(16)) . '.' . $ext;
$targetPath = $targetDir . '/' . $fileName;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Falha ao salvar a imagem no servidor.']);
    exit;
}
@chmod($targetPath, 0644);

$fileUrl = '/uploads/blog/' . $fileName;
Logger::log('IMAGE_UPLOAD', 'media', null, "Upload de imagem realizado: {$fileName}");

echo json_encode([
    'success' => true,
    'url' => $fileUrl,
    'filename' => $fileName
]);

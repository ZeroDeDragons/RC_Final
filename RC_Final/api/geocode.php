<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['erro' => 'Não autenticado']);
    exit;
}

$q = trim($_GET['q'] ?? '');
if (!$q) {
    echo json_encode(['erro' => 'Termo de pesquisa obrigatório']);
    exit;
}

$cacheDir = __DIR__ . '/../cache/';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

$cacheFile = $cacheDir . md5(strtolower($q)) . '.json';

// Cache de 7 dias
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 604800) {
    echo file_get_contents($cacheFile);
    exit;
}

$url = 'https://nominatim.openstreetmap.org/search?format=json&limit=5&addressdetails=1&q=' . urlencode($q);
$ctx = stream_context_create([
    'http' => [
        'header' => "User-Agent: MapaApp/2.0\r\n",
        'timeout' => 10
    ]
]);

$res = @file_get_contents($url, false, $ctx);
if ($res === false) {
    echo json_encode(['erro' => 'Falha na geocodificação']);
    exit;
}

file_put_contents($cacheFile, $res);
echo $res;
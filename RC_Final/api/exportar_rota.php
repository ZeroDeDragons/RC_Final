<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../db.php';
require_once '../lib/kml_generator.php';
require_once '../lib/gpx_generator.php';
require_once '../lib/route_optimizer.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Não autenticado']);
    exit;
}

$dados = json_decode(file_get_contents('php://input'), true);

$pontos_ids = $dados['pontos'] ?? [];
$formato = $dados['formato'] ?? 'kml';
$ordem = $dados['ordem'] ?? 'distancia';
$tipo_rota = $dados['tipo_rota'] ?? 'aberta';
$incluir_descricao = $dados['incluir_descricao'] ?? true;
$incluir_info = $dados['incluir_info'] ?? true;
$incluir_linha = $dados['incluir_linha'] ?? true;

if (count($pontos_ids) < 2) {
    http_response_code(400);
    echo json_encode(['erro' => 'Selecione pelo menos 2 pontos']);
    exit;
}

// Buscar detalhes dos pontos
$placeholders = implode(',', array_fill(0, count($pontos_ids), '?'));
$sql = "
    SELECT 
        l.LocalID, l.Nome, l.Descricao, l.Latitude, l.Longitude,
        l.Telefone, l.Email, l.Website, l.Morada, l.Cidade, l.Pais,
        c.Nome AS categoria, c.Cor, c.Letra
    FROM locais l
    JOIN categorias c ON l.Categoria_Nome = c.Nome
    WHERE l.LocalID IN ($placeholders)
";

$stmt = $pdo->prepare($sql);
$stmt->execute($pontos_ids);
$pontos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Otimizar ordem se necessário
if ($ordem === 'distancia') {
    $otimizador = new RouteOptimizer();
    $pontos = $otimizador->otimizar($pontos, $tipo_rota === 'circular');
} elseif ($ordem === 'nome') {
    usort($pontos, function($a, $b) {
        return strcmp($a['Nome'], $b['Nome']);
    });
}

// Gerar ficheiro conforme formato
switch ($formato) {
    case 'kml':
        $gerador = new KMLGenerator();
        $conteudo = $gerador->gerar($pontos, [
            'incluir_descricao' => $incluir_descricao,
            'incluir_info' => $incluir_info,
            'incluir_linha' => $incluir_linha,
            'tipo_rota' => $tipo_rota
        ]);
        
        $filename = 'rota_' . date('Ymd_His') . '.kml';
        
        header('Content-Type: application/vnd.google-earth.kml+xml');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($conteudo));
        
        echo $conteudo;
        exit;
        
    case 'gpx':
        $gerador = new GPXGenerator();
        $conteudo = $gerador->gerar($pontos, [
            'incluir_descricao' => $incluir_descricao,
            'tipo_rota' => $tipo_rota
        ]);
        
        $filename = 'rota_' . date('Ymd_His') . '.gpx';
        
        header('Content-Type: application/gpx+xml');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($conteudo));
        
        echo $conteudo;
        exit;
        
    case 'csv':
        $filename = 'rota_' . date('Ymd_His') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, "\xEF\xBB\xBF"); // BOM UTF-8
        
        fputcsv($output, ['Nome', 'Categoria', 'Latitude', 'Longitude', 'País', 'Cidade', 'Morada', 'Telefone', 'Email', 'Website']);
        
        foreach ($pontos as $ponto) {
            fputcsv($output, [
                $ponto['Nome'],
                $ponto['categoria'],
                $ponto['Latitude'],
                $ponto['Longitude'],
                $ponto['Pais'],
                $ponto['Cidade'],
                $ponto['Morada'],
                $ponto['Telefone'],
                $ponto['Email'],
                $ponto['Website']
            ]);
        }
        
        fclose($output);
        exit;
        
    case 'url':
        // Gerar URL do Google Maps
        $origin = $pontos[0];
        $destination = $tipo_rota === 'circular' ? $origin : end($pontos);
        
        $waypoints = [];
        $pontos_intermedios = $tipo_rota === 'circular' 
            ? array_slice($pontos, 0, -1) 
            : array_slice($pontos, 1, -1);
        
        foreach ($pontos_intermedios as $p) {
            $waypoints[] = $p['Latitude'] . ',' . $p['Longitude'];
        }
        
        $url = 'https://www.google.com/maps/dir/?api=1';
        $url .= '&origin=' . $origin['Latitude'] . ',' . $origin['Longitude'];
        $url .= '&destination=' . $destination['Latitude'] . ',' . $destination['Longitude'];
        if (!empty($waypoints)) {
            $url .= '&waypoints=' . implode('|', $waypoints);
        }
        $url .= '&travelmode=driving';
        
        echo json_encode(['url' => $url]);
        exit;
        
    default:
        http_response_code(400);
        echo json_encode(['erro' => 'Formato não suportado']);
        exit;
}
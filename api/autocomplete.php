<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../db.php';

$tipo = $_GET['tipo'] ?? 'nome';
$termo = $_GET['termo'] ?? '';
$limite = 10;

if (empty($termo)) {
    echo json_encode([]);
    exit;
}

switch ($tipo) {
    case 'nome':
        $stmt = $pdo->prepare("
            SELECT DISTINCT Nome FROM locais 
            WHERE Nome LIKE ? 
            ORDER BY Nome LIMIT ?
        ");
        $stmt->execute(["%$termo%", $limite]);
        break;
        
    case 'pais':
        $stmt = $pdo->prepare("
            SELECT DISTINCT Pais FROM locais 
            WHERE Pais IS NOT NULL AND Pais LIKE ? 
            ORDER BY Pais LIMIT ?
        ");
        $stmt->execute(["%$termo%", $limite]);
        break;
        
    case 'cidade':
        $stmt = $pdo->prepare("
            SELECT DISTINCT Cidade FROM locais 
            WHERE Cidade IS NOT NULL AND Cidade LIKE ? 
            ORDER BY Cidade LIMIT ?
        ");
        $stmt->execute(["%$termo%", $limite]);
        break;
        
    default:
        echo json_encode([]);
        exit;
}

$resultados = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo json_encode($resultados, JSON_UNESCAPED_UNICODE);
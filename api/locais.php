<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../db.php';

// Parâmetros de filtro
$nome = $_GET['nome'] ?? '';
$categoria = $_GET['categoria'] ?? '';
$pais = $_GET['pais'] ?? '';
$cidade = $_GET['cidade'] ?? '';
$pagina = max(1, intval($_GET['pagina'] ?? 1));
$porPagina = 10;
$offset = ($pagina - 1) * $porPagina;

// Construir WHERE dinâmico
$where = ["1=1"];
$params = [];

if (!empty($nome)) {
    $where[] = "l.Nome LIKE ?";
    $params[] = "%$nome%";
}
if (!empty($categoria)) {
    $where[] = "l.Categoria_id = ?";
    $params[] = $categoria;
}
if (!empty($pais)) {
    $where[] = "l.Pais LIKE ?";
    $params[] = "%$pais%";
}
if (!empty($cidade)) {
    $where[] = "l.Cidade LIKE ?";
    $params[] = "%$cidade%";
}

$whereClause = implode(" AND ", $where);

// Contar total
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM locais l WHERE $whereClause");
$stmtCount->execute($params);
$total = $stmtCount->fetchColumn();

// Buscar locais
$sql = "
    SELECT 
        l.LocalID AS id,
        l.Nome AS nome,
        l.Pais AS pais,
        l.Cidade AS cidade,
        l.Morada AS morada,
        l.Telefone AS telefone,
        l.Email AS email,
        l.Website AS website,
        l.Descricao AS descricao,
        l.Latitude AS latitude,
        l.Longitude AS longitude,
        l.Criado_por AS criador_id,
        l.Data_Criacao,
        c.CategoriaID AS categoria_id,
        c.Nome AS categoria_nome,
        c.Cor AS categoria_cor,
        c.Letra AS categoria_letra,
        u.Nome AS criador_nome,
        u.Tipo AS criador_tipo
    FROM locais l
    JOIN categorias c ON l.Categoria_id = c.CategoriaID
    JOIN usuarios u ON l.Criado_por = u.UsuarioID
    WHERE $whereClause
    ORDER BY l.Nome
    LIMIT ? OFFSET ?
";

$allParams = array_merge($params, [$porPagina, $offset]);
$stmt = $pdo->prepare($sql);
$stmt->execute($allParams);
$locais = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Formatar para JSON
foreach ($locais as &$local) {
    $local['latitude'] = floatval($local['latitude']);
    $local['longitude'] = floatval($local['longitude']);
}

echo json_encode([
    'status' => 'ok',
    'locais' => $locais,
    'total' => $total,
    'pagina' => $pagina,
    'total_paginas' => ceil($total / $porPagina)
], JSON_UNESCAPED_UNICODE);
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
    $where[] = "l.Categoria_Nome = ?";
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

// Buscar locais (QUERY MODIFICADA PARA FOTOS)
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
        c.Nome AS Categoria_Nome,
        c.Cor AS categoria_cor,
        c.Letra AS categoria_letra,
        u.Nome AS criador_nome,
        u.Tipo AS criador_tipo,
        -- SUBQUERY PARA BUSCAR FOTOS
        (SELECT GROUP_CONCAT(Arquivos) FROM fotos WHERE Local_id = l.LocalID) as lista_fotos
    FROM locais l
    JOIN categorias c ON l.Categoria_Nome = c.Nome
    JOIN usuarios u ON l.Criado_por = u.UsuarioID
    WHERE $whereClause
    ORDER BY l.Nome
    LIMIT ? OFFSET ?
";

// Em PDO, o LIMIT e OFFSET precisam de tipos específicos se usar execute() direto com array
$stmt = $pdo->prepare($sql);
foreach ($params as $i => $val) {
    $stmt->bindValue($i + 1, $val);
}
$stmt->bindValue(count($params) + 1, (int)$porPagina, PDO::PARAM_INT);
$stmt->bindValue(count($params) + 2, (int)$offset, PDO::PARAM_INT);
$stmt->execute();

$locais = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Formatar para JSON e tratar a lista de fotos
foreach ($locais as &$local) {
    $local['latitude'] = floatval($local['latitude']);
    $local['longitude'] = floatval($local['longitude']);

    // CONVERTER A STRING DE FOTOS EM ARRAY
    if (!empty($local['lista_fotos'])) {
        $local['fotos'] = explode(',', $local['lista_fotos']);
    } else {
        $local['fotos'] = [];
    }
    // Remove o campo temporário de string para limpar o JSON
    unset($local['lista_fotos']);
}

echo json_encode([
    'status' => 'ok',
    'locais' => $locais,
    'total' => (int)$total,
    'pagina' => $pagina,
    'total_paginas' => ceil($total / $porPagina)
], JSON_UNESCAPED_UNICODE);
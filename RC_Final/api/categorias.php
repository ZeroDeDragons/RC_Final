<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../db.php';

$stmt = $pdo->query("SELECT Nome, Cor, Letra FROM categorias ORDER BY Nome");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($categorias, JSON_UNESCAPED_UNICODE);
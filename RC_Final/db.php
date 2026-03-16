<?php
$host   = "localhost";
$banco  = "turismo";
$usuario = "root";
$senha   = ""; 

$portas = ["3306", "3307"];
$pdo = null;

foreach ($portas as $porta) {
    try {
        $dsn = "mysql:host=$host;port=$porta;dbname=$banco;charset=utf8mb4";
        $pdo = new PDO($dsn, $usuario, $senha, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        break; 
    } catch (PDOException $e) {
        $erro_log = $e->getMessage();
        continue; 
    }
}

if (!$pdo) {
    header('Content-Type: application/json');
    die(json_encode(["status" => "erro", "mensagem" => "Erro de conexão: " . $erro_log]));
}
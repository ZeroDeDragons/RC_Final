<?php
$host = "localhost:3307"; 
$usuario = "root";
$senha = ""; 
$banco = "turismo";
$porta = 3306;

try {
    // MySQLi para compatibilidade
    $conn = new mysqli($host, $usuario, $senha, $banco, $porta);
    if ($conn->connect_error) {
        throw new Exception("Conexão falhou: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    
    // PDO para funcionalidades avançadas
    $pdo = new PDO("mysql:host=$host;port=$porta;dbname=$banco;charset=utf8mb4", $usuario, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (Exception $e) {
    die("Erro na base de dados: " . $e->getMessage());
}
?>
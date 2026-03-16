<?php
session_start();
require 'db.php'; // Carrega a conexão $pdo definida anteriormente
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}


$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $sexo = $_POST['sexo'] ?? '';
    
    // Validações básicas
    if (empty($nome) || empty($email) || empty($password) || empty($sexo)) {
        $erro = 'Todos os campos são obrigatórios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Email inválido.';
    } elseif (strlen($password) < 6) {
        $erro = 'A password deve ter pelo menos 6 caracteres.';
    } else {
        try {
            // 1. Verificar se email já existe usando PDO
            $check = $pdo->prepare("SELECT UsuarioID FROM usuarios WHERE Email = :email");
            $check->execute(['email' => $email]);
            
            if ($check->fetch()) {
                $erro = 'Este email já está registado.';
            } else {
                // 2. Criar Hash da password
                $hash = password_hash($password, PASSWORD_DEFAULT);
                
                // 3. Inserir novo utilizador
                $sql = "INSERT INTO usuarios (Nome, Email, Password, Sexo, Tipo) 
                        VALUES (:nome, :email, :password, :sexo, 'Normal')";
                
                $stmt = $pdo->prepare($sql);
                $sucesso = $stmt->execute([
                    'nome'     => $nome,
                    'email'    => $email,
                    'password' => $hash,
                    'sexo'     => $sexo
                ]);
                
                if ($sucesso) {
                    // No PDO usamos $pdo->lastInsertId() em vez de $conn->insert_id
                    $_SESSION['usuario_id'] = $pdo->lastInsertId();
                    $_SESSION['nome'] = $nome;
                    $_SESSION['tipo'] = 'Normal';
                    $_SESSION['sexo'] = $sexo;
                    
                    header('Location: index.php');
                    exit;
                } else {
                    $erro = 'Erro ao criar conta. Tente novamente.';
                }
            }
        } catch (PDOException $e) {
            // Em desenvolvimento podes usar: $erro = 'Erro: ' . $e->getMessage();
            $erro = 'Erro interno no servidor ao processar o registo.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registo - MapaApp</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="pg-auth">
    <div class="auth-box">
        <div class="auth-icon">🗺️</div>
        <h1>Criar Conta</h1>
        <p class="auth-sub">Junta-te ao MapaApp</p>
        
        <?php if ($erro): ?>
            <div class="msg erro"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="campo">
                <label><i class="fas fa-user"></i> Nome</label>
                <input type="text" name="nome" placeholder="O teu nome completo" required>
            </div>
            <div class="campo">
                <label><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" placeholder="email@exemplo.com" required>
            </div>
            <div class="campo">
                <label><i class="fas fa-lock"></i> Password <small>(mín. 6 caracteres)</small></label>
                <input type="password" name="password" placeholder="••••••" required>
            </div>
            <div class="campo">
                <label><i class="fas fa-venus-mars"></i> Sexo *</label>
                <select name="sexo" required>
                    <option value="">Seleciona...</option>
                    <option value="M">Masculino</option>
                    <option value="F">Feminino</option>
                    <option value="T">Transgénero</option>
                    <option value="Q">Queer</option>
                    <option value="V">Prefiro não dizer</option>
                    <option value="s">Outro</option>
                </select>
            </div>
            <button type="submit" class="btn primary full">
                <i class="fas fa-user-plus"></i> Criar Conta
            </button>
        </form>
        
        <p class="auth-link">Já tens conta? <a href="login.php">Inicia sessão</a></p>
    </div>
</body>
</html>
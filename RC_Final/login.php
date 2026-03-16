<?php
session_start();
require 'db.php';
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $erro = 'Preencha todos os campos.';
    } else {
        try {
            // Sintaxe PDO: Usamos ":email" como placeholder nomeado
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE Email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(); // Com PDO::FETCH_ASSOC no db.php, isto já devolve um array
            
            if ($user && password_verify($password, $user['Password'])) {
                $_SESSION['usuario_id'] = $user['UsuarioID'];
                $_SESSION['nome'] = $user['Nome'];
                $_SESSION['tipo'] = $user['Tipo'];
                $_SESSION['sexo'] = $user['Sexo'];
                
                header('Location: index.php');
                exit;
            } else {
                $erro = 'Email ou password incorretos.';
            }
        } catch (PDOException $e) {
            $erro = 'Erro no sistema. Tente mais tarde.';
            // Opcional: log do erro $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MapaApp</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="pg-auth">
    <div class="auth-box">
        <div class="auth-icon">🗺️</div>
        <h1>MapaApp</h1>
        <p class="auth-sub">Inicia sessão para criar pontos e rotas</p>
        
        <?php if ($erro): ?>
            <div class="msg erro"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="campo">
                <label><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" placeholder="email@exemplo.com" required>
            </div>
            <div class="campo">
                <label><i class="fas fa-lock"></i> Password</label>
                <input type="password" name="password" placeholder="••••••" required>
            </div>
            <button type="submit" class="btn primary full">
                <i class="fas fa-sign-in-alt"></i> Entrar
            </button>
        </form>
        
        <p class="auth-link">Não tens conta? <a href="register.php">Regista-te aqui</a></p>
        
        <div class="auth-hint">
            <i class="fas fa-info-circle"></i>
            <strong>Contas de teste:</strong><br>
            admin@mapa.com / password (Admin)<br>
            user@mapa.com / password (Normal)
        </div>
    </div>
</body>
</html>
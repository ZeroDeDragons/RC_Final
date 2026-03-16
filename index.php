<?php
session_start();
require_once 'db.php';

// Buscar categorias para filtros
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY Nome")->fetchAll(PDO::FETCH_ASSOC);

// Verificar se utilizador está logado
$logado = isset($_SESSION['usuario_id']);
$usuario_nome = $_SESSION['nome'] ?? '';
$usuario_tipo = $_SESSION['tipo'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MapaApp - Pontos Turísticos</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="<?php echo $logado ? 'logado' : 'visitante'; ?>">

    <!-- TOP BAR -->
    <header class="top-bar">
        <button class="menu-btn" id="toggle-sidebar" aria-label="Menu">
            <i class="fas fa-bars"></i>
        </button>

        <div class="title-container">
            <h1 class="title">🗺️ MapaApp</h1>
            <div class="title-sub">Pontos Turísticos Interativos</div>
        </div>

        <div class="user-profile">
            <?php if ($logado): ?>
                <?php if ($usuario_tipo == 'Admin'): ?>
                    <span class="badge-admin">ADMIN</span>
                <?php endif; ?>
                <button class="btn-novo" id="btn-novo-ponto" onclick="abrirModalCriar()" title="Criar novo ponto">
                    <i class="fas fa-plus-circle"></i>
                    <span>Novo</span>
                </button>
                <button class="btn-rota" id="btn-criar-rota" title="Criar rota">
                    <i class="fas fa-route"></i>
                    <span>Rota</span>
                </button>
                <div class="avatar">
                    <i class="fas fa-user"></i>
                </div>
                <span class="username">Olá, <?php echo htmlspecialchars($usuario_nome ?: 'User'); ?></span>
                <a href="logout.php" class="btn-logout" title="Sair">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            <?php else: ?>
                <a href="login.php" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Entrar</span>
                </a>
                <a href="register.php" class="btn-register">
                    <i class="fas fa-user-plus"></i>
                    <span>Registar</span>
                </a>
            <?php endif; ?>
        </div>
    </header>

    <!-- LAYOUT PRINCIPAL -->
    <div class="layout-principal">
        <!-- SIDEBAR (incluída de ficheiro separado) -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- MAPA -->
        <div class="map-wrapper">
            <div id="map"></div>
        </div>
    </div>

    <!-- MODAIS -->
    <?php include 'includes/modal_detalhe.php'; ?>
    <?php if ($logado)
        include 'includes/modal_criar.php'; ?>
    <?php if ($logado)
        include 'includes/modal_rota.php'; ?>

    <!-- SCRIPTS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>

    <!-- PASSAR DADOS DO PHP PARA JS -->
    <script>
        const USUARIO_LOGADO = <?php echo $logado ? 'true' : 'false'; ?>;
        const USUARIO_ID = <?php echo $_SESSION['usuario_id'] ?? 0; ?>;
        const USUARIO_TIPO = '<?php echo $_SESSION['tipo'] ?? ""; ?>';
        const CATEGORIAS = <?php echo json_encode($categorias); ?>;
    </script>

    <script src="js/mapa.js"></script>
    <script src="js/autocomplete.js"></script>
    <?php if ($logado): ?>
        <script src="js/rotas.js"></script>
    <?php endif; ?>
</body>

</html>
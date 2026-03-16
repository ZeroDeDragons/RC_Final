================================================================================
                        MAPAAPP - DOCUMENTAÇÃO DO PROJETO
                         Pontos Turísticos Interativos
================================================================================

  Versão    : 1.0
  Linguagens: PHP 8+, JavaScript (ES6+), HTML5, CSS3
  Base de   : MySQL (via PDO)
  Dados
  Criado em : 2025

================================================================================
  ÍNDICE
================================================================================

  1. O QUE É ESTE PROJETO?
  2. ESTRUTURA DE FICHEIROS
  3. COMO INSTALAR E CONFIGURAR
  4. BASE DE DADOS - TABELAS NECESSÁRIAS
  5. COMO FUNCIONA O SISTEMA DE LOGIN
  6. COMO FUNCIONA O MAPA
  7. COMO FUNCIONA O SISTEMA DE ROTAS
  8. COMO FUNCIONA A EXPORTAÇÃO (GPX / KML)
  9. API ENDPOINTS (ficheiros que o JS chama)
  10. TECNOLOGIAS E BIBLIOTECAS EXTERNAS
  11. PERMISSÕES DE UTILIZADOR
  12. PROBLEMAS CONHECIDOS / NOTAS DE DESENVOLVIMENTO

================================================================================
  1. O QUE É ESTE PROJETO?
================================================================================

  MapaApp é uma aplicação web interativa que permite:

  - Ver pontos turísticos num mapa (baseado em OpenStreetMap)
  - Filtrar pontos por Nome, Categoria, País e Cidade
  - Criar novos pontos turísticos (utilizadores registados)
  - Editar e apagar pontos (criador ou Admin)
  - Selecionar múltiplos pontos e criar rotas turísticas
  - Exportar rotas em vários formatos: KML, GPX, CSV, ou link do Google Maps
  - Sistema de contas com dois tipos: "Normal" e "Admin"

  É uma aplicação do tipo "full-stack":
    → O BACK-END é feito em PHP (lida com a base de dados e lógica do servidor)
    → O FRONT-END é feito em JavaScript + HTML (o que o utilizador vê e clica)
    → A BASE DE DADOS é MySQL

================================================================================
  2. ESTRUTURA DE FICHEIROS
================================================================================

  RAIZ DO PROJETO
  ├── index.php             → Página principal (mapa + sidebar + header)
  ├── login.php             → Página de login
  ├── register.php          → Página de registo de nova conta
  ├── logout.php            → Termina a sessão e redireciona
  ├── db.php                → Ligação à base de dados (PDO)
  │
  ├── includes/
  │   ├── sidebar.php       → Barra lateral (filtros + lista de pontos)
  │   ├── modal_detalhe.php → Modal de ver detalhes de um ponto
  │   ├── modal_criar.php   → Modal de criar/editar ponto (só para logados)
  │   └── modal_rota.php    → Modal de criar rota (só para logados)
  │
  ├── api/
  │   ├── locais.php        → [GET] Lista pontos com filtros, paginação e fotos
  │   ├── locais_crud.php   → [POST] Cria ponto | [PUT] Edita | [DELETE] Apaga
  │   ├── autocomplete.php  → [GET] Sugestões de nome/país/cidade para filtros
  │   ├── exportar_rota.php → [POST] Gera e devolve ficheiro de rota
  │   ├── geocode.php       → [GET] Pesquisa de endereço via Nominatim (OSM)
  │   └── categorias.php    → [GET] Lista todas as categorias disponíveis
  │
  ├── js/
  │   ├── mapa.js           → Lógica principal do mapa, filtros, marcadores, modais
  │   ├── rotas.js          → Lógica de seleção e exportação de rotas
  │   └── autocomplete.js   → (Atualmente vazio - lógica está em mapa.js)
  │
  ├── css/
  │   └── styles.css        → Todos os estilos da aplicação
  │
  └── lib/
      ├── gpx_generator.php   → Classe que gera ficheiros GPX (formato GPS)
      ├── kml_generator.php   → Classe que gera ficheiros KML (Google Earth)
      └── route_optimizer.php → Classe que otimiza a ordem dos pontos numa rota

  NOTA: A pasta das classes chama-se "lib/" (não "classes/").
  O exportar_rota.php usa: require_once '../lib/kml_generator.php' etc.

================================================================================
  3. COMO INSTALAR E CONFIGURAR
================================================================================

  PRÉ-REQUISITOS:
  ---------------
  - PHP 8.0 ou superior
  - MySQL 5.7 ou superior (ou MariaDB equivalente)
  - Servidor web (Apache com XAMPP/WAMP, ou Nginx)
  - Extensão PDO_MySQL activada no PHP (normalmente já vem por defeito)

  PASSOS DE INSTALAÇÃO:
  ---------------------

  Passo 1 - Copiar ficheiros
    → Coloca todos os ficheiros na pasta do teu servidor web.
    → Exemplo com XAMPP: C:\xampp\htdocs\mapaapp\

  Passo 2 - Criar a base de dados
    → Abre o phpMyAdmin (http://localhost/phpmyadmin)
    → Cria uma base de dados chamada: turismo
    → Corre o SQL da Secção 4 deste documento para criar as tabelas

  Passo 3 - Configurar a ligação à base de dados
    → Abre o ficheiro db.php
    → Verifica / altera estas variáveis:

        $host    = "localhost";   // Normalmente não precisa mudar
        $banco   = "turismo";     // Nome da base de dados que criaste
        $usuario = "root";        // Utilizador do MySQL
        $senha   = "";            // Password do MySQL (vazio no XAMPP por defeito)

    → O ficheiro tenta ligar nas portas 3306 e 3307 automaticamente.
      Se o teu MySQL usa outra porta, acrescenta-a ao array $portas.

  Passo 4 - Abrir no browser
    → Acede a: http://localhost/mapaapp/index.php
    → Se a ligação à BD falhar, verás uma mensagem de erro JSON.

  CONTAS DE TESTE (criadas via SQL na Secção 4):
    → Admin : admin@mapa.com  / password
    → Normal: user@mapa.com   / password

================================================================================
  4. BASE DE DADOS - TABELAS NECESSÁRIAS
================================================================================

  A base de dados chama-se "turismo" e precisa das seguintes tabelas:

  ── TABELA: usuarios ──────────────────────────────────────────────────────────

    CREATE TABLE usuarios (
        UsuarioID  INT AUTO_INCREMENT PRIMARY KEY,
        Nome       VARCHAR(100) NOT NULL,
        Email      VARCHAR(150) NOT NULL UNIQUE,
        Password   VARCHAR(255) NOT NULL,       -- Guardada com password_hash()
        Sexo       CHAR(1),                     -- M, F, T, Q, V ou s
        Tipo       ENUM('Normal','Admin') DEFAULT 'Normal',
        CriadoEm  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

  ── TABELA: categorias ────────────────────────────────────────────────────────

    CREATE TABLE categorias (
        CategoriaID  INT AUTO_INCREMENT PRIMARY KEY,
        Nome         VARCHAR(100) NOT NULL,
        Cor          VARCHAR(7) DEFAULT '#b7d630',  -- Código de cor hex, ex: #FF5733
        Letra        VARCHAR(2)                     -- Símbolo/letra, ex: 🏛 ou "M"
    );

  ── TABELA: locais ────────────────────────────────────────────────────────────

    ATENÇÃO: A tabela usa Categoria_Nome (texto) em vez de CategoriaID (número).
    A ligação à categoria é feita por nome, não por chave estrangeira numérica.

    CREATE TABLE locais (
        LocalID       INT AUTO_INCREMENT PRIMARY KEY,
        Nome          VARCHAR(150) NOT NULL,
        Descricao     TEXT,
        Latitude      DECIMAL(10,7) NOT NULL,
        Longitude     DECIMAL(10,7) NOT NULL,
        Morada        VARCHAR(255),
        Cidade        VARCHAR(100),
        Pais          VARCHAR(100),
        Telefone      VARCHAR(30),
        Email         VARCHAR(150),
        Website       VARCHAR(255),
        Categoria_Nome VARCHAR(100),               -- Liga pelo NOME da categoria
        Criado_por    INT,
        Data_Criacao  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (Criado_por) REFERENCES usuarios(UsuarioID)
    );

  ── TABELA: fotos ─────────────────────────────────────────────────────────────

    ATENÇÃO: A coluna chama-se "Arquivos" (não "URL") e a FK é "Local_id".

    CREATE TABLE fotos (
        FotoID    INT AUTO_INCREMENT PRIMARY KEY,
        Arquivos  VARCHAR(500) NOT NULL,     -- URL/link da foto
        Local_id  INT NOT NULL,
        FOREIGN KEY (Local_id) REFERENCES locais(LocalID) ON DELETE CASCADE
    );

  ── DADOS INICIAIS (inserir após criar as tabelas) ───────────────────────────

    -- Categorias exemplo:
    INSERT INTO categorias (Nome, Cor, Letra) VALUES
        ('Monumento',  '#e74c3c', '🏛'),
        ('Museu',      '#3498db', '🏛'),
        ('Praia',      '#00bcd4', '🏖'),
        ('Restaurante','#f39c12', '🍽'),
        ('Hotel',      '#9b59b6', '🏨');

    -- Utilizadores de teste (password = "password"):
    INSERT INTO usuarios (Nome, Email, Password, Sexo, Tipo) VALUES
        ('Administrador', 'admin@mapa.com',
         '$2y$10$...hash_gerado_pelo_php...', 'M', 'Admin'),
        ('Utilizador Teste', 'user@mapa.com',
         '$2y$10$...hash_gerado_pelo_php...', 'M', 'Normal');

    NOTA: Para gerar o hash correto de "password", corre este PHP:
        echo password_hash('password', PASSWORD_DEFAULT);
    E substitui o valor acima.

================================================================================
  5. COMO FUNCIONA O SISTEMA DE LOGIN
================================================================================

  FLUXO COMPLETO:
  ---------------

  [Utilizador abre login.php]
         ↓
  [Preenche email + password e clica "Entrar"]
         ↓
  [PHP verifica: o formulário foi submetido? (REQUEST_METHOD === 'POST')]
         ↓
  [PHP valida: campos não estão vazios?]
         ↓
  [PHP faz query: SELECT * FROM usuarios WHERE Email = :email]
         ↓
  [Encontrou utilizador? → verifica password com password_verify()]
         ↓
  [Senha correta? → guarda na SESSÃO: usuario_id, nome, tipo, sexo]
         ↓
  [Redireciona para index.php]

  FICHEIROS ENVOLVIDOS:
  - login.php      → Formulário HTML + lógica PHP de verificação
  - register.php   → Registo de nova conta (valida, faz hash da senha, insere na BD)
  - logout.php     → Destrói sessão com session_destroy() e redireciona
  - db.php         → Fornece a ligação $pdo usada nas queries

  COMO O PHP SABE SE ESTÁS LOGADO:
  - Cada página começa com session_start()
  - Se $_SESSION['usuario_id'] existir → utilizador logado
  - A variável $logado é passada para o JavaScript:
      const USUARIO_LOGADO = true/false;
      const USUARIO_ID = 123;
      const USUARIO_TIPO = 'Admin' ou 'Normal';

  SEGURANÇA IMPLEMENTADA:
  - Passwords guardadas com password_hash() (nunca em texto simples!)
  - Verificação com password_verify() (nunca comparação direta)
  - htmlspecialchars() nos outputs para prevenir XSS
  - Prepared Statements do PDO para prevenir SQL Injection
  - Se já estiver logado, login.php e register.php redirecionam para index.php

================================================================================
  6. COMO FUNCIONA O MAPA
================================================================================

  TECNOLOGIA USADA: Leaflet.js (biblioteca JavaScript gratuita para mapas)
  TILES (imagens do mapa): OpenStreetMap (gratuito, sem chave de API)

  INICIALIZAÇÃO (mapa.js):
  -------------------------

  Quando a página carrega (evento DOMContentLoaded), são chamadas 4 funções:

  1. inicializarMapa()
     → Cria o mapa centrado em Portugal (lat: 39.5, lng: -8.0, zoom: 7)
     → Adiciona os tiles do OpenStreetMap
     → Cria o clusterGroup (agrupa marcadores próximos num círculo com número)

  2. carregarLocais()
     → Faz um pedido fetch() para api/locais.php
     → Recebe JSON com a lista de pontos da base de dados
     → Chama atualizarMarcadores() e atualizarLista()

  3. configurarEventos()
     → Liga todos os botões e campos aos seus eventos (cliques, teclas, etc.)

  4. configurarFormularioCriacao()
     → Prepara o formulário do modal de criar/editar pontos

  MARCADORES PERSONALIZADOS:
  --------------------------
  Cada ponto tem um marcador colorido com uma letra/emoji, definido pela sua
  categoria. A função criarIconePersonalizado(cor, letra) usa L.divIcon()
  do Leaflet para criar um marcador HTML personalizado.

  POPUP DO MARCADOR:
  ------------------
  Ao clicar num marcador abre um popup com:
  - Foto do local (se existir)
  - Nome e categoria com cor
  - Cidade e País
  - Botões: Ver Detalhes, Editar (se tiver permissão), Apagar (se tiver permissão)

  FILTROS:
  --------
  Os filtros na sidebar fazem um novo pedido à API (carregarLocais())
  com os parâmetros atualizados. O servidor filtra e devolve só os pontos
  que correspondem. Os marcadores e a lista são atualizados automaticamente.

  PAGINAÇÃO:
  ----------
  Os resultados são divididos em páginas (definido no servidor em api/locais.php).
  Os botões "Anterior" e "Próxima" mudam a variável paginaAtual e recarregam.

  AUTOCOMPLETE:
  -------------
  Ao escrever nos campos de Nome, País ou Cidade, após 300ms de pausa,
  é feito um pedido a api/autocomplete.php que devolve sugestões da BD.
  As sugestões aparecem numa lista abaixo do campo.

================================================================================
  7. COMO FUNCIONA O SISTEMA DE ROTAS
================================================================================

  FICHEIROS: rotas.js + modal_rota.php

  FLUXO:
  ------

  [Utilizador seleciona pontos com checkboxes na sidebar]
         ↓
  [pontosSelecionados (Set de IDs) é atualizado]
         ↓
  [Aparece barra de seleção na base da sidebar com contador]
         ↓
  [Clica "Criar Rota" → abrirModalRota()]
         ↓
  [Modal mostra lista dos pontos selecionados com opções:]
    - Ordenar por: seleção manual / nome / distância otimizada / categoria
    - Tipo: Aberta (A→B→C) ou Circular (A→B→C→A)
    - Formato: KML / GPX / CSV / Link Google Maps
    - Opções extra: incluir descrições, informações de contacto, linha da rota
         ↓
  [Clica "Visualizar" → desenha linha no mapa e fecha o modal]
         ↓
  [Clica "Exportar" → envia pedido POST para api/exportar_rota.php]
         ↓
  [Servidor gera o ficheiro e devolve para download]

  OTIMIZAÇÃO DE ROTA (route_optimizer.php):
  ------------------------------------------
  Quando se escolhe ordenar por "distância", a classe RouteOptimizer usa
  o algoritmo "Nearest Neighbor" (vizinho mais próximo):

  1. Começa pelo primeiro ponto selecionado
  2. Encontra o ponto mais próximo que ainda não foi visitado
     (usa a fórmula de Haversine para calcular distância real em km)
  3. Vai para esse ponto e repete até visitar todos
  
  Este algoritmo não garante a rota MAIS curta possível, mas é rápido e
  dá bons resultados para um número pequeno de pontos (até ~15).

  DRAG & DROP (reordenação manual):
  ----------------------------------
  Quando "Ordem de seleção" está selecionado, os pontos podem ser
  arrastados para reordenar. Está parcialmente implementado via HTML5
  Drag & Drop API (eventos dragstart, dragover, drop).

================================================================================
  8. COMO FUNCIONA A EXPORTAÇÃO (GPX / KML)
================================================================================

  FICHEIROS: gpx_generator.php, kml_generator.php, api/exportar_rota.php

  GPX (GPS Exchange Format):
  --------------------------
  - Formato padrão para dispositivos GPS (Garmin, etc.)
  - Estrutura XML com <wpt> (waypoints) e <rte> (rota)
  - A classe GPXGenerator usa a classe DOMDocument do PHP para gerar XML válido
  - Cada ponto turístico torna-se um <wpt> com latitude, longitude e nome
  - A sequência de pontos forma um <rte> (rota)

  KML (Keyhole Markup Language):
  ------------------------------
  - Formato para Google Earth e Google Maps
  - Estrutura XML com <Placemark> para cada ponto
  - Inclui estilos visuais (cor, ícones)
  - Pode incluir descrição HTML dentro de <![CDATA[...]]>
  - A linha da rota é um <LineString> com todas as coordenadas

  URL do Google Maps:
  -------------------
  - Gera um link do tipo:
    https://www.google.com/maps/dir/lat1,lng1/lat2,lng2/lat3,lng3/
  - Limitado a aproximadamente 10 pontos (limitação do Google Maps)
  - Abre diretamente no browser/telemóvel com a rota desenhada

================================================================================
  9. API ENDPOINTS
================================================================================

  Estes são os ficheiros PHP que o JavaScript chama via fetch():

  ─────────────────────────────────────────────────────────────
  GET  api/locais.php
  ─────────────────────────────────────────────────────────────
  Parâmetros (query string):
    ?nome=texto&categoria=nome&pais=portugal&cidade=lisboa&pagina=1

  O que faz internamente:
    1. Constrói um WHERE dinâmico (só filtra os campos que não estão vazios)
    2. Faz COUNT(*) primeiro para saber o total de resultados
    3. Usa LIMIT + OFFSET para paginação (10 por página)
    4. Usa GROUP_CONCAT para buscar todas as fotos de cada local numa só query
    5. Converte a string de fotos em array com explode(',', ...)

  Resposta JSON:
    {
      "status": "ok",
      "locais": [
        {
          "id": 1, "nome": "Torre de Belém",
          "latitude": 38.692, "longitude": -9.216,
          "pais": "Portugal", "cidade": "Lisboa",
          "Categoria_Nome": "Monumento",
          "categoria_cor": "#e74c3c", "categoria_letra": "🏛",
          "criador_id": 2, "criador_nome": "João",
          "fotos": ["https://...foto1.jpg", "https://...foto2.jpg"]
        }
      ],
      "total": 45,
      "pagina": 1,
      "total_paginas": 5
    }

  ─────────────────────────────────────────────────────────────
  POST  api/locais_crud.php   → Criar novo ponto
  PUT   api/locais_crud.php   → Editar ponto existente
  DELETE api/locais_crud.php?id=X → Apagar ponto
  ─────────────────────────────────────────────────────────────

  POST recebe JSON no body (para criar):
    {
      "nome": "Torre de Belém",
      "categoria_id": "Monumento",       ← nome da categoria, ou "nova"
      "nova_categoria": {                ← só se categoria_id === "nova"
        "nome": "Castelo", "cor": "#e74c3c", "letra": "🏰"
      },
      "latitude": 38.692,
      "longitude": -9.216,
      "pais": "Portugal", "cidade": "Lisboa",
      "morada": "...", "telefone": "...",
      "email_local": "...", "website": "https://...",
      "descricao": "...",
      "fotos": ["https://foto1.jpg", "https://foto2.jpg"]
    }

  Fluxo do POST (criar):
    1. Valida campos obrigatórios (nome, categoria, latitude, longitude)
    2. Se categoria = "nova", insere na tabela categorias (se não existir)
    3. Insere o local na tabela locais
    4. Usa lastInsertId() para obter o ID gerado
    5. Insere cada foto na tabela fotos ligada ao novo LocalID

  PUT recebe os dados via php://input (form-encoded).
  DELETE usa o parâmetro ?id=X na URL.
  Ambos verificam permissões (dono do ponto ou Admin) antes de agir.

  ─────────────────────────────────────────────────────────────
  GET  api/autocomplete.php?tipo=nome&termo=torre
  ─────────────────────────────────────────────────────────────

  Tipos suportados: nome, pais, cidade
  Devolve até 10 resultados únicos (DISTINCT) que contenham o termo.

  Resposta JSON: ["Torre de Belém", "Torre dos Clérigos", ...]

  ─────────────────────────────────────────────────────────────
  POST  api/exportar_rota.php
  ─────────────────────────────────────────────────────────────

  Requer sessão ativa (utilizador logado).
  Recebe JSON:
    {
      "pontos": [1, 5, 12, 7],
      "formato": "kml" | "gpx" | "csv" | "url",
      "ordem": "distancia" | "nome" | "selecao" | "categoria",
      "tipo_rota": "aberta" | "circular",
      "incluir_descricao": true,
      "incluir_info": true,
      "incluir_linha": true
    }

  Resposta:
    - Para KML/GPX/CSV: muda os headers HTTP e devolve o ficheiro direto
      (o browser faz download automaticamente via response.blob() no JS)
    - Para URL: devolve JSON { "url": "https://www.google.com/maps/dir/..." }

  ─────────────────────────────────────────────────────────────
  GET  api/geocode.php?q=Torre+de+Belem+Lisboa
  ─────────────────────────────────────────────────────────────

  Requer sessão ativa (utilizador logado).
  Pesquisa endereços usando a API Nominatim do OpenStreetMap (gratuita).

  Sistema de CACHE:
    → Guarda os resultados em ficheiros na pasta cache/
    → O nome do ficheiro é o MD5 do termo pesquisado
    → Cache válida por 7 dias (604800 segundos)
    → Evita demasiados pedidos à API externa

  Resposta: Array JSON com até 5 resultados do Nominatim (formato padrão OSM).

  ─────────────────────────────────────────────────────────────
  GET  api/categorias.php
  ─────────────────────────────────────────────────────────────

  Devolve todas as categorias ordenadas por nome.
  Resposta JSON: [{"Nome":"Hotel","Cor":"#9b59b6","Letra":"🏨"}, ...]

================================================================================
  10. TECNOLOGIAS E BIBLIOTECAS EXTERNAS
================================================================================

  BACK-END:
  - PHP 8+            → Linguagem do servidor
  - PDO (PHP)         → Abstração da base de dados (mais seguro que mysqli)
  - MySQL             → Base de dados relacional
  - DOMDocument (PHP) → Geração de XML para GPX e KML

  FRONT-END:
  - HTML5 / CSS3      → Estrutura e estilos
  - JavaScript ES6+   → Lógica interativa (sem frameworks)
  - Fetch API         → Pedidos HTTP assíncronos (substitui o antigo XMLHttpRequest)

  BIBLIOTECAS (carregadas via CDN, não precisam de instalação):
  - Leaflet.js 1.9.4
    → https://unpkg.com/leaflet@1.9.4/dist/leaflet.js
    → Biblioteca principal do mapa interativo
  
  - Leaflet.MarkerCluster 1.4.1
    → https://unpkg.com/leaflet.markercluster@1.4.1/
    → Agrupa marcadores próximos para melhor visualização
  
  - Font Awesome 6.4.0
    → https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/
    → Ícones utilizados em botões e labels

  MAPAS:
  - OpenStreetMap (tiles gratuitos, sem chave de API necessária)

================================================================================
  11. PERMISSÕES DE UTILIZADOR
================================================================================

  VISITANTE (não logado):
  ✓ Ver o mapa e todos os pontos
  ✓ Filtrar e pesquisar pontos
  ✓ Ver detalhes dos pontos
  ✗ Criar pontos
  ✗ Editar / apagar pontos
  ✗ Criar rotas / exportar

  UTILIZADOR NORMAL (logado):
  ✓ Tudo o que o visitante pode fazer
  ✓ Criar novos pontos turísticos
  ✓ Editar os seus próprios pontos
  ✓ Apagar os seus próprios pontos
  ✓ Criar e exportar rotas
  ✗ Editar / apagar pontos de outros utilizadores

  ADMINISTRADOR (tipo = 'Admin'):
  ✓ Tudo o que o utilizador normal pode fazer
  ✓ Editar QUALQUER ponto (de qualquer utilizador)
  ✓ Apagar QUALQUER ponto (de qualquer utilizador)
  ✓ Crachá "ADMIN" visível no header

  VERIFICAÇÃO DE PERMISSÕES:
  - No front-end (JavaScript): verifica USUARIO_TIPO e USUARIO_ID
    antes de mostrar botões de editar/apagar
  - No back-end (PHP/API): DEVE ser verificado novamente no servidor
    (nunca confiar apenas no front-end para segurança!)

================================================================================
  12. PROBLEMAS CONHECIDOS / NOTAS DE DESENVOLVIMENTO
================================================================================

  ✅ RESOLVIDO - Ficheiros da API:
  ----------------------------------
  Todos os endpoints da API estão agora implementados:
  locais.php, locais_crud.php, autocomplete.php, exportar_rota.php,
  geocode.php e categorias.php.

  ✅ RESOLVIDO - styles.css:
  ---------------------------
  O ficheiro de estilos foi confirmado e existe em css/styles.css.

  ⚠ INCONSISTÊNCIA - ID do checkbox "incluir-linha":
  ----------------------------------------------------
  Em rotas.js, a função exportarRota() lê:
    document.getElementById('incluir-linha')        ← ID no JS

  Mas em modal_rota.php, o checkbox tem:
    id="incluir-linha-rota"                          ← ID diferente no HTML!

  Resultado: incluir_linha será sempre null/undefined.
  Correção: mudar o HTML para id="incluir-linha"
  (ou mudar o JS para 'incluir-linha-rota', mas o HTML é mais fácil)

  ⚠ INCONSISTÊNCIA - Nomes das colunas da tabela fotos:
  -------------------------------------------------------
  O código PHP usa estas colunas reais:
    - "Arquivos" para o URL da foto   (em locais_crud.php e locais.php)
    - "Local_id" para a chave estrangeira

  Se criares a tabela com nomes diferentes (ex: "URL" ou "LocalID"),
  as fotos não serão guardadas nem carregadas. Usa exactamente:
    CREATE TABLE fotos (
        FotoID    INT AUTO_INCREMENT PRIMARY KEY,
        Arquivos  VARCHAR(500) NOT NULL,
        Local_id  INT NOT NULL,
        FOREIGN KEY (Local_id) REFERENCES locais(LocalID) ON DELETE CASCADE
    );

  ⚠ MÉTODO PUT NÃO USADO PELO FRONT-END:
  ----------------------------------------
  locais_crud.php tem o case 'PUT' implementado para editar pontos.
  Mas em mapa.js, a função submeterFormularioPonto() usa sempre POST,
  independentemente de estar em modo criação ou edição.
  A distinção é feita pelo campo "local_id": vazio = criar, preenchido = editar.
  O case 'PUT' no PHP nunca é chamado pelo código atual.

  ⚠ DRAG & DROP INCOMPLETO:
  ---------------------------
  A reordenação manual dos pontos na lista de rotas está apenas parcialmente
  implementada. O evento "drop" em rotas.js não faz a reordenação efetivamente.
  Precisaria de lógica para mover os elementos no DOM e actualizar a ordem.

  ⚠ AUTOCOMPLETE.JS VAZIO:
  --------------------------
  O ficheiro js/autocomplete.js está vazio. A lógica de autocomplete está
  toda implementada em mapa.js (função configurarAutocomplete).
  O ficheiro pode ser ignorado ou eliminado da inclusão em index.php.

  ⚠ FUNÇÃO abrirModalRota() DUPLICADA:
  ---------------------------------------
  Em mapa.js (última linha) existe uma versão placeholder:
    window.abrirModalRota = function() { alert('Em breve!'); }

  Em rotas.js existe a versão completa da mesma função.
  Como rotas.js é carregado depois de mapa.js, a versão completa prevalece.
  Mesmo assim, a versão placeholder em mapa.js deve ser removida.

  ℹ PASTA CACHE (geocode.php):
  ------------------------------
  O geocode.php cria automaticamente a pasta cache/ se não existir.
  Garante que o servidor web tem permissão de escrita na raiz do projeto,
  ou cria a pasta manualmente: mkdir cache e chmod 755 cache (em Linux).

  ℹ NOTA DE SEGURANÇA GERAL:
  ----------------------------
  Todas as APIs verificam sessão PHP antes de executar operações de escrita.
  As verificações de permissão existem tanto no front-end (JS) como no
  back-end (PHP) — o que está correto. Nunca confiar só no front-end.
  O geocode.php e exportar_rota.php bloqueiam utilizadores não autenticados
  com http_response_code(401).

================================================================================
  FIM DO DOCUMENTO
================================================================================

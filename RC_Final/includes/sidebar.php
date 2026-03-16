<div class="sidebar" id="sidebar">
    <!-- FILTROS -->
    <div class="sb-section">
        <div class="sb-title">
            <i class="fas fa-search"></i> Filtrar Pontos
        </div>

        <div class="filtro-campo">
            <label><i class="fas fa-tag"></i> Nome</label>
            <input type="text" id="filtro-nome" placeholder="Pesquisar por nome..." autocomplete="off">
            <div id="sugestoes-nome" class="sugestoes"></div>
        </div>

        <div class="filtro-campo">
            <label><i class="fas fa-layer-group"></i> Categoria</label>
            <select id="filtro-categoria">
                <option value="">Todas as categorias</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo $cat['Nome']; ?>" style="border-left: 3px solid <?php echo $cat['Cor']; ?>">
                        <?php echo $cat['Letra'] . ' - ' . $cat['Nome']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filtro-campo">
            <label><i class="fas fa-globe"></i> País</label>
            <input type="text" id="filtro-pais" placeholder="Ex: Portugal" autocomplete="off">
            <div id="sugestoes-pais" class="sugestoes"></div>
        </div>

        <div class="filtro-campo">
            <label><i class="fas fa-city"></i> Cidade</label>
            <input type="text" id="filtro-cidade" placeholder="Ex: Lisboa" autocomplete="off">
            <div id="sugestoes-cidade" class="sugestoes"></div>
        </div>

        <div class="sb-botoes">
            <button id="btn-filtrar" class="btn primary small">
                <i class="fas fa-filter"></i> Filtrar
            </button>
            <button id="btn-limpar-filtros" class="btn secondary small">
                <i class="fas fa-times"></i> Limpar
            </button>
        </div>
    </div>

    <!-- LISTA DE LOCAIS -->
    <div class="sb-section lista-section">
        <div class="sb-title">
            <i class="fas fa-map-marker-alt"></i> Pontos Turísticos
            <span class="badge-count" id="total-pontos">0</span>

            <?php if (isset($_SESSION['usuario_id'])): ?>
                <div class="selecao-acoes">
                    <button id="selecionar-todos" class="btn-icon" title="Selecionar todos">
                        <i class="fas fa-check-square"></i>
                    </button>
                    <button id="limpar-selecao" class="btn-icon" title="Limpar seleção">
                        <i class="fas fa-square"></i>
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <div id="lista-locais" class="sb-lista">
            <div class="sb-empty">
                <i class="fas fa-spinner fa-spin"></i> A carregar...
            </div>
        </div>
    </div>

    <!-- PAGINAÇÃO -->
    <div class="paginacao">
        <button id="btn-pagina-anterior" class="btn-pag" disabled>
            <i class="fas fa-chevron-left"></i> Anterior
        </button>
        <span id="info-pagina">1 / 1</span>
        <button id="btn-pagina-proxima" class="btn-pag" disabled>
            Próxima <i class="fas fa-chevron-right"></i>
        </button>
    </div>

    <?php if (isset($_SESSION['usuario_id'])): ?>
        <!-- BARRA DE SELEÇÃO PARA ROTA -->
        <div id="barra-selecao" class="barra-selecao" style="display: none;">
            <div class="selecao-info">
                <span id="contador-selecionados">0</span> pontos selecionados
            </div>
            <button id="btn-criar-rota-sidebar" class="btn primary small">
                <i class="fas fa-route"></i> Criar Rota
            </button>
        </div>
    <?php endif; ?>
</div>
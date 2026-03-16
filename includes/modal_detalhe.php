<div id="modal-detalhe" class="overlay" style="display: none;">
    <div class="modal-overlay" onclick="fecharModalDetalhe()"></div>
    <div class="modal sm">
        <div class="modal-head">
            <h2><i class="fas fa-info-circle"></i> <span id="det-nome">Detalhes</span></h2>
            <button class="btn-close" onclick="fecharModalDetalhe()">✕</button>
        </div>
        
        <div class="modal-body">
            <div id="det-cat" class="detalhe-cat"></div>
            
            <div class="detalhe-linha">
                <i class="fas fa-globe"></i>
                <span id="det-localizacao">—</span>
            </div>
            
            <div class="detalhe-linha">
                <i class="fas fa-map-pin"></i>
                <span id="det-morada">—</span>
            </div>
            
            <div class="detalhe-linha">
                <i class="fas fa-phone"></i>
                <span id="det-telefone">—</span>
            </div>
            
            <div class="detalhe-linha">
                <i class="fas fa-envelope"></i>
                <span id="det-email">—</span>
            </div>
            
            <div class="detalhe-linha">
                <i class="fas fa-globe"></i>
                <span id="det-website">—</span>
            </div>
            
            <div class="detalhe-linha">
                <i class="fas fa-crosshairs"></i>
                <span id="det-coordenadas">—</span>
            </div>
            
            <div class="detalhe-linha">
                <i class="fas fa-user"></i>
                <span id="det-criador">—</span>
            </div>
            
            <div class="detalhe-linha" id="det-descricao-container">
                <i class="fas fa-align-left"></i>
                <span id="det-descricao">—</span>
            </div>
        </div>
        
        <div class="modal-foot">
            <button class="btn secondary" onclick="fecharModalDetalhe()">
                <i class="fas fa-times"></i> Fechar
            </button>
            
            <div id="det-acoes" style="display: none;">
                <?php if (isset($_SESSION['usuario_id'])): ?>
                <button id="det-editar" class="btn secondary">
                    <i class="fas fa-edit"></i> Editar
                </button>
                <button id="det-apagar" class="btn danger">
                    <i class="fas fa-trash"></i> Apagar
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
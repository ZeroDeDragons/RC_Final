<div id="modal-rota" class="overlay" style="display: none;">
    <div class="modal">
        <div class="modal-head">
            <h2><i class="fas fa-route"></i> Criar Rota</h2>
            <button class="btn-close" onclick="fecharModalRota()">✕</button>
        </div>
        
        <div class="modal-body">
            <div class="selecao-resumo">
                <div class="selecao-contador" id="rota-contador">
                    <span id="rota-total-pontos">0</span> pontos selecionados
                </div>
                <button id="rota-limpar" class="btn-link">
                    <i class="fas fa-times"></i> Limpar seleção
                </button>
            </div>
            
            <div id="rota-lista-pontos" class="rota-lista">
                <!-- Pontos serão inseridos aqui via JS -->
            </div>
            
            <div class="rota-config">
                <h3>Configurações da Rota</h3>
                
                <div class="config-grupo">
                    <label>Ordenar por:</label>
                    <select id="rota-ordem">
                        <option value="selecao">Ordem de seleção</option>
                        <option value="nome">Nome (A-Z)</option>
                        <option value="distancia" selected>Distância (otimizada)</option>
                        <option value="categoria">Categoria</option>
                    </select>
                </div>
                
                <div class="config-grupo">
                    <label>Tipo de rota:</label>
                    <select id="rota-tipo">
                        <option value="aberta">Aberta (início → fim)</option>
                        <option value="circular">Circular (volta ao início)</option>
                    </select>
                </div>
                
                <div class="config-grupo">
                    <label>Formato de exportação:</label>
                    <select id="rota-formato">
                        <option value="kml">KML (Google Earth)</option>
                        <option value="gpx">GPX (GPS)</option>
                        <option value="csv">CSV (Excel/Sheets)</option>
                        <option value="url">Link do Google Maps</option>
                    </select>
                </div>
                
                <div class="config-grupo">
                    <label>Incluir no ficheiro:</label>
                    <div class="checkbox-opcoes">
                        <label><input type="checkbox" id="incluir-descricao" checked> Descrições</label>
                        <label><input type="checkbox" id="incluir-info" checked> Informações de contacto</label>
                        <label><input type="checkbox" id="incluir-linha-rota" checked> Linha da rota</label>
                    </div>
                </div>
            </div>
            
            <div id="rota-erro" class="msg erro" style="display: none;"></div>
        </div>
        
        <div class="modal-foot">
            <button class="btn secondary" onclick="fecharModalRota()">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button class="btn secondary" id="rota-visualizar">
                <i class="fas fa-eye"></i> Visualizar
            </button>
            <button class="btn primary" id="rota-exportar">
                <i class="fas fa-download"></i> Exportar
            </button>
            <button class="btn primary" id="rota-maps">
                <i class="fab fa-google"></i> Google Maps
            </button>
        </div>
    </div>
</div>
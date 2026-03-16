<div id="modal-criar" class="overlay" style="display: none;">
    <div class="modal-full">
        <div class="modal-full-head">
            <h2><i class="fas fa-plus-circle"></i> <span id="criar-titulo">Criar Novo Ponto</span></h2>
            <button class="btn-close" onclick="fecharModalCriar()">
                <i class="fas fa-times"></i> Sair
            </button>
        </div>

        <div class="modal-full-body">
            <div class="criar-layout">
                <!-- Mapa para seleção -->
                <div class="criar-mapa">
                    <div id="mapa-criar"></div>
                    <div class="mapa-instrucoes">
                        <i class="fas fa-hand-pointer"></i> Clique no mapa para adicionar um ponto
                    </div>
                </div>

                <!-- Formulário -->
                <div class="criar-form">
                    <form id="form-criar-ponto">
                        <input type="hidden" id="campo-local-id" name="local_id">
                        <input type="hidden" id="campo-latitude" name="latitude">
                        <input type="hidden" id="campo-longitude" name="longitude">

                        <div class="form-grupo">
                            <label><i class="fas fa-tag"></i> Nome *</label>
                            <input type="text" id="campo-nome" name="nome" required>
                        </div>

                        <div class="form-grupo">
                            <label><i class="fas fa-layer-group"></i> Categoria *</label>
                            <select id="campo-categoria" name="categoria_nome" required>
                                <option value="">Seleciona uma categoria</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo $cat['Nome']; ?>">
                                        <?php echo $cat['Letra'] . ' - ' . $cat['Nome']; ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="nova">➕ Criar nova categoria...</option>
                            </select>
                        </div>

                        <!-- Campo para nova categoria (aparece dinamicamente) -->
                        <div id="campo-nova-categoria" class="form-grupo" style="display: none;">
                            <label><i class="fas fa-palette"></i> Nova Categoria</label>
                            <div class="nova-categoria-grid">
                                <input type="text" id="nova-categoria-nome" placeholder="Nome">
                                <input type="color" id="nova-categoria-cor" value="#b7d630">
                                <input type="text" id="nova-categoria-letra" placeholder="Letra" maxlength="2">
                            </div>
                        </div>

                        <div class="form-grupo">
                            <label><i class="fas fa-globe"></i> País</label>
                            <input type="text" id="campo-pais" name="pais" placeholder="Portugal">
                        </div>

                        <div class="form-grupo">
                            <label><i class="fas fa-city"></i> Cidade</label>
                            <input type="text" id="campo-cidade" name="cidade" placeholder="Lisboa">
                        </div>

                        <div class="form-grupo">
                            <label><i class="fas fa-map-pin"></i> Morada</label>
                            <textarea id="campo-morada" name="morada" rows="2"></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-grupo">
                                <label><i class="fas fa-phone"></i> Telefone</label>
                                <input type="text" id="campo-telefone" name="telefone">
                            </div>
                            <div class="form-grupo">
                                <label><i class="fas fa-envelope"></i> Email</label>
                                <input type="email" id="campo-email" name="email_local">
                            </div>
                        </div>

                        <div class="form-grupo">
                            <label><i class="fas fa-globe"></i> Website</label>
                            <input type="url" id="campo-website" name="website" placeholder="https://...">
                        </div>

                        <div class="form-grupo">
                            <label><i class="fas fa-images"></i> Galeria de Fotos</label>

                            <div id="container-fotos-inputs" class="fotos-grid">
                                <div class="foto-input-item">
                                    <input type="url" name="foto_url[]" class="input-foto-link"
                                        placeholder="https://link-da-foto.jpg">
                                    <button type="button" class="btn-remove-foto" onclick="removerInputFoto(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="fotos-controles">
                                <button type="button" class="btn-acao-foto add" onclick="adicionarInputFoto()">
                                    <i class="fas fa-plus"></i> Adicionar Foto
                                </button>
                                <button type="button" id="btn-toggle-fotos" class="btn-acao-foto toggle"
                                    onclick="toggleInputsFotos()">
                                    <i class="fas fa-eye-slash"></i> Recolher Lista
                                </button>
                            </div>
                        </div>

                        <div class="form-grupo">
                            <label><i class="fas fa-align-left"></i> Descrição</label>
                            <textarea id="campo-descricao" name="descricao" rows="3"></textarea>
                        </div>

                        <div class="form-coordenadas">
                            <div class="coord-info">
                                <i class="fas fa-crosshairs"></i>
                                <span id="coord-mostrar">Clique no mapa</span>
                            </div>
                        </div>

                        <div id="form-erro" class="msg erro" style="display: none;"></div>

                        <div class="form-botoes">
                            <button type="button" class="btn secondary" onclick="fecharModalCriar()">
                                Cancelar
                            </button>
                            <button type="submit" class="btn primary">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
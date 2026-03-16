/**
 * mapa.js - Lógica principal do mapa
 */

let mapa;
let clusterGroup;
let marcadores = {};
let locaisAtuais = [];
let filtros = {
    nome: '',
    categoria: '',
    pais: '',
    cidade: ''
};
let paginaAtual = 1;
let totalPaginas = 1;
let pontosSelecionados = new Set();

// Variáveis para o modal de criação
let mapaCriar = null;
let marcadorTemporario = null;
let modoEdicao = false;
let pontoEmEdicao = null;

document.addEventListener('DOMContentLoaded', function () {
    console.log('🚀 Inicializando aplicação...');
    inicializarMapa();
    carregarLocais();
    configurarEventos();
    configurarFormularioCriacao();
});

// ============================================
// FUNÇÕES DO MAPA PRINCIPAL
// ============================================

function inicializarMapa() {
    console.log('🗺️ Inicializando mapa principal');
    
    // Mapa principal
    mapa = L.map('map').setView([39.5, -8.0], 7);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(mapa);

    clusterGroup = L.markerClusterGroup();
    mapa.addLayer(clusterGroup);
    
    console.log('✅ Mapa principal inicializado');
}

function configurarEventos() {
    // Toggle sidebar
    document.getElementById('toggle-sidebar')?.addEventListener('click', function () {
        document.getElementById('sidebar').classList.toggle('fechada');
        setTimeout(() => mapa.invalidateSize(), 300);
    });

    // Botões de filtro
    document.getElementById('btn-filtrar')?.addEventListener('click', aplicarFiltros);
    document.getElementById('btn-limpar-filtros')?.addEventListener('click', limparFiltros);

    // Paginação
    document.getElementById('btn-pagina-anterior')?.addEventListener('click', () => mudarPagina(-1));
    document.getElementById('btn-pagina-proxima')?.addEventListener('click', () => mudarPagina(1));

    // Autocomplete
    configurarAutocomplete('filtro-nome', 'nome');
    configurarAutocomplete('filtro-pais', 'pais');
    configurarAutocomplete('filtro-cidade', 'cidade');

    // Enter nos campos de filtro
    ['filtro-nome', 'filtro-pais', 'filtro-cidade', 'filtro-categoria'].forEach(id => {
        document.getElementById(id)?.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') aplicarFiltros();
        });
    });

    // Botão novo ponto
    document.getElementById('btn-novo-ponto')?.addEventListener('click', abrirModalCriar);

    // Botões de seleção
    document.getElementById('selecionar-todos')?.addEventListener('click', selecionarTodos);
    document.getElementById('limpar-selecao')?.addEventListener('click', limparSelecao);
    document.getElementById('btn-criar-rota-sidebar')?.addEventListener('click', abrirModalRota);
}

function carregarLocais() {
    const params = new URLSearchParams({
        nome: filtros.nome,
        categoria: filtros.categoria,
        pais: filtros.pais,
        cidade: filtros.cidade,
        pagina: paginaAtual
    });

    fetch('api/locais.php?' + params)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'ok') {
                locaisAtuais = data.locais;
                totalPaginas = data.total_paginas;

                atualizarMarcadores();
                atualizarLista();
                atualizarPaginacao(data.total);
            }
        })
        .catch(error => console.error('Erro ao carregar locais:', error));
}

function atualizarMarcadores() {
    clusterGroup.clearLayers();
    marcadores = {};

    locaisAtuais.forEach(local => {
        const icone = criarIconePersonalizado(local.categoria_cor, local.categoria_letra);

        const marcador = L.marker([local.latitude, local.longitude], { icon: icone });

        // Popup
        let popupConteudo = `
            <div class="popup-local">
                <div class="popup-nome">${local.nome}</div>
                <div class="popup-categoria" style="color:${local.categoria_cor}">
                    ${local.categoria_letra} ${local.categoria_nome}
                </div>
                <div class="popup-localizacao">${local.cidade || ''} ${local.pais || ''}</div>
        `;

        if (USUARIO_LOGADO) {
            const podeEditar = USUARIO_TIPO === 'Admin' || local.criador_id == USUARIO_ID;
            popupConteudo += `<div class="popup-acoes">`;
            popupConteudo += `<button class="btn-icon pequeno" onclick="abrirDetalhe(${local.id})">
                <i class="fas fa-info-circle"></i>
            </button>`;

            if (podeEditar) {
                popupConteudo += `
                    <button class="btn-icon pequeno" onclick="editarPonto(${local.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon pequeno danger" onclick="apagarPonto(${local.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
            }

            popupConteudo += `</div>`;
        } else {
            popupConteudo += `<div class="popup-acoes">
                <button class="btn-icon pequeno" onclick="abrirDetalhe(${local.id})">
                    <i class="fas fa-info-circle"></i> Ver detalhes
                </button>
            </div>`;
        }

        popupConteudo += `</div>`;

        marcador.bindPopup(popupConteudo);

        clusterGroup.addLayer(marcador);
        marcadores[local.id] = marcador;
    });
}

function criarIconePersonalizado(cor, letra) {
    return L.divIcon({
        className: 'marcador-personalizado',
        html: `<div style="background-color:${cor}"><span>${letra || '📍'}</span></div>`,
        iconSize: [30, 30],
        iconAnchor: [15, 30],
        popupAnchor: [0, -30]
    });
}

function atualizarLista() {
    const lista = document.getElementById('lista-locais');
    const total = document.getElementById('total-pontos');

    if (!lista) return;

    total.textContent = locaisAtuais.length;

    if (locaisAtuais.length === 0) {
        lista.innerHTML = '<div class="sb-empty"><i class="fas fa-search"></i> Nenhum local encontrado</div>';
        return;
    }

    let html = '';
    locaisAtuais.forEach(local => {
        const selecionado = pontosSelecionados.has(local.id);
        html += `
            <div class="local-card ${selecionado ? 'selecionado' : ''}" data-id="${local.id}">
                ${USUARIO_LOGADO ? `
                    <div class="local-check">
                        <input type="checkbox" class="selecionar-ponto" 
                               data-id="${local.id}" ${selecionado ? 'checked' : ''}>
                    </div>
                ` : ''}
                <div class="local-info" onclick="focarPonto(${local.id})">
                    <div class="local-nome">${local.nome}</div>
                    <div class="local-categoria" style="color:${local.categoria_cor}">
                        <span class="categoria-dot" style="background:${local.categoria_cor}"></span>
                        ${local.categoria_letra} ${local.categoria_nome}
                    </div>
                    <div class="local-localizacao">
                        <i class="fas fa-map-marker-alt"></i> 
                        ${local.cidade || ''} ${local.pais || ''}
                    </div>
                </div>
            </div>
        `;
    });

    lista.innerHTML = html;

    // Eventos dos checkboxes
    document.querySelectorAll('.selecionar-ponto').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const id = parseInt(this.dataset.id);
            if (this.checked) {
                pontosSelecionados.add(id);
                this.closest('.local-card').classList.add('selecionado');
            } else {
                pontosSelecionados.delete(id);
                this.closest('.local-card').classList.remove('selecionado');
            }
            atualizarBarraSelecao();
        });
    });
}

function atualizarBarraSelecao() {
    const barra = document.getElementById('barra-selecao');
    const contador = document.getElementById('contador-selecionados');

    if (!barra || !contador) return;

    const total = pontosSelecionados.size;
    contador.textContent = total;

    barra.style.display = total > 0 ? 'flex' : 'none';
}

function focarPonto(id) {
    const marcador = marcadores[id];
    if (marcador) {
        clusterGroup.zoomToShowLayer(marcador, () => {
            marcador.openPopup();
        });
    }
}

function atualizarPaginacao(total) {
    document.getElementById('info-pagina').textContent = `${paginaAtual} / ${totalPaginas}`;
    document.getElementById('btn-pagina-anterior').disabled = paginaAtual <= 1;
    document.getElementById('btn-pagina-proxima').disabled = paginaAtual >= totalPaginas;
}

function aplicarFiltros() {
    filtros = {
        nome: document.getElementById('filtro-nome').value,
        categoria: document.getElementById('filtro-categoria').value,
        pais: document.getElementById('filtro-pais').value,
        cidade: document.getElementById('filtro-cidade').value
    };
    paginaAtual = 1;
    carregarLocais();
}

function limparFiltros() {
    document.getElementById('filtro-nome').value = '';
    document.getElementById('filtro-categoria').value = '';
    document.getElementById('filtro-pais').value = '';
    document.getElementById('filtro-cidade').value = '';
    filtros = { nome: '', categoria: '', pais: '', cidade: '' };
    paginaAtual = 1;
    carregarLocais();
}

function mudarPagina(direcao) {
    const nova = paginaAtual + direcao;
    if (nova >= 1 && nova <= totalPaginas) {
        paginaAtual = nova;
        carregarLocais();
    }
}

function configurarAutocomplete(inputId, tipo) {
    const input = document.getElementById(inputId);
    const sugestoes = document.getElementById(`sugestoes-${tipo}`);

    if (!input || !sugestoes) return;

    let timeout;

    input.addEventListener('input', function () {
        clearTimeout(timeout);
        const termo = this.value.trim();

        if (termo.length < 2) {
            sugestoes.style.display = 'none';
            return;
        }

        timeout = setTimeout(() => {
            fetch(`api/autocomplete.php?tipo=${tipo}&termo=${encodeURIComponent(termo)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        sugestoes.innerHTML = data.map(item =>
                            `<div class="sugestao-item" onclick="selecionarSugestao('${inputId}', '${item.replace(/'/g, "\\'")}')">
                                ${item}
                            </div>`
                        ).join('');
                        sugestoes.style.display = 'block';
                    } else {
                        sugestoes.style.display = 'none';
                    }
                });
        }, 300);
    });

    document.addEventListener('click', function (e) {
        if (!sugestoes.contains(e.target) && e.target !== input) {
            sugestoes.style.display = 'none';
        }
    });
}

// Função global para selecionar sugestão
window.selecionarSugestao = function (inputId, valor) {
    document.getElementById(inputId).value = valor;
    document.getElementById(`sugestoes-${inputId.split('-')[1]}`).style.display = 'none';
    aplicarFiltros();
};

// Funções de seleção
function selecionarTodos() {
    locaisAtuais.forEach(local => {
        pontosSelecionados.add(local.id);
    });
    atualizarLista();
    atualizarBarraSelecao();
}

function limparSelecao() {
    pontosSelecionados.clear();
    atualizarLista();
    atualizarBarraSelecao();
}

// Funções de detalhe
window.abrirDetalhe = function (id) {
    const local = locaisAtuais.find(l => l.id === id);
    if (!local) return;

    document.getElementById('det-nome').textContent = local.nome;
    document.getElementById('det-cat').textContent = `${local.categoria_letra} ${local.categoria_nome}`;
    document.getElementById('det-cat').style.backgroundColor = local.categoria_cor;
    document.getElementById('det-localizacao').textContent =
        [local.cidade, local.pais].filter(Boolean).join(', ') || '—';
    document.getElementById('det-morada').textContent = local.morada || '—';
    document.getElementById('det-telefone').textContent = local.telefone || '—';
    document.getElementById('det-email').textContent = local.email || '—';
    document.getElementById('det-website').innerHTML = local.website ?
        `<a href="${local.website}" target="_blank">${local.website}</a>` : '—';
    document.getElementById('det-coordenadas').textContent =
        `${local.latitude.toFixed(6)}, ${local.longitude.toFixed(6)}`;
    document.getElementById('det-criador').textContent = local.criador_nome || '—';
    document.getElementById('det-descricao').textContent = local.descricao || '—';

    const podeEditar = USUARIO_TIPO === 'Admin' || local.criador_id == USUARIO_ID;
    document.getElementById('det-acoes').style.display = podeEditar ? 'flex' : 'none';

    document.getElementById('modal-detalhe').style.display = 'flex';
};

window.fecharModalDetalhe = function () {
    document.getElementById('modal-detalhe').style.display = 'none';
};

// ============================================
// FUNÇÕES DO MODAL DE CRIAÇÃO/EDIÇÃO
// ============================================

function configurarFormularioCriacao() {
    console.log('🔧 Configurando formulário de criação');
    
    const form = document.getElementById('form-criar-ponto');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submeterFormularioPonto();
        });
    }
    
    const categoriaSelect = document.getElementById('campo-categoria');
    if (categoriaSelect) {
        categoriaSelect.addEventListener('change', function() {
            const novaCatDiv = document.getElementById('campo-nova-categoria');
            if (novaCatDiv) {
                novaCatDiv.style.display = this.value === 'nova' ? 'block' : 'none';
            }
        });
    }
}

/**
 * Abre o modal para criar novo ponto
 */
window.abrirModalCriar = function() {
    console.log('🔵 abrirModalCriar() chamada');
    
    if (!USUARIO_LOGADO) {
        alert('Precisa de fazer login para criar pontos.');
        window.location.href = 'login.php';
        return;
    }
    
    modoEdicao = false;
    pontoEmEdicao = null;
    
    // Verificar se o modal existe
    const modal = document.getElementById('modal-criar');
    if (!modal) {
        console.error('❌ Modal de criar não encontrado!');
        alert('Erro: Modal de criação não encontrado.');
        return;
    }
    
    // Limpar formulário
    const form = document.getElementById('form-criar-ponto');
    if (form) form.reset();
    
    // Limpar campos específicos
    document.getElementById('campo-local-id').value = '';
    document.getElementById('campo-latitude').value = '';
    document.getElementById('campo-longitude').value = '';
    
    const coordMostrar = document.getElementById('coord-mostrar');
    if (coordMostrar) coordMostrar.textContent = 'Clique no mapa para definir a localização';
    
    const novaCatDiv = document.getElementById('campo-nova-categoria');
    if (novaCatDiv) novaCatDiv.style.display = 'none';
    
    const titulo = document.getElementById('criar-titulo');
    if (titulo) titulo.textContent = 'Criar Novo Ponto';
    
    const erroDiv = document.getElementById('form-erro');
    if (erroDiv) erroDiv.style.display = 'none';
    
    // Mostrar modal
    modal.style.display = 'flex';
    console.log('✅ Modal aberto');
    
    // Inicializar mapa de criação (com delay para o modal renderizar)
    setTimeout(() => {
        inicializarMapaCriacaoModal();
    }, 200);
}

/**
 * Inicializa o mapa dentro do modal de criação
 */
function inicializarMapaCriacaoModal() {
    console.log('🗺️ Inicializando mapa de criação');
    
    const container = document.getElementById('mapa-criar');
    
    if (!container) {
        console.error('❌ Container mapa-criar não encontrado');
        return;
    }
    
    // Se já existe mapa, apenas invalidar tamanho
    if (mapaCriar) {
        console.log('Mapa já existe, invalidando tamanho');
        mapaCriar.invalidateSize();
        return;
    }
    
    // Criar mapa
    try {
        mapaCriar = L.map('mapa-criar').setView([39.5, -8.0], 7);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(mapaCriar);
        
        // Evento de clique no mapa
        mapaCriar.on('click', function(e) {
            if (!modoEdicao) {
                // Só permite clicar em modo criação
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;
                
                console.log(`📍 Clique no mapa: ${lat}, ${lng}`);
                
                // Atualizar campos
                document.getElementById('campo-latitude').value = lat.toFixed(6);
                document.getElementById('campo-longitude').value = lng.toFixed(6);
                document.getElementById('coord-mostrar').textContent = 
                    `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
                
                // Remover marcador anterior
                if (marcadorTemporario) {
                    mapaCriar.removeLayer(marcadorTemporario);
                }
                
                // Adicionar novo marcador
                marcadorTemporario = L.marker([lat, lng]).addTo(mapaCriar);
            }
        });
        
        console.log('✅ Mapa de criação inicializado');
    } catch (error) {
        console.error('❌ Erro ao criar mapa:', error);
    }
}

/**
 * Abre o modal para editar ponto existente
 */
window.editarPonto = function(id) {
    console.log('✏️ Editar ponto:', id);
    
    const local = locaisAtuais.find(l => l.id === id);
    if (!local) {
        console.error('Ponto não encontrado:', id);
        return;
    }
    
    if (!USUARIO_LOGADO) {
        alert('Precisa de fazer login para editar pontos.');
        return;
    }
    
    // Verificar permissões
    if (USUARIO_TIPO !== 'Admin' && local.criador_id != USUARIO_ID) {
        alert('Não tem permissão para editar este ponto.');
        return;
    }
    
    modoEdicao = true;
    pontoEmEdicao = local;
    
    // Verificar se o modal existe
    const modal = document.getElementById('modal-criar');
    if (!modal) {
        console.error('❌ Modal de criar não encontrado!');
        return;
    }
    
    // Preencher formulário
    document.getElementById('campo-local-id').value = local.id;
    document.getElementById('campo-nome').value = local.nome || '';
    document.getElementById('campo-categoria').value = local.categoria_id || '';
    document.getElementById('campo-pais').value = local.pais || '';
    document.getElementById('campo-cidade').value = local.cidade || '';
    document.getElementById('campo-morada').value = local.morada || '';
    document.getElementById('campo-telefone').value = local.telefone || '';
    document.getElementById('campo-email').value = local.email || '';
    document.getElementById('campo-website').value = local.website || '';
    document.getElementById('campo-descricao').value = local.descricao || '';
    document.getElementById('campo-latitude').value = local.latitude;
    document.getElementById('campo-longitude').value = local.longitude;
    document.getElementById('coord-mostrar').textContent = 
        `Lat: ${local.latitude.toFixed(6)}, Lng: ${local.longitude.toFixed(6)}`;
    
    document.getElementById('campo-nova-categoria').style.display = 'none';
    document.getElementById('criar-titulo').textContent = 'Editar Ponto';
    document.getElementById('form-erro').style.display = 'none';
    
    // Mostrar modal
    modal.style.display = 'flex';
    
    // Inicializar mapa de criação e colocar marcador
    setTimeout(() => {
        inicializarMapaCriacaoModal();
        
        if (mapaCriar) {
            mapaCriar.setView([local.latitude, local.longitude], 15);
            
            if (marcadorTemporario) {
                mapaCriar.removeLayer(marcadorTemporario);
            }
            
            marcadorTemporario = L.marker([local.latitude, local.longitude]).addTo(mapaCriar);
        }
    }, 200);
}

/**
 * Fecha o modal de criação
 */
window.fecharModalCriar = function() {
    console.log('🔴 Fechar modal criar');
    const modal = document.getElementById('modal-criar');
    if (modal) modal.style.display = 'none';
    
    // Limpar marcador temporário
    if (marcadorTemporario && mapaCriar) {
        mapaCriar.removeLayer(marcadorTemporario);
        marcadorTemporario = null;
    }
}

/**
 * Apaga um ponto (com confirmação)
 */
window.apagarPonto = function(id) {
    console.log('🗑️ Apagar ponto:', id);
    
    const local = locaisAtuais.find(l => l.id === id);
    
    if (!local) {
        console.error('Ponto não encontrado:', id);
        return;
    }
    
    // Verificar permissões
    if (USUARIO_TIPO !== 'Admin' && local.criador_id != USUARIO_ID) {
        alert('Não tem permissão para apagar este ponto.');
        return;
    }
    
    if (!confirm(`Tem a certeza que deseja apagar "${local.nome}"?`)) {
        return;
    }
    
    fetch(`api/locais_crud.php?id=${id}`, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'ok') {
            // Se o ponto estava selecionado, remover da seleção
            pontosSelecionados.delete(id);
            
            // Fechar modal de detalhe se estiver aberto
            fecharModalDetalhe();
            
            // Recarregar locais
            carregarLocais();
            
            alert('Ponto apagado com sucesso!');
        } else {
            alert(data.mensagem || 'Erro ao apagar ponto.');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro de ligação ao servidor.');
    });
}

/**
 * Submeter formulário de criação/edição
 */
function submeterFormularioPonto() {
    console.log('📤 Submeter formulário');
    
    const erroDiv = document.getElementById('form-erro');
    erroDiv.style.display = 'none';
    
    // Validar campos obrigatórios
    const nome = document.getElementById('campo-nome').value.trim();
    const categoria = document.getElementById('campo-categoria').value;
    const latitude = document.getElementById('campo-latitude').value;
    const longitude = document.getElementById('campo-longitude').value;
    
    if (!nome) {
        mostrarErroForm('O nome é obrigatório.');
        return;
    }
    
    if (!categoria) {
        mostrarErroForm('Selecione uma categoria.');
        return;
    }
    
    if (!latitude || !longitude) {
        mostrarErroForm('Clique no mapa para definir a localização.');
        return;
    }
    
    // Recolher dados do formulário
    const dados = {
        local_id: document.getElementById('campo-local-id').value,
        nome: nome,
        categoria_id: categoria,
        pais: document.getElementById('campo-pais').value,
        cidade: document.getElementById('campo-cidade').value,
        morada: document.getElementById('campo-morada').value,
        telefone: document.getElementById('campo-telefone').value,
        email: document.getElementById('campo-email').value,
        website: document.getElementById('campo-website').value,
        descricao: document.getElementById('campo-descricao').value,
        latitude: parseFloat(latitude),
        longitude: parseFloat(longitude)
    };
    
    // Se for nova categoria
    if (categoria === 'nova') {
        const novaCatNome = document.getElementById('nova-categoria-nome').value.trim();
        const novaCatCor = document.getElementById('nova-categoria-cor').value;
        const novaCatLetra = document.getElementById('nova-categoria-letra').value.trim();
        
        if (!novaCatNome) {
            mostrarErroForm('Preencha o nome da nova categoria.');
            return;
        }
        
        dados.nova_categoria = {
            nome: novaCatNome,
            cor: novaCatCor,
            letra: novaCatLetra || '?'
        };
    }
    
    // Desabilitar botão
    const btn = document.querySelector('#form-criar-ponto button[type="submit"]');
    const btnText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> A guardar...';
    
    // Enviar para API
    fetch('api/locais_crud.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dados)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'ok') {
            // Fechar modal e recarregar locais
            fecharModalCriar();
            carregarLocais();
            
            // Mostrar mensagem de sucesso
            alert(data.mensagem || 'Ponto guardado com sucesso!');
        } else {
            mostrarErroForm(data.mensagem || 'Erro ao guardar ponto.');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        mostrarErroForm('Erro de ligação ao servidor.');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = btnText;
    });
}

/**
 * Mostra mensagem de erro no formulário
 */
function mostrarErroForm(mensagem) {
    const erroDiv = document.getElementById('form-erro');
    if (erroDiv) {
        erroDiv.textContent = mensagem;
        erroDiv.style.display = 'block';
    }
}

// ============================================
// FUNÇÕES DE ROTA (placeholder - serão implementadas depois)
// ============================================

window.abrirModalRota = function() {
    alert('Funcionalidade de rotas será implementada em breve!');
};
/**
 * autocomplete.js - Funcionalidades de autocomplete
 */

// Reutilizar funções definidas no mapa.js

/**
 * mapa.js - Lógica principal do mapa
 */

let mapa, mapaCriar;
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

document.addEventListener('DOMContentLoaded', function() {
    inicializarMapa();
    carregarLocais();
    configurarEventos();
});

function inicializarMapa() {
    // Mapa principal
    mapa = L.map('map').setView([39.5, -8.0], 7);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(mapa);
    
    clusterGroup = L.markerClusterGroup();
    mapa.addLayer(clusterGroup);
    
    // Se estiver logado, preparar mapa de criação
    if (typeof USUARIO_LOGADO !== 'undefined' && USUARIO_LOGADO) {
        inicializarMapaCriacao();
    }
}

function inicializarMapaCriacao() {
    // Mapa para criação (será inicializado quando abrir modal)
    const mapContainer = document.getElementById('mapa-criar');
    if (!mapContainer) return;
}

function configurarEventos() {
    // Toggle sidebar
    document.getElementById('toggle-sidebar')?.addEventListener('click', function() {
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
        document.getElementById(id)?.addEventListener('keypress', function(e) {
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
        html: `<div style="background-color:${cor}">${letra || '📍'}</div>`,
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
        checkbox.addEventListener('change', function() {
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
    
    input.addEventListener('input', function() {
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
                            `<div class="sugestao-item" onclick="selecionarSugestao('${inputId}', '${item}')">
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
    
    document.addEventListener('click', function(e) {
        if (!sugestoes.contains(e.target) && e.target !== input) {
            sugestoes.style.display = 'none';
        }
    });
}

// Função global para selecionar sugestão
window.selecionarSugestao = function(inputId, valor) {
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
window.abrirDetalhe = function(id) {
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

window.fecharModalDetalhe = function() {
    document.getElementById('modal-detalhe').style.display = 'none';
};
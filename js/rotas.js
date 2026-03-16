/**
 * rotas.js - Seleção e exportação de rotas
 */

function abrirModalRota() {
    if (pontosSelecionados.size < 2) {
        alert('Selecione pelo menos 2 pontos para criar uma rota.');
        return;
    }
    
    const modal = document.getElementById('modal-rota');
    const lista = document.getElementById('rota-lista-pontos');
    
    // Carregar pontos selecionados
    const pontosArray = locaisAtuais.filter(l => pontosSelecionados.has(l.id));
    
    let html = '';
    pontosArray.forEach(ponto => {
        html += `
            <div class="rota-ponto-item" data-id="${ponto.id}">
                <i class="fas fa-grip-vertical handle"></i>
                <span class="ponto-nome">${ponto.nome}</span>
                <span class="ponto-categoria" style="color:${ponto.categoria_cor}">
                    ${ponto.categoria_letra}
                </span>
                <button class="btn-icon pequeno" onclick="removerPontoRota(${ponto.id})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
    });
    
    lista.innerHTML = html;
    document.getElementById('rota-total-pontos').textContent = pontosSelecionados.size;
    modal.style.display = 'flex';
    
    // Inicializar drag & drop
    inicializarDragDrop();
}

function fecharModalRota() {
    document.getElementById('modal-rota').style.display = 'none';
}

function removerPontoRota(id) {
    pontosSelecionados.delete(id);
    atualizarLista();
    atualizarBarraSelecao();
    
    if (pontosSelecionados.size < 2) {
        fecharModalRota();
    } else {
        abrirModalRota(); // Recarregar
    }
}

function inicializarDragDrop() {
    // Implementar com SortableJS ou similar
    // Versão simples:
    const items = document.querySelectorAll('.rota-ponto-item');
    items.forEach(item => {
        item.setAttribute('draggable', 'true');
        
        item.addEventListener('dragstart', function(e) {
            e.dataTransfer.setData('text/plain', this.dataset.id);
        });
        
        item.addEventListener('dragover', function(e) {
            e.preventDefault();
        });
        
        item.addEventListener('drop', function(e) {
            e.preventDefault();
            const id = e.dataTransfer.getData('text/plain');
            // Implementar reordenação
        });
    });
}

document.getElementById('rota-ordem')?.addEventListener('change', function() {
    if (this.value === 'selecao') {
        document.querySelectorAll('.handle').forEach(h => h.style.display = 'inline-block');
    } else {
        document.querySelectorAll('.handle').forEach(h => h.style.display = 'none');
    }
});

document.getElementById('rota-visualizar')?.addEventListener('click', function() {
    // Desenhar linha temporária no mapa
    const pontos = locaisAtuais.filter(l => pontosSelecionados.has(l.id));
    
    // Remover linha anterior se existir
    if (window.linhaRota) {
        mapa.removeLayer(window.linhaRota);
    }
    
    const latlngs = pontos.map(p => [p.latitude, p.longitude]);
    window.linhaRota = L.polyline(latlngs, { color: '#b7d630', weight: 4 }).addTo(mapa);
    
    mapa.fitBounds(window.linhaRota.getBounds());
    
    fecharModalRota();
});

document.getElementById('rota-exportar')?.addEventListener('click', function() {
    exportarRota(false);
});

document.getElementById('rota-maps')?.addEventListener('click', function() {
    exportarRota(true);
});

function exportarRota(abrirMaps = false) {
    const formato = document.getElementById('rota-formato').value;
    const ordem = document.getElementById('rota-ordem').value;
    const tipoRota = document.getElementById('rota-tipo').value;
    const incluirDescricao = document.getElementById('incluir-descricao').checked;
    const incluirInfo = document.getElementById('incluir-info').checked;
    const incluirLinha = document.getElementById('incluir-linha').checked;
    
    const dados = {
        pontos: Array.from(pontosSelecionados),
        formato: abrirMaps ? 'url' : formato,
        ordem: ordem,
        tipo_rota: tipoRota,
        incluir_descricao: incluirDescricao,
        incluir_info: incluirInfo,
        incluir_linha: incluirLinha
    };
    
    fetch('api/exportar_rota.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dados)
    })
    .then(response => {
        if (formato === 'url' || abrirMaps) {
            return response.json();
        } else {
            return response.blob();
        }
    })
    .then(data => {
        if (formato === 'url' || abrirMaps) {
            // Abrir Google Maps
            window.open(data.url, '_blank');
            fecharModalRota();
        } else {
            // Download do ficheiro
            const url = window.URL.createObjectURL(data);
            const a = document.createElement('a');
            a.href = url;
            a.download = `rota_${new Date().toISOString().slice(0,10)}.${formato}`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            fecharModalRota();
        }
    })
    .catch(error => {
        console.error('Erro na exportação:', error);
        document.getElementById('rota-erro').textContent = 'Erro ao exportar rota';
        document.getElementById('rota-erro').style.display = 'block';
    });
}
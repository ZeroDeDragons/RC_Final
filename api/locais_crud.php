<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../db.php';

// Verificar autenticação
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Não autenticado']);
    exit;
}

$metodo = $_SERVER['REQUEST_METHOD'];

try {
    switch ($metodo) {
        case 'POST': // Criar novo local
            $dados = json_decode(file_get_contents('php://input'), true);
            
            // Validar campos obrigatórios
            if (empty($dados['nome']) || empty($dados['categoria_id']) || 
                empty($dados['latitude']) || empty($dados['longitude'])) {
                throw new Exception('Campos obrigatórios em falta');
            }
            
            // Se for nova categoria, criar primeiro
            if ($dados['categoria_id'] === 'nova' && !empty($dados['nova_categoria'])) {
                $stmt = $pdo->prepare("
                    INSERT INTO categorias (Nome, Cor, Letra) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([
                    $dados['nova_categoria']['nome'],
                    $dados['nova_categoria']['cor'] ?? '#b7d630',
                    $dados['nova_categoria']['letra'] ?? '?'
                ]);
                $categoria_id = $pdo->lastInsertId();
            } else {
                $categoria_id = $dados['categoria_id'];
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO locais 
                (Nome, Categoria_id, Criado_por, Pais, Cidade, Morada, 
                 Telefone, Email, Website, Descricao, Latitude, Longitude)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $dados['nome'],
                $categoria_id,
                $_SESSION['usuario_id'],
                $dados['pais'] ?? null,
                $dados['cidade'] ?? null,
                $dados['morada'] ?? null,
                $dados['telefone'] ?? null,
                $dados['email'] ?? null,
                $dados['website'] ?? null,
                $dados['descricao'] ?? null,
                $dados['latitude'],
                $dados['longitude']
            ]);
            
            echo json_encode([
                'status' => 'ok',
                'mensagem' => 'Local criado com sucesso',
                'id' => $pdo->lastInsertId()
            ]);
            break;
            
        case 'PUT': // Editar local
            parse_str(file_get_contents('php://input'), $dados);
            
            // Verificar permissões
            $local_id = $dados['local_id'] ?? 0;
            $stmt = $pdo->prepare("SELECT Criado_por FROM locais WHERE LocalID = ?");
            $stmt->execute([$local_id]);
            $local = $stmt->fetch();
            
            if (!$local) {
                throw new Exception('Local não encontrado');
            }
            
            $podeEditar = ($_SESSION['tipo'] === 'Admin' || $local['Criado_por'] == $_SESSION['usuario_id']);
            if (!$podeEditar) {
                throw new Exception('Sem permissão para editar');
            }
            
            $stmt = $pdo->prepare("
                UPDATE locais SET
                    Nome = ?, Categoria_id = ?, Pais = ?, Cidade = ?,
                    Morada = ?, Telefone = ?, Email = ?, Website = ?,
                    Descricao = ?, Latitude = ?, Longitude = ?
                WHERE LocalID = ?
            ");
            
            $stmt->execute([
                $dados['nome'],
                $dados['categoria_id'],
                $dados['pais'] ?? null,
                $dados['cidade'] ?? null,
                $dados['morada'] ?? null,
                $dados['telefone'] ?? null,
                $dados['email'] ?? null,
                $dados['website'] ?? null,
                $dados['descricao'] ?? null,
                $dados['latitude'],
                $dados['longitude'],
                $local_id
            ]);
            
            echo json_encode(['status' => 'ok', 'mensagem' => 'Local atualizado']);
            break;
            
        case 'DELETE': // Apagar local
            $local_id = $_GET['id'] ?? 0;
            
            // Verificar permissões
            $stmt = $pdo->prepare("SELECT Criado_por FROM locais WHERE LocalID = ?");
            $stmt->execute([$local_id]);
            $local = $stmt->fetch();
            
            if (!$local) {
                throw new Exception('Local não encontrado');
            }
            
            $podeApagar = ($_SESSION['tipo'] === 'Admin' || $local['Criado_por'] == $_SESSION['usuario_id']);
            if (!$podeApagar) {
                throw new Exception('Sem permissão para apagar');
            }
            
            $stmt = $pdo->prepare("DELETE FROM locais WHERE LocalID = ?");
            $stmt->execute([$local_id]);
            
            echo json_encode(['status' => 'ok', 'mensagem' => 'Local apagado']);
            break;
            
        default:
            throw new Exception('Método não suportado');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
}
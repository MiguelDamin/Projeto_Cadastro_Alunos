<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}
require 'conexao.php';

$mensagem_sucesso = '';
$mensagem_erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_aviso'])) {
    $titulo = trim($_POST['titulo'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');
    $nivel_permissao = trim($_POST['nivel_permissao'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $data_publicacao = trim($_POST['data_publicacao'] ?? '');
    $duracao_dias = (int)($_POST['duracao_dias'] ?? 7); // Nova funcionalidade
    
    $data_publicacao = empty($data_publicacao) ? date('Y-m-d H:i:s') : date('Y-m-d H:i:s', strtotime($data_publicacao));
    
    // Calcula a data de expiração baseada na duração escolhida
    if ($duracao_dias == 0) {
        // Opção de teste: 1 minuto
        $data_expiracao = date('Y-m-d H:i:s', strtotime('+1 minute'));
    } else {
        // Opções normais: 7, 15 ou 30 dias
        $data_expiracao = date('Y-m-d H:i:s', strtotime("+{$duracao_dias} days"));
    }
    
    if (empty($titulo) || empty($mensagem) || empty($nivel_permissao) || empty($status)) {
        $mensagem_erro = "Todos os campos obrigatórios devem ser preenchidos.";
    } else {
        try {
            // Adicionamos o campo data_expiracao na inserção
            $sql = "INSERT INTO avisos (titulo, mensagem, data_publicacao, usuario_id, nivel_permissao, status, data_expiracao) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$titulo, $mensagem, $data_publicacao, $_SESSION['usuario_id'], $nivel_permissao, $status, $data_expiracao])) {
                $duracao_texto = $duracao_dias == 0 ? '1 minuto' : $duracao_dias . ' dias';
                $mensagem_sucesso = "Aviso publicado com sucesso! Será exibido nos avisos recentes por {$duracao_texto}.";
            } else {
                $mensagem_erro = "Falha ao salvar o aviso no banco de dados.";
            }
        } catch (PDOException $e) {
            error_log("Erro ao cadastrar aviso: " . $e->getMessage());
            $mensagem_erro = "Ocorreu um erro no banco de dados: " . $e->getMessage();
        }
    }
}

try {
    // Consulta atualizada para incluir a data de expiração
    $avisos = $pdo->query("
        SELECT a.*, u.nome_completo as autor_nome,
               CASE 
                   WHEN TIMESTAMPDIFF(MINUTE, NOW(), a.data_expiracao) <= 60 THEN CONCAT(TIMESTAMPDIFF(MINUTE, NOW(), a.data_expiracao), ' min')
                   WHEN TIMESTAMPDIFF(HOUR, NOW(), a.data_expiracao) <= 24 THEN CONCAT(TIMESTAMPDIFF(HOUR, NOW(), a.data_expiracao), ' h')
                   ELSE CONCAT(TIMESTAMPDIFF(DAY, NOW(), a.data_expiracao), ' dias')
               END as tempo_restante,
               CASE 
                   WHEN a.data_expiracao <= NOW() THEN 1 
                   ELSE 0 
               END as expirado
        FROM avisos a 
        LEFT JOIN usuarios u ON a.usuario_id = u.id 
        ORDER BY a.data_publicacao DESC 
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $avisos = [];
    $erro_listagem = "Erro ao carregar avisos: " . $e->getMessage();
}

require 'templates/header.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Avisos - Sistema Escolar</title>
    <style>
        /* === GERAL E LAYOUT === */
        :root {
            --cor-primaria: #007bff;
            --cor-sucesso: #28a745;
            --cor-alerta: #ffc107;
            --cor-erro: #dc3545;
            --cor-cinza-claro: #f8f9fa;
            --cor-cinza-borda: #dee2e6;
            --cor-texto: #212529;
            --cor-texto-claro: #6c757d;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f4f7f9;
            color: var(--cor-texto);
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
        }
        h2, h3 {
            color: var(--cor-texto);
            border-bottom: 2px solid var(--cor-cinza-borda);
            padding-bottom: 10px;
            margin-bottom: 25px;
        }

        /* === FORMULÁRIO === */
        .card-form {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 40px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .full-width {
            grid-column: 1 / -1;
        }
        label {
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        input[type="text"], input[type="datetime-local"], select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--cor-cinza-borda);
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--cor-primaria);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
        }
        textarea {
            resize: vertical;
            min-height: 120px;
        }
        .form-group small {
            color: var(--cor-texto-claro);
            font-size: 0.8rem;
            margin-top: 5px;
        }
        .btn-submit {
            background-color: var(--cor-primaria);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: background-color 0.2s;
            margin-top: 10px;
        }
        .btn-submit:hover {
            background-color: #0056b3;
        }

        /* === ALERTAS === */
        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            font-weight: 500;
        }
        .alert-success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }

        /* === TABELA === */
        .table-container {
            overflow-x: auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--cor-cinza-borda);
            vertical-align: middle;
        }
        th {
            background-color: var(--cor-cinza-claro);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            color: var(--cor-texto-claro);
        }
        tr:last-child td {
            border-bottom: none;
        }
        tr:hover {
            background-color: #f1f5f8;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: white;
            letter-spacing: 0.5px;
        }
        .status-ativo { background-color: var(--cor-sucesso); }
        .status-inativo { background-color: var(--cor-erro); }
        .status-rascunho { background-color: var(--cor-alerta); color: var(--cor-texto); }
        
        /* === NOVOS ESTILOS PARA TEMPO DE EXPIRAÇÃO === */
        .tempo-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        .tempo-urgente { background-color: #ffebee; color: #c62828; }
        .tempo-atencao { background-color: #fff3e0; color: #ef6c00; }
        .tempo-normal { background-color: #e8f5e9; color: #2e7d32; }
        .tempo-expirado { background-color: #f5f5f5; color: #757575; text-decoration: line-through; }
        
        .aviso-expirado {
            opacity: 0.6;
            background-color: #fafafa !important;
        }
        
        .campo-duracao {
            background-color: #f0f8ff;
            border: 2px solid #007bff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .campo-duracao label {
            color: #0056b3;
            font-weight: 700;
            font-size: 1rem;
        }
        
        .actions a {
            display: inline-block;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.8rem;
            font-weight: 600;
            transition: opacity 0.2s;
        }
        .actions a:hover { opacity: 0.8; }
        .btn-edit { background-color: var(--cor-alerta); color: var(--cor-texto); }
        .btn-delete { background-color: var(--cor-erro); color: white; }

        /* === RESPONSIVIDADE === */
        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
            body { padding: 10px; }
            .container { padding: 10px; }
            .card-form { padding: 20px; }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Gerenciar Avisos</h2>

    <!-- Mensagens de Feedback -->
    <?php if (!empty($mensagem_sucesso)): ?>
        <div class="alert alert-success"><?php echo $mensagem_sucesso; ?></div>
    <?php endif; ?>
    <?php if (!empty($mensagem_erro)): ?>
        <div class="alert alert-danger"><?php echo $mensagem_erro; ?></div>
    <?php endif; ?>

    <!-- Formulário de Cadastro -->
    <div class="card-form">
        <h3>Publicar Novo Aviso</h3>
        <form method="POST" action="">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="titulo">Título do Aviso</label>
                    <input type="text" name="titulo" id="titulo" placeholder="Ex: Reunião de Pais e Mestres" required>
                </div>
                <div class="form-group full-width">
                    <label for="mensagem">Mensagem</label>
                    <textarea name="mensagem" id="mensagem" placeholder="Digite os detalhes do aviso aqui..." required></textarea>
                </div>
                
                <!-- NOVO CAMPO: DURAÇÃO DO AVISO -->
                <div class="form-group campo-duracao full-width">
                    <label for="duracao_dias">⏰ Por quanto tempo este aviso deve aparecer nos "Avisos Recentes"?</label>
                    <select name="duracao_dias" id="duracao_dias" required style="border-color: #007bff;">
                        <option value="">Selecione o tempo de exibição...</option>
                        <option value="0">1 minuto (apenas para teste)</option>
                        <option value="7" selected>7 dias</option>
                        <option value="15">15 dias</option>
                        <option value="30">30 dias</option>
                    </select>
                    <small>Após este período, o aviso não aparecerá mais na seção "Avisos Recentes" do painel principal.</small>
                </div>
                
                <div class="form-group">
                    <label for="nivel_permissao">Visível para</label>
                    <select name="nivel_permissao" id="nivel_permissao" required>
                        <option value="">Selecione um grupo...</option>
                        <option value="publico">Público (Todos)</option>
                        <option value="professores">Apenas Professores</option>
                        <option value="administradores">Apenas Administradores</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" required>
                        <option value="ativo" selected>Ativo (Publicar imediatamente)</option>
                        <option value="inativo">Inativo</option>
                        <option value="rascunho">Rascunho (Salvar sem publicar)</option>
                    </select>
                </div>
                <div class="form-group full-width">
                    <label for="data_publicacao">Agendar Publicação (Opcional)</label>
                    <input type="datetime-local" name="data_publicacao" id="data_publicacao">
                    <small>Deixe em branco para publicar agora.</small>
                </div>
            </div>
            <button type="submit" name="adicionar_aviso" class="btn-submit">Publicar Aviso</button>
        </form>
    </div>

    <!-- Lista de Avisos -->
    <h3>Avisos Recentes</h3>
    <div class="table-container">
        <?php if (isset($erro_listagem)): ?>
            <div class="alert alert-danger"><?php echo $erro_listagem; ?></div>
        <?php elseif (empty($avisos)): ?>
            <p style="text-align: center; padding: 30px; color: var(--cor-texto-claro);">Nenhum aviso cadastrado ainda.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Status</th>
                        <th>Data de Publicação</th>
                        <th>Tempo Restante</th>
                        <th style="text-align: right;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($avisos as $aviso): ?>
                        <tr <?php echo $aviso['expirado'] ? 'class="aviso-expirado"' : ''; ?>>
                            <td><strong><?php echo htmlspecialchars($aviso['titulo']); ?></strong></td>
                            <td><?php echo htmlspecialchars($aviso['autor_nome'] ?: 'N/A'); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo htmlspecialchars($aviso['status']); ?>">
                                    <?php echo htmlspecialchars($aviso['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y \à\s H:i', strtotime($aviso['data_publicacao'])); ?></td>
                            <td>
                                <?php if ($aviso['expirado']): ?>
                                    <span class="tempo-badge tempo-expirado">Expirado</span>
                                <?php else: ?>
                                    <?php
                                    $tempo_num = (int)$aviso['tempo_restante'];
                                    $tempo_classe = 'tempo-normal';
                                    
                                    if (strpos($aviso['tempo_restante'], 'min') !== false && $tempo_num <= 60) {
                                        $tempo_classe = 'tempo-urgente';
                                    } elseif (strpos($aviso['tempo_restante'], 'h') !== false && $tempo_num <= 24) {
                                        $tempo_classe = 'tempo-urgente';
                                    } elseif (strpos($aviso['tempo_restante'], 'dias') !== false && $tempo_num <= 2) {
                                        $tempo_classe = 'tempo-atencao';
                                    }
                                    ?>
                                    <span class="tempo-badge <?php echo $tempo_classe; ?>">
                                        <?php echo $aviso['tempo_restante']; ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="actions" style="text-align: right;">
                                <a href="excluir_aviso.php?id=<?php echo $aviso['id']; ?>" class="btn-delete" onclick="return confirm('Tem certeza que deseja excluir este aviso?');">Excluir</a>
                                
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                                
        <?php endif; ?>
    </div>
</div>

</body>
</html>

<?php require 'templates/footer.php'; ?>